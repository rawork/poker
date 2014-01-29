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
			$error = $this->call('Fuga:Public:Account:login');
			return $this->render('quiz/error.tpl', compact('error'));
		}
		
		if ($user['group_id_name'] != 'admin' && $user['group_id_name'] != 'gamer') {
			$error = 'К сожалению вы не можете участвовать в отборочном туре. Вы не зарегистрировались в качестве игрока.';
			return $this->render('quiz/error.tpl', compact('error'));
		}
		
		$account = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
		if (!$account) {
			$error = 'Для вашей учетной записи не зарегистрирован участник';
			return $this->render('quiz/error.tpl', compact('error'));
		}
		
		$isTooLate = false;
		if ($account['quiz_date'] == '0000-00-00 00:00:00') {
			$error = 'Отборочный тур проводится в строго определенное время.';
			return $this->render('quiz/error.tpl', compact('error'));
		} else {
			$date = new \DateTime($account['quiz_date']);
			if ($date->getTimestamp() - time() > 0) {
				$error = 'Отборочный тур проводится в строго определенное время.';
				return $this->render('quiz/error.tpl', compact('error'));
			} elseif ($date->getTimestamp() + intval($this->getParam('quiz_timer')) - time() < 0) {
				$isTooLate = true;
			} else {
				$now = new \DateTime();
				$date->add(new \DateInterval('PT'.$this->getParam('quiz_timer').'S'));
				$diff = $now->diff($date);
				setcookie('timerhandler', 'stopGame', time()+86400, '/');
				setcookie('timerminute', intval($diff->format('%i')), time()+86400, '/');
				setcookie('timersecond', intval($diff->format('%s')), time()+86400, '/');
			}
		}
		
		$result = $this->get('container')->getItem('quiz_result', 'user_id='.$user['id']);
		if (!$result) {
			$error = 'Не создан набор вопросов. Обратитесь к администратору клуба';
			return $this->render('quiz/error.tpl', compact('error'));
		}
		if ($result['totaltime']) {
			$minutes = floor($result['totaltime'] / 60);
			if ($minutes == 1) {
				$minutes .= ' минуту'; 
			} elseif (in_array($minutes, array(2,3,4))) {
				$minutes .= ' минуты';
			} else {
				$minutes .= ' минут';
			}
			$seconds = $result['totaltime'] % 60;
			if ($seconds == 0) {
				$seconds = '';
			} else {
				$seconds .= ' c';
			}
			$error = 'За '.$minutes.' '.$seconds.' Вы ответили верно<br>на <span class="text-red">'.$result['correct'].'</span> вопросов!';
			return $this->render('quiz/error.tpl', compact('error'));
		} elseif ($isTooLate) {
			$error = 'Отборочный тур проводится в строго определенное время.';
			return $this->render('quiz/error.tpl', compact('error'));
		}
		
		$questions = array();
		$questions0 = json_decode($result['questions']);
		$answers = json_decode($result['answers'], true);
		foreach ($questions0 as $questionId) {
			$questions[] = array(
				'id'   => $questionId,
				'card' => isset($answers[$questionId]) ? $answers[$questionId] : null,
			);
		}
		$answers_count = count($answers);
		$this->get('container')->setVar('javascript', 'victorina');
		
		return $this->render('quiz/index.tpl', compact('result', 'user', 'questions'));
	}
	
	public function stopAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/victorina');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return json_encode(array(
				'ok' => false
			));
		}
		
		$this->get('container')->updateItem('quiz_result', array(
				'totaltime' => 780,
			),
			array('user_id' => $user['id'])
		);
		
		return json_encode(array(
			'ok' => true
		));
	}
	
	public function questionAction($params = array()) {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/victorina');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return json_encode(array(
				'ok' => false
			));
		}
		
		if ($user['group_id_name'] != 'admin' && $user['group_id_name'] != 'gamer') {
			return json_encode(array(
				'ok' => false
			));
		}
		
		$account = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
		if (!$account) {
			return json_encode(array(
				'ok' => false
			));
		}
		
		if ($account['quiz_date'] == '0000-00-00 00:00:00') {
			return json_encode(array(
				'ok' => false
			));
		} else {
			$date = new \DateTime($account['quiz_date']);
			if ($date->getTimestamp() - time() > 0) {
				return json_encode(array(
					'ok' => false
				));
			} elseif ($date->getTimestamp() + intval($this->getParam('quiz_timer')) - time() < 0) {
				return json_encode(array(
					'ok' => false
				));
			}
		}
		
		if (!isset($params[0])) {
			return json_encode(array(
				'ok' => false
			));
		}
		
		$question = $this->get('container')->getItem('quiz_poll', intval($params[0]));
		if (!$question) {
			return json_encode(array(
				'ok' => false
			));
		}
		
		return json_encode(array(
			'ok' => true,
			'content' => $this->render('quiz/question.tpl', compact('question')),
		));
	}
	
	public function answerAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/victorina');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return json_encode(array(
				'ok' => false
			));
		}
		
		if ($user['group_id_name'] != 'admin' && $user['group_id_name'] != 'gamer') {
			return json_encode(array(
				'ok' => false
			));
		}
		
		$account = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
		if (!$account) {
			return json_encode(array(
				'ok' => false
			));
		}
		
		if ($account['quiz_date'] == '0000-00-00 00:00:00') {
			return json_encode(array(
				'ok' => false
			));
		} else {
			$date = new \DateTime($account['quiz_date']);
			if ($date->getTimestamp() - time() > 0) {
				return json_encode(array(
					'ok' => false
				));
			} elseif ($date->getTimestamp() + intval($this->getParam('quiz_timer')) - time() < 0) {
				return json_encode(array(
					'ok' => false
				));
			} else {
				$now = new \DateTime();
				$diff = $now->diff($date);
			}
		}
		
		$answerNo = $this->get('util')->post('answer_no', true, 0);
		$questionId = $this->get('util')->post('question_id', true, 0);
		
		$question = $this->get('container')->getItem('quiz_poll', $questionId);
		if (!$question) {
			return json_encode(array(
				'ok' => false
			));
		}
		
		$result = $this->get('container')->getItem('quiz_result', 'user_id='.$user['id']);
		if (!$result) {
			return json_encode(array(
				'ok' => false
			));
		}
		
		if ($answerNo == $question['answer']) {
			$result['correct'] += 1;
		}
		$result['total'] += 1;
		$answers = json_decode($result['answers'], true);
		$deck = unserialize($result['deck']);
		$cards = $deck->take(1);
		$answers[$questionId] = $cards[0]['name'];
		$result['answers'] = json_encode($answers);
		$answers_count = count($answers);
		if ($answers_count >= 52) {
			$result['totaltime'] = time() - $date->getTimestamp();
		}
		$this->get('container')->updateItem('quiz_result', array(
				'total'   => $result['total'],
				'correct' => $result['correct'],
				'answers' => $result['answers'],
				'deck'    => serialize($deck),
				'totaltime' => $result['totaltime'],
			),
			array('id' => $result['id'])
		);
		if ($answers_count >= 52) {
			return json_encode(array(
				'ok' => false
			));
		}
		
		return json_encode(array(
			'ok' => true,
			'content' => $this->render('quiz/answer.tpl', compact('answers_count', 'result')),
			'card' => $cards[0]['name'],
		));
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