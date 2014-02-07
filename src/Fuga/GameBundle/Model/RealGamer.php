<?php

namespace Fuga\GameBundle\Model;

class RealGamer {
	
	public $id;
	public $avatar;
	public $name;
	public $lastname;
	public $question;
	public $buying;
	public $change;
	public $rank;
	public $combination;
	public $allin;
	public $timer;
	
	private $bet;
	private $seat;
	private $position;
	private $chips;
	private $ready;
	
	private $timers     = array(
		'answer'     => array('handler' => 'onNoAnswer', 'holder' => 'answer-timer', 'time' => 14),
		'bet'        => array('handler' => 'onFold', 'holder' => 'game-timer', 'time' => 31),
		'buy'        => array('handler' => 'onEndRound', 'holder' => 'buy-timer', 'time' => 121),
	);
	
	public function __construct(array $gamer) {
		$this->id       = $gamer['user_id'];
		$this->avatar   =  isset($gamer['avatar_value']['extra']) 
				? $gamer['avatar_value']['extra']['main']['path'] 
				: '/bundles/public/img/avatar_empty.png';
		$this->name     = $gamer['name'];
		$this->lastname = $gamer['lastname'];
		$this->bet      = $gamer['bet'];
		$this->seat     = intval($gamer['seat']);
		$this->position = 0;
		$this->chips    = intval($gamer['chips']);
	}
	
	public function getRivalPosition($rivalSeat) {
		if ($rivalSeat == $this->seat) {
			throw new Exception\RivalException('Ошибка посадки игроков. Два игрока на одном месте');
		}
		if ($rivalSeat > $this->seat) {
			$position = $rivalSeat - $this->seat;
		} else {
			$position = 6 - ($this->seat - $rivalSeat);
		}
		return $position;
	}
	
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
	
}
