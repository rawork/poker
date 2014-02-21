<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;

class BeginState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function startGame($gamer) {
		if ($this->game->isStarted()) {
			if (!$this->game->lock($gamer->getId())) {
				return $this->game->getStateNo();
			}
			$this->game->removeTimer();
			$this->game->newDeck();
			$gamers = $this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->game->getId())
				->field('active')->equals(true)
				->getQuery()->execute();
			foreach ($gamers as $doc) {
				if ($doc->getState() > 0) {
					$doc->setCards($this->game->getCards(4));
					$doc->setMove('nomove');
					$doc->setTimer(array(array(
						'handler' => 'onClickNoChange', 
						'holder' => 'change-timer', 
						'time' => time() +31
					)));
				} else {
					$doc->setFold(true);
					$doc->setTimes(0);
				}
			}
			$this->game->setFlop($this->game->getCards(3));
			$this->game->nextDealer();
			$this->game->nextMover();
			$this->game->setState(AbstractState::STATE_CHANGE);
			$this->game->syncTime();
			$this->game->setUpdated(time());
			$this->game->save();
			$this->game->unlock($gamer->getId());
		}
		$this->game->startTimer();
		
		return $this->game->getStateNo();
	}
	
}