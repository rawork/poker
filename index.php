<?php

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
	$frontcontroller = new PageController();
	$frontcontroller->handle();
}
