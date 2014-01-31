<?php

namespace Fuga\GameBundle\Model;

class Board {
	
	public $state = 0;
	private $data;
	private $step = 1;
	
	public function __construct($id) {
		$this->data = array(
			'user_id'  => $id,
			'bank'     => 0,
			'bets'     => 0,
			'maxbet'   => 0,
			'minbet'   => 1,
			'allin'    => 0,
			'winner'   => array(),
			'combination' => array(),
			'flop'     => array(),
		);
	}
	
	public function acceptBet($bet) {
		$this->bets += $bet;
	}
	
	public function confirmBets() {
		$this->bank += $this->bets;
		$this->bets = 0;
	}
	
	public function takeBank() {
		$chips = $this->bank;
		$this->bank = 0;
		return $chips;
	}
	
	public function raiseMinBet() {
		$this->minbet += $this->step;
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