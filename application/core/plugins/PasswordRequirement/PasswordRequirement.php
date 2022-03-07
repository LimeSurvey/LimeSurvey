<?php
/**
 * Copre plugin for LimeSurvey
 * @version 1.1.0
 */

class PasswordRequirement extends \LimeSurvey\PluginManager\PluginBase
{
     /**
     * Where to save plugin settings etc.
     * @var string
     */
    protected $storage = 'DbStorage';

    /** @inheritdoc, this plugin didn't have any public method */
    public $allowedPublicMethods = array();

    protected $settings = [
        'needsNumber' => array(
            'label' => 'Require at least one digit',
            'type' => 'checkbox',
            'default' => true,
        ),
        'needsUppercase' => array(
            'label' => 'Require at least one uppercase character',
            'type' => 'checkbox',
            'default' => true,
        ),
        'needsNonAlphanumeric' => array(
            'label' => 'Require at least one special character',
            'type' => 'checkbox',
            'default' => false,
        ),
        'minimumSize' => array(
            'label' => 'Minimum password length',
            'type' => 'int',
            'default' => 12,
        ),
        'surveySaveActive' => array(
            'type' => 'boolean',
            'label' => 'Check password when save survey',
            'default' => false,
        ),
        'surveySaveNeedsNumber' => array(
            'label' => 'Require at least one digit',
            'type' => 'checkbox',
            'default' => false,
        ),
        'surveySaveNeedsUppercase' => array(
            'label' => 'Require at least one uppercase character',
            'type' => 'checkbox',
            'default' => false,
        ),
        'surveySaveNeedsNonAlphanumeric' => array(
            'label' => 'Require at least one special character',
            'type' => 'checkbox',
            'default' => false,
        ),
        'surveySaveMinimumSize' => array(
            'label' => 'Minimum password length',
            'type' => 'int',
            'default' => 8,
        ),
    ];
    /**
     * @return void
     */
    public function init()
    {
        $this->subscribe('checkPasswordRequirement');
        $this->subscribe('createRandomPassword');

        $this->subscribe('validateSaveSurveyForm');
    }

    public function checkPasswordRequirement()
    {
        $oEvent = $this->getEvent();
        $password = $oEvent->get('password');
        $errors = $this->checkValidityOfPassword(
            $password,
            $this->get('needsNumber', null, null, true),
            $this->get('needsUppercase', null, null, true),
            $this->get('needsNonAlphanumeric', null, null, false),
            $this->get('minimumSize', null, null, 12)
        );
        if ($errors) {
            $oEvent->set('passwordOk', false);
            $oEvent->set('passwordError', $errors[0]); // Previous system
            $oEvent->set('passwordErrors', $errors);
        }
    }

    /** @see event 
     * get the current save action password and add errors if needed
     * @return void
     * */
    public function validateSaveSurveyForm()
    {
        if (!$this->get('surveySaveActive', null, null, false)) {
            return;
        }
        $event = $this->getEvent();
        $password = $event->get('savePass');
        $aSaveErrors = $event->get('aSaveErrors');
        $errors = $this->checkValidityOfPassword(
            $password,
            $this->get('surveySaveNeedsNumber', null, null, false),
            $this->get('surveySaveNeedsUppercase', null, null, false),
            $this->get('surveySaveNeedsNonAlphanumeric', null, null, false),
            $this->get('surveySaveMinimumSize', null, null, 8)
        );
        if (empty($errors)) {
            return;
        }
        $event->append('aSaveErrors', $errors);
    }

    /**
     * Chek the validity of a pasword according to option
     * @param string $password
     * @param boolean $needsNumber
     * @param boolean $needsUppercase
     * @param boolean $needsNonAlphanumeric
     * @return null|array, null mean no issue.
     */
    private function checkValidityOfPassword($password, $needsNumber, $needsUppercase, $needsNonAlphanumeric, $minimumSize = 8)
    {
        $errors = [];
        if ($needsNumber && ctype_alpha($password)) {
            $errors[] = gT('The password does require at least one digit');
        }
        if ($needsUppercase && ctype_lower($password)) {
            $errors[] = gT('The password does require at least one uppercase character');
        }
        if ($needsNonAlphanumeric && ctype_alnum($password)) {
            $errors[] = gT('The password does require at least one special character');
        }
        if ($minimumSize && strlen($password) < $minimumSize) {
            $errors[] = sprintf(gT('The password does not reach the minimum length of %s characters'), $minimumSize);
        }
        if (empty($errors)) {
            return null;
        }
        return $errors;
    }

    public function createRandomPassword()
    {
        $oEvent = $this->getEvent();
        $targetSize = $oEvent->get('targetSize', 8);

        $targetSize = $targetSize < $this->get('minimumSize', null, null, 12) ? $this->get('minimumSize', null, null, 12) : $targetSize;
        $uppercase = $this->get('needsUppercase', null, null, true);
        $numeric = $this->get('needsNumber', null, null, true);
        $nonAlpha = $this->get('needsNonAlphanumeric', null, null, false);

        $randomPassword = $this->getRandomString($targetSize, $uppercase, $numeric, $nonAlpha);
        
        $oEvent->set('password', $randomPassword);
    }

      /**
     * Provides meta data on the plugin settings that are available for this plugin.
     * This does not include enable / disable; a disabled plugin is never loaded.
     *
     */
    public function getPluginSettings($getValues = true)
    {
        $settings = parent::getPluginSettings();
        return $settings;
    }

    private function getRandomString($length = 8, $uppercase = false, $numeric = false, $nonAlpha = false)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz";
        
        if ($uppercase) {
            $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if ($numeric) {
            $chars .= '0123456789';
        }
        if ($nonAlpha) {
            $chars .= '-=!@#$%&*_+,.?;:';
        }

        $str = '';
        $max = strlen($chars) - 1;

        if (function_exists('random_int')) {
            for ($i = 0; $i < $length; $i++) {
                $str .= $chars[random_int(0, $max)];
            }
        } else {
            for ($i = 0; $i < $length; $i++) {
                $str .= $chars[mt_rand(0, $max)];
            }
        }

        return $str;
    }
}
