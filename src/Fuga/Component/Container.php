<?php

namespace Fuga\Component;

use Fuga\CommonBundle\Security\SecurityHandler;
use Fuga\Component\Exception\NotFoundHttpException;

class Container 
{
	private $tables;
	private $modules = array();
	private $ownmodules = array();
	private $controllers = array();
	private $templateVars = array();
	private $services = array();
	private $managers = array();
	private $tempmodules = array();
	
	private $loader;
	
	public function __construct($loader) {
		$this->loader = $loader;
		$this->tempmodules = array(
			'user' => array(
				'name'  => 'user',
				'title' => 'Пользователи',
				'ctype' => 'system',
				'entitites' => array(
					array(
						'name' => 'user-user',
						'title' => 'Список пользователей'
					),
					array(
						'name' => 'user-group',
						'title' => 'Группы пользователей'
					),
					array(
						'name' => 'user-address',
						'title' => 'Адреса доставки'
					)
				)
			),
			'template' => array(
				'name'  => 'template',
				'title' => 'Шаблоны',
				'ctype' => 'system',
				'entitites' => array(
					array(
						'name' => 'template-template',
						'title' => 'Шаблоны'
					),
					array(
						'name' => 'template-rule',
						'title' => 'Правила шаблонов'
					),
				)
			),
			'config' => array(
				'name'	=> 'config',
				'title' => 'Настройки',
				'ctype' => 'system',
				'entitites' => array(
					array(
						'name' => 'config-module',
						'title' => 'Модули'
					),
					array(
						'name' => 'config-variable',
						'title' => 'Переменные'
					),
					array(
						'name' => 'config-backup',
						'title' => 'Обслуживание'
					),
				)
			),
			'table' => array(
				'name'	=> 'table',
				'title' => 'Таблицы',
				'ctype' => 'system',
				'entitites' => array(
					array(
						'name' => 'table-table',
						'title' => 'Таблицы'
					),
					array(
						'name' => 'table-field',
						'title' => 'Поля'
					),
				)
			),
//			'maillist' => array(
//				'name'  => 'maillist',
//				'title' => 'Подписка',
//				'ctype' => 'service',
//				'entitites' => array()
//			),
			'form' => array(
				'name'  => 'form',
				'title' => 'Формы',
				'ctype' => 'service',
				'entitites' => array()
			),
		);
	}
	
	public function initialize() {
		$this->tables = $this->getAllTables();
	}

	public function getModule($name) {
		if (empty($this->modules[$name])) {
			throw new \Exception('Модуль '.$name.' отсутствует'); 
		}
		
		return $this->modules[$name];
	}

	public function getModules() {
		if (!$this->ownmodules) {
			if ($this->get('security')->isSuperuser()) {
				$this->ownmodules = $this->modules;
			} elseif ($user = $this->get('security')->getCurrentUser()) {
				if (empty($user['rules'])) {
					$user['rules'] = 0;
				}
				$this->ownmodules = $this->tempmodules;
				if (!$user['is_admin']) {
					unset($this->ownmodules['config'], $this->ownmodules['user'], $this->ownmodules['template'], $this->ownmodules['table']);
				}
				$sql = 'SELECT id, sort, name, title, \'content\' AS ctype 
					FROM config_module WHERE id IN ('.$user['rules'].') ORDER BY sort, title';
				$stmt = $this->get('connection')->prepare($sql);
				$stmt->execute();
				$this->ownmodules = array_merge($this->ownmodules, $stmt->fetchAll());
			}
		}
		
		return $this->ownmodules;
	}
	
	public function getModulesByState($state) {
		$modules = array();
		foreach ($this->getModules() as $module) {
			if ($state == $module['ctype']) {
				$modules[$module['name']] = $module;
			}
		}
		
		return $modules;
	}
	
	private function getAllTables() {
		$ret = array();
		$this->modules = $this->tempmodules;
		$sql = "SELECT id, sort, name, title, 'content' AS ctype FROM config_module ORDER BY sort, title";
		$stmt = $this->get('connection')->prepare($sql);
		$stmt->execute();
		while ($module = $stmt->fetch()) {
			$this->modules[$module['name']] = array(
				'id'    => $module['id'],
				'name'  => $module['name'],
				'title' => $module['title'],
				'ctype' => $module['ctype'],
				'entities' => array()
			);
		}
		foreach ($this->modules as $module) {
			try {
				$className = 'Fuga\\CommonBundle\\Model\\'.ucfirst($module['name']);
				$model = new $className();
				foreach ($model->tables as $table) {
					$table['is_system'] = true;
					$ret[$table['module'].'_'.$table['name']] = new DB\Table($table);
				}
			} catch (Exception\AutoloadException $e) {
				
			}
		}
		$sql = "SELECT t.*, m.name as module 
				FROM table_table t 
				JOIN config_module m ON t.module_id=m.id 
				WHERE t.publish=1 ORDER BY t.sort";
		$stmt = $this->get('connection')->prepare($sql);
		$stmt->execute();
		$tables = $stmt->fetchAll();
		foreach ($tables as $table) {
			$ret[$table['module'].'_'.$table['name']] = new DB\Table($table);
		}
		return $ret;
	}

	public function getTable($name) {
		if (!$this->tables) {
			$this->tables = $this->getAllTables();
		}
		
		if (isset($this->tables[$name])) {
			return $this->tables[$name];
		} else {
			throw new \Exception('Таблица "'.$name.'" не существует');
		}
	}

	public function getTables($moduleName) {
		$tables = array();
		foreach ($this->tables as $table) {
			if ($table->moduleName == $moduleName)
				$tables[$table->tableName] = $table;
		}
		return $tables;
	}
	
	public function getPrev($table, $id, $link = 'parent_id') {
		return $this->getTable($table)->getPrev($id, $link);
	}

	public function getItem($table, $criteria = 0, $sort = null, $select = null) {
		return $this->getTable($table)->getItem($criteria, $sort, $select);
	}

	public function getItems($table, $criteria = null, $sort = null, $limit = null, $select = null, $detailed = true) {
		$options = array('where' => $criteria, 'order_by' => $sort, 'limit' => $limit, 'select' => $select);
		$this->getTable($table)->select($options);
		return $this->getTable($table)->getNextArrays($detailed);
	}

	public function getItemsRaw($sql) {
		$ret = array();
		if (!preg_match('/(delete|truncate|update|insert|drop|alter)+/i', $sql)) {
			$stmt = $this->get('connection')->prepare($sql);
			$stmt->execute();
			$items = $stmt->fetchAll();
			foreach ($items as $item) {
				if (isset($item['id'])) {
					$ret[$item['id']] = $item;
				} else {
					$ret[] = $item;
				}
			}
		}
		return $ret;
	}

	public function getItemRaw($sql) {
		$ret = null;
		if (!preg_match('/(delete|truncate|update|insert|drop|alter)+/i', $sql)) {
			$stmt = $this->get('connection')->prepare($sql);
			$stmt->execute();
			$ret = $stmt->fetch();
		}
		
		return $ret;
	}

	public function count($table, $criteria = '') {
		return $this->getTable($table)->count($criteria);
	}

	public function addItem($class, $values) {
		return $this->getTable($class)->insert($values);
	}

	public function addItemGlobal($class) {
		return $this->getTable($class)->insertGlobals();
	}

	public function updateItem($table, $values, $criteria) {
		return $this->getTable($table)->update($values, $criteria);
	}

	public function deleteItem($table, $query) {
		if ($ids = $this->delRel($table, $this->getItems($table, !empty($query) ? $query : '1<>1'))) {
			return $this->getTable($table)->delete('id IN ('.$ids.')');
		} else {
			return false;
		}	
	}

	public function delRel($table, $items = array()) {
		$ids = array();
		foreach ($items as $item) {
			if ($this->getTable($table)->params['is_system']) {
				foreach ($this->tables as $t) {
					if ($t->moduleName != 'user' && $t->moduleName != 'template' && $t->moduleName != 'page') {
						foreach ($t->fields as $field) {
							$ft = $t->getFieldType($field);
							if (stristr($ft->getParam('type'), 'select') && $ft->getParam('l_table') == $table) {
								$this->deleteItem($t->dbName(), $ft->getName().'='.$item['id']);
							}
							$ft->free();
						}
					}
				}
			}
			foreach ($this->getTable($table)->fields as $field) {
				$this->getTable($table)->getFieldType($field, $item)->free();
			}
			$ids[] = $item['id'];
		}
		return implode(',', $ids);
	}

	public function copyItem($table, $id = 0, $times = 1) {
		$entity = $this->getItem($table, $id);
		if ($entity) {
			for ($i = 1; $i <= $times; $i++)
				$this->getTable($table)->insertArray($entity);
			return true;
		} else {
			return false;
		}
	}

	public function dropTable($table, $complex = false) {
		if ($complex) {
			$this->get('connection')->delete('table_field', array('table_id' => $this->getTable($table)->id));
			$this->get('connection')->delete('table_table', array('name' => $table));
		}
		return $this->get('connection')->query('DROP TABLE '.$table);
	}

	public function truncateTable($table) {
		return $this->get('connection')->query('DROP TRUNCATE '.$table);
	}
	
	public function backupDB($filename) {
		$cwd = getcwd();
		chdir(dirname($filename));
		system('mysqldump -u '.$GLOBALS['DB_USER']. ' -p'.$GLOBALS['DB_PASS'].' -h '.$GLOBALS['DB_HOST'].' '.$GLOBALS['DB_BASE'].' > '.basename($filename));
		chdir($cwd);
		return true;
	}
	
	public function getControllerClass($path) {
		list($vendor, $bundle, $name) = explode(':', $path);
		return $vendor.'\\'.$bundle.'Bundle\\Controller\\'.ucfirst($name).'Controller';
	}
	
	public function createController($path) {
		if (!isset($this->controllers[$path])) {
			$className = $this->getControllerClass($path);
			$this->controllers[$path] = new $className();
		}
		return $this->controllers[$path];
	}

	public function callAction($path, $params = array()) {
		list($vendor, $bundle, $name, $action) = explode(':', $path);
		$obj = new \ReflectionClass($this->getControllerClass($path));
		$action .= 'Action'; 	
		if (!$obj->hasMethod($action)) {
			$this->get('log')->addError('Не найден обработчик ссылки '.$path);
			throw new NotFoundHttpException('Несуществующая страница');
		}
		return $obj->getMethod($action)->invoke($this->createController($path), $params);	
	}

	public function setVar($name, $value) {
		$this->templateVars[$name] = $value;
	}

	public function getVars() {
		return $this->templateVars;	
	}

	public function register($name, $service) {
		$this->services[$name] = $service;
		return $service;
	}

	public function get($name) {
		if (!isset($this->services[$name])) {
			switch ($name) {
				case 'log':
					$log = new \Monolog\Logger('fuga');
					$log->pushHandler(new \Monolog\Handler\StreamHandler(
							PRJ_DIR.'/app/logs/error.log', 
							PRJ_ENV == 'development' ? \Monolog\Logger::DEBUG : \Monolog\Logger::ERROR
						));
					
					$this->services[$name] = $log;
					break;
				case 'util':
					$this->services[$name] = new Util();
					break;
				case 'templating':
					$this->services[$name] = new Templating\SmartyTemplating();
					break;
				case 'templating1':
					$this->services[$name] = new Templating\TwigTemplating();
					break;
				case 'em':
					$isDevMode = true;
					$config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(array(__DIR__."/../src/Fuga/GameBundle/Entity"), $isDevMode);
					$conn = array(
						'dbname'	=> DB_BASE,
						'user'		=> DB_USER,
						'password'	=> DB_PASS,
						'host'		=> DB_HOST,
						'driver'	=> DB_TYPE,
						'charset'	=> 'utf8',
						'collate'   => 'utf8_general_ci',
						'driverOptions' => array(
							1002=>'SET NAMES utf8'
						)
					);
					
					$this->services[$name] = \Doctrine\ORM\EntityManager::create($conn, $config);
					break;
				case 'connection':
					\Doctrine\DBAL\Types\Type::addType('money', 'Fuga\Component\DBAL\Types\MoneyType');
					$config = new \Doctrine\DBAL\Configuration();
					$conn = array(
						'dbname'	=> DB_BASE,
						'user'		=> DB_USER,
						'password'	=> DB_PASS,
						'host'		=> DB_HOST,
						'driver'	=> DB_TYPE,
						'charset'	=> 'utf8',
						'collate'   => 'utf8_general_ci',
						'driverOptions' => array(
							1002=>'SET NAMES utf8'
						)
					);
					$this->services[$name] = \Doctrine\DBAL\DriverManager::getConnection($conn, $config);
					$this->services[$name]->getDatabasePlatform()->registerDoctrineTypeMapping('DECIMAL(14,2)', 'money');
					break;
				case 'odm':
					$this->loader->add('Document', __DIR__.'/../GameBundle');
					
					$connection = new \Doctrine\MongoDB\Connection(sprintf("mongodb://%s:%s@%s:%s/%s", MONGO_USER, MONGO_PASS, MONGO_HOST, MONGO_PORT, MONGO_BASE));
					$config = new \Doctrine\ODM\MongoDB\Configuration();
					
					$config->setProxyDir(__DIR__ . '/../../../app/cache/proxies');
					$config->setProxyNamespace('Proxies');
					$config->setHydratorDir(__DIR__ . '/../../../app/cache/hydrators');
					$config->setHydratorNamespace('Hydrators');
					$config->setAutoGenerateProxyClasses('development' == PRJ_ENV);
					$config->getAutoGenerateHydratorClasses('development' == PRJ_ENV);
					$config->setDefaultDB(MONGO_BASE);
					
					$config->setMetadataDriverImpl(\Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::create(__DIR__ . '/../GameBundle/Document'));

					\Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();
					
					$this->services[$name] = \Doctrine\ODM\MongoDB\DocumentManager::create($connection, $config);
					break;
				case 'mongo':
					$mongo = new \MongoClient(sprintf("mongodb://%s:%s@%s/%s", MONGO_USER, MONGO_PASS, MONGO_HOST, MONGO_BASE));
					$this->services[$name] = $mongo->selectDB('holdem');
					break;
				case 'filestorage':
					$this->services[$name] = new Storage\FileStorage(UPLOAD_REF, UPLOAD_DIR);
					break;
				case 'imagestorage':
					$this->services[$name] = new Storage\ImageStorageDecorator($this->get('filestorage'));
					break;
				case 'translator':
					$this->services[$name] = new Translator($this->get('router')->getParam('locale'));
					break;
				case 'paginator':
					$this->services[$name] = new Paginator($this->get('templating'));
					break;
				case 'mailer':
					$this->services[$name] = new Mailer\Mailer();
					break;
				case 'scheduler':
					$this->services[$name] = new Scheduler\Scheduler();
					break;
				case 'search':
					$this->services[$name] = new Search\SearchEngine($this);
					break;
				case 'router':
					$this->services[$name] = new Router($this);
					break;
				case 'security':
					$this->services[$name] = new SecurityHandler($this);
					break;
				case 'cache':
					$this->services[$name] = new \Doctrine\Common\Cache\ApcCache();
					break;
			}	
		}
		if (!isset($this->services[$name])) {
			throw new \Exception('Cлужба "'.$name.'" отсутствует');
		}
		
		return $this->services[$name];
	}
	
	public function getManager($path) {
		if (!isset($this->managers[$path])) {
			list($vendor, $bundle, $name) = explode(':', $path);
			$className = $vendor.'\\'.$bundle.'Bundle\\Model\\'.ucfirst($name).'Manager';
			$this->managers[$path] = new $className();
		}

		return $this->managers[$path];
	}
	
}
