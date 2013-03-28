<?php
    class AuditLog extends PluginBase {

        protected $storage = 'DbStorage';    
        static protected $description = 'Example plugin';

        public function __construct(PluginManager $manager, $id) {
            parent::__construct($manager, $id);


            /**
            * Here you should handle subscribing to the events your plugin will handle
            */
            //$this->subscribe('afterPluginLoad', 'helloWorld');
            $this->subscribe('beforeSurveySettings');
            $this->subscribe('newSurveySettings');
            $this->subscribe('beforeActivate');
            $this->subscribe('beforeUserSave');
            $this->subscribe('beforeUserDelete');
            $this->subscribe('beforePermissionSetSave');
        }

        public function beforePermissionSetSave(PluginEvent $event)
        {
            $aNewPermissions=$event->get('aNewPermissions');
            $iSurveyID=$event->get('iSurveyID');
            $iUserID=$event->get('iUserID');
            $oCurrentUser=$this->api->getCurrentUser();
            $oOldPermission=$this->api->getUserPermissionSet($iUserID,$iSurveyID);
            $sAction='update';
                        

            
            if (count(array_diff_assoc_recursive($aNewPermissions,$oOldPermission)))
            {
                $oAutoLog=new mdlAuditlog();
                $oAutoLog->uid=$oCurrentUser->uid;
                $oAutoLog->entity='permission';
                $oAutoLog->action=$sAction;
                $oAutoLog->oldvalues=json_encode(array_diff_assoc_recursive($oOldPermission,$aNewPermissions));
                $oAutoLog->newvalues=json_encode(array_diff_assoc_recursive($aNewPermissions,$oOldPermission));
                $oAutoLog->fields=implode(',',array_keys(array_diff_assoc_recursive($aNewPermissions,$oOldPermission)));
                $oAutoLog->save();
            }
        }
        
        
        /**
        * Function catches if a user was modified or created
        * All data except for the password hash is saved
        * 
        * @param PluginEvent $event
        */
        public function beforeUserSave(PluginEvent $event)
        {
            $oUserData=$event->getSender();
            $oCurrentUser=$this->api->getCurrentUser();
            $oOldUser=$this->api->getUser($oUserData->uid);
            if (!$oOldUser)
            {
                $sAction='create';
                $aOldValues=array();
            }
            else
            {                
                $sAction='update';
                $aOldValues=$oOldUser->getAttributes();
            }
            $aNewValues=$oUserData->getAttributes();
                        
            // If the password has changed then indicate that it has changed but assign fake hashes
            if ($aNewValues['password']!=$aOldValues['password'])
            {
                $aOldValues['password']=hash('md5','12345');
                $aNewValues['password']=hash('md5','67890');
            };
            
            if (count(array_diff_assoc($aNewValues,$aOldValues)))
            {
                
                $oAutoLog = $this->api->newModel($this, 'auditlog');
                $oAutoLog->uid=$oCurrentUser->uid;
                $oAutoLog->entity='user';
                $oAutoLog->action=$sAction;
                $oAutoLog->oldvalues=json_encode(array_diff_assoc($aOldValues,$aNewValues));
                $oAutoLog->newvalues=json_encode(array_diff_assoc($aNewValues,$aOldValues));
                $oAutoLog->fields=implode(',',array_keys(array_diff_assoc($aNewValues,$aOldValues)));
                $oAutoLog->save();
            }
        }
                                                            
        public function beforeUserDelete(PluginEvent $event)
        {
            $oUserData=$event->getSender();
            $oCurrentUser=$this->api->getCurrentUser();
            $oOldUser=$this->api->getUser($oUserData->uid);
            if ($oOldUser)
            {
                $aOldValues=$oOldUser->getAttributes();
                unset($aOldValues['password']);
                $oAutoLog = $this->api->newModel($this, 'auditlog');
                $oAutoLog->uid=$oCurrentUser->uid;
                $oAutoLog->entity='user';
                $oAutoLog->action='delete';
                $oAutoLog->oldvalues=json_encode($aOldValues);
                $oAutoLog->fields=implode(',',array_keys($aOldValues));
                $oAutoLog->save();
            }
        }

        
                                                            
        public function beforeActivate(PluginEvent $event)
        {
            if (!$this->api->tableExists($this, 'auditlog'))
            {
                $this->api->createTable($this, 'auditlog', array('id'=>'pk',
                    'created'=>'datetime',
                    'uid'=>'string',
                    'entity'=>'string',
                    'action'=>'string',
                    'fields'=>'text',
                    'oldvalues'=>'text',
                    'newvalues'=>'text'));
            }
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
