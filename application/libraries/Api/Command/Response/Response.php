<?php

namespace LimeSurvey\Api\Command\Response;

use LimeSurvey\Api\Command\Response\Status;

class Response
{
    private Status $status;

    /**
     * @var mixed
     */
    private $data = null;

    /**
     * @var callable|null
     */
    private $streamHandler = null;


     /**
     * @param mixed $data
     * @param Status $status
     * @param callable|null $streamHandler (e.g. file output)
     */
    public function __construct($data, Status $status, ?callable $streamHandler = null)
    {
        $this->status = $status;
        $this->data = $data;
        $this->streamHandler = $streamHandler;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Check if the response should be streamed
     *
     * @return bool
     */
    public function isStream(): bool
    {
        return is_callable($this->streamHandler);
    }

    /**
     * If a stream handler exists, execute it
     */
    public function streamResponse(): void
    {
        if ($this->isStream() && $this->streamHandler !== null) {
            $data = $this->getData();
            call_user_func($this->streamHandler, $data);
        }
    }
}
