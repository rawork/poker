<?php

namespace Fuga\GameBundle\Model;

class AbstractGamer {
	
	public $cards = array();
	public $active = true;
	protected $data = array();
	
	public function bet($bet, $allin = false) {
		if ($this->chips <= 0) {
			$this->chips = 0;
			$this->active = false;
			return 0;
		}
		if ( $allin && $bet > $this->chips ) {
			$bet = $this->chips;
		} elseif ( $bet > $this->chips ) {
			$this->active = false;
			return 0;
		}
		$this->chips -= $bet;
		$this->bet += $bet;
		
		return $bet;
	}
	
	public function emptyBet() {
		$this->bet = 0;
	}
	
	public function isActive() {
		return $this->active;
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
