<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class PrizeController extends PublicController {
	
	public function __construct() {
		parent::__construct('prize');
	}
	
	public function indexAction() {
		$prizes = $this->get('container')->getItems('prize_prize', 'publish=1');
	
		return $this->render('prize/index.tpl', compact('prizes'));
	}
	
}