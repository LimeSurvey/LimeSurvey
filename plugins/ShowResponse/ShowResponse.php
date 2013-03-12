<?php
class ShowResponse extends PluginBase {
    protected $storage = 'DbStorage';    
    static protected $description = 'Example plugin: handle a survey response';
    
    public function __construct(PluginManager $manager, $id) {
        parent::__construct($manager, $id);
        
        
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('afterSurveyCompleted', 'showResponse');
    }
    
    /*
     * Below are the actual methods that handle events
     */
    public function showResponse(PluginEvent $event) 
    {
        $surveyId = $event->get('surveyId');
        $responseId = $event->get('responseId');
        $response = $this->pluginManager->getAPI()->getResponse($surveyId, $responseId);
        $blocks = $event->get('blocks', array());
        $blocks[] = array('contents'=>'You response was:<br/><pre>' . print_r($response, true) . '</pre>');
        $event->set('blocks', $blocks);
    }
    
    
}