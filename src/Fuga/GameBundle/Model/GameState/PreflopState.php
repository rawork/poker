<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;
use Fuga\GameBundle\Model\Exception\GameException;

class PreflopState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function makeMove($gamer) {
		try {
			if (!$this->game->lock($gamer->getId())) {
				return $this->game->getStateNo();
			}
			if ($gamer->getBet() > $this->game->getMaxbet()) {
				$this->game->setMaxbet($gamer->getBet());
			}
			if ($gamer->getAllin()) {
				// TODO надо хранить ставку allin
				if ($this->game->getBank2() == 0) {
					$this->game->setBank2($this->game->getBank());
				}
			}
			$this->game->stopTimer();
			$this->game->container->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->findAndUpdate()
					->field('board')->equals($this->game->getId())
					->field('active')->equals(true)
					->field('state')->notEqual(1)
					->field('fold')->set(true)
					->field('cards')->set(array())
					->getQuery()->execute();
			// Find who not FOLD
			$gamers = $this->game->container->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->field('board')->equals($this->game->getId())
					->field('active')->equals(true)
					->field('fold')->equals(false)
					->getQuery()->execute();

			if (count($gamers) < 1) {
				$this->game->confirmBets();
				$this->game->setWinner();
				$this->game->setTimer('distribute');
				$this->game->startTimer();
				$this->game->setState(AbstractState::STATE_SHOWDOWN);
			} else {
				$gamers = $this->game->container->get('odm')
						->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
						->field('board')->equals($this->game->getId())
						->field('active')->equals(true)
						->field('state')->equals(1)
						->field('fold')->equals(false)
						->field('allin')->equals(false)
						->field('bet')->lt($this->game->getMaxbet())
						->getQuery()->execute();
				if (count($gamers) ==  0) {
					$this->game->confirmBets();
					$this->game->setMover($this->game->getDealer());
					$this->game->nextMover();
					$gamers = $this->game->container->get('odm')
								->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
								->field('board')->equals($this->game->getId())
								->field('active')->equals(true)
								->getQuery()->execute();
					foreach ($gamers as $doc) {
						if (!$doc->getFold()) {
							$combination = new Combination();
							$cards = $combination->get($doc->getCards(), $this->game->getFlop());
							$combinations = array();
							foreach ($cards['cards'] as $card) {
								$combinations[] = $card['name'];
							}
							$doc->setRank($combination->rankName($cards['rank']));
							$doc->setCombination($combinations);
						}
						$doc->setBet(0);
					}
					$this->game->setMaxBet(0);
					$this->game->setTimer('bet');
					$this->game->setState(AbstractState::STATE_FLOP);
				} else {
					$this->game->nextMover();
					$this->game->setTimer('bet');
				}
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