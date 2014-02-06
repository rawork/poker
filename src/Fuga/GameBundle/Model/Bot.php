<?php

namespace Fuga\GameBundle\Model;

class Bot extends AbstractGamer {
	
	public function __construct(array $options = array()) {
		$this->data = array(
			'id'      => $options['id'],
			'avatar'  => '/bundles/public/img/avatar_computer.png',
			'name'    => 'Компьютер '.$options['id'],
			'lastname'=> '',
			'bet'     => 0,
			'seat'    => $options['id'],
			'positon' => $options['id'],
		);
		$this->chips = isset($options['chips']) ? intval($options['chips']) : 10;
	}
		
}