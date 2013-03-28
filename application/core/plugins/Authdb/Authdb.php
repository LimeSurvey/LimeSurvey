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
        $this->subscribe('newLoginForm');
        $this->subscribe('afterLoginFormSubmit');
        $this->subscribe('newUserSession');
    }
    
    public function beforeLogin(PluginEvent $event)
    {
        $event->set('default', get_class($this));   // This is the default login method, should be configurable from plugin settings
        
        // We can skip the login form here and set username/password etc.
        
        /* @var $identity LSUserIdentity */
        $identity = $event->get('identity');
        
        if (App()->getRequest()->getIsPostRequest() && !is_null(Yii::app()->request->getQuery('onepass'))) {
            // We have a one time password, skip the login form
            $identity->setConfig(array('onepass'=>Yii::app()->getRequest()->getQuery('onepass')));
            $identity->username = Yii::app()->getRequest()->getQuery('user');
            $event->stop(); // Skip the login form
        }
    }
    
    public function newLoginForm(PluginEvent $event)
    {
        $event->getContent($this)
              ->addContent(CHtml::tag('li', array(), "<label for='user'>"  . gT("Username") . "</label><input name='user' id='user' type='text' size='40' maxlength='40' value='' />"))
              ->addContent(CHtml::tag('li', array(), "<label for='password'>"  . gT("Password") . "</label><input name='password' id='password' type='password' size='40' maxlength='40' value='' />"));
    }
    
    public function afterLoginFormSubmit(PluginEvent $event)
    {
        // Here we handle moving post data to the identity
        /* @var $identity LSUserIdentity */
        $identity = $event->get('identity');
        
        $request = App()->getRequest();
        if ($request->getIsPostRequest()) {
            $identity->username = $request->getPost('user');
            $identity->password = $request->getPost('password');
        }
        
        $event->set('identity', $identity);
    }
    
    public function newUserSession(PluginEvent $event)
    {
        // Here we do the actual authentication
        /* @var $identity LSUserIdentity */
        $identity = $event->getSender();
        
        $username = $identity->username;
        $password = $identity->password;
        $config = $identity->getConfig();
        $onepass  = isset($config['onepass']) ? $config['onepass'] : '';
        
        $user = User::model()->findByAttributes(array('users_name' => $username));
        
        if ($user !== null)
        {
            if (gettype($user->password)=='resource')
            {
                $sStoredPassword=stream_get_contents($user->password,-1,0);  // Postgres delivers bytea fields as streams :-o
            }
            else
            {
                $sStoredPassword=$user->password;
            }
        }
        else
        {
            $event->set('result', new LSAuthResult(LSUserIdentity::ERROR_USERNAME_INVALID));
            return;
        }

        if ($onepass != '' && Yii::app()->getConfig("use_one_time_passwords") && md5($onepass) == $user->one_time_pw)
        {
            $user->one_time_pw='';
            $user->save();
            $identity->id = $user->uid;
            $identity->user = $user;
            $event->set('result', new LSAuthResult(LSUserIdentity::ERROR_NONE));
            return;
        }
        
        
        if ($sStoredPassword !== hash('sha256', $password))
        {
            $event->set('result', new LSAuthResult(LSUserIdentity::ERROR_PASSWORD_INVALID));
            return;
        }
        else
        {
            $identity->id = $user->uid;
            $identity->user = $user;
            $event->set('result', new LSAuthResult(LSUserIdentity::ERROR_NONE));
            return;
        }
        
    }
    
    
}