<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class QuizController extends PublicController {
	
	public function __construct() {
		parent::__construct('quiz');
	}
	
	public function indexAction() {
		$user = $this->get('security')->getCurrentUser();
		
		return $this->render('quiz/index.tpl', compact('items'));
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
}