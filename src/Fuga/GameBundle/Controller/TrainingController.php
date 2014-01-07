<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Fuga\GameBundle\Model\Training;
use Fuga\GameBundle\Model\Calculator;

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
		
		return $this->render('training/index.tpl', compact('board', 'gamers', 'gamer0'));
	}
	
	public function updateAction() {
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
		
		return json_encode(array('ok' => true));
	}
	
	public function changeAction() {
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
		$training = unserialize($trainingData['state']);
		$cards = isset($_POST['cards']) ? $_POST['cards'] : array();
		$this->get('log')->write(serialize($cards));
		foreach ($cards as $cardNo) {
			$newCards = $training->deck->give(1);
			$training->gamer['cards'][$cardNo] = $newCards[0];
		}
		$training->board['state'] = 2;
		$training->board['timerfunc'] = '';
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
		$training->board['state'] = 2;
		$training->board['timerfunc'] = '';
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
		$training->gamer['cards'] = null;
		$training->board['state'] = 4;
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
		
		$this->get('container')->updateItem('training_training', 
			array('state'   => serialize($training)),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array('ok' => true));
	}
	
}



