<?php
class ShowResponse extends PluginBase {
    protected $storage = 'DbStorage';    
    static protected $description = 'Demo: handle a survey response';
    static protected $name = 'Show response';
    
    public function init()
    {
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('afterSurveyComplete', 'showTheResponse');
    }
    
    /*
     * Below are the actual methods that handle events
     */
    public function showTheResponse() 
    {
        $event      = $this->getEvent();
        $surveyId   = $event->get('surveyId');
        $responseId = $event->get('responseId');
        $response   = $this->pluginManager->getAPI()->getResponse($surveyId, $responseId);
        
        $event->getContent($this)
              ->addContent('You response was:<br/><pre>' . print_r($response, true) . '</pre>');
    }
    
    
}
