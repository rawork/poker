<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class RuleController extends PublicController {
	
	public function __construct() {
		parent::__construct('rule');
	}
	
	public function indexAction() {
		$items = $this->get('container')->getItems('rule_rule', 'publish=1');
		
		return $this->render('rule/index.tpl', compact('items'));
	}
}