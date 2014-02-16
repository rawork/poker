<?php

namespace Fuga\GameBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document(collection="card") */
class Card {
	
	/** @ODM\Id */
    private $id;
	
	/** @ODM\String */
    private $name;
	
	/** @ODM\Int */
    private $weight;
	
	/** @ODM\Int */
    private $suit;

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
     * Set weight
     *
     * @param int $weight
     * @return self
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * Get weight
     *
     * @return int $weight
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set suit
     *
     * @param int $suit
     * @return self
     */
    public function setSuit($suit)
    {
        $this->suit = $suit;
        return $this;
    }

    /**
     * Get suit
     *
     * @return int $suit
     */
    public function getSuit()
    {
        return $this->suit;
    }
}
