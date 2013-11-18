<?php

namespace Fuga\AdminBundle\Action;

class BackupAction extends Action {
	public function __construct(&$adminController) {
		parent::__construct($adminController);
	}

	public function getFilter() {
		return $this->dataTable->getSeachSQL();
	}

	/* Кнопки управления записью */
	protected function getUpdateDelete($file) {
		$buttons = '<td>
<div class="btn-group pull-right">
  <a class="btn btn-small dropdown-toggle admin-dropdown-toggle" id="drop'.md5($file).'" data-toggle="dropdown" href="#">
    <i class="icon-align-justify"></i>
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu admin-dropdown-menu">
    <li><a href="'.$this->fullRef.'/backupget?file='.$file.'"><i class="icon-pencil"></i> Скачать</a></li>
    <li><a href="'.$this->fullRef.'/backupdelete?file='.$file.'"><i class="icon-trash"></i> Удалить</a></li>
  </ul>
</div>
</td>
';
		return $buttons;
	}

	protected function getMainBodyTable() {
		$text = '';
		if ($archiveReport = $this->get('util')->session('archiveReport')) {
			unset($_SESSION['archiveReport']);
			$text .= '<div class="well" id="archive_info">'.$archiveReport.'</div>';
		}
		$text .= '<input type="button" class="btn btn-success" onClick="createArchive()" value="Создать архив" /><br><br>';
		$text .= '<table class="table table-condensed">';
		$text .= '<thead><tr>';
		$text .= '<th width="55%">Имя</th>';
		$text .= '<th width="22%">Размер файла, MБ</th>';
		$text .= '<th width="22%">Создан</th>';
		$text .= '<th><i class="icon-align-justify"></i></th>';
		$text .= '</tr></thead>';
		$dirname = BACKUP_DIR.'/';
		$dir = @opendir($dirname);
		$files = array();
		while ($file = @readdir($dir))
		{
			$fullname = $dirname . $file;
			if ($file == "." || $file == ".." || @is_dir($fullname))
				continue;
			else if (@file_exists($fullname))
				$files[] = array (
					'name' => $fullname, 
					'name2' => $file,
					'type' => 0,
					'ext' => substr($file, strrpos($file, ".")), 'stat' => stat($fullname)
				);
		}
		@closedir($dir);

		foreach ($files as $current) {
			if ($current['ext'] == '.gz') {
				$text .= '<tr>';
				$text .= '<td>'.$current['name2'].'</td>';
				$text .= '<td>'.$this->get('util')->getSize($current['stat'][7]).'</td>';
				$text .= '<td>'.date('d.m.Y H:i:s', $current['stat'][9]).'</td>';
				$text .= $this->getUpdateDelete($current['name2']).'</tr>';
			}
		}
		$text .= '</table>';
		$text .= '<a href="'.$this->fullRef.'/restore">Скачать скрипт восстановления системы</a>';
		return $text;
	}
	
	protected function getCacheManagment() {
		$text = '';
		$text .= '<br><br><input type="button" class="btn btn-danger" onClick="clearCache()" value="Очистить кэш" />';
		$text .= '<br><br><div class="well closed" id="cache_info"></div>';
		return $text;
	}

	public function getText() {
		return $this->getMainBodyTable().$this->getCacheManagment();
	}

}
