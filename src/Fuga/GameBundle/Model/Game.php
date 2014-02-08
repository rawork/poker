<?php

namespace Fuga\GameBundle\Model;

class Game implements GameInterface, ObserverInterface {
	
	const STATE_BEGIN     = 0;
	const STATE_CHANGE    = 1;
	const STATE_PREFLOP   = 2;
	const STATE_FLOP      = 3;
	const STATE_SHOWDOWN  = 4;
	const STATE_JOKER	  = 41;
	const STATE_BUY       = 5;
	const STATE_END       = 6;
	const STATE_ROUND_END = 7;

	public $deck;
	public $flop;
	public $minbet = 0;
	public $bets = 0;
	public $maxbet = 0;
	public $timer;
	public $fromtime;
	public $stopbuytime;
	public $changes;
	public $winner;
	public $combination;
	
	private $bank = 0;
	private $stateNo;
	private $prizes;
	private $state;
	private $user_id;
	
	private $cookietime = 7776000;
	private $upTimer    = 780;
	private $timers     = array(
		'change'     => false, // array('handler' => 'onClickNoChange', 'holder' => 'change-timer', 'time' => 14)
		'distribute' => array('handler' => 'onDistribute', 'holder' => 'game-timer', 'time' => 61),
		'prebuy'     => array('handler' => 'onShowPrebuy', 'holder' => 'null-timer', 'time' => 14),
		'nobuy'      => array('handler' => 'onEndRound', 'holder' => 'prebuy-timer', 'time' => 31),
		'next'		 => array('handler' => 'onNext', 'holder' => 'null-timer', 'time' => 61),
	);
	
	private $log;
	
	public function __construct($board, Deck $deck) {
		$this->fromtime = new \DateTime($board['fromtime']);
		$this->stopbuytime = clone $this->fromtime;
		$this->stopbuytime->add(new \DateInterval('PT35M'));
		
		$this->setState($board['state']);
		$this->deck = $deck;
		$this->syncTime();
	}
	
	public function syncTime() {
		if (!$this->fromtime) {
			return;
		}
		$now = new \DateTime();
		$diff = $now->diff($this->fromtime);
		setcookie('gamehour', intval($diff->format('%H')), time()+$this->cookietime, '/');
		setcookie('gameminute', intval($diff->format('%i')), time()+$this->cookietime, '/');
		setcookie('gamesecond', intval($diff->format('%s')), time()+$this->cookietime, '/');
	}
	
	public function stopTime() {
		$this->fromtime = null;
		$this->stopbuytime = null;
		setcookie('gamehour', 0, time()-$this->cookietime, '/');
		setcookie('gameminute', 0, time()-$this->cookietime, '/');
		setcookie('gamesecond', 0, time()-$this->cookietime, '/');
	}
	
	public function setTimer($name) {
		if (array_key_exists($name, $this->timers) && $this->timers[$name]) {
			$this->timer->set(
				$this->timers[$name]['handler'],
				$this->timers[$name]['holder'],
				$this->timers[$name]['time']
			);
		}
	}
	
	public function setState($state) {
		$this->stateNo = $state;
		if ($state == self::STATE_BEGIN) {
			$this->state = new GameState\BeginState($this);
		} elseif ($state == self::STATE_CHANGE) {
			$this->state = new GameState\ChangeState($this);
		} elseif ($state == self::STATE_PREFLOP) {
			$this->state = new GameState\PreflopState($this);
		} elseif ($state == self::STATE_FLOP) {
			$this->state = new GameState\FlopState($this);
		} elseif ($state == self::STATE_SHOWDOWN) {
			$this->state = new GameState\ShowdownState($this);
		} elseif ($state == self::STATE_JOKER) {
			$this->state = new GameState\JokerState($this);
		} elseif ($state == self::STATE_PREBUY) {
			$this->state = new GameState\PrebuyState($this);
		} elseif ($state == self::STATE_BUY) {
			$this->state = new GameState\BuyState($this);
		} elseif ($state == self::STATE_END) {
			$this->state = new GameState\EndState($this);
		} elseif ($state == self::STATE_ROUND_END) {
			$this->state = new GameState\RoundEndState($this);
		} 
		setcookie('gamestate', $state, time() + $this->cookietime, '/');
		setcookie('gamemaxbet', $this->maxbet, time() + $this->cookietime, '/');
		$this->syncTime();
		$this->minbet();
	}
	
	public function registerObserver(ObserverInterface $o) {
		;
	}
	
	public function removeObserver(ObserverInterface $o) {
		;
	}
	
	public function notifyObserver() {
		;
	}
	
	public function update() {
		;
	}
}