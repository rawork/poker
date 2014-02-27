<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;
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
			$gamer->removeTimer();
			$gamers = $this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->game->getId())
				->field('active')->equals(true)
				->getQuery()->execute();
			foreach ($gamers as $doc) {
				if ($doc->getChips() > 0) {
					$doc->setChips($doc->getChips()-1);
				}
				if (!$doc->getFold()) {
					$doc->setFold($doc->getChips() <= 0);
				}
				$this->game->acceptBet(1);
			}
			$this->game->confirmBets();
			$this->game->setState(AbstractState::STATE_PREFLOP);
			$this->game->setTimer('bet');
			$this->game->setUpdated(time());
			$this->game->save();
			$this->game->unlock($gamer->getId());
			if ($this->game->isMover($gamer->getSeat())) {
				$this->game->startTimer();
			}
		} else {
//			$this->game->setUpdated(time());
//			$this->game->save();
		}
		
		return $this->game->getStateNo();
	}
	
	public function sync() {
		$gamer = null;
		$gamers = $this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->game->getId())
				->field('times')->gt(0)
				->field('state')->gt(0)
				->getQuery()->execute();
		foreach ($gamers as $gamerdoc) {
			$timer = $gamerdoc->getTimer();
			$timer = array_shift($timer);
			if ($timer && intval($timer['time'])+15 < time()) {
				$this->game->container->get('log')->addError(
						'game'.$this->game->getId()
						.'-gamer'.$gamerdoc->getUser()
						.' :change.find.outtimer '
						.(intval($timer['time']) - time())
				);

				$this->game->container->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Board')
					->findAndUpdate()
					->field('board')->equals($this->game->getId())
					->field('updated')->set(time())
					->field('gamer')->set(0)
					->getQuery()->execute();

				$gamerdoc->setTimer(array());
				$gamerdoc->setTimes(0);
				$this->game->save();
				if (!$gamer) {
					$gamer = new RealGamer($gamerdoc, $this->game->container); 
				}
			}
		}
		
		if ($gamer) {
			$this->game->nochange($gamer);
		}
	}
	
}