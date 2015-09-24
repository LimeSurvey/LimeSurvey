<?php

namespace ls\components;

/**
 * This class represents a possible answer to a question.
 *
 */
class QuestionAnswer implements \ls\interfaces\iAnswer
{
    /**
     * @var string The label for this answer.
     */
    protected $label;
    protected $code;

    public function __construct($code, $label)
    {
        $this->code = $code;
        $this->label = $label;
    }


    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}