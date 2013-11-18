<?php

namespace Fuga\AdminBundle\Action;

class SendAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	function getText() {
		$this->messageAction($this->get('scheduler')->everyMinute() ? 'Ошибки при рассылке' : 'Рассылка сделана');
	}
}
