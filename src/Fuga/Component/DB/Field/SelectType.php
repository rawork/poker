<?php

namespace Fuga\Component\DB\Field;

class SelectType extends LookUpType {

	private $entities = null;

	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	private function getSelectInput($value, $name) {
		$value = $value ?: intval($this->dbValue);
		$name = $name?: $this->getName();
		$table = $this->get('router')->getParam('module').'_'.$this->get('router')->getParam('table');
		$id = $this->dbId ?: '0';
		$input_id = strtr($name, '[]', '__');
		$empty = $value ? ' <a href="javascript:void(0)" onClick="emptySelect(\''.$input_id.'\')"><i class="icon-remove"></i></a>' : '';
		$content = '
<div id="'.$input_id.'_title">'.$this->getStatic($value).$empty.'</div>
<button class="btn btn-success" href="javascript:void(0)" type="button" onClick="showSelectPopup(\''.$input_id.'\',\''.$table.'\',\''.$name.'\', \''.$id.'\', \''.$this->getStatic($value).'\');">Выбрать</button>
<input type="hidden" name="'.$name.'" value="'.$value.'" id="'.$input_id.'">
<input type="hidden" name="'.$name.'_type" value="'.$this->getParam('link_type').'" id="'.$input_id.'_type">
';
		
		return $content;
	}

	public function getStatic($value = null) {
		$value = $value ?: intval($this->dbValue);
		$content = '';
		$item = $this->get('container')->getItem($this->getParam('l_table'), $value);
		if ($item) {
			$fields = explode(',', $this->getParam('l_field'));
			foreach ($fields as $fieldName)
				if (isset($item[$fieldName]))
					$content .= ($content ? ' ' : '').$item[$fieldName];
			return $content.' ('.$item['id'].')';
		} else {
			return 'Не выбрано';
		}
	}
	
	public function getNativeValue() {
		$value = array('value' => parent::getNativeValue());
		if (!empty($value['value'])) {
			$sql = 'SELECT * FROM '.$this->getParam('l_table').' WHERE id IN('.$value['value'].')';
			$stmt = $this->get('connection')->prepare($sql);
			$stmt->execute();
			$item = $stmt->fetch();
			if ($item) {
				$value['item'] = array();
				foreach ($item as $k => $v) {
					$value['item'][$k] = $v;
				}
			}
		}
		return $value;
	}

	public function getInput($value = '', $name = '', $class = '') {
		return $this->getSelectInput($value, $name, $class);
	}

	public function getSearchInput() {
		return $this->getSelectInput( parent::getSearchValue(), parent::getSearchName());
	}
	
	public function getSearchSQL() {
		$value = parent::getSearchValue();
		if ($value) {
			return $this->getName()."=".$value;
		} else {
			return '';
		}
	}
}
