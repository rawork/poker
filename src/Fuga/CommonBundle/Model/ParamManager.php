<?php

namespace Fuga\CommonBundle\Model;

class ParamManager extends ModelManager {
	
	protected $entityTable = 'config_param';

	public function findAll($name) {
		$sql = "SELECT * FROM config_param WHERE module= :name ";
		$stmt = $this->get('connection')->prepare($sql);
		$stmt->bindValue("name", $name);
		$stmt->execute();
		return $stmt->fetchAll();
	}
	
}