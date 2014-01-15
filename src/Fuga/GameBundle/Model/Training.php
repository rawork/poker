<?php

namespace Fuga\GameBundle\Model;

class Training {
	
	const STATE_NOSTART   = 0;
	const STATE_CHANGE    = 1;
	const STATE_QUESTION  = 11;
	const STATE_PREFLOP   = 2;
	const STATE_FLOP      = 3;
	const STATE_SHOWDOWN  = 4;
	const STATE_BUY       = 5;
	const STATE_END       = 6;
	
	public $deck;
	public $bots = array();
	public $gamer;
	public $board;
	
	private $log;
	
	public function __construct(array $gamer, $log) {
		$this->log = $log;
		$this->deck = new Deck();
		$this->createGamer($gamer);
		$this->createBots(5);
		$this->createBoard($gamer['user_id']);
		$this->setTime();
	}
	
	public function createGamer($gamer) {
		$this->gamer = new TrainingGamer($gamer);
		$this->gamer->position = 0;
	}
	
	private function createBots($n = 3) {
		$n = $n < 1 ? 1 : $n > 5 ? 5 : $n;
		$this->bots = array();
		for ($i = 1; $i <= $n; $i++) {
			$bot = new Bot($i);
			$bot->position = $this->getPosition($i, $n);
			$this->bots[] = $bot;
		}
	}
	
	public function createBoard($user_id) {
		$this->board = new Board($user_id);
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
	
	public function start() {
		$this->deck->make();
		$this->gamer->cards = $this->deck->take(4);
		$this->gamer->chips = 100;
		foreach ($this->bots as &$bot) {
			$bot->cards = $this->deck->take(4);
			$bot->chips = 100;
		}
		$this->board->state = self::STATE_CHANGE;
		$this->board->flop = $this->deck->take(3);
		$this->board->fromdate = new \DateTime();
		$this->firstBet();
		$this->setTime();
		$this->setTimer('onClickNoChange', 0, 14);
	}
	
	public function setChange($cards, $question) {
		$this->gamer->change = $cards;
		$this->gamer->question = $question;
		$this->board->state = self::STATE_QUESTION;
		
		return $this;
	}
	
	public function nochange() {
		$this->board->state = self::STATE_PREFLOP;
		$this->setTimer();
		
		return $this;
	}
	
	public function makeChange($answerNo) {
		if ($answerNo == $this->gamer->question['answer']) {
			foreach ($this->gamer->change as $cardNo) {
				$cards = $this->gamer->cards;
				$cards[$cardNo] = array_shift($this->deck->take(1));
				$this->gamer->cards = $cards;
			}
		} else {
			$this->gamer->chips -= $this->board->minbet;
		}
		$this->gamer->change = null;
		$this->gamer->question = null;
		$this->board->state = self::STATE_PREFLOP;
		$this->setTimer();
		
		return $this;
	}
	
	public function next() {
		$this->deck->make();
		$this->gamer->cards = $this->deck->take(4);
		foreach ($this->bots as &$bot) {
			$bot->cards = $this->deck->take(4);
		}
		$this->board->state = self::STATE_CHANGE;
		$this->board->flop = $this->deck->take(3);
		$this->firstBet();
		$this->setTimer('onClickNoChange', 0, 14);
		
		return $this;
	}
	
	public function end() {
		$this->board->state = self::STATE_END;
		$this->bots = array();
		$this->gamer->cards = array();
		
		return $this;
	}
	
	public function bet($bet) {
		$allin = ($bet == $this->gamer->chips);
		$this->gamer->chips -= $bet;
		$this->board->bank += $bet;
		
		foreach ($this->bots as $bot) {
			if ($allin) {
				$bet = $bot->chips;
			}
			$bot->chips -= $bet;
			$this->board->bank += $bet; 
		}
		
		$this->board->state += 1;
		
		if ($this->board->state == Training::STATE_SHOWDOWN) {
			$this->showdown();
		}
		
		return $this;
	}
	
	public function fold() {
		$bet = rand(5,10);
		foreach ($this->bots as &$bot) {
			$bot->chips -= $bet;
			$this->board->bank += $bet; 
		}
		$this->gamer->cards = array();
		$this->board->state = Training::STATE_SHOWDOWN;
		$this->showdown();
		
		return $this;
	}
	
	public function win() {
		$numWin = count($this->board->winner);
		$share = intval($this->board->bank / $numWin);
		foreach ($this->board->winner as $winner) {
			if ($winner['position'] == 0) {
				$this->gamer->chips += $share;
				break;
			}
			foreach ($this->bots as &$bot) {
				if ($bot->position == $winner['position']) {
					$bot->chips += $share;
					break;
				}
			}
		}
		foreach ($this->bots as &$bot) {
			$bot->cards = array();
		}
		$this->gamer->cards  = array();
		$this->board->winner = array();
		$this->board->flop   = array();
		$this->board->bank   = 0;
		$this->board->bets   = 0;
		$this->board->allin  = 0;
		$this->board->state = Training::STATE_BUY;
		$this->setTimer('nextGame', 0, 14);
		
		return $this;
	}
	
	public function showdown() {
		if (self::STATE_SHOWDOWN == $this->board->state) {
			$combination = new Combination();
			$suites = array();
			$this->log->write('SHOWDOWN');
			$this->log->write('FLOP');
			$this->log->write(serialize($this->board->flop));
			foreach ($this->bots as $bot) {
				$this->log->write('BOT'.$bot->id);
				$this->log->write(serialize($bot->cards));
				$cards = $combination->get(array_merge($bot->cards, $this->board->flop));
				$cards['position'] = $bot->position;
				$cards['name'] = $combination->rankName($cards['rank']);
				$this->log->write(serialize($cards));
				$suites[] = $cards;
			}
			$this->log->write('GAMER');
			$this->log->write(serialize($this->gamer->cards));
			$cards = $combination->get(array_merge($this->gamer->cards, $this->board->flop));
			$cards['position'] = $this->gamer->position;
			$cards['name'] = $combination->rankName($cards['rank']);
			$this->log->write(serialize($cards));
			$suites[] = $cards;
			$winners = $combination->compare($suites);
			$this->board->winner = $winners;
			$this->setTimer('distributeWin', 0, 30);
		}
		
		return $this;
	}
	
	public function firstBet() {
		foreach($this->bots as &$bot) {
			$bot->bet($this->board->minbet);
			$this->board->acceptBet($bot->takeBet());
		}
		$this->gamer->bet($this->board->minbet);
		$this->board->acceptBet($this->gamer->takeBet());
		$this->board->confirmBets();
	}
	
	public function setTime() {
		if (!$this->board->fromdate) {
			setcookie('gamehour', 0, time()-86400, '/');
			setcookie('gameminute', 0, time()-86400, '/');
			setcookie('gamesecond', 0, time()-86400, '/');
		} else {
			$now = new \DateTime();
			$diff = $now->diff($this->board->fromdate);
			setcookie('gamehour', intval($diff->format('%H')), time()+86400, '/');
			setcookie('gameminute', intval($diff->format('%i')), time()+86400, '/');
			setcookie('gamesecond', intval($diff->format('%s')), time()+86400, '/');
		}
	}
	
	public function setTimer($handler = '', $minutes = 0, $seconds = 0) {
		if ($minutes == 0 && $seconds == 0) {
			setcookie('timerhandler', '', time()-86400, '/');
			setcookie('timerminute', 0, time()-86400, '/');
			setcookie('timersecond', 0, time()-86400, '/');
		} else {
			setcookie('timerhandler', $handler, time()+86400, '/');
			setcookie('timerminute', $minutes, time()+86400, '/');
			setcookie('timersecond', $seconds, time()+86400, '/');
		}
		
	}
	
}
