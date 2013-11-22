<?php

use Fuga\AdminBundle\AdminInterface;
use Fuga\CommonBundle\Security\Captcha\KCaptcha;
use Fuga\CommonBundle\Controller\PageController;

if (preg_match('/^\/secureimage\//', $_SERVER['REQUEST_URI'])) {
	require_once($_SERVER['DOCUMENT_ROOT'].'/src/Fuga/CommonBundle/Security/Captcha/KCaptcha.php');
	session_start();
	require_once('app/config/parameters.php');
	$captcha = new KCaptcha();
	$_SESSION['captchaHash'] = md5($captcha->getKeyString().CAPTCHA_KEY);
	exit;
} else {	
	require_once('app/init.php');
	if ($GLOBALS['container']->get('router')->isAdminAjax()) {
		try {
			$controller = $GLOBALS['container']->createController('Fuga:Admin:AdminAjax');
			$obj = new \ReflectionClass($GLOBALS['container']->getControllerClass('Fuga:Admin:AdminAjax'));
			$post = $_POST;
			unset($post['method']);
			echo $obj->getMethod($_POST['method'])->invokeArgs($controller, $post);
		} catch (\Exception $e) {
			$GLOBALS['container']->get('log')->write(json_encode($_POST));
			$GLOBALS['container']->get('log')->write($e->getMessage());
			$GLOBALS['container']->get('log')->write('Trace% '.$e->getTraceAsString());
			echo '';
		}
	} elseif ($GLOBALS['container']->get('router')->isAdmin()) {
		$frontcontroller = new AdminInterface();
		$frontcontroller->handle();
	} else {
		$frontcontroller = new PageController();
		$frontcontroller->handle();
	}
}
