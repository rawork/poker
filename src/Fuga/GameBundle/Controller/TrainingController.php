<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Fuga\GameBundle\Model\Training;
use Fuga\GameBundle\Model\Combination;

class TrainingController extends PublicController {
	
	public function __construct() {
		parent::__construct('training');
	}
	
	public function indexAction() {
		$user = $this->get('security')->getCurrentUser();
		
		$date = new \DateTime($this->getParam('access_date').' 00:00:01');
		$now  = new \Datetime();
		if ( $date > $now  ) {
			if (!$user || $user['group_id'] != 1) {
				$error = 'Тренировочный зал открыт<br> с 17 февраля по 20 марта';
				return $this->render('quiz/error.tpl', compact('error'));
			}	
		} elseif ( !$user ) {
			$error = $this->call('Fuga:Public:Account:login');
			return $this->render('quiz/error.tpl', compact('error'));
		}
		
		$gamer = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
		
		if (!$gamer || 
			(!$this->get('security')->isGroup('admin') && !$this->get('security')->isGroup('gamer'))) {
			$error = 'Вы не являетесь игроком. Войдите на сайт с логином и паролем игрока<br>'.$this->call('Fuga:Public:Account:login');
			return $this->render('quiz/error.tpl', compact('error'));
		}
		
		$data = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);

		if (!$data) {
			$training = new Training($gamer, $this->get('log'));
			$this->get('container')->addItem('training_training', array(
				'user_id' => $user['id'],
				'state' => serialize($training),
			));
		} else {
			$training = unserialize($data['state']);
		}
		
		$minbet = $this->render('training/minbet.tpl', compact('training'));
		$bank = $this->render('training/bank.tpl', compact('training'));
		$bots = $this->render('training/bots.tpl', compact('training'));
		$gamer = $this->render('training/gamer.tpl', compact('training'));
		
		switch ($training->getState()) {
			case 0:
				$board = $this->render('training/start.tpl');
				break;
			case 1:
				$board = $this->render('training/change.tpl');
				break;
			case 11:
				$board = $this->render('training/question.tpl', array('question' => $training->gamer->question)); 
				break;
			case 2:
			case 3:
			case 4;
				$board = $this->render('training/flop.tpl', compact('training'));
				$winner = $this->render('training/winner.tpl', compact('training'));
				break;
			case 5:
				$board = $this->render('training/buying.tpl', array('question' => $training->gamer->question));
				break;
			case 6:
				$board = $this->render('training/end.tpl', array('isYou' => $training->gamer->chips <= 0));
				break;
		}
		
		$this->get('container')->setVar('javascript', 'training');
		$training->minbet();
		$training->setTime();
		$training->timer->start();
		
		return $this->render('training/index.tpl', compact('training', 'minbet', 'bank', 'bots', 'gamer', 'winner', 'board'));
	}
	
	public function startAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/training');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return json_encode(array(
				'ok' => false,
			));
		}
		
		$this->get('container')->deleteItem('training_training', 'user_id='.$user['id']);
		$gamer = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
		$training = new Training($gamer, $this->get('log'));
		$training->start();
		$this->get('container')->addItem('training_training', array(
			'user_id' => $user['id'],
			'state'   => serialize($training),
		));
		
		return json_encode(array(
			'ok'     => true,
			'bots'   => $this->render('training/bots.tpl', compact('training')),
			'gamer'  => $this->render('training/gamer.tpl', compact('training')),
			'minbet' => $this->render('training/minbet.tpl', compact('training')),
			'bank'	 => $this->render('training/bank.tpl', compact('training')),
			'board'  => $this->render('training/change.tpl', compact('training')),
			'timer'  => $training->timer->start(),
		));
	}
	
	public function nextAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/training');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return json_encode(array('ok' => false));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->next();
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		$botCards = array();
		foreach ($training->bots as $gamer) {
			$botCards[$gamer->id] = $this->render('training/botcards.tpl', compact('gamer', 'training'));
		}
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/change.tpl'),
			'cards' => $this->render('training/cards.tpl', compact($training)),
			'cards0' => $botCards,
			'timer' => $training->timer->start(),
		));
	}
	
	public function questionAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/training');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->timer->set('onClickNoAnswer', 'question-timer', 14);
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/question.tpl', array('question' => $training->gamer->question)),
			'timer' => $training->timer->start(),
		));
	}
	
	public function answerAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/training');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->makeChange($this->get('util')->post('answer', true, 0));
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/flop.tpl', compact('training')),
			'chips' => $training->gamer->chips,
			'cards' => $this->render('training/cards.tpl', compact('training')),
			'timer' => $training->timer->start(),
		));
	}
	
	public function changeAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/training');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) 
		{
			return $this->call('Fuga:Public:Account:login');
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$questions = $this->get('container')->getItems('training_poll');
		$training->setChange(isset($_POST['cards']) ? $_POST['cards'] : array(), $questions[array_rand($questions)]); 
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array('ok' => true));
	}
	
	public function nochangeAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/training');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->nochange();
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/flop.tpl', compact('training')),
			'timer' => $training->timer->start(),
		));
	}
	
	public function foldAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/training');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->fold();
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		$botCards = array();
		foreach ($training->bots as $gamer) {
			$botCards[$gamer->id] = $this->render('training/botcards.tpl', compact('gamer', 'training'));
		}
		
		$botChips = array();
		foreach ($training->bots as $bot) {
			$botChips[$bot->id] = $bot->chips;
		}
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/flop.tpl', compact('training')),
			'winner' => $this->render('training/winner.tpl', compact('training')),
			'chips0' => $botChips,
			'cards0' => $botCards,
			'bank' => $this->render('training/bank.tpl', compact('training')),
			'timer' => $training->timer->start(),
		));
	}
	
	public function betAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/training');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->bet($this->get('util')->post('chips', true, 0));
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		$botChips = array();
		foreach ($training->bots as $bot) {
			$botChips[$bot->id] = $bot->chips;
		}
		
		$botCards = array();
		foreach ($training->bots as $gamer) {
			$botCards[$gamer->id] = $this->render('training/botcards.tpl', compact('gamer', 'training'));
		}
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/flop.tpl', compact('training')),
			'bank'  => $this->render('training/bank.tpl', compact('training')),
			'chips' => $training->gamer->chips,
			'cards' => $this->render('training/cards.tpl', compact($training)),
			'chips0'=> $botChips,
			'cards0'=> $botCards,
			'winner'=> $this->render('training/winner.tpl', compact('training')),
			'timer' => $training->timer->start(),
		));
	}
	
	public function showdownAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/training');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->showdown();
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array('ok' => true));
	}
	
	public function winAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/training');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->win($this->get('container')->getItems('training_poll', '1=1', 'RAND()', 3));
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		$botChips = array();
		foreach ($training->bots as $bot) {
			$botChips[$bot->id] = $bot->chips;
		}
		
		$botCards = array();
		foreach ($training->bots as $gamer) {
			$botCards[$gamer->id] = $this->render('training/botcards.tpl', compact('gamer', 'training'));
		}
		
		if ($training->isState($training::STATE_BUY)) {
			$board = $this->render('training/buying.tpl', array('question' => $training->gamer->question));
		} elseif ($training->isState($training::STATE_CHANGE)) {
			$board = $this->render('training/change.tpl', compact('training'));
		} else {
			$board = $this->render('training/end.tpl');
		}
		
		$this->get('log')->write('gamer.chip:'.$training->gamer->chips);
		
		return json_encode(array(
			'ok' => true,
			'board' => $board,
			'chips' => $training->gamer->chips,
			'chips0' => $botChips,
			'cards'  => $this->render('training/cards.tpl', compact($training)),
			'cards0' => $botCards,
			'bank'	 => $this->render('training/bank.tpl', compact('training')),
			'state'  => $training->getState(),
			'timer'  => $training->timer->start(),
		));
	}
	
		
	public function endAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/training');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->end();
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array('ok' => true));
	}
	
	public function stopAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/training');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->stop();
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/start.tpl'),
		));
	}
	
	public function buyAction () {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/training');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->buying($this->get('util')->post('answer', true, 0));
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		if ($training->isState($training::STATE_CHANGE)) {
			$botCards = array();
			foreach ($training->bots as $gamer) {
				$botCards[$gamer->id] = $this->render('training/botcards.tpl', compact('gamer', 'training'));
			}
			
			return json_encode(array(
				'ok' => true,
				'last' => true,
				'board' => $this->render('training/change.tpl'),
				'chips' => $training->gamer->chips,
				'cards' => $this->render('training/cards.tpl', compact($training)),
				'cards0' => $botCards,
				'timer' => $training->timer->start(),
			));
		}
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/buying.tpl', array('question' => $training->gamer->question)),
			'chips' => $training->gamer->chips,
		));
	}
	
	public function minbetAction(){
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/training');
		}
		
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('ok' => false));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->minbet();
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array(
			'ok' => true,
			'minbet' => $training->board->minbet,
		));
	}
	
}



