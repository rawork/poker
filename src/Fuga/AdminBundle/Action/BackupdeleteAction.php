<?php

namespace Fuga\AdminBundle\Action;

class BackupdeleteAction extends Action {
	
	public function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	
	public function getText() {
		$file = $this->get('util')->request('file');
		unlink(BACKUP_DIR.'/'.$file);
		$this->get('router')->redirect($this->fullRef.'/backup');
	}
}
