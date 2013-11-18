<?php

namespace Fuga\AdminBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;
use Fuga\AdminBundle\Action\Action;

class AdminController extends Controller {
	public $module;
	public $title;
	public $icon;
	public $users;
	
	private $tables;
	
	function __construct($module, $title, $users) {
		$this->module = $module;
		$this->tables = $this->get('container')->getTables($this->module->name);
		$this->title = $title;
		$this->users = $users;
	}
	
	function isAvailable() {
		return $this->get('security')->isSuperuser() || 1 == $this->users[$this->get('util')->session('user')];
	}
	
	function getBaseRef() {
		return PRJ_REF.'/admin/'.$this->get('router')->getParam('state').'/'.$this->module->name.'/';
	}
	
	function createBaseTableRef($tableName) {
		return $this->getBaseRef().$tableName;
	}
	
	function getBaseTableKey() {
		if (!$this->get('router')->hasParam('table')) {
			if (count($this->tables)) {
				foreach ($this->tables as $tableName => $table) {
					$this->get('router')->setParam('table', $tableName);
					break;
				}
			} else {
				throw new \Exception('Таблицы для модуля "'.$this->module->name.'" не найдены');
			}
		}
		return $this->get('router')->getParam('table');
	}
	
	function getBaseTableRef() {
		return $this->createBaseTableRef($this->getBaseTableKey());
	}
	
	function getBaseTable() {
		return $this->get('container')->getTable($this->module->name.'_'.$this->getBaseTableKey());
	}
	
	function messageAction($msg, $path = null) {
		if (!$path) {
			$path = $this->getBaseTableRef();
		}
		$_SESSION['message'] = $msg;
		$this->get('router')->redirect($path);
	}
	
	function getContent() {
		$action = $this->get('router')->getParam('action');
		try {
			$name = 'Fuga\\AdminBundle\\Action\\'.ucfirst($action).'Action';
			$content = new $name($this);
		} catch (\Exception $e) {
			$content = new Action($this);
		}
		return $content->getText();
	}

	function getMenuItems() {
		$ret = array();
		
		foreach ($this->tables as $tableName => $table) {
			if (empty($table->params['is_hidden'])) {
				$ret[] = array (
					'ref' => $this->createBaseTableRef($tableName),
					'name' => $table->title
				);
			}
		}
		if ($this->get('security')->isSuperuser()) {
			if (count($this->module->params)) {
				$ret[] = array (
					'ref' => $this->getBaseTableRef().'/setting',
					'name' => 'Настройки'
				);
			}
		}
		if ($this->module->name == 'config' && $this->get('security')->isSuperuser()) {
			$ret[] = array (
				'ref' => $this->getBaseTableRef().'/backup',
				'name' => 'Обслуживание'
			);
		}
//		if ($this->module->name == 'article' && $this->get('security')->isSuperuser()) {
//			$ret[] = array (
//				'ref' => $this->getBaseTableRef().'/counttag',
//				'name' => 'Расчет тегов'
//			);
//		}
		if ($this->module->name == 'maillist' && $this->get('security')->isSuperuser()) {
			$ret[] = array (
				'ref' => $this->getBaseTableRef().'/send',
				'name' => 'Отправка писем'
			);
		}
		return $ret;
	}

	public function getTableMenu() {
		return $this->render('admin/submenu.tpl', array('tables' => $this->getMenuItems()));
	}
	
	public function getMessage() {
		$message = $this->get('util')->session('message');
		unset($_SESSION['message']);
		return $message;
	}
	
	public function getTitle() {
		$title = str_replace(' ', '&nbsp;', $this->title);
		if ($menu = $this->getMenuItems()) {
			foreach ($menu as $item) {
				if ($this->getBaseTableRef() == $item['ref']) {
					$title .= ': '.$item['name'];
				}
			}
			switch ($this->get('router')->getParam('action')) {
				case 'table':
					$title .= ': Настройка таблицы';break;
				case 'add':
					$title .= ': Добавление';break;
				case 'edit':
					$title .= ': Редактирование';break;
				case 'backup':
					$title .= ': Резервное копирование';break;
				case 'export':
					$title .= ': Экспорт';break;
				case 'import':
					$title .= ': Импорт';break;
				case 'setting':
					$title .= ': Редактирование настроек';break;
				case 'groupedit':
					$title .= ': Груповое редактирование';break;
			}
		}
		return $title;
	}

	public function indexAction() {
		$params = array(
			'title' => $this->getTitle(),
			'message' => $this->getMessage(),
			'content' => $this->getContent()
		);
		return $this->render('admin/content.tpl', $params);
	}
	
}
