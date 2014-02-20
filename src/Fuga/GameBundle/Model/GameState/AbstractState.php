<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Exception\GameException;

class AbstractState implements StateInterface {
	
	const STATE_BEGIN     = 0;
	const STATE_CHANGE    = 1;
	const STATE_PREFLOP   = 2;
	const STATE_FLOP      = 3;
	const STATE_SHOWDOWN  = 4;
	const STATE_JOKER	  = 41;
	const STATE_BUY       = 5;
	const STATE_END       = 6;
	const STATE_ROUND_END = 7;
	const STATE_WAIT      = 8;
	
	protected $game;
	
	public function __construct(GameInterface $game) {
		$this->game = $game;
	}
	
	public function startGame($gamer) {
		$this->game->container->get('log')->write('abstract startGame');
	}
	
	public function changeCards($gamer) {
		$this->game->container->get('log')->write('abstract changeCards');
	}
	
	public function makeBet($gamer) {
		$this->game->container->get('log')->write('abstract makeBet');
	}
	
	public function makeMove($gamer) {
		$this->game->container->get('log')->write('abstract makeMove');
	}
	
	public function distributeWin($gamer) {
		$this->game->container->get('log')->write('abstract distributeWin');
	}
	
	public function buyChips($gamer) {
		$this->game->container->get('log')->write('abstract buyChips');
	}
	
	public function answerBuyQuestion($gamer) { 
		$this->game->container->get('log')->write('abstract answeBuyQuestion');
	}
	
	public function nextGame($gamer) {
		$this->game->container->get('log')->write('abstract nextGame');;
	}

	public function endGame($gamer) {
		if (!$this->game->lock($gamer->getId())) {
			return $this->game->getStateNo();
		}
		try {
			$this->game->removeTimer();
			$this->game->stopTime();
			$this->game->setState(self::STATE_END);
			$this->game->save();
		} catch (\Exception $e) {
			$this->game->container->get('log')->write('END_GAME:'.$e->getMessage());
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
			$this->game->container->get('log')->write('END ROUND:'.$e->getMessage());
		}
		
		return $this->game->getStateNo();
	}
	
	public function wait() {
		$this->game->setTimer('noactive');
		$this->game->startTimer();
		$this->game->save();
		$this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Board')
				->findAndUpdate()
				->field('board')->equals($this->game->getId())
				->field('gamer')->set(0)
				->getQuery()->execute();
		$this->game->setState(self::STATE_WAIT);
		
		return $this->game->getStateNo();
	}
	
	public function sync() {
		$this->game->container->get('log')->write('abstract sync');
	}
	
}