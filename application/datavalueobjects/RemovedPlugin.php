<?php

namespace LimeSurvey\Datavalueobjects;

class RemovedPlugin
{
    /** @var string */
    public $name;

    /** @var string the reason why the plugin was removed */
    public $reason;

    /** @var string|null */
    public $extraInfo;

    /**
     * @param string $name
     * @param string $reason
     * @param string|null $extraInfo
     */
    public function __construct($name, $reason, $extraInfo = null)
    {
        $this->name = $name;
        $this->reason = $reason;
        $this->extraInfo = $extraInfo;
    }
}
