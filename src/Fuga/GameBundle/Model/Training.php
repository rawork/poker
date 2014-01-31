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
	public $bots;
	public $gamer;
	public $board;
	public $timer;
	public $fromtime;
	public $stopbuytime;
	
	private $log;
	
	public function __construct(array $gamer, $log) {
		$this->log   = $log;
		$this->timer = new Timer();
		$this->deck  = new Deck();
		$this->createGamer(new TrainingGamer($gamer));
		$this->createBots(3);
		$this->createBoard($gamer['user_id']);
		$this->setTime();
	}
	
	public function createGamer(TrainingGamer $gamer) {
		$this->gamer = $gamer;
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
		foreach ($this->bots as $bot) {
			$bot->cards = $this->deck->take(4);
			$bot->chips = 100;
		}
		$this->board->state = self::STATE_CHANGE;
		$this->board->flop = $this->deck->take(3);
		$this->fromtime = new \DateTime();
		$this->stopbuytime = new \DateTime();
		$this->stopbuytime->add(new \DateInterval('P35M'));
		$this->setTime();
		$this->timer->set('onClickNoChange', 'change-timer', 14);
	}
	
	public function setChange($cards, $question) {
		$this->gamer->change = $cards;
		$this->gamer->question = $question;
		$this->board->state = self::STATE_QUESTION;
		
		return $this;
	}
	
	public function nochange() {
		$this->board->state = self::STATE_PREFLOP;
		$this->timer->stop();
		
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
		$this->timer->stop();
		
		return $this;
	}
	
	public function next() {
		if ($this->gamer->chips <= 0) {
			return $this->end();
		}
		$this->deck->make();
		$this->gamer->cards = $this->deck->take(4);
		$botsWithCards = 0;
		foreach ($this->bots as $bot) {
			if ($bot->chips <= 0) {
				continue;
			}
			$bot->cards = $this->deck->take(4);
			$botsWithCards++;
		}
		if ($botsWithCards == 0) {
			return $this->end();
		}
		$this->board->state = self::STATE_CHANGE;
		$this->board->flop = $this->deck->take(3);
		$this->timer->set('onClickNoChange', 'change-timer', 14);
		
		return $this;
	}
	
	public function end() {
		$this->board->winner = null;
		$this->board->combination = null;
		$this->fromtime = null;
		$this->board->state = self::STATE_END;
		$this->bots = array();
		$this->gamer->cards = array();
		$this->timer->stop();
		$this->setTime();
		
		return $this;
	}
	
	public function stop() {
		$this->fromtime = null;
		$this->board->state = self::STATE_NOSTART;
		$this->bots = array();
		$this->gamer->cards = array();
		$this->timer->stop();
		$this->setTime();
		
		return $this;
	}
	
	public function bet($bet) {
		$allin = ($bet == $this->gamer->chips);
		$this->gamer->chips -= $bet;
		$this->board->bank += $bet;
		
		foreach ($this->bots as $bot) {
			if ($bot->chips <=0) {
				continue;
			}

			$botbet = $bet;
			
			if ($allin && $bot->chips < $bet) {
				$botbet = $bot->chips;
			}
			
			$bot->chips -= $botbet;
			$this->board->bank += $botbet; 
		}
		
		$this->board->state += 1;
		if ($this->gamer->chips <= 0) {
			$this->board->state = Training::STATE_SHOWDOWN;
		}
		
		if ($this->board->state == Training::STATE_SHOWDOWN) {
			$this->showdown();
		}
		
		return $this;
	}
	
	public function fold() {
		$bet = rand(5,10);
		foreach ($this->bots as $bot) {
			if ($bot->chips <=0) {
				continue;
			}
			$botbet = $bot->chips < $bet ? $bot->chips : $bet;
			$bot->chips -= $botbet;
			$this->board->bank += $botbet; 
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
			foreach ($this->bots as $bot) {
				if ($bot->position == $winner['position']) {
					$bot->chips += $share;
					break;
				}
			}
		}
		$this->gamer->cards  = array();
		$this->board->winner = array();
		$this->board->combination = array();
		$this->board->flop   = array();
		$this->board->bank   = 0;
		$this->board->bets   = 0;
		$this->board->allin  = 0;
		$botsWithoutMoney = 0;
		foreach ($this->bots as $bot) {
			if ($bot->chips <= 0) {
				$botsWithoutMoney++;
				continue;
			}
			$bot->cards = array();
		}
		if ($botsWithoutMoney == count($this->bots) || $this->gamer->chips <=0) {
			return $this->end();
		}
		$now = new \Datetime();
		if ($now > $this->stopbuytime) {
			$this->board->state = Training::STATE_CHANGE;
			$this->timer->set('onClickNoChange', 'change-timer', 14);
		} else {
			$this->board->state = Training::STATE_BUY;
			$this->timer->set('nextGame', 'question-timer', 121);
		}
		
		
		return $this;
	}
	
	public function buying($answer_no = null) {
		if (!is_null($answer_no) && $this->gamer->question) {
			if ($answer_no == $this->gamer->question['answer']) {
				$this->gamer->chips += $this->board->minbet;
			}
		}
		if ($this->gamer->buying) {
			$this->gamer->question = array_shift($this->gamer->buying);
			$this->gamer->question['number'] = 3 - count($this->gamer->buying);
		} else {
			$this->gamer->buying = null;
			$this->gamer->question = null;
			$this->next();
		}
		
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
				if (!$bot->cards) {
					continue;
				}
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
			$combinations = array();
			foreach ($winners as $winner) {
				foreach ($winner['cards'] as $card) {
					$combinations[$card['name']] = 1;
				}
			}
			$this->board->combination = $combinations;
			$this->timer->set('distributeWin', 'game-timer', 14);
		}
		
		return $this;
	}
	
	public function bet1() {
		foreach($this->bots as $bot) {
			$bot->bet($this->board->minbet);
			$this->board->acceptBet($bot->takeBet());
		}
		$this->gamer->bet($this->board->minbet);
		$this->board->acceptBet($this->gamer->takeBet());
		$this->board->confirmBets();
	}
	
	public function setTime() {
		if ($this->fromtime) {
			$now = new \DateTime();
			$diff = $now->diff($this->fromtime);
			setcookie('gamehour', intval($diff->format('%H')), time()+86400, '/');
			setcookie('gameminute', intval($diff->format('%i')), time()+86400, '/');
			setcookie('gamesecond', intval($diff->format('%s')), time()+86400, '/');
		} else {
			setcookie('gamehour', 0, time()-86400, '/');
			setcookie('gameminute', 0, time()-86400, '/');
			setcookie('gamesecond', 0, time()-86400, '/');
		}
	}
	
}
