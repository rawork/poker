<?php

namespace Fuga\GameBundle\Model\State;

use Fuga\GameBundle\Model\GameInterface;

class AbstractState implements StateInterface {
	
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
	
	protected $game;
	
	public function __construct(GameInterface $game) {
		$this->game = $game;
	}
	
	public function startGame() {
		$this->game->timer->stop();
		$this->game->deck->make();
		$this->game->gamer->cards = $this->game->deck->take(4);
		$this->game->gamer->chips = 10;
		$this->game->gamer->active = true;
		$this->game->gamer->bet = 0;
		$this->game->setBank(0);
		$this->game->bets = 0;
		$this->game->minbet = 0;
		foreach ($this->game->bots as $bot) {
			$bot->cards = $this->game->deck->take(4);
			$bot->chips = 10;
			$bot->active = 1;
			$bot->bet = 0;
		}
		
		$this->game->flop = $this->game->deck->take(3);
		$this->game->changes = 2;
		$this->game->startTime();
		$this->game->setTimer('change');
		$this->game->setState(AbstractState::STATE_CHANGE);
		
		return $this->game->getStateNo();
	}
	
	public function changeCards($cardNo, $question) {
		return -1;
	}
	
	public function noChangeCards() {
		return -1;
	}
	
	public function answerQuestion($answerNo, $question) {
		return -1;
	}
	
	public function makeBet($chips) {
		return -1;
	}
	
	public function checkBet(){
		return -1;
	}
	
	public function allinBet() {
		return -1;
	}
	
	public function foldCards() {
		return -1;
	}
	
	public function distributeWin($questions) {
		return -1;
	}
	
	public function buyChips() {
		return -1;
	}
	
	public function answerBuyQuestion($answerNo) { 
		return -1;
	}
	
	public function endGame() {
		return -1;
	}
	
	public function stopGame() {
		return -1;
	}
	
	public function nextGame() {
		return -1;
	}
	
}