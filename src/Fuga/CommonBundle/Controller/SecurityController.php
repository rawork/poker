<?php

namespace Fuga\CommonBundle\Controller;

class SecurityController extends Controller {
	
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
			
			$this->get('router')->reload();
		} 
		$message = $this->flash('danger');
		
		return $this->render('admin/layout.login.tpl', compact('message'));
	}
	
	public function forgetAction() {
		$message = null;
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$login  = $this->get('util')->post('_user');
			$sql = "SELECT id, login, email FROM user_user WHERE login= :login OR email = :login ";
			$stmt = $this->get('connection')->prepare($sql);
			$stmt->bindValue("login", $login);
			$stmt->execute();
			$user = $stmt->fetch();
			if ($user) {
				$key = $this->get('util')->genKey(32);
				$this->get('connection')->update(
						'user_user', 
						array('hashkey' => $key), 
						array('id' => $user['id'])
				);
				$text = 'Информационное сообщение сайта '.$_SERVER['SERVER_NAME']."\n";
				$text .= '------------------------------------------'."\n";
				$text .= 'Вы запросили ваши регистрационные данные.'."\n\n";
				$text .= 'Ваша регистрационная информация:'."\n";
				$text .= 'ID пользователя: '.$user['id']."\n";
				$text .= 'Логин: '.$user['login']."\n\n";
				$text .= 'Для смены пароля перейдите по следующей ссылке:'."\n";
				$text .= 'http://'.$_SERVER['SERVER_NAME'].PRJ_REF.'/admin/password?key='.$key."\n\n";
				$text .= 'Сообщение сгенерировано автоматически.'."\n";
				$this->get('mailer')->send(
					'Новые регистрационные данные. Сайт '.$_SERVER['SERVER_NAME'],
					nl2br($text),
					$user['email']
				);
				$_SESSION['success'] = 'Новые параметры авторизации отправлены Вам на <b>Электронную почту</b>!';
				$this->get('router')->reload();
			} else {
				$_SESSION['danger'] = 'Пользователь не найден';
				$this->get('router')->reload();
			}
		}
		$message = $this->flash('danger') ?: $this->flash('success');
		
		return $this->render('admin/layout.forget.tpl', compact('message'));
	}
	
	public function logoutAction() {
		$this->get('security')->logout();
		if (empty($_SERVER['HTTP_REFERER']) || preg_match('/^'.PRJ_REF.'\/admin\/logout/', $_SERVER['HTTP_REFERER'])) {
			$uri = PRJ_REF.'/admin/';
		} else {
			$uri = $_SERVER['HTTP_REFERER'];
		}
		$this->get('router')->redirect($uri);
	}
	
	public function passwordAction() {
		$key = $this->get('util')->request('key');
		if ($key) {
			$sql = "SELECT id,login,email FROM user_user WHERE hashkey= :key ";
			$stmt = $this->get('connection')->prepare($sql);
			$stmt->bindValue("key", $key);
			$stmt->execute();
			$user = $stmt->fetch();
			if ($user && !empty($user['email'])) {
				$password = $this->get('util')->genKey();
				$this->get('connection')->update(
						'user_user', 
						array('hashkey' => '', 'password' => md5($password)), 
						array('id' => $user['id'])
				);
				$text = 'Информационное сообщение сайта '.$_SERVER['SERVER_NAME']."\n";
				$text .= '------------------------------------------'."\n";
				$text .= 'Вы запросили ваши регистрационные данные.'."\n";
				$text .= 'Ваша регистрационная информация:'."\n";
				$text .= 'ID пользователя: '.$user['id']."\n";
				$text .= 'Логин: '.$user['login']."\n";
				$text .= 'Пароль: '.$password."\n\n";
				$text .= 'Сообщение сгенерировано автоматически.'."\n";
				$this->get('mailer')->send(
					'Новые регистрационные данные. Сайт '.$_SERVER['SERVER_NAME'],
					nl2br($text),
					$user['email']
				);
			}
		}
		$this->get('router')->redirect(PRJ_REF.'/admin/');
	}
	
}
