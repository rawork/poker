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
			$deck->card('7_hearts'),
			$deck->card('9_diams'),
			$deck->card('king_spades'),
		);
		
		$hands[] = array(
			$deck->card('10_clubs'),
			$deck->card('9_clubs'),
			$deck->card('6_spades'),
			$deck->card('2_spades'),
		);
		
		$hands[] = array(
			$deck->card('9_hearts'),
			$deck->card('jack_spades'),
			$deck->card('4_clubs'),
			$deck->card('queen_spades'),
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
