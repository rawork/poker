<?php

namespace Fuga\AdminBundle\Action;

use Fuga\CommonBundle\Controller\Controller;

class Action extends Controller {
	public $uai;
	public $dataTable;
	public $baseRef;
	public $searchRef;
	public $fullRef;
	public $action;
	protected $search_url;
	protected $search_sql;
	protected $tableParams;
	
	public function __construct(&$adminController) {
		$this->uai = $adminController;
		$this->baseRef = $this->uai->getBaseTableRef();
		$this->searchRef = $this->baseRef;
		$this->fullRef = $this->searchRef.($this->get('util')->request('page') ? '?page='.$this->get('util')->request('page') : '');
		if (is_object($this->dataTable = $this->uai->getBaseTable())) {
			if ($filterType = $this->get('util')->post('filter_type')) {
				switch ($filterType) {
					case 'cansel':
						unset($_SESSION[$this->dataTable->dbName()]);
						break;
					default:
						$this->search_url = $this->dataTable->getSearchURL();
						parse_str($this->search_url, $this->tableParams);
						$_SESSION[$this->dataTable->dbName()] = json_encode($this->tableParams, JSON_UNESCAPED_UNICODE);
				}
				$this->get('router')->redirect($this->baseRef);
			} else {
				$tableParams = $this->get('util')->session($this->dataTable->dbName());
				$this->tableParams = json_decode(stripslashes($tableParams), true);
			}
			if (is_array($this->tableParams)) {
				foreach ($this->tableParams as $key => $value) {
					$_REQUEST[$key] = $value;
				}
			}
			$this->search_sql = $this->dataTable->getSearchSQL();
		}

	}
	protected function messageAction($msg) {
		return $this->uai->messageAction($msg, $this->fullRef);
	}
	protected function getTemplateName(&$v) {
		return $this->get('security')->isSuperuser()  ? ' <span class="sfnt">{'.strtolower($v['name']).'}</span>' : '';
	}
	public function getText() {
		$body = 'Вызов неизвестной функции';
		return $body;
	}
	protected function getHelpLink($field) {
		return !empty($field['help']) ? '<i class="icon-exclamation-sign" alt="'.$field['help'].'" title="'.$field['help'].'"></i>' : '';
	}
	protected function getOperationsBar ($links = array()) {
		return $this->render('admin/action/menu.tpl', compact('links'));
	}
	
}
