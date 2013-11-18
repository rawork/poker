<?php

namespace Fuga\AdminBundle\Action;

class ImportAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	function getText() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->uai->messageAction($this->uai->module->importCSV() ? 'Импорт выполнен' : 'Ошибки при импорте', $this->uai->getBaseRef().'&action=s_import');
		}
		$ret = '<b>Импорт CSV</b><br><table border="0" width="70%">
<form enctype="multipart/form-data" action="'.$this->fullRef.'/import" method="post">
<tr bgcolor="#fafafa">
	<th align="left" width="20%">CSV-файл <small>(макс '.get_cfg_var('upload_max_filesize').')</small></th>
	<td><input name="csv_file" type="file" style="width:100%"></td></tr>
<tr><td colspan="2" align="right"><input type="submit" value="ИмпортироватьЭ></td></tr></form></table>';
		return $ret;
	}
}
