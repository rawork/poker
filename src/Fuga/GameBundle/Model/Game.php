<?php

namespace Fuga\GameBundle\Model;

use Fuga\GameBundle\Model\Combination;
use Fuga\GameBundle\Model\Deck;
use Fuga\GameBundle\Model\RealGamer;
use Fuga\GameBundle\Document\Board;
use Fuga\GameBundle\Model\Exception\GameException;
use Fuga\Component\Container;

class Game implements GameInterface {
	
	const STATE_BEGIN     = 0;
	const STATE_CHANGE    = 1;
	const STATE_PREFLOP   = 2;
	const STATE_FLOP      = 3;
	const STATE_SHOWDOWN  = 4;
	const STATE_JOKER	  = 41;
	const STATE_BUY       = 5;
	const STATE_END       = 6;
	const STATE_ROUND_END = 7;
	const STATE_WAIT = 8;

	public $minbet = 1;
	public $stopbuytime;
	public $container;
	
	private $state;
	private $doc;
	
	private $cookietime = 7776000;
	private $upTimer    = 780;
	private $repo       = '\Fuga\GameBundle\Document\Board';
	
	private $timers     = array(
		'begin'      => array('handler' => 'onStart', 'holder' => 'begin-timer', 'time' => 0),
		'change'     => array('handler' => 'onClickNoChange', 'holder' => 'change-timer', 'time' => 31),
		'distribute' => array('handler' => 'onDistribute', 'holder' => 'game-timer', 'time' => 31),
		'prebuy'     => array('handler' => 'onShowBuy', 'holder' => 'joker-timer', 'time' => 6),
		'next'		 => array('handler' => 'onNext', 'holder' => 'round-end-timer', 'time' => 14),
		'nonactive'  => array('handler' => 'onNext', 'holder' => 'wait-timer', 'time' => 61),
		'bet'        => array('handler' => 'onFold', 'holder' => 'game-timer', 'time' => 31),
		'buy'        => array('handler' => 'onEndRound', 'holder' => 'buy-timer', 'time' => 121),
		'answer'     => array('handler' => 'onNoAnswer', 'holder' => 'answer-timer', 'time' => 14),
		
	);
	
	public function __construct( $id, Container $container) {
		$this->container = $container;
		$this->doc = $this->container->get('odm')
				->getRepository($this->repo)
				->findOneByBoard(intval($id));
		
		$board = $this->container->getItem('game_board', intval($id));
		if (!$board || !$this->doc) {
			throw new GameException('Не создан зал для игры. Обратитесь к администратору.');
		}
		
		$this->doc->setFromtime(strtotime($board['fromtime']));
		$this->save();
		
		if ($this->doc->getFromtime() > time()) {
			$this->setTimer('begin');
			$this->startTimer();
		}
		
		$this->stopbuytime = $this->doc->getFromtime() + 35*60;
		$this->setState($this->doc->getState());
		$this->syncTime();
		$this->setMinbet();
	}
	
	public function getId() {
		return $this->doc->getBoard();
	}
	
	public function getName() {
		return $this->doc->getName();
	}
	
	public function getMover() {
		return $this->doc->getMover();
	}
	
	public function getWinner() {
		return $this->doc->getWinner();
	}
	
	public function getTimer() {
		return $this->doc->getTimer();
	}
	
	public function getMaxbet() {
		return $this->doc->getMaxbet();
	}
	
	public function setMover($value) {
		$this->doc->setMover($value);
	}
	
	public function setUpdated($value) {
		$this->doc->setUpdated($value);
	}

	public function newDeck(){
		$deck = new Deck();
		$this->doc->setCards($deck->take());
	}
	
	public function getCards($num) {
		$deck = $this->doc->getCards();
		$cards = array();
		for ($i = 0; $i < $num; $i++) {
			$cards[] = array_shift($deck);
		}
		$this->doc->setCards($deck);
		
		return $cards;
	}
	
	public function setFlop(array $flop) {
		$this->doc->setFlop($flop);
	}
	
	public function getFlop() {
		return $this->doc->getFlop();
	}
	
	
	public function acceptBet($bet) {
		$this->doc->setBets( $this->doc->getBets() + $bet );
		$this->save();
	}
	
	public function confirmBets() {
		$this->doc->setBank($this->getBank() + $this->getBets());
		$this->doc->setBets(0);
		$this->save();
	}
	
	public function takeBank() {
		$chips = $this->doc->getBank();
		$this->doc->setBank(0);
		$this->save();
		return $chips;
	}
	
	public function getDealer() {
		return $this->doc->getDealer();
	}
	
	public function getFromtime() {
		return $this->doc->getFromtime();
	}
	
	public function getBets() {
		return $this->doc->getBets();
	}
	
	public function setBets($value) {
		$this->doc->setBets($value);
	}
	
	public function setMaxbet($value) {
		$this->doc->setMaxbet($value);
	}
	
	public function getBank() {
		return $this->doc->getBank();
	}
	
	public function setBank($value) {
		$this->doc->setBank($value);
	}
	
	public function getBank2() {
		return $this->doc->getBank2();
	}
	
	public function setBank2($value) {
		$this->doc->setBank2($value);
	}
	
	public function getAllin() {
		return $this->doc->getAllin();
	}
	
	public function setAllin($value) {
		$this->doc->setAllin($value);
	}
	
	public function setRound($value) {
		$this->doc->setRound($value);
	}
	
	public function getRound() {
		return $this->doc->getRound();
	}
	
	public function setMinbet() {
		if (time() > $this->doc->getFromtime()) {
			$seconds = time() - $this->doc->getFromtime();
			$this->minbet = ($seconds - $seconds % $this->upTimer) / $this->upTimer + 1;
		} else {
			$this->minbet = 1;
		}		
		
		return $this;
	}
	
	public function existsJoker() {
		$cards = $this->doc->getCards();
		foreach ($cards as $card) {
			if ('joker' == $card['name']) {
				$this->doc->setPrizes($this->doc->getPrizes() + 1);
				return true; 
			}
		}
		
		return false;
	}
	
	public function existsGamers() {
		$gamers = $this->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->getId())
				->field('active')->equals(true)
				->getQuery()->execute();
				
		return count($gamers) > 1;
	}
	
	public function syncTime() {
		if (!$this->doc->getEndtime() && time() > $this->doc->getFromtime() ) {
			setcookie('gamefromtime', $this->doc->getFromtime(), time()+$this->cookietime, '/');
			setcookie('gameboardid', $this->doc->getBoard(), time()+$this->cookietime, '/');
			setcookie('gamestopbuytime', $this->stopbuytime, time()+$this->cookietime, '/');
		} else {
			setcookie('gamefromtime', null, time()+$this->cookietime, '/');
			setcookie('gameboardid', null, time()+$this->cookietime, '/');
			setcookie('gamestopbuytime', null, time()+$this->cookietime, '/');
		}
	}
	
	public function stopTime() {
		$this->doc->setEndtime(time());
		setcookie('gamefromtime', null, time()+$this->cookietime, '/');
		setcookie('gamestopbuytime', null, time()+$this->cookietime, '/');
	}
	
	public function setTimer($name) {
		if (array_key_exists($name, $this->timers) && $this->timers[$name]) {
			$timer = $this->timers[$name];
			if ('begin' == $name) {
				$timer['time'] = $this->doc->getFromtime();
			} else {
				$timer['time'] = time() + $this->timers[$name]['time'];
			}
			$this->doc->setTimer(array($timer));
			$this->save();
		}
	}
	
	public function startTimer() {
		$timer = array_shift($this->doc->getTimer());
		if (is_array($timer) && isset($timer['handler'])) {
			setcookie('timerholder', $timer['holder'], time()+$this->cookietime, '/');
			setcookie('timerhandler', $timer['handler'], time()+$this->cookietime, '/');
			setcookie('timerstop',  $timer['time'], time()+$this->cookietime, '/');
		}
		
		return $this;
	}
	
	public function stopTimer() {
		setcookie('timerholder', '', time()+$this->cookietime, '/');
		setcookie('timerhandler', '', time()+$this->cookietime, '/');
		setcookie('timerstop',  0, time()+$this->cookietime, '/');
	}
	
	public function removeTimer() {
		$this->stopTimer();
		$this->doc->setTimer(array());
		$this->save();
	}
	
	public function setState($state) {
		$this->doc->setState($state);
		
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
		} elseif ($state == self::STATE_BUY) {
			$this->state = new GameState\BuyState($this);
		} elseif ($state == self::STATE_END) {
			$this->state = new GameState\EndState($this);
		} elseif ($state == self::STATE_ROUND_END) {
			$this->state = new GameState\RoundEndState($this);
		}  elseif ($state == self::STATE_WAIT) {
			$this->state = new GameState\WaitState($this);
		} 
		
		setcookie('gamestate', $state, time() + $this->cookietime, '/');
		setcookie('gamemaxbet', $this->doc->getMaxbet(), time() + $this->cookietime, '/');
		setcookie('gamemover', $this->doc->getMover(), time() + $this->cookietime, '/');
		setcookie('gamename', $this->doc->getName(), time() + $this->cookietime, '/');
		$this->syncTime();
		$this->setMinbet();
	}
	
	public function getState() {
		return $this->state;
	}
	
	public function getStateNo() {
		return $this->doc->getState();
	}
	
	public function isState($state) {
		return $state == $this->doc->getState();
	}
	
	public function isStarted() {
		return time() >= $this->doc->getFromtime();
	}
	
	public function numOfGamers() {
		$gamers = $this->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->getId())
				->getQuery()->execute();
		return count($gamers);
	}
	
	public function emptyWinner() {
		$this->doc->setWinner(array());
		$this->doc->setCombination(array());
	}
	
	public function setWinner() {
		$combination = new Combination();
		$suites = array();
		$allins = array();
		$gamers = $this->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->getId())
				->field('active')->equals(true)
				->field('state')->gt(0)
				->field('fold')->equals(false)
				->getQuery()->execute();
		foreach ($gamers as $gamer) {
			$cards = $combination->get($gamer->getCards(), $this->getFlop());
			$cards['user'] = $gamer->getUser();
			$cards['bank'] = $gamer->getBank();
			$cards['win']  = 0;
			$cards['seat'] = $gamer->getSeat();
			$cards['numOfGamers'] = $this->numOfGamers();
			$cards['name'] = $combination->rankName($cards['rank']);
			$cards['allin'] = $gamer->getAllin();
			
			$allins[] = $cards;
			if (!$cards['allin']) {
				$suites[] = $cards;
			}
		}

		$winner = $combination->compare($suites);
		$allinWinner = $combination->compare($allins);
		$this->doc->setWinner(array_merge($winner, $allinWinner));
		$combinations = array();
		foreach ($this->doc->getWinner() as $winner) {
			foreach ($winner['cards'] as $card) {
				$combinations[] = $card['name'];
			}
			$this->container->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->findAndUpdate()
					->field('user')->equals(intval($winner['user']))
					->field('rank')->set('')
					->field('combination')->set(array())
					->field('winner')->set(true)
					->getQuery()->execute();
		}
		$this->doc->setCombination($combinations);
		$this->save();
	}
	
	public function nextDealer() {
		$gamer =  $this->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->getId())
				->field('active')->equals(true)
				->field('state')->gt(0)
				->field('seat')->gt($this->doc->getDealer())
				->sort('seat')
				->getQuery()
				->getSingleResult();
		if (!$gamer) {
			$gamer =  $this->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->getId())
				->field('active')->equals(true)
				->field('state')->gt(0)
				->field('seat')->gt(0)
				->sort('seat')
				->getQuery()
				->getSingleResult();
		}
		if (!$gamer) {
			return $this->waiting();
		}
		$this->doc->setMover($gamer->getSeat());
		$this->doc->setDealer($gamer->getSeat());
	}
	
	public function nextMover() {
		$gamer =  $this->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->getId())
				->field('active')->equals(true)
				->field('fold')->equals(false)
				->field('allin')->equals(false)
				->field('state')->gt(0)
				->field('seat')->gt($this->doc->getMover())
				->sort('seat')
				->getQuery()
				->getSingleResult();
		if (!$gamer) {
			$gamer =  $this->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->getId())
				->field('active')->equals(true)
				->field('fold')->equals(false)
				->field('allin')->equals(false)
				->field('state')->gt(0)
				->field('seat')->gt(0)
				->sort('seat')
				->getQuery()
				->getSingleResult();
		}
		if (!$gamer) {
			return $this->wait();
		}
		$this->doc->setMover($gamer->getSeat());
		
		return $gamer;
	}
	
	public function isDealer($seat) {
		return $seat == $this->doc->getDealer();
	}
	
	public function isMover($seat) {
		return $seat == $this->doc->getMover();
	}
	
	public function isCombination($cardName) {
		$combination = $this->doc->getCombination();
		
		if (is_array($combination)) {
			return in_array($cardName, $combination);
		}
		
		return false;
	}
	
	public function save() {
		return $this->container->get('odm')->flush();
	}
	
	public function clear() {
		$this->container->get('odm')->remove($this->doc);
		$this->save();
	}
	
	public function lock($gamerId) {
		try {
			$board = $this->container->get('odm')
					->createQueryBuilder($this->repo)
					->findAndUpdate()
					->returnNew()
					->field('gamer')->equals(0)
					->field('board')->equals($this->getId())
					->field('gamer')->set($gamerId)
					->getQuery()->execute();
			if (!$board) {
				throw new GameException('lock error');
			}
			
			return true;
		} catch (\Exception $e) {
			$this->container->get('log')->write('LOCK:'.$e->getMessage());
		}
		
		return false;
	}
	
	public function unlock($gamerId) {
		try {
			$board = $this->container->get('odm')
					->createQueryBuilder($this->repo)
					->findAndUpdate()
					->returnNew()
					->field('gamer')->equals($gamerId)
					->field('board')->equals($this->getId())
					->field('gamer')->set(0)
					->getQuery()->execute();
			if (!$board) {
				throw new GameException('unlock error');
			}

			return true;
		} catch (\Exception $e) {
			$this->container->get('log')->write('UNLOCK:'.$e->getMessage());
		}
		
		return false;
	}
	
	public function start($gamerId) {
		$this->state->startGame($gamerId);
	}
	
	public function fold($gamer) {
		$gamer->foldCards();
		$this->state->makeMove($gamer);
	}
	
	public function bet($gamer, $chips) {
		$this->acceptBet($gamer->bet($chips, $this->getMaxbet()));
		if ($gamer->getAllin()) {
			$gamer->setBank($this->getBank() + $this->getBets());
		}

		setcookie('gamemaxbet', $this->doc->getMaxbet(), time() + $this->cookietime, '/');
		$this->state->makeMove($gamer);
	}
	
	public function check($gamer) {
		$this->acceptBet($gamer->check($this->getMaxbet()));
		if ($gamer->getAllin()) {
			$gamer->setBank($this->getBank() + $this->getBets());
		}
		
		setcookie('gamemaxbet', $this->getMaxbet(), time() + $this->cookietime, '/');
		$this->state->makeMove($gamer);
	}
	
	public function distribute($gamer) {
		$this->state->distributeWin($gamer);
	}
	
	public function prebuy($gamer) {
		$this->state->buyChips($gamer);
	}
	
	public function buyanswer($gamer, $n) {
		$gamer->answerBuyQuestion($n);
		$this->state->answerBuyQuestion($gamer);
	}
	
	public function next($gamer) {
		$this->state->nextGame($gamer);
	}
	
	public function end($gamer) {
		$this->state->endGame($gamer);
	}
	
	public function endround($gamer) {
		$this->state->endRound($gamer);
	}
	
	public function change(RealGamer $gamer) {
		$this->state->changeCards($gamer);
	}
	
	public function nochange(RealGamer $gamer) {
		$gamer->removeTimer();
		$gamer->setUpdated(time());
		$gamer->save();
		$this->state->changeCards($gamer);
	}
	
	public function wait() {
		$this->state->wait();
	}
	
	public function sync() {
		$this->state->sync();
	}
	
}