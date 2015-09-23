<?php
namespace befound\ls\ModulePlugin\models;
use \Yii;
class LoginForm extends FormModel 
{
    public $username;
    public $password;
    
    public function attributeLabels() 
    {
        return [
            'username' => Yii::t('dashboard', "Username or email"),
            'password' => Yii::t('dashboard', "Password"),
        ];
    }
    public function rules() 
    {
        return [
            ['username', 'safe', 'on' => 'login'],
            ['password', 'validatePassword', 'on' => 'login']
        ];
    }
    
    public function validatePassword($attribute, $params) {
        if (strpos($this->username, '@') !== false) {
            $user = User::model()->findByAttributes([
                'username' => $this->username
            ]);
        } elseif (null !== $email = Email::model()->findByAttributes(['email' => $this->username])) {
            $user = $email;
        }
        
        if (!isset($user)) {
            /**
             * @todo Remove [1] so no data leaks (ie wrong password is 100% the same as user not existent)
             */
            $this->addError($attribute, 'Username or password incorrect. [1]');
        } elseif (!$user->verifyPassword($this->password)) {
            /**
             * @todo Remove [2] so no data leaks (ie wrong password is 100% the same as user not existent)
             */
            $this->addError($attribute, 'Username or password incorrect. [2]');
            
        }
    }
}