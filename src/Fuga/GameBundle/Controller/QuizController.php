<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class ClubController extends PublicController {
	
	public function __construct() {
		parent::__construct('quiz');
	}
	
	public function indexAction() {

		
		return $this->render('quiz/index.tpl', compact('items'));
	}
}