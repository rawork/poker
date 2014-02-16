<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\RealGamer;

class BeginState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function startGame($gamer) {
		if ($this->game->isStarted() && $this->game->isState(AbstractState::STATE_BEGIN)) {
			if (!$this->game->lock($gamer->getId())) {
				return $this->game->getStateNo();
			}
			$this->game->removeTimer();
			$this->game->newDeck();
			$gamers = $this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->game->getId())
				->field('active')->equals(true)
				->field('state')->equals(1)
				->getQuery()->execute();
			foreach ($gamers as $doc) {
				$doc->setCards($this->game->getCards(4));
			}
			$this->game->setFlop($this->game->getCards(3));
			$this->game->nextDealer();
			$this->game->nextMover();
			$this->game->setTimer('change');
			$this->game->setState(AbstractState::STATE_CHANGE);
			$this->game->syncTime();
			$this->game->save();
			$this->game->unlock($gamer->getId());
		}
		$this->game->startTimer();
		
		return $this->game->getStateNo();
	}
}