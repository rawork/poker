<?php

namespace Fuga\AdminBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;
use Fuga\AdminBundle\Controller\AdminController;
use Fuga\AdminBundle\Admin\Admin;
use Fuga\Component\Archive\GZipArchive;

class AdminAjaxController extends Controller {
	
	/** 
	 * Смена Меню компонентов при выборе группы функций
	 * @param string $state
	 * @param string $moduleName
	 * @return string 
	 */
	function getComponentList($state, $moduleName = '') 
	{
		$this->get('router')->setParam('state', $state);
		$this->get('router')->setParam('module', $moduleName);
		
		$modules = array();
		if ($this->get('util')->session('user')) {
			$modulesAll = $this->get('container')->getModulesByState($state);
			foreach ($modulesAll as $module) {
				$modules[] = array(
					'name' => $module['name'],
					'title' => $module['title']
				);	
			}
		} else {
			return json_encode(array('alertText' => 'Сессия окончилась. Перезагрузите страницу'));
		}
		$text = $this->render('admin/mainmenu.tpl', compact('state', 'moduleName', 'modules'));
		
		return json_encode(array('content' => $text));
	}
	
	// Показать Меню таблиц для модуля
	function getTableList($state, $moduleName) {
		if ($this->get('util')->session('user')) {
			$this->get('router')->setParam('state', $state);
			$this->get('router')->setParam('module', $moduleName);
			$uai = new AdminController(new Admin($moduleName), '', array($this->get('util')->session('user') => 1));
			$text = $uai->getTableMenu();

			return json_encode(array('content' => $text));
		} else {
			return json_encode(array('alertText' => 'Сессия окончилась. Перезагрузите страницу'));
		}
	}
	
	// Выбор из списка разделов
	function showSelectPopup($inputId, $tableName, $fieldName, $entityId, $title) {
		$table = $this->get('container')->getTable($tableName);
		$fieldName = str_replace($entityId, '', $fieldName);
		$fieldName = str_replace('search_filter_', '', $fieldName);
		$field = $table->fields[$fieldName];
		$text = '<input type="hidden" id="popupChoiceId" value="'.$entityId.'">
Выбранный элемент:  <span id="popupChoiceTitle">'.$title.'</span>
<div id="selectlist">
<table class="table table-condensed">
<thead><tr>
<th>Название</th>
</tr></thead>';
		$criteria = array();
		if ($field['l_table'] == 'page_page' && isset($field['dir'])) {
			$module = $this->get('container')->getModule($table->moduleName);
			$criteria[] = 'module_id='.(isset($module['id']) ? $module['id'] : 0 ); 
		}
		$criteria = implode(' AND ', $criteria);
		$paginator = $this->get('paginator');
		$paginator->paginate(
				$this->get('container')->getTable($field['l_table']), 
				'javascript:showPage(\'selectlist\',\''.$tableName.'\', \''.$fieldName.'\', '.$entityId.', ###)', 
				$criteria, 
				10, 
				1, 
				6);
		$items = $this->get('container')->getItems($field['l_table'], $criteria, $field['l_field'], $paginator->limit);
		$fields = explode(',', $field['l_field']);
		foreach ($items as $item) {
			$fieldTitle = ''; 
			foreach ($fields as $fieldName)
				if (isset($item[$fieldName]))
					$fieldTitle .= ($fieldTitle ? ' ' : '').$item[$fieldName];
			$fieldTitle .= ' ('.$item['id'].')';
			$text .= '<tr>
<td><a href="javascript:void(0)" rel="'.$item['id'].'" class="popup-item">'.$fieldTitle.'</a></td>
</tr>';
		}
		$text .= '</table>';
		$text .= $paginator->render();
		$text .= '</div>';
		return json_encode( array(
			'title' => 'Выбор: '.$field['title'], 
			'button' => '<a class="btn btn-success" onclick="makePopupChoice(\''.$inputId.'\')">Выбрать</a>',
			'content' => $text
		));
	}
	
	function showPage($divId, $tableName, $fieldName, $entityId, $page = 1) {
		$table = $this->get('container')->getTable($tableName);
		$field = $table->fields[$fieldName];
		$text = '<table class="table table-condensed">
<thead><tr>
<th>Название</th>
</tr></thead>';
		$where = '';
		if (!empty($field['l_lang'])) {
			$where = "locale='".$this->get('router')->getParam('locale')."'";
		}
		$paginator = $this->get('paginator');
		$paginator->paginate($this->get('container')->getTable($field['l_table']), 'javascript:showPage(\''.$divId.'\',\''.$tableName.'\', \''.$fieldName.'\', '.$entityId.', ###)', $where, 8, $page, 6);
		$items = $this->get('container')->getItems($field['l_table'], $where, $field['l_field'], $paginator->limit);
		$fields = explode(',', $field['l_field']);
		foreach ($items as $item) {
			$fieldTitle = ''; 
			foreach ($fields as $fieldName)
				if (isset($item[$fieldName]))
					$fieldTitle .= ($fieldTitle ? ' ' : '').$item[$fieldName];
			$fieldTitle .= ' ('.$item['id'].')';
			$text .= '<tr>
<td><a href="javascript:void(0)" rel="'.$item['id'].'" class="popup-item">'.$fieldTitle.'</a></td>
</tr>';
		}
		$text .= '</table>';
		$text .= $paginator->render();
		return json_encode( array(
			'content' => $text
		));
	}
	
	// Выбор из дерева разделов
	function showTreePopup($inputId, $tableName, $fieldName, $entityId, $title) {
		$table = $this->get('container')->getTable($tableName);
		$fieldName = str_replace($entityId, '', $fieldName);
		$fieldName = str_replace('search_filter_', '', $fieldName);
		$field = $table->fields[$fieldName];
		$text = '<input type="hidden" id="popupChoiceId" value="'.$entityId.'">
Выбранный элемент: <span id="popupChoiceTitle">'.$title.'</span>
<ul id="navigation">
<li><a href="javascript:void(0)" rel="0" class="popup-item">Не выбрано</a></li>';
		if (!empty($field['l_lang'])) {
			$lang_where = "locale='".$this->get('router')->getParam('locale')."'";
		} else {
			$lang_where = '';
		}
		$field['l_sort'] = !empty($field['l_sort']) ? $field['l_sort'] : $field['l_field'];
		
		$nodes = $this->get('container')->getItems($field['l_table'], $lang_where, $field['l_sort']);
		$rootNodes = array();
		$readyNodes = array();
		foreach ($nodes as $node) {
			$node['children'] = array();
			$readyNodes[$node['id']] = $node;
		}
		foreach ($readyNodes as $node) {
			if ($node['parent_id'] == 0) {
				$rootNodes[$node['id']] = $node;
			} elseif (isset($readyNodes[$node['parent_id']])) {
				$readyNodes[$node['parent_id']]['children'][$node['id']] = $node;
			}
			
		}
		foreach ($rootNodes as $node) {
			$text .= $this->buildTree($node, $readyNodes, $field);
		}
		$text .= '</ul>';
		return json_encode( array(
			'title' => 'Выбор: '.$field['title'], 
			'button' => '<a class="btn btn-success" onclick="makePopupChoice(\''.$inputId.'\')">Выбрать</a>',
			'content' => $text
		));
	}
	
	private function buildTree($node, $nodes, $field) {
		$fields = explode(',', $field['l_field']);
		$vname = '';
		foreach ($fields as $fieldName)
			if (isset($node[$fieldName]))
				$vname .= ($vname ? ' ' : '').$node[$fieldName];
		$text = '<li><a rel="'.$node['id'].'" href="javascript:void(0)" class="popup-item">'.$vname.' ('.$node['id'].')</a>';
//		$this->counter++;
		$children = $nodes[$node['id']]['children'];
		if (count($children)) {
			$text .= '<ul>'; 
			foreach($children as $child) {
				$text .= $this->buildTree($child, $nodes, $field);
			}
			$text .= '</ul>';
		}	
		$text .= '</li>';
		return $text;
	}
	
	// Множественный выбор
	function showListPopup($inputId, $tableName, $fieldName, $value) {
		$values = explode(',', $value);
		$table = $this->get('container')->getTable($tableName);
		$field = $table->fields[$fieldName];
		$text = '<table class="table table-condensed">
<thead><tr>
<th>Название</th>
<th><i class="icon icon-align-justify"></i></th>
</tr></thead>';
		$text .= $this->getPopupList($field, $values);
		$text .= '</table>';
		
		return json_encode( array(
			'title' => 'Выбор: '.$field['title'], 
			'button' => '<a class="btn btn-success" onclick="makeListChoice('."'".$inputId."'".')">Выбрать</a>',
			'content' => $text
		));
	}
	
    function getPopupList($field, $values) {
		$content = '';
		$lang_where = !empty($field['l_lang']) ? "locale='".$this->get('router')->getParam('locale')."'" : '';
		if (!empty($field['query'])) {
			$lang_where .= ($lang_where ? ' AND ' : '').'('.$field['query'].')';
		}
		$field['l_sort'] = !empty($field['l_sort']) ? $field['l_sort'] : $field['l_field'];
        $items = $this->get('container')->getItems($field["l_table"], $lang_where, $field["l_sort"]);
		$fields = explode(",", $field["l_field"]);
        foreach ($items as $item) {
			$fullName = '';
			foreach ($fields as $fieldName) {
				if (array_key_exists($fieldName, $item)) {
					$fullName .= ($fullName ? ' ' : '').$item[$fieldName];
				}
			}
			$content .= '
<tr>
<td width="93%" valign="center"><span id="itemTitle'.$item['id'].'">'.$fullName.' ('.$item['id'].')</span></td>
<td width="3%"><input class="popup-item" value="'.$item['id'].'" type="checkbox"';
			if (in_array($item['id'], $values)) {
				$content .= ' checked';
			}
        $content .= '></td>
</tr>';
        }
		return $content;
    }
	
	// Окно с версиями шаблона
	function showTemplateVersion($version_id) {
		$version = $this->get('container')->getItem('template_version', $version_id);
		$text = @file_get_contents(PRJ_DIR.'/app/Resources/views/backup/'.$version['file']);
		return json_encode( array(
			'title' => 'Версия шаблона', 
			'button' => '<a class="btn btn-default" data-dismiss="modal" aria-hidden="true">Закрыть</a>',
			'content' => '<div><pre>'.htmlspecialchars($text).'</pre></div>'
		));
	}
	
	// Настройки копирования элемента
	function showCopyDialog($id) {
		return json_encode( array(
			'title' => 'Копирование элемента', 
			'button' => '<a class="btn btn-default" data-dismiss="modal" aria-hidden="true">Закрыть</a><a class="btn btn-success" onclick="goCopy(\'/copy/'.$id.'\')">Копировать</a>',
			'content' => '
<div class="control-group" id="copyInput">
  <label class="control-label" for="inputError">Количество новых (1-10)</label>
  <div class="controls">
    <input type="text" name="quantity" id="copyQuantity" value="1">
    <span class="help-inline" id="copyHelp"></span>
  </div>
</div>'
		));
	}
	
	// старая новая разработка - неживое
	function editField($fieldId, $formdata) {
		if (count($formdata)) {
			return json_encode( array(
				'title' => 'Редактирование поля: '.$field['title'], 
				'button' => '<a class="btn btn-default" data-dismiss="modal" aria-hidden="true">Закрыть</a><a class="btn btn-success" onclick="updateField()">Сохранить</a>',
				'content' => '<textarea wrap="off" name="mytemplatetemp" readonly style="height:99%; width:100%" rows="15" cols="45">'.htmlspecialchars($text).'</textarea>'
			));
		} else {
			return json_encode(array('alertText' => 'Все плохо :)'));
		}
	}
	
	public function createArchive() {
		$my_time = time();
		$my_key = $this->get('util')->genKey(8);
		
		$filename = date('YmdHi',$my_time).'_'.$my_key.'.tar.gz';
		$filename_sql = date('YmdHi',$my_time).'_'.$my_key.'.sql';
		$filename_sql2 = date('YmdHi',$my_time).'_'.$my_key.'_after_connect.sql';
		$f = fopen(BACKUP_DIR.DIRECTORY_SEPARATOR.$filename_sql2, "a");
		fwrite($f, "/*!41000 SET NAMES 'utf8' */;");
		fclose($f);
		set_time_limit(0);
		$this->get('container')->backupDB(BACKUP_DIR.DIRECTORY_SEPARATOR.$filename_sql);
		$cwd = getcwd();
		chdir(PRJ_DIR.'/');
		system('tar -czf '.BACKUP_DIR.'/'.$filename.' --exclude=*.lock --exclude=autoload.php --exclude=*.tar.gz --exclude=./vendor/bin --exclude=./vendor/composer --exclude=./vendor/doctrine --exclude=./vendor/symfony --exclude=./vendor/twig --exclude=./.git --exclude=*.tpl.php ./');
		chdir($cwd);
		if (file_exists(BACKUP_DIR.'/'.$filename)) {
			chmod(BACKUP_DIR.'/'.$filename, 0664);
		}
		$text = '';
		$text .= '<strong>Архив создан</strong><br>';
//		$text .= 'Количество файлов: '.$cfiles;
//		$text .= '<br>';
//		$text .= 'Размер неупакованых файлов: '.$this->get('util')->getSize($sfiles, 2);
//		$text .= '<br>';
		$text .= 'Размер архива: '.$this->get('filestorage')->size(BACKUP_REF.'/'.$filename);
		@unlink(BACKUP_DIR.'/'.$filename_sql);
		@unlink(BACKUP_DIR.'/'.$filename_sql2);
		$_SESSION['archiveReport'] = $text;
		return json_encode(array('content' => $text));
	}
	
	public function clearCache() {
		$this->get('templating')->clearTpl();
//		$this->get('templating')->clearCache();
		return json_encode(array('content' => 'Кэш очищен'));
	}
	
	
	function gallerydelete($id) {
		$sql = "SELECT * FROM system_files WHERE id= :id ";
		$stmt = $this->get('connection')->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->execute();
		$file = $stmt->fetch();
		if ($file) {
			$field = $this->get('container')->getTable($file['table_name'])->fields[$file['field_name']];
			list($key, $sizes) = explode(':', $field['params']);
			$this->get('imagestorage')->setOptions(array('sizes' => 'default|50x50xadaptive,'.$sizes));
			$this->get('imagestorage')->remove($file['file']);
			$this->get('connection')->delete('system_files', array('id' => $id));
			return json_encode(array('status' => 'ok'));
		} else {
			return json_encode(array('alertText' => 'Ошибка удаления файла'));
		}
	}
	
	public function updateRpp($tableName, $rpp = 25) {
		$_SESSION[$tableName.'_rpp'] = $rpp;
			
		return json_encode(array('status' => $_SESSION[$tableName.'_rpp']));
	}
	
}