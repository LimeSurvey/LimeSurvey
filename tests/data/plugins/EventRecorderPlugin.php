<?php

use LimeSurvey\PluginManager\PluginEvent;

/**
 * Dummy plugin for the plugin event tests.
 *
 * It records the name of every event it listens to, in dispatch order, and can run a
 * handler on the event, so a test can read and change the event like a real plugin does.
 */
class EventRecorderPlugin extends PluginBase
{
    protected static $description = 'Dummy plugin recording the plugin events it listens to';
    protected static $name = 'EventRecorderPlugin';

    /** @var string[] Names of the dispatched events, in dispatch order */
    public $dispatched = [];

    /** @var array<string, callable|null> Handlers of the events listened to, indexed by event name */
    private $handlers = [];

    /**
     * @return void
     */
    public function init()
    {
    }

    /**
     * Listen to an event.
     *
     * @param string $eventName
     * @param callable|null $handler Called with the PluginEvent each time the event is dispatched
     * @return void
     */
    public function listen($eventName, ?callable $handler = null)
    {
        $this->handlers[$eventName] = $handler;
        $this->subscribe($eventName, 'handleEvent');
    }

    /**
     * Stop listening to all events, so the plugin doesn't affect later tests.
     *
     * @return void
     */
    public function stopListening()
    {
        foreach (array_keys($this->handlers) as $eventName) {
            $this->unsubscribe($eventName);
        }
        $this->handlers = [];
    }

    /**
     * Number of times an event was dispatched.
     *
     * @param string $eventName
     * @return int
     */
    public function countDispatched($eventName)
    {
        return count(array_keys($this->dispatched, $eventName, true));
    }

    /**
     * Record the current event and pass it to its handler.
     *
     * @return void
     */
    public function handleEvent()
    {
        /** @var PluginEvent $event */
        $event = $this->getEvent();
        $eventName = $event->getEventName();
        $this->dispatched[] = $eventName;
        if (isset($this->handlers[$eventName])) {
            call_user_func($this->handlers[$eventName], $event);
        }
    }
}
