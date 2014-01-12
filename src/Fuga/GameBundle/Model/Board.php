<?php

namespace Fuga\GameBundle\Model;

class Board {
	
	private $data;
	private $step = 1;
	
	public function __construct($id) {
		$this->data = array(
			'user_id'  => $id,
			'fromtime' => new \DateTime(),
			'bank'     => 0,
			'bets'     => 0,
			'maxbet'   => 0,
			'minbet'   => 1,
			'allin'    => 0,
			'winner'   => array(),
			'flop'     => array(),
			'status'   => 1,
			'state'    => 1,
		);
	}
	
	public function acceptBet(integer $bet) {
		$this->data['bets'] += $bet;
	}
	
	public function confirmBets(integer $bet) {
		$this->data['bank'] += $this->data['bets'];
		$this->data['bets'] = 0;
	}
	
	public function takeBank() {
		$chips = $this->data['bank'];
		$this->data['bank'] = 0;
		return $chips;
	}
	
	public function raiseMinBet() {
		$this->data['minbet'] += $this->step;
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
	
	// TODO Реализовать сохранение в бд
	public function save() {
		
	}
	
}