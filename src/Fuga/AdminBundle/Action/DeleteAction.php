<?php

namespace Fuga\AdminBundle\Action;

class DeleteAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	function getText() {
		$q = 'id='.$this->get('router')->getParam('id');
		$this->messageAction($this->get('container')->deleteItem($this->dataTable->dbName(), $q) ? 'Удалено' : 'Ошибка удаления');
	}
}
