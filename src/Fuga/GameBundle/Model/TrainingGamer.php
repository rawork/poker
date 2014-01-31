<?php

namespace Fuga\GameBundle\Model;

class TrainingGamer {
	
	public $question;
	public $buying;
	public $change;
	public $cards;
	private $data;
	
	public function __construct($gamer, array $options = array()) {
		$this->data = array(
			'id'      => $gamer['user_id'],
			'avatar'  => $gamer['avatar_value']['extra']['main']['path'],
			'name'    => $gamer['name'],
			'lastname'=> $gamer['lastname'],
			'chips'   => isset($options['chips']) ? $options['chips'] : 100,
			'bet'     => 0,
			'status'  => 1,
			'seat'    => 6,
			'position'=> 0,
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
