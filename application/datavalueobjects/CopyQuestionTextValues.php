<?php

namespace LimeSurvey\Datavalueobjects;

/**
 * Class CopyQuestionTextValues
 *
 * This class represents the texts values to use when copying a question
 *
 * @package LimeSurvey\Datavalueobjects
 */
class CopyQuestionTextValues
{
    /** @var string the question text */
    private $questionText;

    /** @var string the question help text */
    private $help;

    /**
     * @param string $question
     * @param string $help
     */
    public function __construct($questionText = '', $help = '') {
        $this->questionText = $questionText;
        $this->help = $help;
    }

    /**
     * @return string
     */
    public function getQuestionText(): string
    {
        return $this->questionText;
    }

    /**
     * @param string $questionText
     */
    public function setQuestionText(string $questionText): void
    {
        $this->questionText = $questionText;
    }

    /**
     * @return string
     */
    public function getHelp(): string
    {
        return $this->help;
    }

    /**
     * @param string $help
     */
    public function setHelp(string $help): void
    {
        $this->help = $help;
    }
}
