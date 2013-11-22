<?php

namespace Fuga\PokerBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Fuga\PokerBundle\Model\Calculator;
use Fuga\PokerBundle\Model\Deck;

class DefaultController extends PublicController {
	
	public function __construct() {
		parent::__construct('poker');
	}
	
	public function indexAction($params) {
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
//		$suite = array(
//			array('name' => 'ace hearts', 'suit' => 2, 'weight' => 4096),
//			array('name' => '8 spade', 'suit' => 4, 'weight' => 64),
//			array('name' => 'queen diamonds', 'suit' => 1, 'weight' => 1024),
//			array('name' => 'jack diamonds', 'suit' => 1, 'weight' => 512),
//			array('name' => '2 hearts', 'suit' => 2, 'weight' => 1),
//			array('name' => '9 diamonds', 'suit' => 1, 'weight' => 128),
//			array('name' => '10 diamonds', 'suit' => 1, 'weight' => 256),
//		);
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
		$deck = new Deck();
		$calculator = new Calculator();
		$gamers = array(); 
		for ($i = 0; $i < 4; $i++) {
			$gamers[] = array('suite' => $deck->give(4));
		}
		$suits = array(
			1 => 'diams',
			2 => 'hearts',
			4 => 'spades',
			8 => 'clubs'
		);
		$flop = $deck->give(3);
		foreach ($gamers as &$gamer) {
			$suite = array_merge($gamer['suite'], $flop);
			$gamer['rank'] = $calculator->checkRank($suite); 
		}
		unset($gamer);
		
		return $this->render('poker/index.tpl', compact('gamers', 'flop', 'suits')) ;
	}

}