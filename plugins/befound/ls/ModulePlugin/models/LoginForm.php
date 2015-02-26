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
            ['username,password', 'safe']
        ];
    }
}