<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class PartnerController extends PublicController {
    
    public function __construct() {
		parent::__construct('partner');
	}
    
    public function indexAction() {
        $items = $this->get('container')->getItems('partner_partner', 'publish=1');
		$quantity = ceil(12 / count($items));
		
        return $this->get('templating')->render('partner/index.tpl', compact('items', 'quantity'));
    }
    
}