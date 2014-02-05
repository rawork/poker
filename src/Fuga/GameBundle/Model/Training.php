<?php

namespace Fuga\GameBundle\Model;

class Training {
	
	const STATE_NOSTART   = 0;
	const STATE_CHANGE    = 1;
	const STATE_QUESTION  = 11;
	const STATE_PREFLOP   = 2;
	const STATE_FLOP      = 3;
	const STATE_SHOWDOWN  = 4;
	const STATE_JOKER	  = 41;
	const STATE_PREBUY    = 42;
	const STATE_BUY       = 5;
	const STATE_END       = 6;
	
	public $deck;
	public $bots;
	public $gamer;
	public $board;
	public $timer;
	public $fromtime;
	public $stopbuytime;
	private $state = 0;
	private $joker = false;
	
	private $upTimer      = 780;
	private $timers = array(
		'change' => false, //14
		'answer' => 14,
		'bet'    => 31,
		'win'    => 14,
		'joker'  => 14,
		'buy'    => 121,
	);
	
	private $log;
	
	public function __construct(array $gamer, $log) {
		$this->log   = $log;
		$this->timer = new Timer();
		$this->deck  = new Deck();
		$this->createGamer(new TrainingGamer($gamer));
		$this->createBots(5);
		$this->createBoard($gamer['user_id']);
		$this->setTime();
		$this->setState();
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
		$this->gamer->chips = 10;
		foreach ($this->bots as $bot) {
			$bot->cards = $this->deck->take(4);
			$bot->chips = 10;
		}
		$this->setState(self::STATE_CHANGE);
		$this->board->flop = $this->deck->take(3);
		$this->fromtime = new \DateTime();
		$this->stopbuytime = new \DateTime();
		$this->stopbuytime->add(new \DateInterval('PT35M'));
		$this->setTime();
		if ($this->timers['change']) {
			$this->timer->set('onClickNoChange', 'change-timer', $this->timers['change']);
		}
	}
	
	public function setChange($cards, $question) {
		$this->gamer->change = $cards;
		$this->gamer->question = $question;
		$this->setState(self::STATE_QUESTION);
		$this->timer->stop();
		
		return $this;
	}
	
	public function nochange() {
		$this->setState(self::STATE_PREFLOP);
		if ($this->timers['bet']) {
			$this->timer->set('onClickFold', 'game-timer', $this->timers['bet']);
		}
		
		return $this;
	}
	
	public function question(){
		if ($this->timers['answer']) {
		 $this->timer->set('onClickNoAnswer', 'question-timer', $this->timers['answer']);
		}
		
		return $this;
	}
	
	public function answer($answerNo) {
		if ($answerNo == $this->gamer->question['answer']) {
			foreach ($this->gamer->change as $cardNo) {
				$this->gamer->cards[$cardNo] = array_shift($this->deck->take(1));
			}
		} else {
			$this->gamer->chips -= $this->board->minbet;
		}
		$this->gamer->change = null;
		$this->gamer->question = null;
		$this->setState(self::STATE_PREFLOP);
		if ($this->timers['bet']) {
			$this->timer->set('onClickFold', 'game-timer', $this->timers['bet']);
		}
		
		return $this;
	}
	
	public function next() {
		$this->timer->stop();
		$this->gamer->question = null;
		$this->gamer->buying = null;
		if (!$this->gamer->isActive()) {
			return $this->end();
		}
		$this->deck->make();
		$this->gamer->cards = $this->deck->take(4);
		$botsWithCards = 0;
		foreach ($this->bots as $bot) {
			if (!$bot->isActive()) {
				continue;
			}
			$bot->cards = $this->deck->take(4);
			$botsWithCards++;
		}
		if (0 == $botsWithCards) {
			return $this->end();
		}
		$this->setState(self::STATE_CHANGE);
		$this->board->flop = $this->deck->take(3);
		if ($this->timers['change']) {
			$this->timer->set('onClickNoChange', 'change-timer', $this->timers['change']);
		}
		
		return $this;
	}
	
	public function end() {
		$this->board->winner = null;
		$this->board->combination = null;
		$this->fromtime = null;
		$this->setState(self::STATE_END);
		$this->bots = array();
		$this->gamer->cards = array();
		$this->timer->stop();
		$this->setTime();
		
		return $this;
	}
	
	public function stop() {
		$this->fromtime = null;
		$this->setState(self::STATE_NOSTART);
		$this->bots = array();
		$this->gamer->cards = array();
		$this->timer->stop();
		$this->setTime();
		
		return $this;
	}
	
	public function bet($bet) {
		$allin = ($bet == $this->gamer->chips);
		$this->board->acceptBet($this->gamer->bet($bet, $allin));
		
		foreach ($this->bots as $bot) {
			if (!$bot->isActive()) {
				continue;
			}
			$this->board->acceptBet($bot->bet($bet, $allin)); 
		}
		$this->board->confirmBets();
		$this->setState($this->getState() + 1);
		if ($this->gamer->chips <= 0) {
			$this->setState(self::STATE_SHOWDOWN);
		}
		
		if ($this->isState(self::STATE_SHOWDOWN)) {
			$this->showdown();
		} else {
			if ($this->timers['bet']) {
				$this->timer->set('onClickFold', 'game-timer', $this->timers['bet']);
			}
		}
		
		return $this;
	}
	
	public function fold() {
		foreach ($this->gamer->cards as $card) {
			if ('joker' == $card['name']) {
				$this->joker = true;
				break;
			}
		}
		$this->gamer->cards = array();
		foreach ($this->bots as $bot) {
			$this->board->acceptBet($bot->bet($this->board->minbet));
		}
		$this->board->confirmBets();
		$this->setState(self::STATE_SHOWDOWN);
		$this->showdown();
		
		return $this;
	}
	
	public function win($buying = null) {
		$bank = $this->board->takeBank();
		$numWin = count($this->board->winner);
		$share = $numWin ? ceil($bank / $numWin) : $bank;
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
		$this->gamer->active = $this->gamer->chips > 0;
		$this->gamer->emptyBet();
		$this->gamer->cards  = null;
		$this->board->winner = null;
		$this->board->combination = null;
		$this->board->flop   = null;
		$this->board->bets   = 0;
		$this->board->allin  = 0;
		$botsWithoutMoney = 0;
		foreach ($this->bots as $bot) {
			$bot->cards = null;
			$bot->emptyBet();
			$this->log->write('WIN_BOT_CHIPS'.$bot->id.':'.$bot->chips);
			if ($bot->chips <= 0) {
				$this->log->write('WIN_BOT_CHIPS'.$bot->id.':'.$bot->chips);
				$bot->active = false;
				$botsWithoutMoney++;
			}
		}
		if ($botsWithoutMoney == count($this->bots) || !$this->gamer->isActive()) {
			return $this->end();
		}
		$deck = $this->deck->take();
		$this->joker = true;
		foreach ($deck as $card) {
			if ('joker' == $card['name']) {
				$this->joker = false;
				break;
			}
		} 
		// TODO поискать джокера в остатке колоды
		if ($this->joker) {
			$this->setState(self::STATE_JOKER);
			$this->joker = false;
		}
		$this->gamer->buying = $buying;
		$now = new \Datetime();
		if ($this->isState(self::STATE_JOKER)) {
			if ($this->timers['joker']) {
				$this->timer->set('onJoker', 'joker-timer', $this->timers['joker']);
			}
			
		} elseif ($now > $this->stopbuytime) {
			$this->next();
		} else {
			$this->setState(self::STATE_BUY);
			$this->buy();
		}
		
		return $this;
	}
	
	public function joker() {
		$now = new \Datetime();
		if ($now > $this->stopbuytime) {
			$this->next();
		} else {
			$this->setState(self::STATE_BUY);
			$this->buy();
		}
		
		return $this;
	}
	
	public function buy($answer_no = null) {
		if (is_null($answer_no)) {
			if ($this->timers['buy']) {
				$this->timer->set('onClickNext', 'question-timer', $this->timers['buy']);
			}
		}
		if (!is_null($answer_no) && $this->gamer->question) {
			if ($answer_no == $this->gamer->question['answer']) {
				$this->gamer->chips += $this->board->minbet;
			}
		}
		if (is_array($this->gamer->buying) && count($this->gamer->buying) > 0) {
			$this->gamer->question = array_shift($this->gamer->buying);
			$this->gamer->question['number'] = 3 - count($this->gamer->buying);
		} else {
			$this->next();
		}
		
		return $this;
	}
	
	public function showdown() {
		if ($this->isState(self::STATE_SHOWDOWN)) {
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
			if ($this->timers['win']) {
				$this->timer->set('onWin', 'game-timer', $this->timers['win']);
			}
		}
		
		return $this;
	}
	
	public function minbet() {
		if ($this->fromtime) {
			$now = new \DateTime();
			$seconds = $now->getTimestamp() - $this->fromtime->getTimestamp();
			$this->board->minbet = ($seconds - $seconds % $this->upTimer) / $this->upTimer + 1;
		} else {
			$this->board->minbet = 1;
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
	
	public function setState($state = 0) {
		$this->state = $state;
		setcookie('gamestate', $this->state, time() + 3600*24*365, '/');
		$this->setTime();
		$this->minbet();
	}
	
	public function getState() {
		return $this->state;
	}
	
	public function isState($state) {
		return $this->state == $state; 
	}
}
