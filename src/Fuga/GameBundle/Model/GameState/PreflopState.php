<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;
use Fuga\GameBundle\Model\Combination;
use Fuga\GameBundle\Model\RealGamer;

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
			$this->game->stopTimer();
			$this->game->container->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->findAndUpdate()
					->field('board')->equals($this->game->getId())
					->field('active')->equals(true)
					->field('state')->lt(1)
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
			if (count($gamers) > 2) {
				$gamers = $this->game->container->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->field('board')->equals($this->game->getId())
					->field('active')->equals(true)
					->field('chips')->gt(0)
					->getQuery()->execute();
			}

			if (count($gamers) < 2) {
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
						->field('move')->equals('nomove')
						->getQuery()->execute();
				if (count($gamers) == 0) {
					$gamers = $this->game->container->get('odm')
							->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
							->field('board')->equals($this->game->getId())
							->field('active')->equals(true)
							->field('state')->equals(1)
							->field('fold')->equals(false)
							->field('allin')->equals(false)
							->field('chips')->gt(0)
							->field('bet')->lt($this->game->getMaxbet())
							->getQuery()->execute();
				}	
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
						$doc->setMove('nomove');
					}
					$this->game->setMaxBet(0);
					$this->game->setTimer('bet');
					$this->game->setState(AbstractState::STATE_FLOP);
				} else {
					$this->game->nextMover();
					$this->game->setTimer('bet');
				}
			}
			
			$this->game->setUpdated(time());
			$this->game->save();
			$this->game->unlock($gamer->getId());
		} catch (\Exception $e) {
			$this->game->container->get('log')->write('STATE:'.$e->getMessage());
			$this->game->unlock($gamer->getId());
		}
		
		return $this->game->getStateNo();
	}
	
	public function sync() {
		$gamer = null;
		
		$gamerdoc = $this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->game->getId())
				->field('seat')->equals($this->game->getMover())
				->getQuery()->getSingleResult();
		if ($gamerdoc) {
			$timer = $this->game->getTimer();
			$timer = array_shift($timer);
			if (!$timer || intval($timer['time']) < time()) { 
				$this->game->container->get('log')->write(
						'game'.$this->game->getId()
						.' :preflop.find.outtimer '
						.(intval($timer['time']) - time())
				);
				$gamer = new RealGamer($gamerdoc->getUser(), $this->game->container);
			}
		}
		
		if ($gamer) {
			$this->game->fold($gamer);
		}
	}
	
}