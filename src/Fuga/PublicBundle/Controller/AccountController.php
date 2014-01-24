<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class AccountController extends PublicController {
	
	public function __construct() {
		parent::__construct('account');
	}
	
	public function indexAction() {
		$user = $this->get('security')->getCurrentUser();
		$members = $this->get('container')->getitems('account_member', '1=1');
		$this->get('container')->setVar('title', 'Участники клуба');
		$this->get('container')->setVar('h1', 'Участники клуба');
		
		return $this->render('account/index.tpl', compact('user', 'members'));
	}
	
	public function cabinetAction() {
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return $this->call('Fuga:Public:Account:login');
		}
		
		$group = $this->get('container')->getItem('user_group', $user['group_id']);
		$account = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
		$this->get('container')->setVar('title', 'Личный кабинет');
		$this->get('container')->setVar('h1', 'Личный кабинет');
		
		return $this->render('account/cabinet.tpl', compact('account', 'user', 'group'));
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
			
			$url = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/members/cabinet';
			$this->get('router')->redirect($url);
		}
		$message = $this->flash('danger');
		$this->get('container')->setVar('title', 'Вход на сайт');
		$this->get('container')->setVar('h1', 'Вход на сайт');
		
		return $this->render('account/login.tpl', compact('message'));
	}
	
	public function logoutAction() {
		$this->get('security')->logout();
		if (empty($_SERVER['HTTP_REFERER']) || preg_match('/^'.PRJ_REF.'\/members\/logout/', $_SERVER['HTTP_REFERER'])) {
			$uri = PRJ_REF.'/members';
		} else {
			$uri = $_SERVER['HTTP_REFERER'];
		}
		$this->get('router')->redirect($uri);
	}
	
	public function registerAction() {
		$user = $this->get('security')->getCurrentUser();
		if ($user) {
			$this->get('router')->redirect('/members');
		}
		
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			$user = array();
			$user['group_id'] = $this->get('util')->post('group_id');
			$user['login']    = $this->get('util')->post('login');
			$user['password'] = $this->get('util')->post('password');
			$user['name']     = $this->get('util')->post('name');
			$user['lastname'] = $this->get('util')->post('lastname');
			
			$account['sbe']      = $this->get('util')->post('sbe');
			$account['city']     = $this->get('util')->post('city');
			$account['position'] = $this->get('util')->post('position');
			$account['slogan']   = $this->get('util')->post('slogan');
			$account['name']     = $this->get('util')->post('name');
			$account['lastname'] = $this->get('util')->post('lastname');
			
			$errors = array();
			if (empty($user['group_id']) || !in_array($user['group_id'], array(2,3))) {
				$user['group_id'] = 3;
			}
			if (empty($user['name'])) {
				$errors[] = 'Не заполнено имя';
			}
			if (empty($user['lastname'])) {
				$errors[] = 'Не заполнена фамилия';
			}
			if (empty($user['login'])) {
				$errors[] = 'Не заполнен логин';
			} elseif (!$this->get('util')->isEmail($user['login'])) {
				$errors[] = 'Логин не является e-mail';
			}
			if ($user['login'] && 0 < $this->get('container')->count('user_user', 'login="'.$user['login']."'")) {
				$errors[] = 'Такой логин уже занят. Попробуйте <a href="/members/forget">восстановить пароль</a> или обратиться к администратору клуба';
			}
			if (empty($user['password'])) {
				$errors[] = 'Не заполнен пароль';
			}
			if ($user['password'] != $this->get('util')->post('password_again')) {
				$errors[] = 'Не совпадает проверочный пароль';
			}
			
			if ($errors) {
				$_SESSION['danger'] = implode('<br>', $errors);
				$_SESSION['register'] = json_encode($user);
				$_SESSION['account'] = json_encode($account);
				$this->get('router')->reload();
			} else {
				$userId = $this->get('container')->getTable('user_user')->insertGlobals();
				$this->get('container')->updateItem('user_user',
						array('is_active' => 1, 'email' => $user['login']),
						array('id' => $userId)
				);
				if ($userId) {
					$accountId = $this->get('container')->getTable('account_member')->insertGlobals();
					$this->get('container')->updateItem('account_member',
						array('user_id' => $userId),
						array('id' => $accountId)
					);
				}
					
				$this->get('mailer')->send(
					'Вы зарегистрировались на сайте клуба Чертова дюжина',
					$this->render('mail/register.tpl', compact('user', 'account')),
					$user['login']
				);
				unset($_SESSION['register']);
				unset($_SESSION['account']);
				
				$this->get('router')->redirect('/members/cabinet');
			}
		}
		
		$message = $this->flash('danger');
		$register = json_decode($this->get('util')->session('register'), true);
		$account = json_decode($this->get('util')->session('account'), true);
		$this->get('container')->setVar('title', 'Регистрация');
		$this->get('container')->setVar('h1', 'Регистрация');
		
		return $this->render('account/register.tpl', compact('message', 'register', 'account'));
	}
	
	public function forgetAction() {
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			$email = $this->get('util')->post('email');
			if ($email) {
				$user = $this->get('container')->getItem('user_user', 'login="'.$email.'"');
				
				if ($user) {
					$newPassword = $this->get('util')->genKey(8);
					$this->get('log')->write($email.':'.$newPassword);
					$this->get('container')->updateItem('user_user',
							array('password' => md5($newPassword)),
							array('id' => $user['id'])
					);
					
					$this->get('mailer')->send(
						'Напоминание пароля на сайте клуба Чертова дюжина',
						$this->render('mail/forget.tpl', compact('email', 'newPassword')),
						$user['email']
					);
					$_SESSION['success'] = 'На e-mail '.$email.' выслано письмо с Вашим логином и паролем.';
				} else {
					$_SESSION['danger'] = 'В клубе &laquo;Чертова дюжина&raquo; нет зарегистрированного пользователя с e-mail '.$email.'.<br>Возможно, Вы ошиблись при написании адреса. Попробуйте еще раз.';
				}
			}
			$this->get('router')->reload();
		}
		
		$message = $this->flash('danger') ?: $this->flash('success');
		$this->get('container')->setVar('title', 'Забыли пароль?');
		$this->get('container')->setVar('h1', 'Забыли пароль?');
		
		return $this->render('account/forget.tpl', compact('message'));
	}
	
	public function widgetAction() {
		$user = $this->get('security')->getCurrentUser();
		
		return $this->render('account/widget.tpl', compact('user'));
	}
	
	public function editAction() {
		$user = $this->get('security');
		if (!$user) {
			return $this->call('Fuga:Public:Account:login');
		}
		
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			
		}
		
		$message = $this->flash('danger') ?: $this->flash('success');
		$this->get('container')->setVar('title', 'Редактирование анкеты');
		$this->get('container')->setVar('h1', 'Редактирование анкеты');
		
		return $this->render('account/edit.tpl', compact('user'));
	}
	
	public function testAction() {
		$fh = fopen($_SERVER['DOCUMENT_ROOT']).'/'.'q.txt';
		 
	}
	
}
