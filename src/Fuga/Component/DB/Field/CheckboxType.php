<?php

namespace Fuga\Component\DB\Field;

class CheckboxType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getSQLValue($name = '') {
		return $this->getValue($name) == "1" ? $this->getValue($name) : 0;
	}

	public function getStatic() {
		return $this->dbValue ? 'Да' : 'Нет';
	}

	public function getInput($value = '', $name = '') {
		return '<input type="checkbox" value="1" name="'.($name ? $name : $this->getName()).'" '.(empty($this->dbValue) ? '' : 'checked').'>';
	}

	public function getSearchInput() {
		$name = parent::getSearchName();
		$value = parent::getSearchValue();
		$yes = $no = $no_matter = "";
		switch ($value) {
			case "on":
				$yes = 'checked';
				break;
			case "off":
				$no = 'checked';
				break;
			default: 
				$no_matter = 'checked';
		}
		return '
<label class="radio-inline">
  <input type="radio" name="'.$name.'" id="'.$name.'_yes" value="on" '.$yes.'>
  да
</label>
<label class="radio-inline">
  <input type="radio" name="'.$name.'" id="'.$name.'_no" value="off" '.$no.'>
  нет
</label>
<label class="radio-inline">
  <input type="radio" name="'.$name.'" id="'.$name.'_nomatter" value="" '.$no_matter.'>
  все равно
</label>';
	}

	public function getSearchSQL() {
		$value = parent::getSearchValue();
		if ($value == 'off') {
			return $this->getName()."=0";
		} elseif ($value == 'on') {
			return $this->getName()."=1";
		} else {
			return '';
		}
	}
	
	public function getType() {
		return 'boolean';
	}
}
