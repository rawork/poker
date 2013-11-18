<?php

namespace Fuga\AdminBundle\Action;

class ExportAction extends Action {
	function __construct(&$adminController) {
		parent::__construct($adminController);
	}
	function getText() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$ret = $this->uai->module->exportCSV();
			$ret = '<textarea width="90%" cols="50" rows="10" name="data" id="data">'.addslashes($ret).'</textarea>';
			return $ret;
		}
		$ret = '<b>Экспорт CSV</b><br><table border="0" width="70%">
<form enctype="multipart/form-data" action="'.$this->fullRef.'/export" method="post">
<tr bgcolor="#fafafa"><td align="right"><input type="submit" value="Экспорт -&gt;"></td></tr>
</form></table>';
		return $ret;
	}
}
