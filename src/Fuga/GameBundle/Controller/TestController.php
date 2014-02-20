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
			$deck->card('ace_diams'),
			$deck->card('queen_clubs'),
			$deck->card('5_diams'),
		);
		$hands[] = array(
			$deck->card('king_spades'),
			$deck->card('jack_hearts'),
			$deck->card('joker'),
			$deck->card('6_hearts'),
		);
		
		
		$hands[] = array(
			$deck->card('queen_diams'),
			$deck->card('10_clubs'),
			$deck->card('10_hearts'),
			$deck->card('7_hearts'),
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
	
	public function importAction() {
		$fh = fopen($_SERVER['DOCUMENT_ROOT'].'/'.'seat.csv', 'r');
		if ($fh) {
			$seat = 0;
			$board = 0;
			while (($buffer = fgetcsv($fh, 4096, ';')) !== false) {
//				var_dump($buffer);
				if (intval($buffer[6]) > $board) {
					$board = intval($buffer[6]);
					$seat = 1;
				} else {
					$seat++; 
				}
				$user = $this->get('container')->getItem('user_user', 'email="'.$buffer[3].'"');
				$account = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
				echo 'UPDATE account_member SET board_id='.($board+1).', seat='.$seat.'  WHERE id='.$account['id'].";\n<br>";
			}
			if (!feof($fh)) {
//				echo "Error: unexpected fgets() fail\n";
				exit;
			}
			fclose($fh);
			exit;
		}
	}
}
