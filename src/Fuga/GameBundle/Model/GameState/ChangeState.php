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
			$gamer->removeTimer();
			$this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->findAndUpdate()
				->field('board')->equals($this->game->getId())
				->field('active')->equals(true)
				->field('state')->equals(0)
				->field('fold')->set(true)
				->field('cards')->set(array())
				->getQuery()->execute();
			$gamers = $this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->game->getId())
				->field('active')->equals(true)
				->field('state')->gt(0)
				->field('fold')->equals(false)
				->getQuery()->execute();
			foreach ($gamers as $doc) {
				$doc->setChips($doc->getChips()-1);
				$doc->setFold(!($doc->getChips() > 0));
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
		}
		
		return $this->game->getStateNo();
	}
	
	public function sync($gamer) {
		$gamer->removeTimer();
		$gamer->setTimes(0);
		$gamer->save();
		$this->changeCards($gamer);
	}
	
}