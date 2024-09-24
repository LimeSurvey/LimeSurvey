<?php

namespace LimeSurvey\Datavalueobjects;

/**
 * Class OperationResult
 *
 * This class represents the result of an operation (ie. the result of a
 * service's method), with a status and an optional list of messages.
 *
 * @package LimeSurvey\Datavalueobjects
 */
class OperationResult
{
    /** @var bool the basic result of the operation */
    private $success;

    /** @var TypedMessage[] an array of messages providing extra details */
    private $messages;

    /**
     * @param bool $success
     * @param TypedMessage[]|TypedMessage $messages
     */
    public function __construct($success = false, $messages = []) {
        $this->success = $success;
        if (!is_array($messages)) {
            $messages = [$messages];
        }
        $this->messages = $messages;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     */
    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * Returns the messages of the given type, or all messages if
     * no type is specified.
     * @param string|null $type
     * @return TypedMessage[]
     */
    public function getMessages($type = null)
    {
        if ($type === null) {
            return $this->messages;
        }
        $messages = [];
        foreach ($this->messages as $message) {
            if ($message->getType() === $type) {
                $messages[] = $message;
            }
        }
        return $messages;
    }

    /**
     * @param TypedMessage[] $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @param TypedMessage $message
     */
    public function appendMessage(TypedMessage $message)
    {
        $this->messages[] = $message;
    }

    /**
     * @param string $message
     * @param string $type
     */
    public function addMessage($message, $type)
    {
        $this->appendMessage(new TypedMessage($message, $type));
    }

    /**
     * Sets messages from an array of strings
     * @param string[] $messages
     */
    public function setRawMessages($messages)
    {
        $this->messages = [];
        foreach ($messages as $message) {
            $this->messages[] = new TypedMessage($message);
        }
    }

    /**
     * Returns the raw messages of the given type, or all messages if
     * no type is specified.
     * @param string|null $type
     * @return string[]
     */
    public function getRawMessages($type = null)
    {
        $messages = [];
        foreach ($this->getMessages($type) as $message) {
            $messages[] = $message->getMessage();
        }
        return $messages;
    }
}
