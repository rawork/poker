<?php

namespace Fuga\CommonBundle\Controller;

use Fuga\Component\Exception\NotFoundHttpException;

abstract class Controller {
	
	public function get($name) 
	{
		global $container;
		if ($name == 'container') {
			return $container;
		} else {
			return $container->get($name);
		}
	}
	
	public function getManager($path) {
		return $this->get('container')->getManager($path);
	}
	
	public function render($template, $params = array(), $silent = false) 
	{
		return $this->get('templating')->render($template, $params, $silent);
	}
	
	public function t($name) {
		return $this->get('translator')->t($name);
	}
	
	public function flash($name) {
		return $this->get('util')->getMessage($name);
	}
	
	public function createNotFoundException($message = 'Not Found', \Exception $previous = null)
    {
        return new NotFoundHttpException($message, $previous);
    }
	
}