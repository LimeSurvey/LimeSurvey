<?php
class Authdb extends PluginBase
{
    protected $storage = 'DbStorage';    
    
    static protected $description = 'Core: Database authentication';
    
    public function __construct(PluginManager $manager, $id) {
        parent::__construct($manager, $id);
        
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('beforeLogin');
        $this->subscribe('afterCreateLoginForm');
    }
    
    public function beforeLogin(PluginEvent $event)
    {
        // We can skip the login form here and set username/password etc.
        
        /* @var $identity UserIdentity */
        $identity = $event->get('identity');
        
        if (App()->getRequest()->isPostRequest() && !is_null(Yii::app()->request->getQuery('onepass'))) {
            // We have a one time password, skip the login form
            $identity->onepass = Yii::app()->getRequest()->getQuery('onepass');
            $identity->username = Yii::app()->getRequest()->getQuery('user');
            $event->stop(); // Skip the login form
        }
    }
    
    public function afterCreateLoginForm(PluginEvent $event)
    {
        // Here we can influence the way the login form looks
        $blocks = $event->get('blocks', array());
        
        $blocks['Authdb'] = '';
        $event->set('blocks', $blocks);
    }
    
    public function afterLoginPost(PluginEvent $event)
    {
        // Here we handle the authentication, using the posted form data
        $blocks = $event->get('blocks', array());
        $event->set('blocks', $blocks);
    }
    
    
}