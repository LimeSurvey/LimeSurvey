<?php

use LimeSurvey\PluginManager\PluginEvent;

/*
* LimeSurvey
* Copyright (C) 2007-2013 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/**
 * For 2.06 most of the functionality in this class will be moved to the LSWebUser class.
 * To not delay release of 2.05 this class was kept the way it is now.
 *
 * @@TODO Move to LSWebUser and change documentation / workflow for authentication plugins
 */
class LSUserIdentity extends CUserIdentity
{
    const ERROR_IP_LOCKED_OUT = 98;
    const ERROR_UNKNOWN_HANDLER = 99;

    protected $config = array();

    /**
     * The userid
     *
     * @var int
     */
    public $id = null;

    /**
     * A User::model() object
     *
     * @var User
     */
    public $user;

    /**
     * This is the name of the plugin to handle authentication
     * default handler is used for remote control
     *
     * @var string
     */
    public $plugin = 'Authdb';

    public function authenticate()
    {
        // First initialize the result, we can later retieve it to get the exact error code/message
        $result = new LSAuthResult(self::ERROR_NONE);

        // Check if the ip is locked out
        if (FailedLoginAttempt::model()->isLockedOut(FailedLoginAttempt::TYPE_LOGIN)) {
            $message = sprintf(gT('You have exceeded the number of maximum login attempts. Please wait %d minutes before trying again.'), App()->getConfig('timeOutTime') / 60);
            $result->setError(self::ERROR_IP_LOCKED_OUT, $message);
        }

        /* Plugin action(s) : need a plugin */
        if ($result->isValid()) {
            if (is_null($this->plugin)) {
                $result->setError(self::ERROR_UNKNOWN_HANDLER);
            } else {
                // Delegate actual authentication to plugin
                $authEvent = new PluginEvent('newUserSession', $this); // TODO: rename the plugin function authenticate()
                $authEvent->set('identity', $this);
                App()->getPluginManager()->dispatchEvent($authEvent);
                $pluginResult = $authEvent->get('result');
                if ($pluginResult instanceof LSAuthResult) {
                    $result = $pluginResult;
                } else {
                    $result->setError(self::ERROR_UNKNOWN_IDENTITY);
                }
            }
        }

        /* Check user exist, and can login after plugin actions */
        if ($result->isValid()) {
            /** @var \User|null */
            $user = User::model()->findByAttributes(array('users_name' => $this->username));
            if (is_null($user) || !$user->canLogin()) {
                // Set the result as invalid if user is  not active : no specific message
                $result->setError(self::ERROR_USERNAME_INVALID);
            }
        }
        /* All action and test done : finalize */
        if ($result->isValid()) {
            // Perform postlogin
            regenerateCSRFToken();
            $this->postLogin();
            // Reset counter after successful login
            FailedLoginAttempt::model()->deleteAttempts(FailedLoginAttempt::TYPE_LOGIN);
        } else {
            // Log a failed attempt
            FailedLoginAttempt::model()->addAttempt(FailedLoginAttempt::TYPE_LOGIN);
            regenerateCSRFToken();
            App()->session->regenerateID(); // Handled on login by Yii
        }

        $this->errorCode = $result->getCode();
        $this->errorMessage = $result->getMessage();

        return $result->isValid();
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns the current user's ID
     *
     * @access public
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the active user's record
     *
     * @access public
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    protected function postLogin()
    {
        $user = $this->getUser();
        App()->user->login($this);

        if ((int) App()->request->getPost('width', '1220') < 1220) {
            // Should be 1280 but allow 60 lenience pixels for browser frame and scrollbar
            Yii::app()->setFlashMessage(gT("Your browser screen size is too small to use the administration properly. The minimum size required is 1280*1024 px."), 'error');
        }

        // Do session setup
        Yii::app()->session['loginID'] = (int) $user->uid;
        Yii::app()->session['user'] = $user->users_name;
        Yii::app()->session['full_name'] = $user->full_name;
        Yii::app()->session['htmleditormode'] = $user->htmleditormode;
        Yii::app()->session['templateeditormode'] = $user->templateeditormode;
        Yii::app()->session['questionselectormode'] = $user->questionselectormode;
        Yii::app()->session['dateformat'] = $user->dateformat;
        Yii::app()->session['session_hash'] = hash('sha256', getGlobalSetting('SessionName') . $user->users_name . $user->uid);

        // Perform language settings
        if (App()->request->getPost('loginlang', 'default') != 'default') {
            $user->lang = sanitize_languagecode(App()->request->getPost('loginlang'));
            $user->save();
            $sLanguage = $user->lang;
        } elseif ($user->lang == 'auto' || $user->lang == '') {
            $sLanguage = getBrowserLanguage();
        } else {
            $sLanguage = $user->lang;
        }

        Yii::app()->session['adminlang'] = $sLanguage;
        App()->setLanguage($sLanguage);

        // Read all plugin config files if superadmin logged in
        if (Permission::model()->hasGlobalPermission('superadmin')) {
            $pm = Yii::app()->getPluginManager();
            $pm->readConfigFiles();
        }

        //At last store the login time in the user table
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();
    }

    public function setPlugin($name)
    {
        $this->plugin = $name;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }
}
