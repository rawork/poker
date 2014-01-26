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
}