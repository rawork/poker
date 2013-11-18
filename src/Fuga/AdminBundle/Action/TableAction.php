<?php

namespace Fuga\AdminBundle\Action;

class TableAction extends Action {
	function __construct(&$adminController) {	
		parent::__construct($adminController);
	}

	function getTableUpdate() {
		$unit = $this->get('router')->getParam('module');
		$table = $this->get('router')->getParam('table');
		$types = array();
		$svalues = explode(';', 'HTML|html;Булево|checkbox;Вещественное число|float;Выбор|select;Выбор из дерева|select_tree;Выбор множества|select_list;Дата|date;Дата и время|datetime;Мемо|text;Пароль|password;Перечисление|enum;Рисунок|image;Строка|string;Файл|file;Целое число|number;Шаблон|template');
			foreach ($svalues as $a) {
				$types[] = explode('|', $a);
			}
		$params = array(	
			'types' => $types,
			'fields' => $this->dataTable->fields,
			'groups' => $this->get('container')->getItems('user_group'),
			'rights' => array(
				'' => 'По-умолчанию (чтение)',
				'D' => 'Закрыт',
				'R' => 'Чтение',
				'W' => 'Чтение и запись',
				'X' => 'Полный доступ'
			),
			'entity' => $this->get('container')->getTable($unit.'_'.$table)
		);
		return $this->render('admin/table.edit.tpl', $params);
	}

	function getText() {
		$links = array(
			array(
				'ref' => $this->fullRef,
				'name' => 'Список элементов'
			),
			array(
				'ref' => $this->fullRef.'/create',
				'name' => 'Создать таблицу'
			),
			array(
				'ref' => $this->fullRef.'/alter',
				'name' => 'Обновить таблицу'
			)
		);
		$ret = $this->getOperationsBar($links);
		$ret .= $this->getTableUpdate();
		return $ret;
	}

}
