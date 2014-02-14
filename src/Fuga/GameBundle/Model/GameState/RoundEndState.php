<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;

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
				$this->game->newDeck();
				$gamers = $this->game->container->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->field('board')->equals($this->game->getId())
					->field('active')->equals(true)
					->getQuery()->execute();
				foreach ($gamers as $doc) {
					$doc->setCards($this->game->getCards(4));
					$doc->setBet(0);
					$doc->setAllin(false);
					$doc->setWinner(false);
					$doc->setFold(false);
					$doc->setTimes(2);
					$doc->setQuestion(array());
					$doc->setBuy(array());
				}
				$this->game->setBank(0);
				$this->game->setBets(0);
				$this->game->setFlop($this->game->getCards(3));

				$this->game->setTimer('change');
				$this->game->startTimer();
				$this->game->setState(AbstractState::STATE_CHANGE);
			}
			$this->game->save();
			$this->game->unlock($gamer->getId());
		} catch (\Exception $e) {
			$this->game->container->get('log')->write('STATE:'.$e->getMessage());
			$this->game->unlock($gamer->getId());
		}
		
		return $this->game->getStateNo();
	}
	
}