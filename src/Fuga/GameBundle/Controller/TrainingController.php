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
			(!$this->get('security')->isGroup('admin') && !$this->get('security')->isGroup('gamer'))) {
			return 'Вы не являетесь игроком. Войдите на сайт с логином и паролем игрока<br>'.$this->call('Fuga:Public:Account:login');
		}
		
		$data = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);

		if (!$data) {
			$training = new Training($gamer);
			$this->get('container')->addItem('training_training', array(
				'user_id' => $user['id'],
				'state' => serialize($training)
			));
		} else {
			$training = unserialize($data['state']);
		}
		
		$question = $training->gamer->question 
				? $this->render('training/question.tpl', array('question' => $training->gamer->question)) 
				: null;
		
		$start = $training->board->state == 0 
				? $this->render('training/start.tpl') 
				: null;
		
		return $this->render('training/index.tpl', compact('training', 'question', 'start'));
	}
	
	public function nextAction() {
		$user = $this->get('security')->getCurrentUser();
		if (!$user) 
		{
			return json_encode(array('error' => true));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->next();
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
		$training = new Training($gamer);
		$training->start();
		$this->get('container')->addItem('training_training', array(
			'user_id' => $user['id'],
			'state'   => serialize($training),
		));
		
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
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->makeChange($this->get('util')->post('answer', true, 0));
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
		$training->setChange(isset($_POST['cards']) ? $_POST['cards'] : array(), $questions[array_rand($questions)]); 
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
		$training->nochange();
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



