<?php

namespace Fuga\AdminBundle;

use Fuga\CommonBundle\Controller\Controller;
use Fuga\AdminBundle\Controller\AdminController;
use Fuga\AdminBundle\Admin\Admin;

class AdminInterface extends Controller {
	
	private $currentModuleName;
	private $currentModule;
	private $currentState;
	private $modules = array();
	private $states = array(
		'content' => 'Структура и контент',
		'service' => 'Сервисы',
		'system'  => 'Настройки',
	);
	
	public function __construct() {
		$this->currentModuleName = $this->get('router')->getParam('module');
		$this->currentState = $this->get('router')->getParam('state');
		$this->setModules();
		 
	}
	
	private function setModules() {
		$modules = $this->get('container')->getModulesByState($this->currentState);
		if (count($modules)) {
			$basePath = PRJ_REF.'/bundles/admin/img/module/';
			foreach ($modules as $module) {
				if ($module['name'] == $this->currentModuleName) {
					$this->currentModule = new AdminController(new Admin($module['name']), $module['title'], array($this->get('util')->session('user', false, 'veryunknownuser') => 1));
					$tablelist = $this->currentModule->getTableMenu();
				} else {
					$tablelist = '';
				}
				$this->modules[] = array(
					'name' => $module['name'],
					'title' => $module['title'],
					'ref' => $this->getBaseRef($module['name']),
					'icon' => (file_exists(PRJ_DIR.$basePath.$module['name'].'.gif') ? $basePath.$module['name'] : $basePath.'folder').'.gif',
					'tablelist' => $tablelist,
					'current' => $module['name'] == $this->currentModuleName
				);	
			}
		} else {
			$this->get('router')->redirect('/');
		}	
	}

	public function getModule($moduleName) {
		if (isset($this->modules[$moduleName]) && $this->modules[$moduleName]->isAvailable()){
			return $this->modules[$moduleName];
		} else {
			throw new \Exception('Отсутствует запрашиваемый модуль: '.$moduleName);
		}
	}

	public function getBaseRef($moduleName) {
		return $this->currentState.'/'.$moduleName.'/';
	}

	private function backupAction() {
		$file = $this->get('util')->request('file', false, 'empty.file');
		$filename = BACKUP_DIR.'/'.$file;
		$sfilename = $file;
		if (!file_exists($filename)) {
			header ("HTTP/1.0 404 Not Found");
			die();
		}
		// сообщаем размер файла
		header( 'Content-Length: '.filesize($filename) );
		// дата модификации файла для кеширования
		header( 'Last-Modified: '.date("D, d M Y H:i:s T", filemtime($filename)) );
		// сообщаем тип данных - zip-архив
		header('Content-type: text/rtf');
		// файл будет получен с именем $filename
		header('Content-Disposition: attachment; filename="'.$sfilename.'"');
		// начинаем передачу содержимого файла
		$handle = fopen($filename, 'rb');
		while (!feof($handle)) {
			echo fread($handle, 8192);
		}
		fclose($handle);
	}
	
	public function restoreAction() {
		$filepath = $_SERVER['DOCUMENT_ROOT'].'/app/restore.php';
		$filename = 'restore.php';

		if (!file_exists($filepath)) {
		  header ("HTTP/1.0 404 Not Found");
		  die();
		}
		// сообщаем размер файла
		header( 'Content-Length: '.filesize($filepath) );
		// дата модификации файла для кеширования
		header( 'Last-Modified: '.date("D, d M Y H:i:s T", filemtime($filepath)) );
		// сообщаем тип данных - zip-архив
		header('Content-type: text/rtf');
		// файл будет получен с именем $filename
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		// начинаем передачу содержимого файла
		readfile($filepath);
	}

	public function handle() {
		if ($this->get('router')->hasParam('action') && $this->get('router')->getParam('action') == 'backupget') {
			$this->backupAction();
		} elseif ($this->get('router')->hasParam('action') && $this->get('router')->getParam('action') == 'restore') {
			$this->restoreAction();
		} else {
			$params = array(
				'user' => $this->get('security')->getCurrentUser(),
				'locales' => $this->get('router')->getLocales(),
				'currentLocale' => $this->get('router')->getParam('locale'),
				'module' => $this->currentModuleName,
				'modules' => $this->modules,
				'states' => $this->states,
				'state' => $this->currentState,
				'version' => LIB_VERSION,
				'content' => $this->currentModuleName ? $this->currentModule->indexAction() : ''
			);
			echo $this->render('admin/layout.tpl', $params);
		}
	}
	
}
