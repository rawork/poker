<?php

namespace Fuga\CommonBundle\Model;

class ModelManager implements ModelManagerInterface {
	
	protected $entityTable;
	protected $connection;
	
	public function findBy($query = '', $sort = '', $limit = null, $offset = null) {
		$this->get('container')->getItems($this->entityTable, $query, $sort, $select);
	}
	
	public function get($name) {
		global $container;
		if ($name == 'container') {
			return $container;
		} else {
			return $container->get($name);
		}
	}
}