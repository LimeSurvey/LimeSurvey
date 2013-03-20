<?php
class AuditLog extends PluginBase {

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
        //$this->subscribe('afterPluginLoad', 'helloWorld');
        $this->subscribe('beforeSurveySettings');
        $this->subscribe('newSurveySettings');
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
                'auditing' => array(
                    'type' => 'select',
                    'options'=>array(0=>'No',
                                     1=>'Yes'),       
                    'default'=>0,             
                    'tab'=>'notification', // @todo: Setting no used yet
                    'category'=>'Auditing for person-related data', // @todo: Setting no used yet
                    'label' => 'Audit log for this survey:',
                    'current' => $this->get('auditing', 'Survey', $event->get('survey'))
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
