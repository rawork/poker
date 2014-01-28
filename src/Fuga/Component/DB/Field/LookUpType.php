<?php

namespace Fuga\Component\DB\Field;

class LookUpType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getSearchSQL() {
		if ($value = $this->getSearchValue()) {
			return $this->getName().'='.$value;
		}
		
		return '';
	}

	public function getValue($name = '') {
		$name = $name ?: $this->getName();
		$value = isset($_REQUEST[$name]) ? intval($_REQUEST[$name]) : ($this->dbValue ?: 0);
		
		return $value;
	}
	
	public function getType() {
		return 'integer';
	}

}
