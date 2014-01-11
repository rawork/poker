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
			$training->createBoard();
			$this->get('container')->addItem('training_training', array(
				'user_id' => $user['id'],
				'state' => serialize($training)
			));
		} else {
			$training = unserialize($trainingData['state']);
		}
		
		$fromtime = new \DateTime($training->board['fromtime']);
		$now = new \DateTime();
		$diff = $now->diff($fromtime);
		$training->board['hour'] = intval($diff->format('%H'));
		$training->board['minute'] = intval($diff->format('%i'));
		$training->board['second'] = intval($diff->format('%s'));
		$board = $training->board;
		$gamers = $training->bots;
		$gamer0 = $training->gamer;
		$question = $training->gamer['question'] 
				? $this->render('training/question.tpl', array('question' => $training->gamer['question'])) 
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
		$training->board['timerevent'] = '';
		$training->board['timerminute'] = 0;
		$training->board['timersecond'] = 13;
		
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
		$training->createBoard();
		$this->get('container')->addItem('training_training', array(
			'user_id' => $user['id'],
			'state'   => serialize($training),
		));
		setcookie('timerevent', '', time()-3600, '/training');
		setcookie('timerminute', '', time()-3600, '/training');
		setcookie('timersecond', '', time()-3600, '/training');
		
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
			'content' => $this->render('training/question.tpl', array('question' => $training->gamer['question'])),
			'timerevent' => $training->board['timerevent'],
			'timerminute' => $training->board['timerminute'],
			'timersecond' => $training->board['timersecond'],
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
		if ($answerNo == $training->gamer['question']['answer']) {
			foreach ($training->gamer['change'] as $cardNo) {
				$newCards = $training->deck->give(1);
				$training->gamer['cards'][$cardNo] = $newCards[0];
			}
		} else {
			$training->gamer['chips'] -= $training->board['minbet'];
		}
		$training->gamer['change'] = null;
		$training->gamer['question'] = null;
		$training->board['state'] = Training::STATE_PREFLOP;
		$training->board['timerevent'] = '';
		$training->board['timerminute'] = 0;
		$training->board['timersecond'] = 0;
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
		$training->gamer['change'] = isset($_POST['cards']) ? $_POST['cards'] : array(); 
		$training->gamer['question'] = $questions[array_rand($questions)];
		$training->board['state'] = Training::STATE_QUESTION;
		$training->board['timerevent'] = 'clickNoAnswer';
		$training->board['timerminute'] = 0;
		$training->board['timersecond'] = 13;
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
		$training->board['state'] = Training::STATE_PREFLOP;
		$training->board['timerevent'] = '';
		$training->board['timerminute'] = 0;
		$training->board['timersecond'] = 0;
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		setcookie('timerevent', '', time()-3600, '/training');
		setcookie('timerminute', '', time()-3600, '/training');
		setcookie('timersecond', '', time()-3600, '/training');
		
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
		$training->gamer['cards'] = null;
		$training->board['state'] = Training::STATE_WIN;
		$training->board['timerevent'] = 'showQuestion(true)';
		$training->board['timerminute'] = 0;
		$training->board['timersecond'] = 15;
		
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
		$allin = $bet == $training->gamer['chips'];
		$training->gamer['chips'] -= $bet;
		$training->board['bank'] += $bet;
		
		foreach ($training->bots as &$bot) {
			if ($allin) {
				$bet = $bot['chips'];
			}
			$bot['chips'] -= $bet;
			$training->board['bank'] += $bet; 
		}
		
		$training->board['state'] += 1;
		
		if (4 == $training->board['state']) {
			$training->board['timerevent'] = 'showBuy';
			$training->board['timerminute'] = 2;
			$training->board['timersecond'] = 0;
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



