<?php

namespace Fuga\AdminBundle\Action;

class DropAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	function getText() {
		$this->messageAction($this->get('container')->deleteClass($this->dataTable->dbName()) ? 'Таблица удалена' : 'Ошибка удаления таблицы');
	}
}
