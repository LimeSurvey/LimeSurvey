<?php

namespace LimeSurvey\Datavalueobjects;

class EmailPluginInfo
{
    /** @var int */
    public $pluginId;

    /** @var string */
    public $name;

    /** @var string */
    public $class;

    /**
     * @param int $pluginId
     * @param string $name
     * @param string $class
     */
    public function __construct($pluginId, $name, $class)
    {
        $this->pluginId = $pluginId;
        $this->name = $name;
        $this->class = $class;
    }
}
