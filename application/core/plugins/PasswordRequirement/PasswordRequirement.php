<?php

class PasswordRequirement  extends \LimeSurvey\PluginManager\PluginBase {
     /**
     * Where to save plugin settings etc.
     * @var string
     */
    protected $storage = 'DbStorage';

    protected $settings = [
        'needsNumber' => array(
            'label' => 'Require at least one digit',
            'type' => 'checkbox',
            'default' => false,
        ),
        'needsUppercase' => array(
            'label' => 'Require at least one uppercase character',
            'type' => 'checkbox',
            'default' => false,
        ),
        'needsNonAlphanumeric' => array(
            'label' => 'Require at least one special character',
            'type' => 'checkbox',
            'default' => false,
        ),
        'minimumSize' => array(
            'label' => 'Minimum password length',
            'type' => 'int',
            'default' => '8',
        ),
    ];
    /**
     * @return void
     */
    public function init()
    {
        $this->subscribe('checkPasswordRequirement');
        $this->subscribe('createRandomPassword');
    }

    public function checkPasswordRequirement() {
        $oEvent = $this->getEvent();
        $password = $oEvent->get('password');

        if($this->get('needsNumber',null,null,false) && ctype_alpha($password)){
            $oEvent->set('passwordOk', false);
            $oEvent->set('passwordError', gT('The password does require at least one digit'));
            return;
        }

        if($this->get('needsUppercase',null,null,false) && ctype_lower($password)){
            $oEvent->set('passwordOk', false);
            $oEvent->set('passwordError', gT('The password does require at least one uppercase character'));
            return;
        }

        if($this->get('needsNonAlphanumeric',null,null,false) && ctype_alnum($password)){
            $oEvent->set('passwordOk', false);
            $oEvent->set('passwordError', gT('The password does require at least one special character'));
            return;
        }

        if(strlen($password) < $this->get('minimumSize',null,null,8)){
            $oEvent->set('passwordOk', false);
            $oEvent->set('passwordError', sprintf(gT('The password does not reach the minimum length of %s characters'), $this->get('minimumSize',null,null,8)));
            return;
        }
    }

    public function createRandomPassword(){
        $oEvent = $this->getEvent();
        $targetSize = $oEvent->get('targetSize',8);

        $targetSize = $targetSize < $this->get('minimumSize',null,null,8) ? $this->get('minimumSize',null,null,8) : $targetSize;
        $uppercase = $this->get('needsUppercase',null,null,false);
        $numeric = $this->get('needsNumber',null,null,false);
        $nonAlpha = $this->get('needsNonAlphanumeric',null,null,false);

        $randomPassword = $this->getRandomString($targetSize, $uppercase ,$numeric, $nonAlpha);
        
        $oEvent->set('password', $randomPassword);
        return;
    }

      /**
     * Provides meta data on the plugin settings that are available for this plugin.
     * This does not include enable / disable; a disabled plugin is never loaded.
     *
     */
    public function getPluginSettings($getValues = true)
    {
        $settings = parent::getPluginSettings();
        $settings = [
            'needsNumber' => array(
                'label' => gT('Require at least one digit'),
                'type' => 'checkbox',
                'default' => false,
            ),
            'needsUppercase' => array(
                'label' => gT('Require at least one uppercase letter'),
                'type' => 'checkbox',
                'default' => false,
            ),
            'needsNonAlphanumeric' => array(
                'label' => gT('Require at least one special character'),
                'type' => 'checkbox',
                'default' => false,
            ),
            'minimumSize' => array(
                'label' => gT('Minimum password length'),
                'type' => 'int',
                'default' => '8',
                'value' => 8
            ),
        ];
        return $settings;
    }

    private function getRandomString($length=8, $uppercase=false, $numeric=false, $nonAlpha=false)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz";
        
        if($uppercase) {
            $chars .=  'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if($numeric) {
            $chars .=  '0123456789';
        }
        if($nonAlpha) {
            $chars .=  '-=!@#$%&*_+,.?;:';
        }

        $str = '';
        $max = strlen($chars) - 1;

        if(function_exists('random_int')) {
            for ($i=0; $i < $length; $i++){
                $str .= $chars[random_int(0, $max)];
            }
        } else {
            for ($i=0; $i < $length; $i++) {
                $str .= $chars[mt_rand(0, $max)];
            }
        }

        return $str;
    }
}