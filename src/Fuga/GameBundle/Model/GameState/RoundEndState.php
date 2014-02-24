<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\RealGamer;

class RoundEndState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function nextGame($gamer) {
		try {
			if (!$this->game->lock($gamer->getId())) {
				return $this->game->getStateNo();
			}
			$this->game->stopTimer();
			if (!$this->game->existsGamers()) {
				$this->game->removeTimer();
				$this->game->stopTime();
				$this->game->setState(self::STATE_END);
			} else {
				$this->game->removeTimer();
				$this->game->newDeck();
				$this->game->setBank2(0);
				$this->game->setAllin(0);
				$this->game->setBets(0);
				$this->game->setRound($this->game->getRound() + 1);
				$gamers = $this->game->container->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->field('board')->equals($this->game->getId())
					->field('active')->equals(true)
					->getQuery()->execute();
				foreach ($gamers as $doc) {
					$doc->setFold(false);
					if ($doc->getState() > 0) {
						$doc->setCards($this->game->getCards(4));
						$doc->setTimes(2);
						$doc->setTimer(array(array(
							'handler' => 'onClickNoChange', 
							'holder' => 'change-timer', 
							'time' => time() +31
						)));
					} else {
						$doc->setFold(true);
						$doc->setTimes(0);
					}
					$doc->setBet(0);
					$doc->setBank(0);
					$doc->setAllin(false);
					$doc->setWinner(false);
					
					$doc->setQuestion(array());
					$doc->setBuy(array());
					if ($this->game->getRound() >= 3 
						&& $doc->getState() == 0) {
						$this->game->acceptBet($doc->getChips());
						$doc->setChips(0);
						$doc->setActive(false);
						$doc->setState(0);
					}
					$this->game->save();
				}
				$this->game->confirmBets();
				
				$this->game->nextDealer();
				$this->game->nextMover();
				$this->game->setFlop($this->game->getCards(3));

				$this->game->setState(AbstractState::STATE_CHANGE);
			}

			$this->game->setUpdated(time());
			$this->game->save();
			$this->game->unlock($gamer->getId());
			
		} catch (\Exception $e) {
			$this->game->setUpdated(time());
			$this->game->save();
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
					.' :roundend.find.outtimer '
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
			$this->nextGame($gamer);			
		}
	}
	
}