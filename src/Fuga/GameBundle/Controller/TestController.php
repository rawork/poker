<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Fuga\GameBundle\Model\Combination;
use Fuga\GameBundle\Model\Deck;

class TestController extends PublicController {
	
	public function __construct() {
		parent::__construct('test');
	}
	
	public function indexAction() {
		
		$deck = new Deck();
		
		$hands = array();
		
		$flop = array(
			$deck->card('4_diams'),
			$deck->card('9_clubs'),
			$deck->card('queen_diams'),
		);
		$hands[] = array(
			$deck->card('10_spades'),
			$deck->card('10_hearts'),
			$deck->card('king_diams'),
			$deck->card('jack_diams'),
		);
		
		
		$hands[] = array(
			$deck->card('ace_clubs'),
			$deck->card('5_clubs'),
			$deck->card('7_hearts'),
			$deck->card('10_diams'),
		);
		
		
		$combination = new Combination();
		$combinations = array();
		foreach ($hands as $hand) {
			$cards = $combination->get($hand, $flop);
			if (is_array($cards)) {
				$cards['name'] = $combination->rankName($cards['rank']);
			}
			$combinations[] = $cards;
		}
		
		$combinations[] = $combination->compare($combinations);
		
		echo json_encode($combinations);
		exit;
	}
}
