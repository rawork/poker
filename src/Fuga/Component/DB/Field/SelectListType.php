<?php

namespace Fuga\Component\DB\Field;

class SelectListType extends Type {

	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getSearchInput($name = '', $value = '') {
		$value = $value ?: intval(parent::getSearchValue());
		$name = $name ?: parent::getSearchName();
		$table = $this->get('router')->getParam('module').'_'.$this->get('router')->getParam('table');
		$id = $this->dbId ?: '0';
		$inputId = strtr($name, '[]', '__');
		$content = '
<div class="input-append">
<input class="input-xxlarge" id="'.$inputId.'_title"  type="text" value="'.$this->getStatic($value).'" readonly>
<button class="btn btn-default" href="javascript:void(0)" type="button" onClick="showSelectPopup(\''.$inputId.'\',\''.$table.'\',\''.$name.'\', \''.$id.'\', \''.$this->getStatic($value).'\');">&hellip;</button>
</div>
<input type="hidden" name="'.$name.'" value="'.$value.'" id="'.$inputId.'">
';
		
		return $content;
	}

	public function getSearchSQL() {
		return $this->getSearchValue() ? ' FIND_IN_SET(\''.$this->getSearchValue().'\','.$this->getName().') ' : '';
	}

	public function getStatic($value = null) {
		$value = $value ?: $this->dbValue;
		$content = '';
		$fields = explode(',', $this->getParam('l_field'));
		$items = null;
		if ($value) {
			$sql = 'SELECT id,'.$this->getParam('l_field').
				' FROM '.$this->getParam('l_table').
				' WHERE id IN('.$value.')'.
				($this->getParam('l_sort') ? ' ORDER BY '.$this->getParam('l_sort') : '');
			$stmt = $this->get('connection')->prepare($sql);
			$stmt->execute();
			$items = $stmt->fetchAll();
		}
		if ($items) {
			foreach ($items as $k => $item) {
				$content .= '';
				$content .= (!empty($content) && $k) ? ', ' : '';
				foreach ($fields as $fieldName) {
					if (array_key_exists($fieldName, $item)) {
						$content .= ' '.$item[$fieldName];
					}	
				}
				$content .= ' ('.$item['id'].')';
			}
			
			return $content;
		} else {
			return 'Не выбрано';
		}
	}

	public function getInput($value = '', $name = '') {
		$name = $name ? $name : $this->getName();
		$value = $value ? $value : $this->dbValue;
		$input_id = strtr($name, '[]', '__');
		$table = $this->get('router')->getParam('module').'_'.$this->get('router')->getParam('table');
		$content = '
<div class="input-append">
<input class="input-xxlarge" id="'.$input_id.'_title"  type="text" value="'.$this->getStatic($value).'" readonly>
<button class="btn btn-default" type="button" onClick="showListPopup(\''.$input_id.'\',\''.$table.'\',\''.$this->getName().'\', \''.$value.'\');">&hellip;</button>
</div>
<input type="hidden" name="'.$name.'" value="'.$value.'" id="'.$input_id.'">
';
		return $content;
	}
}
