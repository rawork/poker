<?php

namespace Fuga\AdminBundle\Action;

class CopyAction extends Action {
	
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	
	function getText() {
		set_time_limit(0);
		$this->messageAction($this->get('container')->copyItem($this->dataTable->dbName(), $this->get('router')->getParam('id'), $this->get('util')->request('quantity', true, 1)) ? 'Скопировано' : 'Ошибка копирования');
	}

}
