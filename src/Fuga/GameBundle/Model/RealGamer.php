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
	public $dealer;
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
		$this->active   = $gamer['status'] == 1;
		$this->dealer   = $gamer['dealer'] == 1;
	}
	
	public function getRivalPosition($rivalSeat, $numOfSeats = 6) {
		switch($numOfSeats){
			case 1:
				throw new Exception\GamerException('За столом только один игрок');
			case 2:
				return 3;
			case 3:
			case 4:
				$offset = 7;
				break;
			default:
				$offset = 6;
				break;
		}
		if ($rivalSeat == $this->seat) {
			throw new Exception\RivalException('Ошибка посадки игроков. Два игрока на одном месте');
		}
		if ($rivalSeat > $this->seat) {
			$position = $rivalSeat - $this->seat;
		} else {
			$position = $offset - ($this->seat - $rivalSeat);
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
