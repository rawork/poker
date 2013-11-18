<?php

namespace Fuga\Component\DB\Field;

class stringType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
		$this->dbValue = str_replace("'", '`', $this->dbValue);
	}
}
