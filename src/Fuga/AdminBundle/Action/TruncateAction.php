<?php

namespace Fuga\AdminBundle\Action;

class TruncateAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	function getText() {
		$this->messageAction($this->get('container')->truncateTable($this->dataTable->dbName()) ? 'Все записи таблицы удалены' : 'Ошибка удаления записей таблицы');
	}
}
