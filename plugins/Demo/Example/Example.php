<?php
class Example extends PluginBase {

    protected $storage = 'DbStorage';
    static protected $name = 'Example';
    static protected $description = 'Example plugin';
    
    protected $settings = array(
        'test' => array(
            'type' => 'string',
            'label' => 'Message'
        ),
    );
    
    public function init()
    {
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('beforeSurveySettings');
        $this->subscribe('newSurveySettings');
    }

    /**
     * This event is fired by the administration panel to gather extra settings
     * available for a survey.
     * The plugin should return setting meta data.
     * @param PluginEvent $event
     */
    public function beforeSurveySettings()
    {
        $event = $this->event;
        $event->set("surveysettings.{$this->id}", array(
            'name' => get_class($this),
            'settings' => array(
                'message' => array(
                    'type' => 'string',
                    'label' => 'Message to show to users:',
                    'current' => $this->get('message', 'Survey', $event->get('survey'))
                )
            )
         ));
    }
    
    public function newSurveySettings()
    {
        $event = $this->event;
        foreach ($event->get('settings') as $name => $value)
        {
            $this->set($name, $value, 'Survey', $event->get('survey'));
        }
    }

}
