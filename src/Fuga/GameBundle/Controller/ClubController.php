<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class ClubController extends PublicController {
	
	public function __construct() {
		parent::__construct('club');
	}
	
	public function indexAction() {

		
		return $this->render('club/index.tpl', compact('items'));
	}
}