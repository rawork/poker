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
			$deck->card('2_diams'),
			$deck->card('king_clubs'),
			$deck->card('queen_diams'),
		);
		$hands[] = array(
			$deck->card('10_spades'),
			$deck->card('10_hearts'),
			$deck->card('6_diams'),
			$deck->card('6_hearts'),
		);
		
		
		$hands[] = array(
			$deck->card('ace_clubs'),
			$deck->card('9_clubs'),
			$deck->card('queen_hearts'),
			$deck->card('2_hearts'),
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
