<?php

namespace LimeSurvey\Datavalueobjects;

/**
 * Class BlacklistResult
 *
 * This class represents the result of a blocklist operation
 *
 * @package LimeSurvey\Datavalueobjects
 */
class BlacklistResult
{
    /** @var bool the basic result of the operation */
    private $blacklisted;

    /** @var string[] an array of messages providing extra details */
    private $messages;

    /**
     * @param bool $blacklisted
     * @param string[]|string $messages
     */
    public function __construct($blacklisted = false, $messages = ['']) {
        $this->blacklisted = $blacklisted;
        if (!is_array($messages)) {
            $messages = [$messages];
        }
        $this->messages = $messages;
    }

    /**
     * @return bool
     */
    public function isBlacklisted(): bool
    {
        return $this->blacklisted;
    }

    /**
     * @param bool $blacklisted
     */
    public function setBlacklisted(bool $blacklisted): void
    {
        $this->blacklisted = $blacklisted;
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
