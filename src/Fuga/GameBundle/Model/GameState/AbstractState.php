<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;
use Fuga\GameBundle\Model\Exception\GameException;

class AbstractState implements StateInterface {
	
	const STATE_BEGIN    = 0;
	const STATE_CHANGE    = 1;
	const STATE_PREFLOP   = 2;
	const STATE_FLOP      = 3;
	const STATE_SHOWDOWN  = 4;
	const STATE_JOKER	  = 41;
	const STATE_BUY       = 5;
	const STATE_END       = 6;
	const STATE_ROUND_END = 7;
	
	protected $game;
	
	public function __construct(GameInterface $game) {
		$this->game = $game;
	}
	
	public function startGame($gamer) {
		throw new GameException('abstract startGame');
	}
	
	public function changeCards($gamer) {
		throw new GameException('abstract changeCards');
	}
	
	public function makeBet($gamer) {
		throw new GameException('abstract makeBet');
	}
	
	public function makeMove($gamer) {
		throw new GameException('abstract makeMove');
	}
	
	public function distributeWin($gamer) {
		throw new GameException('abstract distributeWin');
	}
	
	public function buyChips($gamer) {
		throw new GameException('abstract buyChips');
	}
	
	public function answerBuyQuestion($gamer) { 
		throw new GameException('abstract answeBuyQuestion');
	}
	
	public function nextGame($gamer) {
		throw new GameException('abstract nextGame');;
	}

		public function endGame($gamer) {
		try {
			$this->game->removeTimer();
			$this->game->stopTime();
			$this->game->setState(self::STATE_END);
			$this->game->save();
		} catch (\Exception $e) {
			$this->game->container->get('log')->write('STATE:'.$e->getMessage());
		}
		
		return $this->game->getStateNo();
	}
	
	public function endRound($gamer) {
		try {
			$this->game->setTimer('next');
			$this->game->startTimer();
			$this->game->setState(self::STATE_ROUND_END);
			$this->game->save();
			
		} catch (\Exception $e) {
			$this->game->container->get('log')->write('STATE:'.$e->getMessage());
		}
		
		return $this->game->getStateNo();
	}
	
}