<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Fuga\GameBundle\Model\Training;
use Fuga\GameBundle\Model\Deck;

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
		
		$training->minbet();
		$minbet = $this->render('training/minbet.tpl', compact('training'));
		$bank   = $this->render('training/bank.tpl',   compact('training'));
		$bots   = $this->render('training/bots.tpl',   compact('training'));
		$gamer  = $this->render('training/gamer.tpl',  compact('training'));
		$winner = $this->render('training/winner.tpl', compact('training'));
		$hint = $this->render('training/hint.tpl',   compact('training'));
		$board  = $this->render('training/board.tpl',  compact('training'));
		$this->get('container')->setVar('javascript', 'training');
		$deck = $training->deck->names(true);
		$training->syncTime();
		$training->timer->start();
		
		
		return $this->render('training/index.tpl', compact('training', 'minbet', 'bank', 'bots', 'gamer', 'winner', 'board','deck', 'hint'));
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
		
		$gamer = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->start();
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array(
			'ok'     => true,
			'bots'   => $this->render('training/bots.tpl', compact('training')),
			'gamer'  => $this->render('training/gamer.tpl', compact('training')),
			'minbet' => $this->render('training/minbet.tpl', compact('training')),
			'bank'	 => $this->render('training/bank.tpl', compact('training')),
			'board'  => $this->render('training/board.tpl', compact('training')),
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
		
		$bots = array();
		foreach ($training->bots as $gamer) {
			$bots[$gamer->id] = array(
				'chips' => $gamer->chips,
				'cards' => $this->render('training/botcards.tpl', compact('gamer', 'training')),
				'bet'   => $gamer->bet,
				'active' => $gamer->isActive(),
			);
		}
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/board.tpl', compact('training')),
			'cards' => $this->render('training/cards.tpl', compact($training)),
			'bet'   => $training->gamer->bet,
			'bots'  => $bots,
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
		$questions = $this->get('container')->getItems('training_poll');
		$training->answer($this->get('util')->post('answer', true, 0), $questions[array_rand($questions)]);
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/board.tpl', compact('training')),
			'chips' => $training->gamer->chips,
			'cards' => $this->render('training/cards.tpl', compact('training')),
			'hint'  => $this->render('training/hint.tpl', compact('training')),
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
		$training->change($this->get('util')->post('card_no', true, 999), $questions[array_rand($questions)]); 
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/board.tpl', compact('training')),
			'timer' => $training->timer->start(),
		));
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
			'board' => $this->render('training/board.tpl', compact('training')),
			'cards' => $this->render('training/cards.tpl', compact('training')),
			'hint'  => $this->render('training/hint.tpl', compact('training')),
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
		
		$bots = array();
		foreach ($training->bots as $gamer) {
			$bots[$gamer->id] = array(
				'chips' => $gamer->chips,
				'cards' => $this->render('training/botcards.tpl', compact('gamer', 'training')),
				'bet'   => $gamer->bet,
				'active' => $gamer->isActive(),
			);
		}
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/board.tpl', compact('training')),
			'cards' => $this->render('training/cards.tpl', compact($training)),
			'winner' => $this->render('training/winner.tpl', compact('training')),
			'bots' => $bots,
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
		
		$bots = array();
		foreach ($training->bots as $gamer) {
			$bots[$gamer->id] = array(
				'chips' => $gamer->chips,
				'cards' => $this->render('training/botcards.tpl', compact('gamer', 'training')),
				'bet'   => $gamer->bet,
				'active' => $gamer->isActive(),
			);
		}
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/board.tpl', compact('training')),
			'bank'  => $this->render('training/bank.tpl', compact('training')),
			'chips' => $training->gamer->chips,
			'cards' => $this->render('training/cards.tpl', compact($training)),
			'bet'   => $training->gamer->bet,
			'bots'  => $bots,
			'winner'=> $this->render('training/winner.tpl', compact('training')),
			'hint'  => $this->render('training/hint.tpl', compact('training')),
			'timer' => $training->timer->start(),
		));
	}
	
	public function checkAction() {
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
		$training->check();
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		$bots = array();
		foreach ($training->bots as $gamer) {
			$bots[$gamer->id] = array(
				'chips' => $gamer->chips,
				'cards' => $this->render('training/botcards.tpl', compact('gamer', 'training')),
				'bet'   => $gamer->bet,
				'active' => $gamer->isActive(),
			);
		}
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/board.tpl', compact('training')),
			'bank'  => $this->render('training/bank.tpl', compact('training')),
			'chips' => $training->gamer->chips,
			'cards' => $this->render('training/cards.tpl', compact($training)),
			'bet'   => $training->gamer->bet,
			'bots'  => $bots,
			'winner'=> $this->render('training/winner.tpl', compact('training')),
			'hint'  => $this->render('training/hint.tpl', compact('training')),
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
	
	public function distributeAction() {
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
		$training->distribute($this->get('container')->getItems('training_poll', '1=1', 'RAND()', 3));
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		$bots = array();
		foreach ($training->bots as $gamer) {
			$bots[$gamer->id] = array(
				'chips' => $gamer->chips,
				'cards' => $this->render('training/botcards.tpl', compact('gamer', 'training')),
				'bet'   => $gamer->bet,
				'active' => $gamer->isActive(),
			);
		}
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/board.tpl', compact('training')),
			'chips' => $training->gamer->chips,
			'cards' => $this->render('training/cards.tpl', compact($training)),
			'bet'   => $training->gamer->bet,
			'bots'  => $bots,
			'bank'	=> $this->render('training/bank.tpl', compact('training')),
			'state' => $training->getStateNo(),
			'timer' => $training->timer->start(),
		));
	}
	
	public function prebuyAction() {
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
		$training->prebuy();
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		$bots = array();
		foreach ($training->bots as $gamer) {
			$bots[$gamer->id] = array(
				'chips' => $gamer->chips,
				'cards' => $this->render('training/botcards.tpl', compact('gamer', 'training')),
				'bet'   => $gamer->bet,
				'active' => $gamer->isActive(),
			);
		}
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/board.tpl', compact('training')),
			'chips' => $training->gamer->chips,
			'cards' => $this->render('training/cards.tpl', compact($training)),
			'bet'   => $training->gamer->bet,
			'bots'  => $bots,
			'bank'	=> $this->render('training/bank.tpl', compact('training')),
			'state' => $training->getState(),
			'timer' => $training->timer->start(),
		));
	}
	
	public function endroundAction() {
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
		$training->endround();
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/board.tpl', compact('training')),
			'timer' => $training->timer->start(),
		));
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
			'board' => $this->render('training/board.tpl', compact('training')),
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
		$training->buy();
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		$bots = array();
		foreach ($training->bots as $gamer) {
			$bots[$gamer->id] = array(
				'chips' => $gamer->chips,
				'cards' => $this->render('training/botcards.tpl', compact('gamer', 'training')),
				'bet'   => $gamer->bet,
				'active' => $gamer->isActive(),
			);
		}
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/board.tpl', compact('training')),
			'chips' => $training->gamer->chips,
			'cards' => $this->render('training/cards.tpl', compact($training)),
			'bet'   => $training->gamer->bet,
			'bots'  => $bots,
			'bank'	=> $this->render('training/bank.tpl', compact('training')),
			'state' => $training->getState(),
			'timer' => $training->timer->start(),
		));
	}
	
	public function buyanswerAction () {
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
		$training->buyanswer($this->get('util')->post('answer', true, 0));
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		if ($training->isState(Training::STATE_CHANGE)) {
			$bots = array();
			foreach ($training->bots as $gamer) {
				$bots[$gamer->id] = array(
					'chips' => $gamer->chips,
					'cards' => $this->render('training/botcards.tpl', compact('gamer', 'training')),
					'bet'   => $gamer->bet,
					'active' => $gamer->isActive(),
				);
			}
			
			return json_encode(array(
				'ok' => true,
				'last' => true,
				'board' => $this->render('training/board.tpl', compact('training')),
				'chips' => $training->gamer->chips,
				'cards' => $this->render('training/cards.tpl', compact('training')),
				'bet'   => $training->gamer->bet,
				'bots' => $bots,
				'timer' => $training->timer->start(),
			));
		}
		
		return json_encode(array(
			'ok' => true,
			'board' => $this->render('training/board.tpl', compact('training')),
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
		if (!$trainingData) {
			return json_encode(array('ok' => false));
		}
		
		$training = unserialize($trainingData['state']);
		$training->minbet();
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array(
			'ok' => true,
			'minbet' => $training->minbet,
		));
	}
	
}



