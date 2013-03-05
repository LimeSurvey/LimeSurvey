<?php
class Example extends PluginBase {

    protected $storage = 'DbStorage';    
    static protected $description = 'Example plugin';
    
    protected $settings = array(
        'logo' => array(
                'type' => 'logo',
                'path' => 'assets/logo.png'
            ),
        'message' => array(
            'type' => 'string',
            'label' => 'Message'
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
    
    public function afterAdminMenuLoaded(PluginEvent $event)
    {
        $menu = $event->get('menu', array());
        $menu['left'][]=array(
                'href' => "http://docs.limesurvey.org",
                'alt' => gT('LimeSurvey online manual'),
                'image' => 'showhelp.png'
            );
        
        $event->set('menu', $menu);
    }

    public function helloWorld(PluginEvent $event) 
    {
        $this->pluginManager->getAPI()->setFlash($this->get('message', null, null, 'Example popup. Change this via plugin settings.'));
    }
    
    /**
     * This event is fired by the administration panel to gather extra settings
     * available for a survey.
     * The plugin should return setting meta data.
     * @param PluginEvent $event
     */
    public function beforeSurveySettings(PluginEvent $event)
    {
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
    
    public function newSurveySettings(PluginEvent $event)
    {
        foreach ($event->get('settings') as $name => $value)
        {
            
            $this->set($name, $value, 'Survey', $event->get('survey'));
        }
    }

}
