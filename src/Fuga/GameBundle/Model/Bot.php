<?php

namespace Fuga\GameBundle\Model;

class Bot {
	
	private $data;
	
	public function __construct($id, array $options = array()) {
		$this->data = array(
			'id'      => $id,
			'avatar'  => '/bundles/public/img/avatar_computer.png',
			'name'    => 'Компьютер '.$id,
			'lastname'=> '',
			'chips'   => isset($options['chips']) ? $options['chips'] : 100,
			'bet'     => 0,
			'status'  => 1,
			'seat'    => isset($options['seat']) ? $options['seat'] : $id,
			'positon' => $id,
			'cards'   => array(),
		);
	}
	
	public function bet($bet) {
		$this->chips -= $bet;
		$this->bet += $bet;
	}
	
	public function takeBet() {
		$bet = $this->bet;
		$this->bet = 0;
		return $bet;
	}
	
	public function __set($name, $value) 
    {
        $this->data[$name] = $value;
    }

    public function __get($name) 
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return null;
    }

    public function __isset($name) 
    {
        return isset($this->data[$name]);
    }

    public function __unset($name) 
    {
        unset($this->data[$name]);
    }
	
}