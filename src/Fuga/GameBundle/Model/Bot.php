<?php

namespace Fuga\GameBundle\Model;

class Bot extends AbstractGamer {
	
	public function __construct(array $options = array()) {
		$this->data = array(
			'id'      => $options['id'],
			'avatar'  => '/bundles/public/img/avatar_computer.png',
			'name'    => 'Компьютер '.$options['id'],
			'lastname'=> '',
			'chips'   => isset($options['chips']) ? $options['chips'] : 10,
			'bet'     => 0,
			'seat'    => $options['id'],
			'positon' => $options['id'],
		);
	}
		
}