<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;

class WaitState extends AbstractState {
	
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
				$this->game->save();
				$this->game->newDeck();
				$this->game->setBank(0);
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
					if ($doc->getState() == 1) {
						$doc->setCards($this->game->getCards(4));
					} else {
						$doc->setFold(true);
					}
					$doc->setBet(0);
					$doc->setAllin(false);
					$doc->setWinner(false);
					$doc->setTimes(2);
					$doc->setQuestion(array());
					$doc->setBuy(array());
					$this->game->container->get('log')->write('WROUNDS'.$this->game->getRound());
					$this->game->container->get('log')->write('WUPDATED'.serialize($doc->getUpdated()));
					$this->game->container->get('log')->write('WFROMTIME'.serialize($this->game->fromtime));
					if ($this->game->getRound() >= 3 
						&& $doc->getUpdated() < $this->game->fromtime) {
						$this->game->acceptBet($doc->getChips());
						$doc->setChips(0);
						$doc->setActive(false);
						$doc->setState(0);
					}
				}
				$this->game->confirmBets();
				
				$this->game->nextDealer();
				$this->game->nextMover();
				$this->game->setFlop($this->game->getCards(3));

				$this->game->setTimer('change');
				$this->game->startTimer();
				$this->game->setState(AbstractState::STATE_CHANGE);
			}
			
			$this->game->save();
			$this->game->unlock($gamer->getId());
			
		} catch (\Exception $e) {
			$this->game->unlock($gamer->getId());
		}
		
		return $this->game->getStateNo();
	}
	
}