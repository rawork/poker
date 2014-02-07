<?php

namespace Fuga\GameBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document(db="holdem", collection="decks") */
class Deck
{
	/** @ODM\Id */
    private $id;
	
	/** @ODM\String */
    private $board;
	
	/** @ODM\Collection */
	private $cards = array();

}