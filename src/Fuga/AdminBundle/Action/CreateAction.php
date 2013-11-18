<?php

namespace Fuga\AdminBundle\Action;

class CreateAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	function getText() {
		$this->messageAction($this->dataTable->create() ? 'Таблица создана' : 'Таблица уже существует');
	}
}
