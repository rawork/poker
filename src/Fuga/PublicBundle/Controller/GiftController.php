<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class GiftController extends PublicController {
    
    public function __construct() {
		parent::__construct('gift');
	}
    
    public function indexAction($params) {
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			$result = 0;
			try {
				$id = $this->get('container')->getTable('gift_message')->insertGlobals();
				$item = $this->get('container')->getItem('gift_message', $id);
				$this->get('mailer')->send(
					'Добавлена полезная вещь на сайте Анкор. Добро 2013',
					$this->render('mail/present.tpl', compact('item')),
					array(ADMIN_EMAIL)
				);
				$result = 1;
			} catch (\Exception $e) {
				$this->get('log')->write($e->getMessage());
			}
			
			return '
<script type="text/javascript">
   window.top.window.stopUpload('.$result.', \''.$this->t('Thank you for the gift of children').'\');
</script>';
		}
		$node = $this->getManager('Fuga:Common:Page')->getCurrentNode();
		$page = $this->get('util')->request('page', true, 1);
		$paginator = $this->get('paginator');
		$paginator->paginate(
			$this->get('container')->getTable('gift_message'),
			$this->get('router')->generateUrl($node['name']).'?page=###',
			'publish=1',
			5,
			$page
		);
		$paginator->setTemplate('public');
		$items = $this->get('container')->getItems('gift_message', 'publish=1', null, $paginator->limit);
		$this->get('container')->setVar('colnum', '6');
		$this->get('container')->setVar('gifts', true);
    
        return $this->get('templating')->render('gift/index.tpl', compact('items', 'paginator'));
    }
    
	public function addAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			throw $this->createNotFoundException('Несуществующая страница');
		}
		parse_str($this->get('util')->post('formdata'));
		// TODO Сохранение в БД
		
		
		return json_encode(array('content' => $this->t('Thank you for the gift of children'), 'error' => false));
	}
	
	public function formAction() {
		if (!$this->get('router')->isXmlHttpRequest()) {
			return '';
		}

		return json_encode(array(
			'header' => '<a class="close" data-dismiss="modal" aria-hidden="true">&times;</a><h4 class="modal-title"><img src="'.PRJ_REF.'/bundles/public/img/stars-small.png"> &nbsp;'.$this->t('Add useful thing').'</h4>',
			'content' => $this->render('gift/form.tpl'), 
			'footer' => '<a class="pull-left btn btn-success btn-lg" href="javascript:void(0)" onclick="sendPresent()"><img src="'.PRJ_REF.'/bundles/public/img/man-small-white.png"> '.$this->t('I give').'</a>'
		));
	}
    
}