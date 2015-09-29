<?php
namespace ls\pluginmanager;

trait InternalUserDbTrait {
    /**
     * 
     * @return array|\IDataProvider The user objects or a dataprovider for them.
     */
    public function getUsers() {
        return new \CActiveDataProvider('ls\models\User');
    }
    
    public function getUser($id) {
        return \ls\models\User::model()->findByPk($id);
    }
    
    public function enumerable() {
        return true;
    }
    /**
     * 
     * @return boolean False if users from this authenticator can not be updated.
     */
    public function writable() {
        return true;
    }
    
    public function getLoginSettings()
    {
        return [
            'label' => $this->name,
            'settings' => [
                'username' => [
                    'type' => 'string',
                    'label' => gT("Username"),
                ],
                'password' => [
                    'type' => 'password',
                    'label' => gT("Password"),
                ],
            ]
        ];
    }
}