<?php

namespace Fuga\GameBundle\Model;

class Board {
	
	public $winner;
	public $combination;
	public $flop;
	private $data;
	
	public function __construct($id) {
		$this->data = array(
			'user_id'  => $id,
			'bank'     => 0,
			'bets'     => 0,
			'maxbet'   => 0,
			'minbet'   => 1,
			'allin'    => 0,
			
		);
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