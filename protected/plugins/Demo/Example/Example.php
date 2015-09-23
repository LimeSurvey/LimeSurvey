<?php
class Example extends PluginBase {

    protected $storage = 'DbStorage';    
    static protected $description = 'Example plugin';
    
    protected $settings = array(
        'test' => array(
            'type' => 'string',
            'label' => 'Message'
        ),
        'messages' => array(
            'type' => 'list',
            'label' => 'messages',
            'items' => array(
                'number' => array(
                    'type' => 'int',
                    'label' => 'Index'
                ),
                'message' => array(
                    'type' => 'string',
                    'label' => 'Message'
                ),
                
            )
        )
    );
    
    public function __construct(PluginManager $manager, $id) {
        parent::__construct($manager, $id);
        
        
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('afterPluginLoad', 'helloWorld');
        $this->subscribe('afterAdminMenuLoaded');
        $this->subscribe('beforeSurveySettings');
        $this->subscribe('newSurveySettings');
    }
    
    
    /*
     * Below are the actual methods that handle events
     */
    
    public function afterAdminMenuLoaded()
    {
        $event = $this->event;
        $menu = $event->get('menu', array());
        $menu['left'][]=array(
                'href' => "http://docs.limesurvey.org",
                'alt' => gT('LimeSurvey online manual'),
                'image' => 'showhelp.png'
            );
        
        $event->set('menu', $menu);
    }

    public function helloWorld() 
    {
        $event = $this->event;
        $count = (int) $this->get('count');
        if ($count === false) $count = 0;
        $count++;
        $this->pluginManager->getAPI()->setFlash($this->get('message') . $count);
        $this->set('count', $count);
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
