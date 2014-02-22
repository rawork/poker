<?php

namespace Fuga\GameBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document(collection="question") */
class Question {
	
	/** @ODM\Id */
    private $id;

	/** @ODM\Int */
	private $question;
	
	/** @ODM\String */
    private $name;
	
	/** @ODM\String */
    private $answer1;

	/** @ODM\String */
	private $answer2;

	/** @ODM\String */
	private $answer3;

	/** @ODM\String */
	private $answer4;
	
	/** @ODM\Int */
    private $answer;

	/** @ODM\Boolean */
	private $denied = false;


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
     * Set answer1
     *
     * @param string $answer1
     * @return self
     */
    public function setAnswer1($answer1)
    {
        $this->answer1 = $answer1;
        return $this;
    }

    /**
     * Get answer1
     *
     * @return string $answer1
     */
    public function getAnswer1()
    {
        return $this->answer1;
    }

    /**
     * Set answer2
     *
     * @param string $answer2
     * @return self
     */
    public function setAnswer2($answer2)
    {
        $this->answer2 = $answer2;
        return $this;
    }

    /**
     * Get answer2
     *
     * @return string $answer2
     */
    public function getAnswer2()
    {
        return $this->answer2;
    }

    /**
     * Set answer3
     *
     * @param string $answer3
     * @return self
     */
    public function setAnswer3($answer3)
    {
        $this->answer3 = $answer3;
        return $this;
    }

    /**
     * Get answer3
     *
     * @return string $answer3
     */
    public function getAnswer3()
    {
        return $this->answer3;
    }

    /**
     * Set answer4
     *
     * @param string $answer4
     * @return self
     */
    public function setAnswer4($answer4)
    {
        $this->answer4 = $answer4;
        return $this;
    }

    /**
     * Get answer4
     *
     * @return string $answer4
     */
    public function getAnswer4()
    {
        return $this->answer4;
    }

    /**
     * Set answer
     *
     * @param int $answer
     * @return self
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
        return $this;
    }

    /**
     * Get answer
     *
     * @return int $answer
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Set denied
     *
     * @param boolean $denied
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
     * @return boolean $denied
     */
    public function getDenied()
    {
        return $this->denied;
    }

    /**
     * Set question
     *
     * @param int $question
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
     * @return int $question
     */
    public function getQuestion()
    {
        return $this->question;
    }
}
