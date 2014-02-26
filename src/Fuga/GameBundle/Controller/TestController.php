<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Fuga\GameBundle\Model\Combination;
use Fuga\GameBundle\Model\Deck;
use Fuga\GameBundle\Document\Question;

class TestController extends PublicController {
	
	public function __construct() {
		parent::__construct('test');
	}
	
	public function indexAction() {
		
		$deck = new Deck();
		
		$hands = array();
		
		$flop = array(
			$deck->card('8_diams'),
			$deck->card('queen_clubs'),
			$deck->card('5_diams'),
		);
		$hands[] = array(
			$deck->card('ace_spades'),
			$deck->card('ace_hearts'),
			$deck->card('2_spades'),
			$deck->card('2_hearts'),
		);
		
		
		$hands[] = array(
			$deck->card('king_diams'),
			$deck->card('king_clubs'),
			$deck->card('4_hearts'),
			$deck->card('4_diams'),
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
				if (intval($buffer[4]) > $board) {
					$board = intval($buffer[4]);
					$seat = 1;
				} else {
					$seat++; 
				}
				$user = $this->get('container')->getItem('user_user', 'email="'.$buffer[0].'"');
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

	public function qAction() {
//		$questions = $this->get('container')->getItems('game_poll', '1=1');
//		foreach ($questions as $question) {
//			$q = new Question();
//			$q->setQuestion(intval($question['id']));
//			$q->setName($question['name']);
//			$q->setAnswer1($question['answer1']);
//			$q->setAnswer2($question['answer2']);
//			$q->setAnswer3($question['answer3']);
//			$q->setAnswer4($question['answer4']);
//			$q->setAnswer(intval($question['answer']));
//			$this->get('odm')->persist($q);
//		}
//		$this->get('odm')->flush();
	}
}
