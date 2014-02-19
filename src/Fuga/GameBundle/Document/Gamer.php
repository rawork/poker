<?php

namespace Fuga\GameBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document(collection="gamer") */
class Gamer {
	
	/** @ODM\Id */
    private $id;
	
	/** @ODM\String */
    private $name;
	
	/** @ODM\String */
    private $lastname;
	
	/** @ODM\String */
    private $avatar;
	
	/** @ODM\Int */
    private $user;
	
	/** @ODM\Int */
    private $member;
	
	/** @ODM\Int */
    private $board;
	
	/** @ODM\Int */
    private $bet = 0;
	
	/** @ODM\Int */
    private $bet2 = 0;
	
	/** @ODM\Int */
    private $chips;
	
	/** @ODM\Int */
    private $seat;
	
	/** @ODM\Int */
    private $times = 2;
	
	/** @ODM\Int */
    private $state = 0;
	
	/** @ODM\Int */
    private $card = -1;
	
	/** @ODM\Boolean */
    private $allin = false;
	
	/** @ODM\Boolean */
    private $fold = false;
	
	/** @ODM\Boolean */
    private $active = true;
	
	/** @ODM\Boolean */
    private $winner = false;
	
	/** @ODM\Collection */
    private $cards = array();
	
	/** @ODM\Collection */
    private $denied = array();
	
	/** @ODM\Collection */
    private $question = array();
	
	/** @ODM\Collection */
    private $buy = array();
	
	/** @ODM\String */
    private $rank;
	
	/** @ODM\String */
    private $move;
	
	/** @ODM\Collection */
    private $combination = array();
	
	/** @ODM\Collection */
	private $timer = array();
	
	/** @ODM\Date */
    private $updated;
	
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
     * Set lastname
     *
     * @param string $lastname
     * @return self
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * Get lastname
     *
     * @return string $lastname
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     * @return self
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * Get avatar
     *
     * @return string $avatar
     */
    public function getAvatar()
    {
        return $this->avatar;
    }
	
	/**
     * Set member
     *
     * @param int $member
     * @return self
     */
    public function setMember($member)
    {
        $this->member = $member;
        return $this;
    }

    /**
     * Get member
     *
     * @return int $member
     */
    public function getMember()
    {
        return $this->member;
    }
	
    /**
     * Set user
     *
     * @param int $user
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return int $user
     */
    public function getUser()
    {
        return $this->user;
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
     * Set bet
     *
     * @param int $bet
     * @return self
     */
    public function setBet($bet)
    {
        $this->bet = $bet;
        return $this;
    }

    /**
     * Get bet
     *
     * @return int $bet
     */
    public function getBet()
    {
        return $this->bet;
    }

    /**
     * Set chips
     *
     * @param int $chips
     * @return self
     */
    public function setChips($chips)
    {
        $this->chips = $chips;
        return $this;
    }

    /**
     * Get chips
     *
     * @return int $chips
     */
    public function getChips()
    {
        return $this->chips;
    }

    /**
     * Set seat
     *
     * @param int $seat
     * @return self
     */
    public function setSeat($seat)
    {
        $this->seat = $seat;
        return $this;
    }

    /**
     * Get seat
     *
     * @return int $seat
     */
    public function getSeat()
    {
        return $this->seat;
    }

    /**
     * Set times
     *
     * @param int $times
     * @return self
     */
    public function setTimes($times)
    {
        $this->times = $times;
        return $this;
    }

    /**
     * Get times
     *
     * @return int $times
     */
    public function getTimes()
    {
        return $this->times;
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
     * Set card
     *
     * @param integer $card
     * @return self
     */
    public function setCard($card)
    {
        $this->card = $card;
        return $this;
    }

    /**
     * Get card
     *
     * @return integer $card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * Set allin
     *
     * @param boolean $allin
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
     * @return boolean $allin
     */
    public function getAllin()
    {
        return $this->allin;
    }
	
	 /**
     * Set fold
     *
     * @param boolean $fold
     * @return self
     */
    public function setFold($fold)
    {
        $this->fold = $fold;
        return $this;
    }

    /**
     * Get fold
     *
     * @return boolean $fold
     */
    public function getFold()
    {
        return $this->fold;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return self
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean $active
     */
    public function getActive()
    {
        return $this->active;
    }
	
	/**
     * Set winner
     *
     * @param boolean $winner
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
     * @return boolean $winner
     */
    public function getWinner()
    {
        return $this->winner;
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
     * Set denied
     *
     * @param collection $denied
     * @return self
     */
    public function setDenied($denied)
    {
        $this->denied = $denied;
        return $this;
    }

    /**
     * Get denied
     *
     * @return collection $denied
     */
    public function getDenied()
    {
        return $this->denied;
    }

    /**
     * Set question
     *
     * @param collection $question
     * @return self
     */
    public function setQuestion($question)
    {
        $this->question = $question;
        return $this;
    }

    /**
     * Get question
     *
     * @return collection $question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set buy
     *
     * @param collection $buy
     * @return self
     */
    public function setBuy($buy)
    {
        $this->buy = $buy;
        return $this;
    }

    /**
     * Get buy
     *
     * @return collection $buy
     */
    public function getBuy()
    {
        return $this->buy;
    }

    /**
     * Set rank
     *
     * @param string $rank
     * @return self
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
        return $this;
    }

    /**
     * Get rank
     *
     * @return string $rank
     */
    public function getRank()
    {
        return $this->rank;
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
     * Set move
     *
     * @param string $move
     * @return self
     */
    public function setMove($move)
    {
        $this->move = $move;
        return $this;
    }

    /**
     * Get move
     *
     * @return string $move
     */
    public function getMove()
    {
        return $this->move;
    }

    /**
     * Set updated
     *
     * @param date $updated
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
     * @return date $updated
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set bet2
     *
     * @param int $bet2
     * @return self
     */
    public function setBet2($bet2)
    {
        $this->bet2 = $bet2;
        return $this;
    }

    /**
     * Get bet2
     *
     * @return int $bet2
     */
    public function getBet2()
    {
        return $this->bet2;
    }
}
