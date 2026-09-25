<?php

namespace LimeSurvey\Datavalueobjects;

/**
 * Class BlocklistResult
 *
 * This class represents the result of a blocklist operation
 *
 * @package LimeSurvey\Datavalueobjects
 */
class BlocklistResult
{
    /** @var bool the basic result of the operation */
    private $blocklisted;

    /** @var string[] an array of messages providing extra details */
    private $messages;

    /**
     * @param bool $blocklisted
     * @param string[]|string $messages
     */
    public function __construct($blocklisted = false, $messages = [''])
    {
        $this->blocklisted = $blocklisted;
        if (!is_array($messages)) {
            $messages = [$messages];
        }
        $this->messages = $messages;
    }

    /**
     * @return bool
     */
    public function isBlocklisted(): bool
    {
        return $this->blocklisted;
    }

    /**
     * @param bool $blocklisted
     */
    public function setBlocklisted(bool $blocklisted): void
    {
        $this->blocklisted = $blocklisted;
    }

    /**
     * @return string[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param string[] $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    public function appendMessage(string $message)
    {
        $this->messages[] = $message;
    }
}
