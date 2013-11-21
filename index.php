<?php

use Fuga\AdminBundle\AdminInterface;
use Fuga\CommonBundle\Security\Captcha\KCaptcha;
use Fuga\CommonBundle\Controller\PageController;

use Fuga\PokerBundle\Model\Calculator;
use Fuga\PokerBundle\Model\Deck;

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
		$deck = new Deck();
		$calculator = new Calculator();
		$suite = array(
			array('name' => 'ace hearts', 'suit' => 2, 'weight' => 4096),
			array('name' => '4 diamonds', 'suit' => 1, 'weight' => 4),
			array('name' => 'queen spade', 'suit' => 4, 'weight' => 1024),
			array('name' => '3 diamonds', 'suit' => 1, 'weight' => 2),
			array('name' => '8 hearts', 'suit' => 2, 'weight' => 64),
			array('name' => 'king clubs', 'suit' => 8, 'weight' => 2048),
			array('name' => '2 diamonds', 'suit' => 1, 'weight' => 1),
		);
//		$suite = array(
//			array('name' => 'ace hearts', 'suit' => 2, 'weight' => 4096),
//			array('name' => 'king spade', 'suit' => 4, 'weight' => 2048),
//			array('name' => '10 diamonds', 'suit' => 1, 'weight' => 256),
//			array('name' => '9 diamonds', 'suit' => 1, 'weight' => 128),
//			array('name' => 'joker', 'suit' => 0, 'weight' => 8192),
//			array('name' => '5 clubs', 'suit' => 8, 'weight' => 8),
//			array('name' => '2 diamonds', 'suit' => 1, 'weight' => 1),
//		);
//		$suite = array(
//			array('name' => 'ace hearts', 'suit' => 2, 'weight' => 4096),
//			array('name' => '4 diamonds', 'suit' => 1, 'weight' => 4),
//			array('name' => '2 clubs', 'suit' => 8, 'weight' => 1),
//			array('name' => '9 diamonds', 'suit' => 1, 'weight' => 128),
//			array('name' => '10 hearts', 'suit' => 2, 'weight' => 256),
//			array('name' => 'ace clubs', 'suit' => 8, 'weight' => 4096),
//			array('name' => '2 diamonds', 'suit' => 1, 'weight' => 1),
//		);
//		$suite = array(
//			array('name' => 'ace hearts', 'suit' => 2, 'weight' => 4096),
//			array('name' => '4 diamonds', 'suit' => 1, 'weight' => 4),
//			array('name' => 'joker', 'suit' => 0, 'weight' => 8192),
//			array('name' => '3 diamonds', 'suit' => 1, 'weight' => 2),
//			array('name' => '4 hearts', 'suit' => 2, 'weight' => 4),
//			array('name' => '2 clubs', 'suit' => 8, 'weight' => 1),
//			array('name' => '2 diamonds', 'suit' => 1, 'weight' => 1),
//		);
//		$suite = array(
//			array('name' => 'ace hearts', 'suit' => 2, 'weight' => 4096),
//			array('name' => '4 diamonds', 'suit' => 1, 'weight' => 4),
//			array('name' => 'joker', 'suit' => 0, 'weight' => 8192),
//			array('name' => '2 spade', 'suit' => 4, 'weight' => 1),
//			array('name' => '2 hearts', 'suit' => 2, 'weight' => 1),
//			array('name' => '2 clubs', 'suit' => 8, 'weight' => 1),
//			array('name' => '2 diamonds', 'suit' => 1, 'weight' => 1),
//		);
//		$suite = array(
//			array('name' => 'ace hearts', 'suit' => 2, 'weight' => 4096),
//			array('name' => '8 spade', 'suit' => 4, 'weight' => 64),
//			array('name' => 'queen diamonds', 'suit' => 1, 'weight' => 1024),
//			array('name' => 'jack diamonds', 'suit' => 1, 'weight' => 512),
//			array('name' => '2 hearts', 'suit' => 2, 'weight' => 1),
//			array('name' => '9 diamonds', 'suit' => 1, 'weight' => 128),
//			array('name' => '10 diamonds', 'suit' => 1, 'weight' => 256),
//		);
//		$suite = array(
//			array('name' => '2 diamonds', 'suit' => 1, 'weight' => 1),
//			array('name' => 'king diamonds', 'suit' => 1, 'weight' => 2048),
//			array('name' => 'joker', 'suit' => 0, 'weight' => 8192),
//			array('name' => 'jack diamonds', 'suit' => 1, 'weight' => 512),
//			array('name' => '2 hearts', 'suit' => 2, 'weight' => 1),
//			array('name' => '9 diamonds', 'suit' => 1, 'weight' => 128),
//			array('name' => '10 diamonds', 'suit' => 1, 'weight' => 256),
//		);
//		$suite = array(
//			array('name' => 'queen diamonds', 'suit' => 1, 'weight' => 1024),
//			array('name' => 'king diamonds', 'suit' => 1, 'weight' => 2048),
//			array('name' => 'joker', 'suit' => 0, 'weight' => 8192),
//			array('name' => 'jack diamonds', 'suit' => 1, 'weight' => 512),
//			array('name' => '2 hearts', 'suit' => 2, 'weight' => 1),
//			array('name' => '9 diamonds', 'suit' => 1, 'weight' => 128),
//			array('name' => '10 diamonds', 'suit' => 1, 'weight' => 256),
//		);
		echo $calculator->checkRank($suite).'<br>';
		exit;
		$frontcontroller = new PageController();
		$frontcontroller->handle();
	}
}
