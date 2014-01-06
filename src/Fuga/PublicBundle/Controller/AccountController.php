<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class AccountController extends PublicController {
	
	public function __construct() {
		parent::__construct('account');
	}
	
	public function indexAction() {
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return $this->call('Fuga:Public:Account:login');
		}
		
		$account = $this->get('container')->getItem('account_gamer', 'user_id='.$user['id']);
		
		return $this->render('account/index.tpl', compact('account'));
	}
	
	public function loginAction() {
		$message = null;
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			$login = $this->get('util')->post('_user');
			$password = $this->get('util')->post('_password');
			$is_remember = $this->get('util')->post('_remember_me');
			
			if (!$login || !$password){
				$_SESSION['danger'] = 'Неверный Логин или Пароль';
			} elseif ($this->get('security')->isServer()) {
				if (!$this->get('security')->login($login, $password, $is_remember)) {
					$_SESSION['danger'] = 'Неверный Логин или Пароль';
				}
			}
			
			$url = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/account';
			$this->get('router')->redirect($url);
		}
		$message = $this->flash('danger');
		
		return $this->render('account/login.tpl', compact('message'));
	}
	
	public function logoutAction() {
		$this->get('security')->logout();
		if (empty($_SERVER['HTTP_REFERER']) || preg_match('/^'.PRJ_REF.'\/account\/logout/', $_SERVER['HTTP_REFERER'])) {
			$uri = PRJ_REF.'/account';
		} else {
			$uri = $_SERVER['HTTP_REFERER'];
		}
		$this->get('router')->redirect($uri);
	}
	
}
