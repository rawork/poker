<?php

$LIB_VERSION = '5.5.0';
$LIB_DATE = '2013.11.01';

mb_http_input('UTF-8'); 
mb_http_output('UTF-8'); 
mb_internal_encoding("UTF-8");

require_once 'config/config.php';
$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('Fuga', __DIR__.'/../src/');
$loader->add('Smarty', __DIR__.'/../vendor/smarty/');

use Fuga\Component\Container;
use Fuga\Component\Registry;
use Fuga\Component\Exception\AutoloadException;
use Fuga\CommonBundle\Controller\SecurityController;
use Doctrine\DBAL\Types\Type;

Type::addType('money', 'Fuga\Component\DBAL\Types\MoneyType');

$se_mask = "/(Yandex|Googlebot|StackRambler|Yahoo Slurp|WebAlta|msnbot)/";
if (preg_match($se_mask,$_SERVER['HTTP_USER_AGENT']) > 0) {
	if (!empty($_GET[session_name()])) {
		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
		exit();
	}
} else {
	session_start();
}

function exception_handler($exception) 
{	
	$statusCode = $exception instanceof Fuga\Component\Exception\NotFoundHttpException 
			? $exception->getStatusCode() 
			: 500;
	$controller = new Fuga\CommonBundle\Controller\ExceptionController();
	
	echo $controller->indexAction($statusCode, $exception->getMessage());
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

if ($_SERVER['SCRIPT_NAME'] != '/restore.php' && file_exists('/../restore.php')) {
	throw new \Exception('Удалите файл restore.php в корне сайта');
}

// ID запрашиваемой страницы
$GLOBALS['cur_page_id'] = preg_replace('/(\/|-|\.|:|\?|[|])/', '_', str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));

//Registry::init('app/config/parameters.yml');

$container = new Container($loader);

// инициализация переменных
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

// Включаем Роутер запросов
$container->get('router')->setLocale();
$container->get('router')->setParams();

if (!$container->get('security')->isAuthenticated() && $container->get('security')->isSecuredArea()) {
	$controller = new SecurityController();
	echo $controller->loginAction();
	exit;
} elseif (preg_match('/^'.PRJ_REF.'\/admin\/(logout|forget|password)/', $_SERVER['REQUEST_URI'], $matches)) {
	$controller = new SecurityController();
	$methodName = $matches[1].'Action';
	echo $controller->$methodName();
	exit;
}

// TODO убрать инициализацию всех таблиц 
$container->initialize();