<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;

class 		BuyState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function endRound($gamer) {
		try {
			$gamers = $this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->game->getId())
				->getQuery()->execute();
			foreach ($gamers as $doc) {
				$doc->setActive($doc->getChips() >= $this->game->minbet);
				if (!$doc->getActive()) {
					$this->game->acceptBet($doc->getChips());
					$this->game->confirmBets();
					$doc->setChips(0);
				}
			}
			$this->game->confirmBets();
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
			$this->game->setUpdated(time());
			$this->game->save();
			
		} catch (\Exception $e) {
			
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
					.' :buy.find.outtimer '
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
			$this->endRound($gamer);			
		}
	}
	
}
