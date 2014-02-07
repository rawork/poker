<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class AccountController extends PublicController {
	
	public function __construct() {
		parent::__construct('account');
	}
	
	public function indexAction() {
		$text = $this->get('util')->post('text') ?: $this->get('util')->session('member_search_text');
		$criteria = array('1=1');
		if ($text && $text != '1=1') {
			$criteria = array();
			$_SESSION['member_search_text'] = $text;
			$words = explode(' ', $text);
			$fields = array('lastname', 'name');
			foreach($words as $word) {
				$word_criteria = array();
				foreach ($fields as $field) {
					$word_criteria[] = $field." LIKE('".$word."%')";
				}
				$criteria[] = '('.implode(' OR ', $word_criteria).')';
			}
		} else {
			unset($_SESSION['member_search_text']);
		}
		$user = $this->get('security')->getCurrentUser();
		$page = $this->get('util')->request('page', true, 1);
        $paginator = $this->get('paginator');
        $paginator->paginate(
				$this->get('container')->getTable('account_member'),
				$this->get('router')->generateUrl($this->get('router')->getParam('node')).'?page=###',
				implode(' AND ', $criteria),
				$this->getParam('per_page'),
				$page
		);
		$paginator->setTemplate('public');
		$members = $this->get('container')->getitems('account_member', implode(' AND ', $criteria), 'prizes DESC,rounds DESC,lastname,name', $paginator->limit);
		foreach ($members as &$member) {
			$member['group'] = $this->get('container')->getItem('user_group', $member['user_id_value']['item']['group_id']);
		}
		
		$this->get('container')->setVar('title', 'Участники клуба');
		$this->get('container')->setVar('h1', 'Участники клуба');
		$this->get('container')->setVar('javascript', 'members');
		
		$date1_start = new \DateTime($this->getParam('bet1_start').' 00:00:01');
		$date1_end = new \DateTime($this->getParam('bet1_end').' 23:59:59');
		$date2_start = new \DateTime($this->getParam('bet2_start').' 00:00:01');
		$date2_end = new \DateTime($this->getParam('bet2_end').' 23:59:59');
		$now = new \Datetime();
		
		$bets = array();
		if ($now > $date1_start && $now < $date1_end) {
			$isBet1 = true;
			if ($user) {
				$bets = $this->get('container')->getItems('account_bet1', 'user_id='.$user['id']);
			}
		}
		
		if ($now > $date2_start && $now < $date2_end) {
			$isBet2 = true;
			if ($user) {
				$bets = $this->get('container')->getItems('account_bet2', 'user_id='.$user['id']);
			}
		}
		
		$selected = array();
		foreach ($bets as $bet){
			$selected[$bet['member_id']] = 1;
		} 
		
		if ($this->get('router')->isXmlHttpRequest()) {
			$isAjax = true;
			return json_encode(array(
				'ok' => true,
				'content' => $this->render('account/index.tpl', compact('isAjax', 'user', 'members', 'paginator', 'isBet1', 'isBet2', 'selected')),
			));	
		}
		
		return $this->render('account/index.tpl', compact('user', 'members', 'paginator', 'isBet1', 'isBet2', 'selected'));
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
			
			$account['sbe_id']   = $this->get('util')->post('sbe_id');
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
			if ($user['login'] && 0 < $this->get('container')->count('user_user', "login='".$user['login']."' OR email='".$user['login']."'")) {
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
				$accountId = null;
				$this->get('connection')->beginTransaction();
				try{
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

					$this->get('connection')->commit();
				} catch(\Exception $e) {
					$this->get('connection')->rollback();
					$this->get('log')->write('Account:register Error:'.$e->getMessage());
					$this->get('log')->write('Account:register Error:'.$e->getTraceAsString());
					$errors[] = $e->getMessage();
					$_SESSION['danger'] = implode('<br>', $errors);
					$_SESSION['register'] = json_encode($user);
					$_SESSION['account'] = json_encode($account);
					$this->get('router')->reload();
				}
				
				if ($accountId) {
					$account = $this->get('container')->getItem('account_member', $accountId);

					$this->get('mailer')->send(
						'Вы зарегистрировались на сайте клуба Чертова дюжина',
						$this->render('mail/register.tpl', compact('user', 'account')),
						$user['login']
					);
					$this->get('mailer')->send(
						'Новый участник на сайте клуба Чертова дюжина',
						$this->render('mail/register.admin.tpl', compact('user', 'account')),
						ADMIN_EMAIL
					);
				}
				
				unset($_SESSION['register']);
				unset($_SESSION['account']);
				$this->get('security')->login($user['login'], $user['password']);
				
				$this->get('router')->redirect('/members/cabinet');
			}
		}
		
		$message = $this->flash('danger');
		$register = json_decode($this->get('util')->session('register'), true);
		$account = json_decode($this->get('util')->session('account'), true);
		$sbe = $this->get('container')->getItems('account_sbe', 'publish=1');
		$date = new \DateTime($this->getParam('end_of_gamer_register').' 23:59:59');
		$now = new \DateTime();
		if ($now > $date) {
			$groups = $this->get('container')->getItems('user_group', 'id>2', 'id');
		} else {
			$groups = $this->get('container')->getItems('user_group', 'id>1', 'id');
		}
		$this->get('container')->setVar('title', 'Регистрация');
		$this->get('container')->setVar('h1', 'Регистрация');
		$this->get('container')->setVar('javascript', 'register');
		
		return $this->render('account/register.tpl', compact('message', 'register', 'account', 'groups', 'sbe'));
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
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			$this->get('router')->redirect('/members');
		}
		
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			$user['group_id'] = $this->get('util')->post('group_id');
			$user['name']     = $this->get('util')->post('name');
			$user['lastname'] = $this->get('util')->post('lastname');
			
			$account['sbe_id']   = $this->get('util')->post('sbe_id');
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
			
			if ($errors) {
				$_SESSION['danger'] = implode('<br>', $errors);
				$this->get('router')->reload();
			} else {
				$this->get('connection')->beginTransaction();
				try{
					$this->get('container')->updateItem('user_user',
						array('name' => $user['name'], 'lastname' => $user['lastname'], 'group_id' => $user['group_id']),
						array('id' => $user['id'])
					);
					$this->get('container')->getTable('account_member')->updateGlobals();
					$this->get('connection')->commit();
				} catch(\Exception $e) {
					$this->get('connection')->rollback();
					$this->get('log')->write('Account:edit Error:'.$e->getMessage());
					$this->get('log')->write('Account:edit Error:'.$e->getTraceAsString());
					$errors[] = $e->getMessage();
					$_SESSION['danger'] = implode('<br>', $errors);
					$this->get('router')->reload();
				}
					
				$this->get('mailer')->send(
					'Вы зарегистрировались на сайте клуба Чертова дюжина',
					$this->render('mail/register.tpl', compact('user', 'account')),
					$user['login']
				);
				
				$this->get('router')->redirect('/members/cabinet');
			}
		}
		
		$message = $this->flash('danger') ?: $this->flash('success');
		$account = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
		$sbe = $this->get('container')->getItems('account_sbe', 'publish=1');
		$date = new \DateTime($this->getParam('end_of_gamer_register').' 23:59:59');
		$now = new \DateTime();
		if ($now > $date) {
			$groups = $this->get('container')->getItems('user_group', 'id>2', 'id');
		} else {
			$groups = $this->get('container')->getItems('user_group', 'id>1', 'id');
		}
		$this->get('container')->setVar('title', 'Редактирование анкеты');
		$this->get('container')->setVar('h1', 'Редактирование анкеты');
		$this->get('container')->setVar('javascript', 'register');
		
		return $this->render('account/edit.tpl', compact('message', 'account', 'groups', 'sbe'));
	}
	
	public function membersAction() {
		$members0 = $this->get('container')->getItems('account_member', '1=1');
		$members = array();
		shuffle($members0);
		foreach ($members0 as $member) {
			if ($member['user_id_value']['item']['group_id'] == 1) {
				continue;
			}
				
			$members[] = $member;	
				
			if (count($members) >= $this->getParam('per_mainpage')) {
				break;
			}
		}
		
		return $this->render('account/members.tpl', compact('members'));
	}
	
	public function cardAction($params) {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/members');
		}
		
		if (empty($params[0])) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Не выбран участник',
			));
		}

		$account = $this->get('container')->getItem('account_member', intval($params[0]));
		$group = $this->get('container')->getItem('user_group', $account['user_id_value']['item']['group_id']);
		
		return json_encode(array(
			'ok' => true,
			'content' => $this->render('account/card.tpl', compact('account', 'group')),
		));
	}
	
	public function likeAction($params) {
		$user = $this->get('security')->getCurrentUser();
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/members');
		}
		
		if (!$user) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Голосовать могут только зарегистрированные пользователи',
			));
		}
		
		if (empty($params[0])) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Не выбран участник для голосования',
			));
		}
		
		$memberId = intval($params[0]);
		$account = $this->get('container')->getItem('account_member', $memberId);
		
		if (!$account) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Не выбран участник для голосования',
			));
		}
		
		if (0 < $this->get('container')->count('account_likes', 'user_id='.$user['id'].' AND member_id='.$memberId)) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Вы уже голосовали за выбранного участника',
			));
		}
		
		$this->get('container')->addItem('account_likes', array(
			'user_id' => $user['id'],
			'member_id' => $memberId,
		));
		$this->get('container')->updateItem('account_member', 
				array('likes' => $account['likes']+1),
				array('id' => $memberId)
		);
		
		return json_encode(array(
			'ok' => true,
			'content' => $account['likes']+1,
		));
	}
	
	public function bet1Action ($params) {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/members');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Голосовать могут только зарегистрированные пользователи',
			));
		}
		
		if (empty($params[0])) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Не выбран участник для голосования',
			));
		}
		
		$memberId = intval($params[0]);
		$account = $this->get('container')->getItem('account_member', $memberId);
		
		if (!$account) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Не выбран участник для голосования',
			));
		}
		
		if (0 < $this->get('container')->count('account_bet1', 'user_id='.$user['id'].' AND member_id='.$memberId)) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Вы уже голосовали за выбранного участника',
			));
		}
		
		if (13 <= $this->get('container')->count('account_bet1', 'user_id='.$user['id'])) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Вы уже проголосовали за 13 участников',
			));
		}
		
		$this->get('container')->addItem('account_bet1', array(
			'user_id' => $user['id'],
			'member_id' => $memberId,
			'created' => date('Y-m-d H:i:s'),
			'updated' => '0000-00-00 00:00:00',
		));
		
		return json_encode(array(
			'ok' => true,
			'content' => 1,
		));
	}
	
	public function bet2Action ($params) {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/members');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Голосовать могут только зарегистрированные пользователи',
			));
		}
		
		if (empty($params[0])) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Не выбран участник для голосования',
			));
		}
		
		$memberId = intval($params[0]);
		$account = $this->get('container')->getItem('account_member', $memberId);
		
		if (!$account) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Не выбран участник для голосования',
			));
		}
		
		if (0 < $this->get('container')->count('account_bet2', 'user_id='.$user['id'])) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Вы уже проголосовали за 1 участника',
			));
		}
		
		$this->get('container')->addItem('account_bet2', array(
			'user_id' => $user['id'],
			'member_id' => $memberId,
			'created' => date('Y-m-d H:i:s'),
			'updated' => '0000-00-00 00:00:00',
		));
		
		return json_encode(array(
			'ok' => true,
			'content' => 1,
		));
	}
	
}
