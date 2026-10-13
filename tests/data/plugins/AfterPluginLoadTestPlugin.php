<?php

/**
 * Dummy plugin fetching the plugin manager from the application in afterPluginLoad
 */
class AfterPluginLoadTestPlugin extends PluginBase
{
    protected static $description = 'Dummy plugin for testing the afterPluginLoad event';
    protected static $name = 'AfterPluginLoadTestPlugin';
    protected $storage = 'DbStorage';

    /** @var int Number of afterPluginLoad events received by any instance */
    public static $afterPluginLoadCount = 0;

    /** @var \LimeSurvey\PluginManager\PluginManager|null Plugin manager returned by the application in the first afterPluginLoad */
    public static $applicationPluginManager;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->subscribe('afterPluginLoad');
    }

    /**
     * Get the plugin manager from the application, like a call to the permission model does.
     * Without the plugin manager registered in the application, this created a new plugin manager,
     * which loaded the plugins again and fired afterPluginLoad again, endlessly.
     * @return void
     */
    public function afterPluginLoad()
    {
        self::$afterPluginLoadCount++;
        // Avoid endless recursion when the plugin manager is not registered.
        if (self::$afterPluginLoadCount > 1) {
            return;
        }
        self::$applicationPluginManager = App()->getPluginManager();
    }
}
