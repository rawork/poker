<?php

namespace Fuga\GameBundle\Model\TrainingState;

use Fuga\GameBundle\Model\Combination;

abstract class AbstractState implements StateInterface {
	
	const STATE_BEGIN    = 0;
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
	
	protected $game;
	
	public function __construct($game) {
		$this->game = $game;
	}
	
	public function startGame() {
		$this->game->timer->stop();
		$this->game->deck->make();
		$this->game->gamer->cards = $this->game->deck->take(4);
		$this->game->gamer->chips = 10;
		$this->game->gamer->active = true;
		$this->game->gamer->bet = 0;
		$this->game->gamer->allin = false;
		$this->game->gamer->winner = false;
		$this->game->setBank(0);
		$this->game->bets = 0;
		$this->game->minbet = 0;
		foreach ($this->game->bots as $bot) {
			$bot->cards = $this->game->deck->take(4);
			$bot->chips = 10;
			$bot->active = 1;
			$bot->bet = 0;
			$bot->allin = false;
		}
		
		$this->game->flop = $this->game->deck->take(3);
		$this->game->changes = 2;
		$this->game->startTime();
		$this->game->setTimer('change');
		$this->game->setState(AbstractState::STATE_CHANGE);
		
		return $this->game->getStateNo();
	}
	
	public function changeCards($cardNo, $question) {
		
	}
	
	public function noChangeCards() {
		
	}
	
	public function answerQuestion($answerNo, $question) {
		
	}
	
	public function makeBet($chips) {
		
	}
	
	public function checkBet(){
		
	}
	
	public function allinBet() {
		
	}
	
	public function foldCards() {
		
	}
	
	public function distributeWin($questions) {
		
	}
	
	public function buyChips() {
		
	}
	
	public function answerBuyQuestion($answerNo) { 
		
	}
	
	public function endGame() {
		$this->game->timer->stop();
		$this->game->stopTime();
		$this->game->winner = null;
		$this->game->combination = null;
		$this->game->gamer->cards = array();
		$this->game->setState(self::STATE_END);
		
		return $this->game->getStateNo();
	}
	
	public function stopGame() {
		$this->game->timer->stop();
		$this->game->stopTime();
		$this->game->setState(self::STATE_BEGIN);
		
		return $this->game->getStateNo();
	}
	
	public function nextGame() {
		$this->game->timer->stop();
		$this->game->gamer->buying = null;
		$this->game->gamer->combination = null;
		$this->game->gamer->question = null;
		$this->game->deck->make();
		$this->game->gamer->cards = $this->game->deck->take(4);
		foreach ($this->game->bots as $bot) {
			$bot->cards = $bot->isActive() ? $this->game->deck->take(4) : null;
		}
		$this->game->flop = $this->game->deck->take(3);
		$this->game->setTimer('change');
		$this->game->setState(self::STATE_CHANGE);
		
		return $this->game->getStateNo();
	}
	
	public function endRound() {
		$this->game->setTimer('next');
		$this->game->setState(self::STATE_ROUND_END);
		
		return $this->game->getStateNo();
	}
	
	protected function setWinner() {
		$combination = new Combination();
		$suites = array();
		foreach ($this->game->bots as $bot) {
			if (!$bot->cards) {
				continue;
			}
			$cards = $combination->get($bot->cards, $this->game->flop);
			$cards['position'] = $bot->position;
			$cards['name'] = $combination->rankName($cards['rank']);
			$suites[] = $cards;
		}
		if ($this->game->gamer->cards) {
			$cards = $combination->get($this->game->gamer->cards, $this->game->flop);
			$cards['position'] = $this->game->gamer->position;
			$cards['name'] = $combination->rankName($cards['rank']);
			$suites[] = $cards;
		}
		$winners = $combination->compare($suites);
		$this->game->winner = $winners;
		$combinations = array();
		foreach ($winners as $winner) {
			if ($winner['position'] == 0) {
				$this->game->gamer->rank = null;
				$this->game->gamer->winner = true;
			}
			foreach ($winner['cards'] as $card) {
				$combinations[$card['name']] = 1;
			}
		}
		$this->game->combination = $combinations;
		$this->game->setTimer('distribute');
	}
	
}