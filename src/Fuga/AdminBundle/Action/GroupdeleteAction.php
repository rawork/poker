<?php

namespace Fuga\AdminBundle\Action;    

class GroupdeleteAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}

	function getText() {
		$query = 'id IN('.$this->get('util')->post('ids').') ';
		$this->messageAction($this->get('container')->deleteItem($this->dataTable->dbName(), $query) ? 'Удалено' : 'Ошибка группового удаления');
	}
}
