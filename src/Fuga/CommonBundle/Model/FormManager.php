<?php

namespace Fuga\CommonBundle\Model;

use Fuga\Component\Form\FormBuilder;

class FormManager extends ModelManager {
	
	private $params;
	
	public function __construct() {
		$params = $this->get('container')->getManager('Fuga:Common:Param')->findAll('form');
		$this->params = array();
		foreach ($params as $param) {
			$this->params[$param['name']] = $param['type'] == 'int' ? intval($param['value']) : $param['value'];
		}
	}
	
	public function getForm($name){
		$form = $this->get('container')->getItem('form_form', "name='$name'");
		if ($form) {
			$form['fields'] = $this->get('container')->getItems('form_field', 'form_id='.$form['id']);
			$builder = new FormBuilder($form, '');
			$builder->items = $form['fields'];
			$builder->message = $this->processForm($builder);
			if ($builder->message[0] == 'error')
				$builder->fillGlobals();
			$content = $builder->render();
		} else {
			$content = "Форма $name не существует";
		}
		
		return $content;
	}

	private function processForm($form) {
		$message = null;
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if($form->defense && $this->get('util')->session('captchaHash') != md5($this->get('util')->post('securecode').CAPTCHA_KEY)){
				$message[0] = 'error';
				$message[1] = $this->params['no_antispam'];
			} else {
				$errors = $form->sendMail($this->params);
				if ($errors === true){
					$message[0] = 'success';
					$message[1] = $this->params['text_inserted'];
				} else {
					$message[0] = 'error';
					$message[1] = implode('<br>', $errors);
				}
			}
			unset($_SESSION['captcha_keystring']);
		}
		
		return $message;
	}

}
