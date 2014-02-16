<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;

class FlopState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function makeMove($gamer) {
		try {
			if (!$this->game->lock($gamer->getId())) {
				return $this->game->getStateNo();
			}
			if ($gamer->getBet() > $this->game->getMaxbet()) {
				$this->game->setMaxbet($gamer->getBet());
			}
			if ($gamer->getAllin()) {
				if ($this->game->getBank2() == 0) {
					$this->game->setBank2($this->game->getBank());
				}
			}
			$gamers = $this->game->container->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->findAndUpdate()
					->field('board')->equals($this->game->getId())
					->field('active')->equals(true)
					->field('state')->notEqual(1)
					->field('fold')->set(true)
					->field('cards')->set(array())
					->getQuery()->execute();
			
			$this->game->stopTimer();
			// Find who not FOLD
			$gamers = $this->game->container->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->field('board')->equals($this->game->getId())
					->field('active')->equals(true)
					->field('fold')->equals(false)
					->getQuery()->execute();

			if (count($gamers) < 2) {
				$this->game->confirmBets();
				$this->game->setWinner();
				$this->game->setTimer('distribute');
				$this->game->startTimer();
				$this->game->setState(AbstractState::STATE_SHOWDOWN);
			} else {
				$gamers = $this->game->container->get('odm')
						->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
						->field('board')->equals($this->game->getId())
						->field('active')->equals(true)
						->field('state')->equals(1)
						->field('fold')->equals(false)
						->field('allin')->equals(false)
						->field('bet')->lt($this->game->getMaxbet())
						->getQuery()->execute();
				if (count($gamers) ==  0) {
					$this->game->confirmBets();
					$this->game->setWinner();
					$this->game->setTimer('distribute');
					$this->game->startTimer();
					$this->game->setState(AbstractState::STATE_SHOWDOWN);
				} else {
					$this->game->nextMover();
					$this->game->setTimer('bet');
				}
			}

			$this->game->save();
			$this->game->unlock($gamer->getId());
		} catch (\Exception $e) {
			$this->game->container->get('log')->write('STATE:'.$e->getMessage());
			$this->game->unlock($gamer->getId());
		}
		
		return $this->game->getStateNo();
	}
	
}