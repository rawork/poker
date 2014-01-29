<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Fuga\GameBundle\Model\Deck;

class QuizController extends PublicController {
	
	public function __construct() {
		parent::__construct('quiz');
	}
	
	public function indexAction() {
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return $this->call('Fuga:Public:Account:login');
		}
		
		if ($user['group_id_name'] == 'viewer') {
			return 'зритель';
		}
		
		$account = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
		if (!$account) {
			return 'ошибка account';
		}
		
		if ($account['quiz_date'] == '0000-00-00 00:00:00') {
			return 'время не назначено';
		}
		
		$date = new \DateTime($account['quiz_date']);
		if ($date->getTimestamp() - time() > 0) {
			return 'викторина не началась';
		} elseif ($date->getTimestamp() + 780 - time() < 0) {
			return 'викторина закончилась';
		}
		
		$result = $this->get('comntainer')->getItem('quiz_result', 'user_id='.$user['id']);
		if (!$result) {
			return 'error result';
		}
		
		$questions = json_decode($result['questions']);
		$answers = json_decode($result, true);
		
		var_dump($questions, $answers);
		
		return $this->render('quiz/index.tpl', compact('user', 'answers', 'questions'));
	}
	
	public function questionAction($params) {
		
	}
	
	public function answerAction() {
		
	}
	
	public function stopAction() {
		
	}
	
	public function importAction() {
		$fh = fopen($_SERVER['DOCUMENT_ROOT'].'/'.'q.txt', 'r');
		if ($fh) {
			$i = 1;
			$name = '';
			$answer1 =$answer2 = $answer3 = $answer4 = '';
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
						echo "INSERT INTO quiz_poll(name,answer1,answer2,answer3,answer4,answer,created,updated) VALUES('$name','$answer1','$answer2','$answer3','$answer4',$answer,NOW(),'0000-00-00 00:00:00');\n<br>";
						$i = 0;
						$name = '';
						$answer1 =$answer2 = $answer3 = $answer4 = '';
						$answer = 0;
						break;
				}
//				echo $buffer.' - '.$i.'<br>';
				$i++;
			}
			echo "INSERT INTO quiz_poll(name,answer1,answer2,answer3,answer4,answer,created,updated) VALUES('$name','$answer1','$answer2','$answer3','$answer4',$answer,NOW(),'0000-00-00 00:00:00');\n<br>";
			if (!feof($fh)) {
//				echo "Error: unexpected fgets() fail\n";
				exit;
			}
			fclose($fh);
			exit;
		}
	}
	
	public function applyAction() {
		$questions = $this->get('container')->getItems('quiz_poll', '1=1');
		$ids = array_keys($questions);
		$users = $this->get('container')->getItems('user_user', 'group_id=2 OR group_id=1');
		$i = 0;
		foreach ($users as $user) {
			$result = $this->get('container')->getItem('quiz_result', 'user_id='.$user['id']);
			if ($result) {
				continue;
			}
			
			shuffle($ids);
			$readyIds = array_slice($ids, 0, 52);
			$this->get('container')->addItem('quiz_result', array(
				'user_id' => $user['id'],
				'totaltime' => '',
				'total' => 0,
				'correct' => 0,
				'questions' => json_encode($readyIds),
				'answers'   => '',
				'deck'		=> serialize(new Deck()),
				'created' => date('Y-m-d H:i:s'),
				'updated' => '0000-00-00 00:00:00',
			));
			$i++;
		}
		return 'applied '.$i.' users';
	}
}