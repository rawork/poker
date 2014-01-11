<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Fuga\GameBundle\Model\Combination;
use Fuga\GameBundle\Model\Deck;

class DefaultController extends PublicController {
	
	public function __construct() {
		parent::__construct('game');
	}
	
	public function indexAction() {
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return $this->call('Fuga:Public:Account:login');
		}
		$gamer0 = $this->get('container')->getItem('account_gamer', 'user_id='.$user['id']);
		if (!$gamer0) {
			return 'Вы не являетесь DEMO игроком. Войдите на сайт с логином demo и паролем demo<br>'.$this->call('Fuga:Public:Account:login');
		}
		$board = $this->get('container')->getItem('game_board', $gamer0['board_id']);
		$gamers0 = $this->get('container')->getItems('account_gamer', 'id<>'.$gamer0['id'].' AND board_id='.$board['id']);
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
				$gamer['cards'] = $deck->give(4);
				$this->get('container')->updateItem('account_gamer', 
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
			$gamer0['cards'] = $deck->give(4);
			$this->get('container')->updateItem('account_gamer', 
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
			$board['flop'] = $deck->give(3);
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
		
		return $this->render('game/index.tpl', compact('gamers', 'gamer0', 'board', 'suits')) ;
	}
	
	public function calcAction() {
//		$suite = array(
//			array('name' => 'ace hearts', 'suit' => 2, 'weight' => 4096),
//			array('name' => '4 diamonds', 'suit' => 1, 'weight' => 4),
//			array('name' => 'queen spade', 'suit' => 4, 'weight' => 1024),
//			array('name' => '3 diamonds', 'suit' => 1, 'weight' => 2),
//			array('name' => '8 hearts', 'suit' => 2, 'weight' => 64),
//			array('name' => 'king clubs', 'suit' => 8, 'weight' => 2048),
//			array('name' => '2 diamonds', 'suit' => 1, 'weight' => 1),
//		);
//		$suite = array(
//			array('name' => 'ace hearts', 'suit' => 2, 'weight' => 4096),
//			array('name' => 'king spade', 'suit' => 4, 'weight' => 2048),
//			array('name' => '10 diamonds', 'suit' => 1, 'weight' => 256),
//			array('name' => '9 diamonds', 'suit' => 1, 'weight' => 128),
//			array('name' => 'joker', 'suit' => 0, 'weight' => 8192),
//			array('name' => '5 clubs', 'suit' => 8, 'weight' => 8),
//			array('name' => '2 diamonds', 'suit' => 1, 'weight' => 1),
//		);
//		$suite = array(
//			array('name' => 'ace hearts', 'suit' => 2, 'weight' => 4096),
//			array('name' => '4 diamonds', 'suit' => 1, 'weight' => 4),
//			array('name' => '2 clubs', 'suit' => 8, 'weight' => 1),
//			array('name' => '9 diamonds', 'suit' => 1, 'weight' => 128),
//			array('name' => '10 hearts', 'suit' => 2, 'weight' => 256),
//			array('name' => 'ace clubs', 'suit' => 8, 'weight' => 4096),
//			array('name' => '2 diamonds', 'suit' => 1, 'weight' => 1),
//		);
//		$suite = array(
//			array('name' => 'ace hearts', 'suit' => 2, 'weight' => 4096),
//			array('name' => '4 diamonds', 'suit' => 1, 'weight' => 4),
//			array('name' => 'joker', 'suit' => 0, 'weight' => 8192),
//			array('name' => '3 diamonds', 'suit' => 1, 'weight' => 2),
//			array('name' => '4 hearts', 'suit' => 2, 'weight' => 4),
//			array('name' => '2 clubs', 'suit' => 8, 'weight' => 1),
//			array('name' => '2 diamonds', 'suit' => 1, 'weight' => 1),
//		);
//		$suite = array(
//			array('name' => 'ace hearts', 'suit' => 2, 'weight' => 4096),
//			array('name' => '4 diamonds', 'suit' => 1, 'weight' => 4),
//			array('name' => 'joker', 'suit' => 0, 'weight' => 8192),
//			array('name' => '2 spade', 'suit' => 4, 'weight' => 1),
//			array('name' => '2 hearts', 'suit' => 2, 'weight' => 1),
//			array('name' => '2 clubs', 'suit' => 8, 'weight' => 1),
//			array('name' => '2 diamonds', 'suit' => 1, 'weight' => 1),
//		);
		$suite = array(
			array('name' => 'jack_diams', 'suit' => 1, 'weight' => 512),
			array('name' => 'ace_hearts', 'suit' => 2, 'weight' => 4096),
			array('name' => '2_hearts', 'suit' => 2, 'weight' => 1),
			array('name' => 'queen_diams', 'suit' => 1, 'weight' => 1024),
			array('name' => '9_diams', 'suit' => 1, 'weight' => 128),
			array('name' => 'joker', 'suit' => 8, 'weight' => 8192),
			array('name' => '10_diams', 'suit' => 1, 'weight' => 256),
		);
//		$suite = array(
//			array('name' => '2 diamonds', 'suit' => 1, 'weight' => 1),
//			array('name' => 'king diamonds', 'suit' => 1, 'weight' => 2048),
//			array('name' => 'joker', 'suit' => 0, 'weight' => 8192),
//			array('name' => 'jack diamonds', 'suit' => 1, 'weight' => 512),
//			array('name' => '2 hearts', 'suit' => 2, 'weight' => 1),
//			array('name' => '9 diamonds', 'suit' => 1, 'weight' => 128),
//			array('name' => '10 diamonds', 'suit' => 1, 'weight' => 256),
//		);
//		$suite = array(
//			array('name' => 'queen diamonds', 'suit' => 1, 'weight' => 1024),
//			array('name' => 'king diamonds', 'suit' => 1, 'weight' => 2048),
//			array('name' => 'joker', 'suit' => 0, 'weight' => 8192),
//			array('name' => 'jack diamonds', 'suit' => 1, 'weight' => 512),
//			array('name' => '2 hearts', 'suit' => 2, 'weight' => 1),
//			array('name' => '9 diamonds', 'suit' => 1, 'weight' => 128),
//			array('name' => '10 diamonds', 'suit' => 1, 'weight' => 256),
//		);		
		$combination = new Combination();
		$cards = $combination->get($suite);
		var_dump($suite);
		var_dump($cards);
		if (is_array($cards)) {
			var_dump($combination->rankName($cards['rank']));
		}
	}
	
	public function startgameAction() {
		
	}
	
	public function activateAction() {
		
	}
	
	public function deactivateAction() {
		
	}
	
	public function moveAction() {
		
	}
	
	public function renewAction() {
		
	}
	
	public function buyAction() {
		
	}
	
	public function changeAction() {
		
	}
	
	public function winnerAction() {
		
	}
	
	public function givecardsAction() {
		
	}

}