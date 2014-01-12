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
		if (!$user) 
		{
			return $this->call('Fuga:Public:Account:login');
		}
		
		$gamer = $this->get('container')->getItem('account_gamer', 'user_id='.$user['id']);
		
		if (!$gamer || 
			(!$this->get('security')->isGroup('admin') && !$this->get('security')->isGroup('gamer'))) 
		{
			return 'Вы не являетесь игроком. Войдите на сайт с логином и паролем игрока<br>'.$this->call('Fuga:Public:Account:login');
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);

		if (!$trainingData) 
		{
			$training = new Training();
			$training->createGamer($gamer);
			$training->createBots(3);
			$training->createBoard($gamer['user_id']);
			$this->get('container')->addItem('training_training', array(
				'user_id' => $user['id'],
				'state' => serialize($training)
			));
			$fromtime = $training->board->fromtime;
			$now = new \DateTime();
			$diff = $now->diff($fromtime);
			setcookie('gamehour', intval($diff->format('%H')), time()+86400, '/');
			setcookie('gameminute', intval($diff->format('%i')), time()+86400, '/');
			setcookie('gamesecond', intval($diff->format('%s')), time()+86400, '/');
			setcookie('timerhandler', 'onClickNoChange', time()+86400, '/');
			setcookie('timerminute', 0, time()+86400, '/');
			setcookie('timersecond', 14, time()+86400, '/');
		} else {
			$training = unserialize($trainingData['state']);
		}
		
		$board = $training->board;
		$gamers = $training->bots;
		$gamer0 = $training->gamer;
		$question = $training->gamer->question 
				? $this->render('training/question.tpl', array('question' => $training->gamer->question)) 
				: null;
		
		return $this->render('training/index.tpl', compact('board', 'gamers', 'gamer0', 'question'));
	}
	
	public function nextAction() {
		$user = $this->get('security')->getCurrentUser();
		if (!$user) 
		{
			return json_encode(array('error' => true));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		foreach ($training->bots as &$bot) {
			
		}
		
		$training->board['state'] = 1;
		setcookie('timerhandler', 'onClickNoChange', time()+86400, '/');
		setcookie('timerminute', 0, time()+86400, '/');
		setcookie('timersecond', 14, time()+86400, '/');
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array('ok' => true));
	}
	
	public function newAction() {
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return json_encode(array('error' => true));
		}
		
		$this->get('container')->deleteItem('training_training', 'user_id='.$user['id']);
		$gamer = $this->get('container')->getItem('account_gamer', 'user_id='.$user['id']);
		$training = new Training();
		$training->createGamer($gamer);
		$training->createBots(3);
		$training->createBoard($gamer['user_id']);
		$this->get('container')->addItem('training_training', array(
			'user_id' => $user['id'],
			'state'   => serialize($training),
		));
		$fromtime = $training->board->fromtime;
		$now = new \DateTime();
		$diff = $now->diff($fromtime);
		setcookie('gamehour', intval($diff->format('%H')), time()+86400, '/');
		setcookie('gameminute', intval($diff->format('%i')), time()+86400, '/');
		setcookie('gamesecond', intval($diff->format('%s')), time()+86400, '/');
		setcookie('timerhandler', 'onClickNoChange', time()+86400, '/');
		setcookie('timerminute', 0, time()+86400, '/');
		setcookie('timersecond', 14, time()+86400, '/');
		
		return json_encode(array('ok' => true));
	}
	
	public function questionAction() {
		$user = $this->get('security')->getCurrentUser();
		if (!$user) 
		{
			return json_encode(array('error' => true));
		}
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		
		return json_encode(array(
			'ok' => true,
			'content' => $this->render('training/question.tpl', array('question' => $training->gamer->question)),
			'timerhandler' => 'clickNoAnswer',
			'timerminute' => 0,
			'timersecond' => 14,
		));
	}
	
	public function answerAction() {
		$user = $this->get('security')->getCurrentUser();
		if (!$user) 
		{
			return json_encode(array('error' => true));
		}
		
		$answerNo = $this->get('util')->post('answer', true, 0); 
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		if ($answerNo == $training->gamer->question['answer']) {
			foreach ($training->gamer->change as $cardNo) {
				$newCards = $training->deck->give(1);
				$cards = $training->gamer->cards;
				$cards[$cardNo] = $newCards[0];
				$training->gamer->cards = $cards;
			}
		} else {
			$training->gamer->chips -= $training->board->minbet;
		}
		$training->gamer->change = null;
		$training->gamer->question = null;
		$training->board->state = Training::STATE_PREFLOP;
		setcookie('timerhandler', '', time()-3600, '/');
		setcookie('timerminute', 0, time()-3600, '/');
		setcookie('timersecond', 0, time()-3600, '/');
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array('ok' => true));
	}
	
	public function changeAction() {
		$user = $this->get('security')->getCurrentUser();
		if (!$user) 
		{
			return $this->call('Fuga:Public:Account:login');
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$questions = $this->get('container')->getItems('training_poll', 'id<>0');
		$training->gamer->change = isset($_POST['cards']) ? $_POST['cards'] : array(); 
		$training->gamer->question = $questions[array_rand($questions)];
		$training->board->state = Training::STATE_QUESTION;
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array('ok' => true));
	}
	
	public function nochangeAction() {
		$user = $this->get('security')->getCurrentUser();
		if (!$user) 
		{
			return json_encode(array('error' => true));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->board->state = Training::STATE_PREFLOP;
		setcookie('timerhandler', '', time()-3600, '/');
		setcookie('timerminute', 0, time()-3600, '/');
		setcookie('timersecond', 0, time()-3600, '/');
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array('ok' => true));
	}
	
	public function foldAction() {
		$user = $this->get('security')->getCurrentUser();
		if (!$user) 
		{
			return json_encode(array('error' => true));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$bet = rand(5,10);
		foreach ($training->bots as &$bot) {
			$bot['chips'] -= $bet;
			$training->board['bank'] += $bet; 
		}
		$training->gamer->cards = null;
		$training->board->state = Training::STATE_WIN;
		setcookie('timerhandler', 'showBuy', time()+86400, '/');
		setcookie('timerminute', 0, time()+86400, '/');
		setcookie('timersecond', 15, time()+86400, '/');
		// TODO Добавить подсчет победителя среди ботов
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array('ok' => true));
	}
	
	public function betAction() {
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('error' => true));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$bet = $this->get('util')->post('bet', true, 0);
		$allin = ($bet == $training->gamer->chips);
		$training->gamer->chips -= $bet;
		$training->board->bank += $bet;
		
		foreach ($training->bots as $bot) {
			if ($allin) {
				$bet = $bot->chips;
			}
			$bot->chips -= $bet;
			$training->board->bank += $bet; 
		}
		
		$training->board->state += 1;
		
		if (4 == $training->board->state) {
			$combination = new Combination();
			$suites = array();
			foreach ($training->bots as $bot) {
				$cards = $combination->get(array_merge($bot->cards, $training->board->flop));
				$cards['position'] = $bot->position;
				$cards['name'] = $combination->rankName($cards['rank']);
				$suites[] = $cards;
			}
			$cards = $combination->get(array_merge($training->gamer->cards, $training->board->flop));
			$cards['position'] = $training->gamer->position;
			$cards['name'] = $combination->rankName($cards['rank']);
			$suites[] = $cards;
			$winners = $combination->compare($suites);
			$training->board->winner = $winners;
			setcookie('timerhandler', 'showBuy', time()+86400, '/');
			setcookie('timerminute', 0, time()+86400, '/');
			setcookie('timersecond', 15, time()+86400, '/');
		}
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array('ok' => true));
	}
	
	public function buyAction () {
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('error' => true));
		}
		
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			return json_encode(array('ok' => true));
		}
		
		
	}
	
}



