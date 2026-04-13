<?php

namespace LimeSurvey\Datavalueobjects;

/**
 * Class TypedMessage
 *
 * This class represents a message with a type (ie. success, error, etc.).
 *
 * @package LimeSurvey\Datavalueobjects
 */
class TypedMessage
{
    /** @var string the type of the message */
    private $type;

    /** @var string the message */
    private $message;

    /**
     * @param string $type
     * @param string $message
     */
    public function __construct($message, $type = '')
    {
        $this->message = $message;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}