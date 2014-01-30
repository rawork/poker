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
			return 'Вы не являетесь игроком. Войдите на сайт с логином и паролем игрока<br>'.$this->call('Fuga:Public:Account:login');
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
		
		$question = $training->gamer->question 
				? $this->render('training/question.tpl', array('question' => $training->gamer->question)) 
				: null;
		
		$start = $training->board->state == 0 
				? $this->render('training/start.tpl') 
				: null;
		
		$end = $training->board->state == 6 
				? $this->render('training/end.tpl', array('isYou' => $training->gamer->chips <= 0)) 
				: null;
		
		$this->get('container')->setVar('javascript', 'training');
		
		return $this->render('training/index.tpl', compact('training', 'question', 'start', 'end'));
	}
	
	public function startAction() {
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return json_encode(array('error' => true));
		}
		
		$this->get('container')->deleteItem('training_training', 'user_id='.$user['id']);
		$gamer = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
		$training = new Training($gamer, $this->get('log'));
		$training->start();
		$this->get('container')->addItem('training_training', array(
			'user_id' => $user['id'],
			'state'   => serialize($training),
		));
		
		return json_encode(array('ok' => true));
	}
	
	public function nextAction() {
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
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
		$training->fold();
		
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
		$training->bet($this->get('util')->post('chips', true, 0));
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array('ok' => true));
	}
	
	public function showdownAction() {
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('error' => true));
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
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('error' => true));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->win();
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array('ok' => true));
	}
	
		
	public function endAction() {
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('error' => true));
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
		$user = $this->get('security')->getCurrentUser();
		
		if (!$user) 
		{
			return json_encode(array('error' => true));
		}
		
		$trainingData = $this->get('container')->getItem('training_training', 'user_id='.$user['id']);
		$training = unserialize($trainingData['state']);
		$training->stop();
		
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



