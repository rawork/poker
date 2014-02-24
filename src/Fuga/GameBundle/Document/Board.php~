<?php

namespace Fuga\GameBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document(collection="board") */
class Board
{
	/** @ODM\Id */
    private $id;

    /** @ODM\Increment */
    private $prizes = 0;
	
	 /** @ODM\Increment */
    private $round = 0;

    /** @ODM\Collection */
    private $flop = array();
	
	/** @ODM\Collection */
    private $winner = array();
	
	/** @ODM\Collection */
    private $combination = array();

    /** @ODM\String */
    private $name;
	
	/** @ODM\Int */
    private $board;
	
	/** @ODM\Int */
    private $bank = 0;

	/** @ODM\Int */
	private $bank2 = 0;

    /** @ODM\Int */
    private $bets = 0;
	
	/** @ODM\Int */
    private $maxbet = 0;

	/** @ODM\Int */
	private $minbet = 1;
	
	/** @ODM\Int */
    private $dealer = 0;
	
	/** @ODM\Int */
    private $mover = 0;
	
	/** @ODM\Int */
    private $firstmover = 0;
	
	/** @ODM\Int */
	private $state = 0;
	
	/** @ODM\Int */
	private $gamer = 0;

    /** @ODM\Int */
    private $fromtime;

    /** @ODM\Int */
    private $endtime;
	
	/** @ODM\Int */
    private $updated;
	
	/** @ODM\Collection */
    private $cards = array();
	
	/** @ODM\Collection */
	private $timer = array();
	
	/** @ODM\Int */
	private $allin = 0;
	

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set prizes
     *
     * @param increment $prizes
     * @return self
     */
    public function setPrizes($prizes)
    {
        $this->prizes = $prizes;
        return $this;
    }

    /**
     * Get prizes
     *
     * @return increment $prizes
     */
    public function getPrizes()
    {
        return $this->prizes;
    }

    /**
     * Set flop
     *
     * @param collection $flop
     * @return self
     */
    public function setFlop($flop)
    {
        $this->flop = $flop;
        return $this;
    }

    /**
     * Get flop
     *
     * @return collection $flop
     */
    public function getFlop()
    {
        return $this->flop;
    }

    /**
     * Set winner
     *
     * @param collection $winner
     * @return self
     */
    public function setWinner($winner)
    {
        $this->winner = $winner;
        return $this;
    }

    /**
     * Get winner
     *
     * @return collection $winner
     */
    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * Set combination
     *
     * @param collection $combination
     * @return self
     */
    public function setCombination($combination)
    {
        $this->combination = $combination;
        return $this;
    }

    /**
     * Get combination
     *
     * @return collection $combination
     */
    public function getCombination()
    {
        return $this->combination;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set board
     *
     * @param int $board
     * @return self
     */
    public function setBoard($board)
    {
        $this->board = $board;
        return $this;
    }

    /**
     * Get board
     *
     * @return int $board
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * Set bank
     *
     * @param int $bank
     * @return self
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
        return $this;
    }

    /**
     * Get bank
     *
     * @return int $bank
     */
    public function getBank()
    {
        return $this->bank;
    }

	/**
	 * Set bank2
	 *
	 * @param int $bank2
	 * @return self
	 */
	public function setBank2($bank2)
	{
		$this->bank2 = $bank2;
		return $this;
	}

	/**
	 * Get bank2
	 *
	 * @return int $bank2
	 */
	public function getBank2()
	{
		return $this->bank2;
	}

    /**
     * Set bets
     *
     * @param int $bets
     * @return self
     */
    public function setBets($bets)
    {
        $this->bets = $bets;
        return $this;
    }

    /**
     * Get bets
     *
     * @return int $bets
     */
    public function getBets()
    {
        return $this->bets;
    }

    /**
     * Set maxbet
     *
     * @param int $maxbet
     * @return self
     */
    public function setMaxbet($maxbet)
    {
        $this->maxbet = $maxbet;
        return $this;
    }

    /**
     * Get maxbet
     *
     * @return int $maxbet
     */
    public function getMaxbet()
    {
        return $this->maxbet;
    }

    /**
     * Set dealer
     *
     * @param int $dealer
     * @return self
     */
    public function setDealer($dealer)
    {
        $this->dealer = $dealer;
        return $this;
    }

    /**
     * Get dealer
     *
     * @return int $dealer
     */
    public function getDealer()
    {
        return $this->dealer;
    }

    /**
     * Set mover
     *
     * @param int $mover
     * @return self
     */
    public function setMover($mover)
    {
        $this->mover = $mover;
        return $this;
    }

    /**
     * Get mover
     *
     * @return int $mover
     */
    public function getMover()
    {
        return $this->mover;
    }

    /**
     * Set state
     *
     * @param int $state
     * @return self
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Get state
     *
     * @return int $state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set gamer
     *
     * @param int $gamer
     * @return self
     */
    public function setGamer($gamer)
    {
        $this->gamer = $gamer;
        return $this;
    }

    /**
     * Get gamer
     *
     * @return int $gamer
     */
    public function getGamer()
    {
        return $this->gamer;
    }

    /**
     * Set cards
     *
     * @param collection $cards
     * @return self
     */
    public function setCards($cards)
    {
        $this->cards = $cards;
        return $this;
    }

    /**
     * Get cards
     *
     * @return collection $cards
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Set timer
     *
     * @param collection $timer
     * @return self
     */
    public function setTimer($timer)
    {
        $this->timer = $timer;
        return $this;
    }

    /**
     * Get timer
     *
     * @return collection $timer
     */
    public function getTimer()
    {
        return $this->timer;
    }

    /**
     * Set firstmover
     *
     * @param int $firstmover
     * @return self
     */
    public function setFirstmover($firstmover)
    {
        $this->firstmover = $firstmover;
        return $this;
    }

    /**
     * Get firstmover
     *
     * @return int $firstmover
     */
    public function getFirstmover()
    {
        return $this->firstmover;
    }

    /**
     * Set round
     *
     * @param increment $round
     * @return self
     */
    public function setRound($round)
    {
        $this->round = $round;
        return $this;
    }

    /**
     * Get round
     *
     * @return increment $round
     */
    public function getRound()
    {
        return $this->round;
    }

    /**
     * Set allin
     *
     * @param int $allin
     * @return self
     */
    public function setAllin($allin)
    {
        $this->allin = $allin;
        return $this;
    }

    /**
     * Get allin
     *
     * @return int $allin
     */
    public function getAllin()
    {
        return $this->allin;
    }


    /**
     * Set fromtime
     *
     * @param int $fromtime
     * @return self
     */
    public function setFromtime($fromtime)
    {
        $this->fromtime = $fromtime;
        return $this;
    }

    /**
     * Get fromtime
     *
     * @return int $fromtime
     */
    public function getFromtime()
    {
        return $this->fromtime;
    }

    /**
     * Set endtime
     *
     * @param int $endtime
     * @return self
     */
    public function setEndtime($endtime)
    {
        $this->endtime = $endtime;
        return $this;
    }

    /**
     * Get endtime
     *
     * @return int $endtime
     */
    public function getEndtime()
    {
        return $this->endtime;
    }

    /**
     * Set updated
     *
     * @param int $updated
     * @return self
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * Get updated
     *
     * @return int $updated
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set minbet
     *
     * @param int $minbet
     * @return self
     */
    public function setMinbet($minbet)
    {
        $this->minbet = $minbet;
        return $this;
    }

    /**
     * Get minbet
     *
     * @return int $minbet
     */
    public function getMinbet()
    {
        return $this->minbet;
    }
}
