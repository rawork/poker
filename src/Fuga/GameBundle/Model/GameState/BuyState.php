<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;

class BuyState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function endRound($gamer) {
		try {
			$gamers = $this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->game->getId())
				->field('active')->equals(true)
				->getQuery()->execute();
			foreach ($gamers as $doc) {
				$doc->setActive($doc->getChips() > 0);
			}
			$this->game->save();
			if (!$this->game->existsGamers()) {
				$this->game->removeTimer();
				$this->game->stopTime();
				$this->game->setState(self::STATE_END);
			} else {
				$this->game->setTimer('next');
				$this->game->startTimer();
				$this->game->setState(self::STATE_ROUND_END);
			}
			$this->game->save();
			
		} catch (\Exception $e) {
			$this->game->container->get('log')->write('STATE:'.$e->getMessage());
		}
		
		return $this->game->getStateNo();
	}
	
}
