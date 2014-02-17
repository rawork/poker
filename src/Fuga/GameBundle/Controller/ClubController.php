<?php

namespace Fuga\GameBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class ClubController extends PublicController {
	
	public function __construct() {
		parent::__construct('club');
	}
	
	public function indexAction() {
		$user = $this->get('security')->getCurrentUser();
		if ($user) {
			$account = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
		}
		$page = $this->get('util')->post('page', true, 1);
        $paginator = $this->get('paginator');
        $paginator->paginate(
				$this->get('container')->getTable('club_message'),
				$this->get('router')->generateUrl($this->get('router')->getParam('node')).'?page=###',
				'publish=1 AND message_id=0',
				$this->getParam('per_page'),
				$page
		);
		$paginator->setTemplate('public');
		$messages = $this->get('container')->getItems('club_message', 'publish=1 AND message_id=0', 'id DESC', $paginator->limit);
		foreach ($messages as &$message) {
			$message['account'] = $this->get('container')->getItem('account_member', $message['member_id']);
			$message['comments_count'] = $this->get('container')->count('club_message', 'publish=1 AND message_id='.$message['id']);
			if ($message['account']) {
				$message['user'] = $this->get('container')->getItem('user_user', $message['account']['user_id']);
			}
		}
		$this->get('container')->setVar('javascript', 'club');
		
		return $this->render('club/index.tpl', compact('messages', 'user', 'account'));
	}
	
	public function messageAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/club');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Добавлять сообщения могут только зарегистрированные пользователи',
			));
		}
		
		$message = strip_tags($this->get('util')->post('message'), '<img>');
		
		if (!$message) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Пустое сообщение',
			));
		}
		
		$account = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
		
		$messageId = $this->get('container')->addItem('club_message', array(
			'message' => $message,
			'message_id' => 0,
			'member_id' => $account['id'],
			'publish' => 1,
			'created' => date('Y-m-d H:i:s'),
			'updated' => '0000-00-00 00:00:00',
			'likes'   => 0,
		));
		$this->get('mailer')->send(
			'Новое сообщение на сайте клуба Чертова дюжина',
			$this->render('mail/message.admin.tpl', compact('account', 'message', 'messageId')),
			ADMIN_EMAIL
		);
		
		$message = $this->get('container')->getItem('club_message', $messageId);
		
		return json_encode(array(
			'ok' => true,
			'content' => $this->render('club/message.tpl', compact('message', 'account', 'user')),
		)); 
	}
	
	public function commentAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/club');
		}
		
		$user = $this->get('security')->getCurrentUser();
		if (!$user) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Оставлять комментарии могут только зарегистрированные пользователи',
			));
		}
		
		$message = strip_tags($this->get('util')->post('message'), '<img>');
		$messageId = $this->get('util')->post('message_id');
		
		if (!$message) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Пустой комментарий',
			));
		}
		
		$account = $this->get('container')->getItem('account_member', 'user_id='.$user['id']);
		
		$commentId = $this->get('container')->addItem('club_message', array(
			'message' => $message,
			'message_id' => $messageId,
			'member_id' => $account['id'],
			'publish' => 1,
			'created' => date('Y-m-d H:i:s'),
			'updated' => '0000-00-00 00:00:00',
			'likes'   => 0,
		));
		$this->get('mailer')->send(
			'Новый комментарий на сайте клуба Чертова дюжина',
			$this->render('mail/comment.admin.tpl', compact('account', 'message', 'commentId')),
			ADMIN_EMAIL
		);
		
		$comment = $this->get('container')->getItem('club_message', $commentId);
		$counter = $this->get('container')->count('club_message', 'publish=1 AND message_id='.$messageId);
		
		return json_encode(array(
			'ok' => true,
			'content' => $this->render('club/comment.tpl', compact('comment', 'account', 'user')),
			'counter' => 'Показать комментарии ('.$counter.')',
		)); 
	}
	
	public function commentsAction($params) {
		$user = $this->get('security')->getCurrentUser();
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/club');
		}
		
		if (empty($params[0])) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Не выбрано сообщение для комментариев',
			));
		}
		
		$message = $this->get('container')->getItem('club_message', intval($params[0]));
		if (empty($params[0])) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Не выбрано сообщение для комментариев',
			));
		}
		
		$comments = $this->get('container')->getItems('club_message', 'publish=1 AND message_id='.$message['id'], 'id');
		foreach ($comments as &$comment) {
			$comment['account'] = $this->get('container')->getItem('account_member', $comment['member_id']);
		}
		
		return json_encode(array(
			'ok' => true,
			'content' => $this->render('club/comments.tpl', compact('message', 'comments', 'user')),
		));
	}
	
	public function likeAction($params) {
		$user = $this->get('security')->getCurrentUser();
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/club');
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
				'content' => 'Не выбрано сообщение для голосования',
			));
		}
		
		$messageId = intval($params[0]);
		$message = $this->get('container')->getItem('club_message', $messageId);
		
		if (!$message) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Не выбрано сообщение для голосования',
			));
		}
		
		if (0 < $this->get('container')->count('club_likes', 'user_id='.$user['id'].' AND message_id='.$messageId)) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Вы уже голосовали за выбранное сообщение',
			));
		}
		
		$this->get('container')->addItem('club_likes', array(
			'user_id' => $user['id'],
			'message_id' => $messageId,
		));
		$this->get('container')->updateItem('club_message', 
				array('likes' => $message['likes']+1),
				array('id' => $messageId)
		);
		
		return json_encode(array(
			'ok' => true,
			'content' => $message['likes']+1,
		));
	}
	
	public function moreAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			$this->get('router')->redirect('/club');
		}
		
		$user = $this->get('security')->getCurrentUser();
		$page = $this->get('util')->post('page',true, 0);
		if (!$page) {
			return json_encode(array(
				'ok' => false,
				'content' => 'Error',
			));
		}
        $paginator = $this->get('paginator');
        $paginator->paginate(
				$this->get('container')->getTable('club_message'),
				$this->get('router')->generateUrl($this->get('router')->getParam('node')).'?page=###',
				'publish=1 AND message_id=0',
				$this->getParam('per_page'),
				$page
		);
		$paginator->setTemplate('public');
		$messages = $this->get('container')->getItems('club_message', 'publish=1 AND message_id=0', 'id DESC', $paginator->limit);
		foreach ($messages as &$message) {
			$message['account'] = $this->get('container')->getItem('account_member', $message['member_id']);
			$message['comments_count'] = $this->get('container')->count('club_message', 'publish=1 AND message_id='.$message['id']);
			if ($message['account']) {
				$message['user'] = $this->get('container')->getItem('user_user', $message['account']['user_id']);
			}
		}
		$isAjax = true;
		
		if (!$messages) {
			return json_encode(array(
				'ok' => false,
				'content' => 'error',
			));
		}
		
		return json_encode(array(
			'ok' => true,
			'content' => $this->render('club/index.tpl', compact('messages', 'isAjax', 'user')),
		));
	}
}