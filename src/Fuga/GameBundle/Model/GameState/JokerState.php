<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;

class JokerState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function buyChips($gamer) {
		try {
		if (!$this->game->lock($gamer->getId())) {
			return $this->game->getStateNo();
		}
			if (time() > $this->game->stopbuytime) {
				if (!$this->game->existsGamers()) {
					$this->game->removeTimer();
					$this->game->stopTime();
					$this->game->setState(self::STATE_END);
				} else {
					$this->game->setTimer('next');
					$this->game->startTimer();
					$this->game->setState(self::STATE_ROUND_END);
				}
			} else {
				$this->game->setTimer('buy');
				$this->game->startTimer();
				$this->game->setState(AbstractState::STATE_BUY);
			}
			
			$this->game->setUpdated(time());
			$this->game->save();
			
			$this->game->unlock($gamer->getId());
		} catch (\Exception $e) {
			$this->game->unlock($gamer->getId());
		}
		return $this->game->getStateNo();
	}
	
	public function sync() {
		$gamer = null;
		$gamerdoc = $this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->game->getId())
				->field('state')->gt(0)
				->getQuery()->getSingleResult();
		$timer = $this->game->getTimer();
		$timer = array_shift($timer);
		if (!$timer || intval($timer['time'])+5 < time()) { 
			$this->game->container->get('log')->addError(
					'game'.$this->game->getId()
					.' :joker.find.outtimer '
					.(intval($timer['time']) - time())
			);
			$this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Board')
				->findAndUpdate()
				->field('board')->equals($this->game->getId())
				->field('gamer')->set(0)
				->getQuery()->execute();
			$gamer = new RealGamer($gamerdoc, $this->game->container);
		}
		
		if ($gamer) {
			$this->changeCards($gamer);			
		}
	}
	
}