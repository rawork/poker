<?php

define('LIB_VERSION', '6.0.1');
define('LIB_DATE', '2014.02.11');

mb_http_input('UTF-8'); 
mb_http_output('UTF-8'); 
mb_internal_encoding("UTF-8");

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('Fuga', __DIR__.'/../src/');
$loader->add('Smarty', __DIR__.'/../vendor/smarty/');

use Fuga\Component\Container;
use Fuga\Component\Registry;
use Fuga\Component\Exception\AutoloadException;
use Fuga\CommonBundle\Controller\SecurityController;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

function exception_handler($exception) 
{	
	$statusCode = $exception instanceof \Fuga\Component\Exception\NotFoundHttpException 
			? $exception->getStatusCode() 
			: 500;
	if (isset($_SERVER['REQUEST_URI'])) {
		$controller = new Fuga\CommonBundle\Controller\ExceptionController();
		echo $controller->indexAction($statusCode, $exception->getMessage());
	} else {
		echo $exception->getMessage();
	}
}

function autoloader($className)
{
	if ($className == 'Smarty') {
		require_once(__DIR__.'/../vendor/smarty/Smarty.class.php');
	} else {
		$basePath = __DIR__.'/../src/';
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		if (file_exists($basePath.$fileName)) {
			require_once $basePath.$fileName;
		} else {
			// TODO LOG + nice error text
			throw new AutoloadException('Не возможно загрузить класс "'.$fileName.'"');
		}
	}	
}

set_exception_handler('exception_handler');
spl_autoload_register('autoloader');

$container = new Container($loader);

// инициализация переменных
if (isset($_SERVER['REQUEST_URI'])) {
	require_once 'config/config.php';
	$params = array();
	$sql = 'SELECT name, value FROM config_variable';
	$stmt = $container->get('connection')->prepare($sql);
	$stmt->execute();
	$vars = $stmt->fetchAll();
	foreach ($vars as $var) {
		$params[strtolower($var['name'])] = $var['value'];
		define($var['name'], $var['value']);
	}
	$params['prj_ref'] = PRJ_REF;
	$params['theme_ref'] = THEME_REF;
	$container->get('templating')->assign($params);
	
	// TODO убрать инициализацию всех таблиц 
	$container->initialize();
}

