<?php

namespace Fuga\Component\Templating;

interface TemplatingInterface {
	
	public function render($template, $params = array(), $silent = false);
	public function assign($param);
	
}