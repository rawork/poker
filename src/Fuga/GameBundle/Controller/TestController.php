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
			$deck->card('ace_spades'),
			$deck->card('9_hearts'),
			$deck->card('3_hearts'),
		);
		$hands[] = array(
			$deck->card('ace_clubs'),
			$deck->card('ace_diams'),
			$deck->card('jack_spades'),
			$deck->card('4_spades'),
		);
		
		
		$hands[] = array(
			$deck->card('10_diams'),
			$deck->card('9_clubs'),
			$deck->card('7_hearts'),
			$deck->card('6_diams'),
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
				if (intval($buffer[5]) > $board) {
					$board = intval($buffer[5]);
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

	public function testAction() {
		$quesCount = $this->get('odm')
			->createQueryBuilder('\Fuga\GameBundle\Document\Question')
			->field('question')->notIn(array(101,102,103,104))
			->field('question')->range(10,20)
			->count()
			->getQuery()->execute();
		var_dump($quesCount);
	}

	public function resAction() {
		$gamers = $this->get('odm')
			->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
			->field('active')->equals(true)
			->field('chips')->gt(7)
			->sort('board')
			->getQuery()->execute();
		foreach ($gamers as $gamer) {
			$board = $this->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Board')
				->field('state')->equals(6)
				->field('board')->equals($gamer->getBoard())
				->getQuery()->execute();
			$data = array(
				$gamer->getLastname(),
				$gamer->getName(),
				'Фишки '.$gamer->getChips(),
				'Зал '.($gamer->getBoard()-1),
			);
			if ($board) {
				echo implode(';', $data);
				echo '<br>';
			}
		}
	}

	public function res2Action() {
		$gamers = $this->get('odm')
			->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
			->sort('board')
			->getQuery()->execute();
		foreach ($gamers as $gamer) {
			$board = $this->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Board')
				->field('state')->equals(6)
				->field('board')->equals($gamer->getBoard())
				->getQuery()->execute();
			$denied = $gamer->getDenied();
			$data = array(
				$gamer->getLastname(),
				$gamer->getName(),
				'Вопросов '.count(array_unique($denied)),
				'Ответов '.($gamer->getQues()-1),
			);
			if ($board) {
				echo implode(';', $data);
				echo '<br>';
			}
		}
	}

	public function chipsAction() {
		$winners = array(
			array('user' => 1 , 'win' => 5, 'cards' => array(array('name' => 'joker'))),
			array('user' => 1 , 'win' => 5, 'cards' => array(array('name' => 'joker'))),
		);

		$gamers = $this->get('odm')
			->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
			->field('board')->equals(1)
			->getQuery()->execute();

			$ids = array();

			foreach ($gamers as $doc) {
				foreach ($winners as $winner) {
					if ($doc->getUser() == $winner['user']) {

						$this->get('log')->addError(
							'distribute game1'
							.' gamer '.$doc->getUser()
							.' chips before '.$doc->getChips()
						);

						$doc->setChips( $doc->getChips() + $winner['win'] );

						$this->get('log')->addError(
							'distribute game1'
							.' gamer '.$doc->getUser()
							.' win '.$winner['win']
						);

						foreach ($winner['cards'] as $card) {
							if ($card['name'] == 'joker' && !in_array($winner['user'], $ids)) {
								$ids[] = $winner['user'];
								$doc->setChips( $doc->getChips() + 2 );

								$this->get('log')->addError(
									'distribute game1'
									.' gamer '.$doc->getUser()
									.' joker add 2 chips'
								);
							}
						}

						$this->get('log')->addError(
							'distribute game1'
							.' gamer '.$doc->getUser()
							.' chips after '.$doc->getChips()
						);
					}
				}

			}
		$this->get('odm')->flush();
	}

	public function winnerAction() {
		$fh = fopen($_SERVER['DOCUMENT_ROOT'].'/'.'winner.csv', 'r');
		if ($fh) {
			$seat = 0;
			$board = 0;
			while (($buffer = fgetcsv($fh, 4096, ';')) !== false) {
//				var_dump($buffer);

				$user = $this->get('container')->getItem('user_user', 'email="'.$buffer[0].'"');
				$account = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
				echo 'UPDATE user_user SET group_id=2 WHERE id='.$user['id'].";<br>";
				echo 'UPDATE account_member SET rounds=2, chips='.$buffer[3].'  WHERE id='.$account['id'].";\n<br>";
			}
			if (!feof($fh)) {
//				echo "Error: unexpected fgets() fail\n";
				exit;
			}
			fclose($fh);
			exit;
		}
	}

	public function import2Action() {
		$questions = array();
		$fh = fopen($_SERVER['DOCUMENT_ROOT'].'/'.'questions.txt', 'r');
		if ($fh) {
			$i = 1;
			$name = '';
			$answer1 = $answer2 = $answer3 = $answer4 = '';
			$answer = 0;
			while (($buffer = fgets($fh, 4096)) !== false) {
				switch ($i) {
					case 1:
						$buffer = substr($buffer, strpos($buffer, '.') + 2);
						$name = trim($buffer);
						break;
					case 2:
						if (substr($buffer, 0, 1) == '>') {
							$answer = 1;
							$buffer = trim(substr($buffer, 1));
						}
						$answer1 = trim($buffer);
						break;
					case 3:
						if (substr($buffer, 0, 1) == '>') {
							$answer = 2;
							$buffer = trim(substr($buffer, 1));
						}
						$answer2 = trim($buffer);
						break;
					case 4:
						if (substr($buffer, 0, 1) == '>') {
							$answer = 3;
							$buffer = trim(substr($buffer, 1));
						}
						$answer3 = trim($buffer);
						break;
					case 5;
						if (substr($buffer, 0, 1) == '>') {
							$answer = 4;
							$buffer = trim(substr($buffer, 1));
						}
						$answer4 = trim($buffer);
						break;
					case 6:
						$questions[] = array(
							$name,
							$answer1,
							$answer2,
							$answer3,
							$answer4,
							$answer,
						);
						$i = 0;
						$name = '';
						$answer1 =$answer2 = $answer3 = $answer4 = '';
						$answer = 0;
						break;
				}

//				echo $buffer.' - '.$i.'<br>';
				$i++;
			}
			$questions[] = array(
				$name,
				$answer1,
				$answer2,
				$answer3,
				$answer4,
				$answer,
			);

			if (!feof($fh)) {
//				echo "Error: unexpected fgets() fail\n";
			}
			fclose($fh);
		}

		for ($i = 0; $i < 20; $i++) {
			shuffle($questions);
		}

		foreach ($questions as $question) {
			list($name, $answer1, $answer2, $answer3, $answer4, $answer) = $question;
			echo "INSERT INTO game_poll(name,answer1,answer2,answer3,answer4,answer,created,updated) VALUES('$name','$answer1','$answer2','$answer3','$answer4',$answer,NOW(),'0000-00-00 00:00:00');\n<br>";
		}
		exit;
	}

	public function quAction() {
		$questions0 = $this->get('odm')
			->createQueryBuilder('\Fuga\GameBundle\Document\Question')
			->field('question')->notIn(array(0))
			->getQuery()->execute()->toArray();

		var_dump(array_values($questions0));
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
