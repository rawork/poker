<?php

namespace Fuga\CommonBundle\Security;
	
class SecurityHandler {
	
	private $user;
	private $userkey;
	private $container;
	private $ttl = 86400000;

	public function __construct($container) {
		$this->container = $container;
	}
	
	public function isAuthenticated() {
		$this->userkey = $this->container->get('util')->session('ukey');
		$this->check();
		return !empty($this->userkey);
	}
	
	public function isSecuredArea() {
		if (preg_match('/^'.PRJ_REF.'\/admin\/(logout|forget|password)/', $_SERVER['REQUEST_URI'])) {
			return false;
		}
		
		return 'Y' == PROJECT_LOCKED || preg_match('/^'.PRJ_REF.'\/admin\//', $_SERVER['REQUEST_URI']);
	}
	
	public function getCurrentUser() {
		if (!$this->user) {
			$login = $this->container->get('util')->session('user');
			if ($this->isDeveloper()) {
				$this->user = array(
					'id' => 0,
					'name' => 'dev',
					'email'    => DEV_EMAIL,
					'group_id' => 1,
					'is_admin' => 1,
					'is_active' => 1,
				);
			} else {
				$sql = "
					SELECT u.*, g.rules FROM user_user u 
					JOIN user_group g ON u.group_id=g.id 
					WHERE u.login = :login OR u.email = :login LIMIT 1";
				$stmt = $this->container->get('connection')->prepare($sql);
				$stmt->bindValue("login", $login);
				$stmt->execute();
				$this->user = $stmt->fetch();
				unset($this->user['password']);
			}
		}
		
		return $this->user;
	}

	private function check() {
		if ($this->container->get('util')->cookie('userkey')) {
			if ($this->container->get('util')->cookie('userkey') == $this->hash(DEV_USER, DEV_PASS)) {
				$user = array('login' => DEV_USER);
			} else {
				$sql = "SELECT login FROM user_user WHERE MD5(CONCAT(password, login, :addr )) = :key LIMIT 1";
				$stmt = $this->container->get('connection')->prepare($sql);
				$stmt->bindValue("addr", $_SERVER['REMOTE_ADDR']);
				$stmt->bindValue("key", $_COOKIE['userkey']);
				$stmt->execute();
				$user = $stmt->fetch();
			}
			if ($user) {
				$_SESSION['user'] = $user['login'];
				$this->userkey = $_SESSION['ukey'] = $this->container->get('util')->cookie('userkey');
				setcookie('userkey', $this->container->get('util')->cookie('userkey'), time() + $this->ttl, '/');	
			}
		}
	}

	public function logout() {
		unset($_SESSION['user']);
		unset($_SESSION['locale']);
		unset($_SESSION['ukey']);
		setcookie('userkey', '', time()-86400, '/');
		session_destroy();
	}

	public function login($login, $password, $isRemember = false ) {
		$password = md5($password);
		if ($login == DEV_USER && $password == DEV_PASS) {
			$user = array('login' => $login);
		} else {
			$sql = "SELECT login FROM user_user WHERE login= :login AND password= :password AND is_active=1 LIMIT 1";
			$stmt = $this->container->get('connection')->prepare($sql);
			$stmt->bindValue("login", $login);
			$stmt->bindValue("password", $password);
			$stmt->execute();
			$user = $stmt->fetch();
		}
		if ($user){
			$_SESSION['user'] = $user['login'];
			$_SESSION['ukey'] = $this->hash($login, $password);
			if ($isRemember) {
				setcookie('userkey', $this->hash($login, $password), time() + $this->ttl, '/');
			}
			return true;
		} else {
			return false;
		}
		
	}
	
	private function hash($login, $password) {
		return md5($password.$login.$_SERVER['REMOTE_ADDR']);
	}
	
	public function getGroup($name) {
		return $this->container->getItem('user_group', "name='$name'");
	}
	
	public function isGroup($name) {
		$group = $this->getGroup($name);
		$user = $this->getCurrentUser();
		return !empty($user['group_id']) && !empty($group['id']) && $user['group_id'] == $group['id'];
	}

	public function isAdmin() {
		return isset($_SESSION['user']) && $_SESSION['user'] == 'admin';
	}

	public function isDeveloper() {
		return isset($_SESSION['user']) && $_SESSION['user'] == 'dev';
	}

	public function isSuperuser() {
		return $this->isAdmin() || $this->isDeveloper();
	}

	public function isLocal() {
		return empty($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] == gethostbyname($_SERVER['SERVER_NAME']);
	}

	public function isServer() {
		return isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']);
	}
	
}
