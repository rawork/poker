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
		if (!$this->game->lock($gamer->getId())) {
			return $this->game->getStateNo();
		}
		$this->game->stopTimer();
		// Find who not FOLD
		$gamers = $this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->game->getId())
				->field('fold')->equals(false)
				->getQuery()->execute();
		
		if (count($gamers) == 0) {
			throw new GameException('Ошибка. Все игроки сбросили карты.');
		} elseif (count($gamers) == 1) {
			$this->game->confirmBets();
			$this->game->setWinner();
			$this->game->setTimer('distribute');
			$this->game->startTimer();
			$this->game->setState(AbstractState::STATE_SHOWDOWN);
		} else {
			// Find who not FOLD & not ALLIN
			$gamers = $this->game->container->get('odm')
					->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
					->field('board')->equals($this->game->getId())
					->field('fold')->equals(false)
					->field('allin')->equals(false)		
					->getQuery()->execute();
			if (count($gamers) < 2) {
				$this->game->confirmBets();
				$this->game->setWinner();
				$this->game->setTimer('distribute');
				$this->game->startTimer();
				$this->game->setState(AbstractState::STATE_SHOWDOWN);
			} else {
				try {
					$this->game->nextMover();
					$this->game->setTimer('bet');
				} catch (GameException $e) {
					$this->game->confirmBets();
					$gamers = $this->game->container->get('odm')
							->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
							->field('board')->equals($this->game->getId())
							->field('fold')->equals(false)
							->getQuery()->execute();
					foreach ($gamers as $doc) {
						$combination = new Combination();
						$cards = $combination->get(array_merge($doc->getCards(), $this->game->getFlop()));
						$combinations = array();
						foreach ($cards['cards'] as $card) {
							$combinations[] = $card['name'];
						}
						$doc->setRank($combination->rankName($cards['rank']));
						$doc->setCombination($combinations);
					}
					$this->game->setState(AbstractState::STATE_FLOP);
				}
			}
		}
		
		$this->game->save();
		$this->game->unlock($gamer->getId());
		
		return $this->game->getStateNo();
	}
	
}