<?php

namespace Fuga\GameBundle\Model\GameState;

use Fuga\GameBundle\Model\GameInterface;

class ShowdownState extends AbstractState {
	
	public function __construct(GameInterface $game) {
		parent::__construct($game);
	}
	
	public function distributeWin($gamer) {
		if (!$this->game->lock($gamer->getId())) {
			return $this->game->getStateNo();
		}
			
		$bank = $this->game->takeBank();
		$numWin = count($this->game->getWinner());
		if ($numWin > 1) {
			$nextBank = $bank % $numWin;
			$share = ($bank - $nextBank) / $numWin;
			$this->game->setBank($nextBank);
		} else {
			$share = $bank;
		}
		$gamers = $this->game->container->get('odm')
				->createQueryBuilder('\Fuga\GameBundle\Document\Gamer')
				->field('board')->equals($this->game->getId())
				->field('active')->equals(true)
				->getQuery()->execute();
		foreach ($gamers as $doc) {
			foreach ($this->game->getWinner() as $winner) {
				if ($doc->getUser() == $winner['user']) {
					$doc->setChips( $doc->getChips() + $share );
					break;
				}
			}
			$doc->setActive($doc->getChips() > 0);
			$doc->setBet(0);
			$doc->setCards(array());
			$doc->setRank('');
			$doc->setCombination(array());
			$doc->setWinner(false);
			$query = '1=1';
			if ($denied = $doc->getDenied()) {
				$query = 'id NOT IN('.implode(',', $denied).')';
			}
			$questions = $this->game->container->getItems('game_poll', $query);
			shuffle($questions);
			$buy = array_slice($questions, 0, 3);
			foreach ($buy as $question) {
				$denied[] = $question['id'];
			}
			$doc->setBuy($buy);
			$doc->setDenied($denied);
		}
		
		$this->game->emptyWinner();
		$this->game->setFlop(array());
		$this->game->setBets(0);
		$this->game->setMaxbet(0);
		$this->game->save();
		
		$now = new \DateTime();
		if (!$this->game->existsJoker()) {
			$this->game->setTimer('prebuy');
			$this->game->startTimer();
			$this->game->setState(AbstractState::STATE_JOKER);
		} elseif ($now > $this->game->stopbuytime) {
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
		$this->game->save();
		$this->game->unlock($gamer->getId());
		
		return $this->game->getStateNo();
	}
	
}


