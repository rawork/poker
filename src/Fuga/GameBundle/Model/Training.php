<?php

namespace Fuga\GameBundle\Model;

class Training {
	
	const STATE_BEGIN     = 0;
	const STATE_CHANGE    = 1;
	const STATE_QUESTION  = 11;
	const STATE_PREFLOP   = 2;
	const STATE_FLOP      = 3;
	const STATE_SHOWDOWN  = 4;
	const STATE_JOKER	  = 41;
	const STATE_PREBUY    = 42;
	const STATE_BUY       = 5;
	const STATE_END       = 6;
	const STATE_ROUND_END = 7;
	
	public $deck;
	public $flop;
	
	public $minbet = 0;
	public $bots;
	public $gamer;
	public $bets = 0;
	public $maxbet = 0;
	public $allin = false;
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
		'answer'     => array('handler' => 'onNoAnswer', 'holder' => 'answer-timer', 'time' => 14),
		'bet'        => array('handler' => 'onFold', 'holder' => 'game-timer', 'time' => 31),
		'distribute' => array('handler' => 'onDistribute', 'holder' => 'game-timer', 'time' => 61),
		'prebuy'     => array('handler' => 'onShowPrebuy', 'holder' => 'joker-timer', 'time' => 14),
		'nobuy'      => array('handler' => 'onEndRound', 'holder' => 'prebuy-timer', 'time' => 31),
		'buy'        => array('handler' => 'onEndRound', 'holder' => 'buy-timer', 'time' => 121),
		'next'		 => false,//array('handler' => 'onNext', 'holder' => 'round-end-timer', 'time' => 121),
	);
	
	private $log;
	
	public function __construct(array $gamer, $log) {
		$this->log   = $log;
		$this->timer = new Timer();
		$this->deck  = new Deck();
		$this->user_id = $gamer['user_id'];
		$this->createGamer(new TrainingGamer($gamer));
		$this->createBots(3);
		$this->setState(self::STATE_BEGIN);
	}
	
	public function createGamer(TrainingGamer $gamer) {
		$this->gamer = $gamer;
		$this->gamer->position = 0;
	}
	
	private function createBots($n = 3) {
		$n = $n < 1 ? 1 : $n > 5 ? 5 : $n;
		$this->bots = array();
		for ($i = 1; $i <= $n; $i++) {
			$bot = new Bot(array('id' => $i));
			$bot->position = $this->getPosition($i, $n);
			$this->bots[] = $bot;
		}
	}
	
	public function getPosition($seat, $quantity) {
		if ($seat == 6) {
			return 0;
		}
		
		switch ($quantity) {
			case 1:
				return 2;
			case 2:
			case 3:
				return $seat + 1;
			default:
				return $seat;
		}
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
	
	public function getBank() {
		return $this->bank;
	}
	
	public function setBank($value) {
		$this->bank = $value;
	}
	
	public function minbet() {
		if ($this->fromtime) {
			$now = new \DateTime();
			$seconds = $now->getTimestamp() - $this->fromtime->getTimestamp();
			$this->minbet = ($seconds - $seconds % $this->upTimer) / $this->upTimer + 1;
		} else {
			$this->minbet = 1;
		}		
		return $this;
	}
	
	public function bet0() {
		foreach($this->bots as $bot) {
			$bot->bet($this->minbet);
			$this->acceptBet($bot->bet($this->minbet));
		}
		$this->gamer->bet($this->board->minbet);
		$this->acceptBet($this->gamer->bet($this->minbet));
		$this->confirmBets();
	}
	
	public function existsJoker() {
		$cards = $this->deck->take();
		foreach ($cards as $card) {
			if ('joker' == $card['name']) {
				$this->prizes += 1;
				return true; 
			}
		}
		
		return false;
	}
	
	public function existsBots() {
		$exists = false;
		foreach ($this->bots as $bot) {
			if ($bot->chips > 0) {
				$exists = $bot->active = true;
			} else {
				$bot->active = false;
			}
		}
		
		return $exists;
	}
	
	public function startTime() {
		$this->fromtime = new \DateTime();
		$this->stopbuytime = new \DateTime();
		$this->stopbuytime->add(new \DateInterval('PT35M'));
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
			$this->state = new TrainingState\BeginState($this);
		} elseif ($state == self::STATE_CHANGE) {
			$this->state = new TrainingState\ChangeState($this);
		} elseif ($state == self::STATE_QUESTION) {
			$this->state = new TrainingState\QuestionState($this);
		} elseif ($state == self::STATE_PREFLOP) {
			$this->state = new TrainingState\PreflopState($this);
		} elseif ($state == self::STATE_FLOP) {
			$this->state = new TrainingState\FlopState($this);
		} elseif ($state == self::STATE_SHOWDOWN) {
			$this->state = new TrainingState\ShowdownState($this);
		} elseif ($state == self::STATE_JOKER) {
			$this->state = new TrainingState\JokerState($this);
		} elseif ($state == self::STATE_PREBUY) {
			$this->state = new TrainingState\PrebuyState($this);
		} elseif ($state == self::STATE_BUY) {
			$this->state = new TrainingState\BuyState($this);
		} elseif ($state == self::STATE_ROUND_END) {
			$this->state = new TrainingState\RoundEndState($this);
		} elseif ($state == self::STATE_END) {
			$this->state = new TrainingState\EndState($this);
		} 
		setcookie('gamestate', $state, time() + $this->cookietime, '/');
		setcookie('gamemaxbet', $this->maxbet, time() + $this->cookietime, '/');
		$this->syncTime();
		$this->minbet();
	}
	
	public function getState() {
		return $this->state;
	}
	
	public function getStateNo() {
		return $this->stateNo;
	}
	
	public function isState($state) {
		return $state == $this->stateNo;
	}
	
	public function start() {
		return $this->state->startGame();
	}
	
	public function change($cardNo, $question) {
		return $this->state->changeCards($cardNo, $question);
	}
	
	public function nochange() {
		return $this->state->noChangeCards();
	}
	
	public function answer($answerNo, $question) {
		return $this->state->answerQuestion($answerNo, $question);
	}
	
	public function bet($chips) {
		return $this->state->makeBet($chips);
	}
	
	public function check() {
		return $this->state->checkBet();
	}
	
	public function fold() {
		return $this->state->foldCards();
	}
	
	public function distribute($questions) {
		return $this->state->distributeWin($questions);
	}
	
	public function prebuy() {
		return $this->state->buyChips();
	}
	
	public function buy() {
		return $this->state->buyChips();
	}
	
	public function buyanswer($answerNo) {
		return $this->state->answerBuyQuestion($answerNo);
	}
	
	public function next() {
		return $this->state->nextGame();
	}
	
	public function endround() {
		return $this->state->endRound();
	}
	
	public function stop() {
		return $this->state->stopGame();
	}
	
	public function registerObserver(ObserverInterface $o) {
		throw new Exception\GameException('registerObserver not implemented');
	}
	
	public function removeObserver(ObserverInterface $o) {
		throw new Exception\GameException('removeObserver not implemented');
	}
	
	public function notifyObservers() {
		throw new Exception\GameException('notifyObserver not implemented');
	}
}
