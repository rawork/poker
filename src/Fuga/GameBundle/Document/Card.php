<?php

namespace Fuga\GameBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document(db="holdem", collection="cards") */
class Card
{
	/** @ODM\Id */
    private $id;
	
	/** @ODM\String */
    private $name;

	/** @ODM\Int */
	private $weight;
	
	/** @ODM\Int */
	private $suite;

}