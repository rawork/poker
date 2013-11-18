<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class ContactController extends PublicController {
    
    public function __construct() {
		parent::__construct('contact');
	}
    
    public function indexAction() {
        $items = $this->get('container')->getItems('contact_contact', 'publish=1'); 
		
        return $this->get('templating')->render('contact/index.tpl', compact('items'));
    }
	
	public function messageAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		parse_str($this->get('util')->post('formdata'));
		$this->get('mailer')->send(
			'Обращение к оргкомитету на сайте Анкор. Добро 2013',
			$this->render('mail/message.tpl', compact('message', 'email')),
			array(ADMIN_EMAIL)
		);
		
		return json_encode(array('content' => 'Сообщение отправлено', 'error' => false));
	}
	
	public function adviceAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		parse_str($this->get('util')->post('formdata'));
		$id = $this->get('container')->addItem('catalog_advice',array(
			'author'     => $author,
			'message'    => $message,
			'present_id' => $present_id,
			'advice_id'  => 0,
			'created'    => date('Y-m-d H:i:s'),
			'locale'	 => $this->get('router')->getParam('locale'),
			'publish'    => 1
		));
		$message = $this->get('container')->getItem('catalog_advice', $id);
		$this->get('mailer')->send(
			'Новый совет на сайте Анкор. Добро 2013',
			$this->render('mail/advice.tpl', compact('message')),
			array(ADMIN_EMAIL)
		);
		$message = $this->get('container')->getItem('catalog_advice', $id);
		
		return json_encode(array(
			'content' => $this->render('catalog/advice.tpl', compact('message')),
			'message' => 'Сообщение отправлено', 
			'error' => false
		));
	}
	
	public function commentAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		parse_str($this->get('util')->post('formdata'));
		$present_id = $this->get('util')->post('present_id');
		$advice_id = $this->get('util')->post('advice_id');
		$id = $this->get('container')->addItem('catalog_advice',array(
			'author'     => $author,
			'message'    => $message,
			'present_id' => $present_id,
			'advice_id'  => $advice_id,
			'created'    => date('Y-m-d H:i:s'),
			'locale'	 => $this->get('router')->getParam('locale'),
			'publish'    => 1
		));
		$comment = $this->get('container')->getItem('catalog_advice', $id);
		$this->get('mailer')->send(
			'Новый комментарий к совету на сайте Анкор. Добро 2013',
			$this->render('mail/comment.tpl', compact('comment')),
			array(ADMIN_EMAIL)
		);
		
		return json_encode(array(
			'content' => $this->render('catalog/comment.tpl', compact('comment')),
			'message' => 'Сообщение отправлено', 
			'error' => false
		));
	}
	
	public function oldAction() {
		$advice_id = $this->get('util')->post('advice_id');
		$present_id = $this->get('util')->post('present_id');
		$lastid = 0;
		$messages = $this->get('container')->getItems('catalog_advice', 'id<'.$advice_id.' AND publish=1 AND advice_id=0 AND present_id='.$present_id, null, '5');
		if ($messages) {
			foreach ($messages as &$message) {
				$message['children'] = $this->get('container')->getItems('catalog_advice', 'publish=1 AND advice_id='.$message['id'], 'id ASC');
			}
			$lastid = $message['id'];
			unset($message);
		}
		
		return json_encode(array(
			'content' => $messages ? $this->render('catalog/old.tpl', compact('messages')) : '',
			'id' => $lastid
		));
	}
	
}