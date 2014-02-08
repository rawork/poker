<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Fuga\GameBundle\Model\Combination;
use Fuga\GameBundle\Model\Deck;
use Fuga\GameBundle\Model\Game;

class GameController extends PublicController {
	
	public function __construct() {
		parent::__construct('game');
	}
	
	public function indexAction() {
		$now  = new \Datetime();
		$date = new \DateTime('2014-02-17 00:00:01');
		if ($date > $now) {
			$this->get('router')->redirect('/victorina');
		} else {
			$this->get('router')->redirect('/game/game');
		}
		
	}
	
	public function gameIndex() {
		$user = $this->get('security')->getCurrentUser();
		$now = new \DateTime();
		$date = new \DateTime($this->getParam('access_date').' 00:00:01');
		
		if ( $date > $now  ) {
			if (!$user || $user['group_id'] != 1) {
				$error = 'Игровой зал открыт<br> только в период проведения игры.<br> Расписание игр 
размещено<br> в рубрике <a href="/rules">"Правила"</a>.';
				return $this->render('quiz/error.tpl', compact('error'));
			}	
		} elseif ( !$user ) {
			$error = $this->call('Fuga:Public:Account:login');
			return $this->render('game/error.tpl', compact('error'));
		}
		
		$gamer0 = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
		if (!$gamer0 || 
			(!$this->get('security')->isGroup('admin') && !$this->get('security')->isGroup('gamer'))) {
			$error = 'Вы не являетесь игроком. Для участия в игре войдите на сайт с логином и паролем игрока<br>'.$this->call('Fuga:Public:Account:login');
			return $this->render('game/error.tpl', compact('error'));
		}
		
		$board = $this->get('container')->getItem('game_board', $gamer0['board_id']);
		if (!$board) {
			$error = 'Вам не назначен зал для игры. Обратитесь к администратору';
			return $this->render('game/error.tpl', compact('error'));
		}
		
		$game = new Game($board, $deck);
		$fromtime = new \DateTime($board['fromtime']);
		if ($fromtime > $now) {
			$error = 'До начала игры осталось <span id="before-timer"></span>';
			return $this->render('game/before.tpl', compact('error'));
		}
		$gamers = $this->get('container')->getItems('account_member', 'id<>'.$gamer0['id'].' AND board_id='.$board['id']);
		//  TODO Для тестов игру запускаем автоматически, потом надо запускать по готовности игроков.
		if ($board['fromtime'] != '0000-00-00 00:00:00') {
			$fromtime = new \DateTime($board['fromtime']);
			$now = new \DateTime();
			$diff = $now->diff($fromtime);
			$board['hour'] = intval($diff->format('%H'));
			$board['minute'] = intval($diff->format('%i'));
			$board['second'] = intval($diff->format('%s'));
		} else {
			$board['fromtime'] = date('Y-m-d H:i:s');
			$this->get('container')->updateItem('game_board', 
				array('fromtime' => $board['fromtime']),
				array('id' => $board['id'])
			);
			$board['hour'] = 0;
			$board['minute'] = 0;
			$board['second'] = 0;
		}
		if ($board['deck']) {
			$deck = unserialize($board['deck']);
		} else {
			$deck = new Deck();
		}
		$gamers = array();
		// TODO Для теста раздаем карты сразу, потом карты надо раздавать по готовности всех игроков
		$gamerQuantity = count($gamers0) + 1;
		foreach ($gamers0 as &$gamer) {
			if ($gamer['cards']) {
				$gamer['cards'] = unserialize($gamer['cards']);
			} else {
				$gamer['cards'] = $deck->take(4);
				$this->get('container')->updateItem('account_member', 
					array('cards' => serialize($gamer['cards'])),
					array('id' => $gamer['id'])
				);
			}
			if ($gamerQuantity == $gamer0['seat'] || 1 == $gamer0['seat']) {
				$key =  - $gamer0['seat'];
			} else {
				$key = $gamer['seat'] - $gamer0['seat'];
				if ($key < 0) {
					$key = $gamerQuantity + $key; 
				}
			}
			
			$gamers[$key] = $gamer;
		}
		unset($gamer);
		unset($gamers0);
		if ($gamer0['cards']) {
			$gamer0['cards'] = unserialize($gamer0['cards']);
		} else {
			$gamer0['cards'] = $deck->take(4);
			$this->get('container')->updateItem('account_member', 
				array('cards' => serialize($gamer0['cards'])),
				array('id' => $gamer0['id'])
			);
		}
		$suits = array(
			1 => 'diams',
			2 => 'hearts',
			4 => 'spades',
			8 => 'clubs'
		);
		if ($board['flop']) {
			$board['flop'] = unserialize($board['flop']);
		} else {
			$board['flop'] = $deck->take(3);
			$this->get('container')->updateItem('game_board', 
				array('flop' => serialize($board['flop'])),
				array('id' => $board['id'])
			);
		}
		if (!$board['deck']) {
			$this->get('container')->updateItem('game_board', 
				array('deck' => serialize($deck)),
				array('id' => $board['id'])
			);
		}
		
		return $this->render('game/index.tpl', compact('gamers', 'gamer0', 'board'));
	}
	
	public function calcAction() {
		$suite = array(
			array('name' => '7_diams', 'suit' => 1, 'weight' => 32),
			array('name' => 'jack_clubs', 'suit' => 8, 'weight' => 512),
			array('name' => 'jack_diams', 'suit' => 1, 'weight' => 512),
			array('name' => '2_clubs', 'suit' => 8, 'weight' => 1),
			array('name' => 'king_hearts', 'suit' => 2, 'weight' => 2048),
			array('name' => 'king_diams', 'suit' => 1, 'weight' => 2048),
			array('name' => 'joker', 'suit' => 16, 'weight' => 8192),
		);
		
		$suite = array(
			array('name' => '6_clubs', 'suit' => 8, 'weight' => 16),
			array('name' => '7_clubs', 'suit' => 8, 'weight' => 32),
			array('name' => '3_hearts', 'suit' => 2, 'weight' => 2),
			array('name' => '3_clubs', 'suit' => 8, 'weight' => 2),
			array('name' => 'king_hearts', 'suit' => 2, 'weight' => 2048),
			array('name' => '5_spades', 'suit' => 4, 'weight' => 8),
			array('name' => '4_clubs', 'suit' => 8, 'weight' => 4),
		);
		
		
		$combination = new Combination();
		$cards = $combination->get($suite);
		if (is_array($cards)) {
			$rank = $combination->rankName($cards['rank']);
		}
		
		return $this->render('game/test.tpl', compact('suite', 'cards', 'rank'));
	}
	
	public function testAction() {
		$deck = new Deck();
		$collection = $this->get('mongo')->decks;
		echo microtime().'<br>';
		for ($i = 0; $i < 5; $i++) {
			$deck->make();
			$item = array(
				'board_id' => 1,
				'cards'    => $deck->take(),
			);
			$collection->insert($item);
		}
		echo microtime().'<br>';
		for ($i = 0; $i < 5; $i++) {
			$deck->make();
			$this->get('container')->addItem('training_training', array(
				'user_id' => 0,
				'state' => serialize($deck),
			));
		}
		echo microtime().'<br>';
		return 'mongo';
	}

}