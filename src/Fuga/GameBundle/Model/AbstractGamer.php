<?php

namespace Fuga\GameBundle\Model;

class AbstractGamer {
	
	public $cards;
	public $chips = 0;
	public $active = false;
	public $allin = false;
	
	protected $data = array();
	
	public function bet($bet, $maxbet = 0) {
		$this->allin = false;
		if ($this->chips <= 0) {
			$this->chips = 0;
			return 0;
		}
		if ( $maxbet > $this->chips ) {
			$this->allin = true;
			$bet = $this->chips;
		} elseif ($bet >= $this->chips) {
			$this->allin = true;
			$bet = $this->chips;
		}
		$this->chips -= $bet;
		$this->bet += $bet;
		
		return $bet;
	}
	
	public function emptyBet() {
		$this->bet = 0;
	}
	
	public function giveChips($chips) {
		$this->chips += $chips;
	}
	
	public function isActive() {
		return $this->active;
	}
	
	public function checkActive() {
		 $this->active = $this->chips > 0;
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
