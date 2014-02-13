<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;
use Fuga\GameBundle\Model\RealGamer;

class ChangeState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function changeCards($gamer) {
		$gamers = $this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('times')->gt(0)
				->field('state')->equals(1)
				->field('board')->equals($this->game->getId())
				->getQuery()->execute();
		if (count($gamers) == 0) {
			if (!$this->game->lock($gamer->getId())) {
				return $this->game->getStateNo();
			}
			$this->game->setState(AbstractState::STATE_PREFLOP);
			$this->game->nextDealer();
			$this->game->nextMover();
			$this->game->setTimer('bet');
			$this->game->save();
			$this->game->unlock($gamer->getId());
			if ($this->game->isMover($gamer->getSeat())) {
				$this->game->startTimer();
			}
		}
		
		return $this->game->getStateNo();
	}
	
}