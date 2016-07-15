<?php
// see: https://scrutinizer-ci.com/g/LimeSurvey/LimeSurvey/issues/master/files/application/controllers/admin/authentication.php?selectedSeverities[0]=10&orderField=path&order=asc&honorSelectedPaths=0
// use ls\pluginmanager\PluginEvent;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
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
* Authentication Controller
*
* This controller performs authentication
*
* @package        LimeSurvey
* @subpackage    Backend
*/
class Authentication extends Survey_Common_Action
{

    /**
    * Show login screen and parse login data
    */
    public function index()
    {
        $this->_redirectIfLoggedIn();

        $aData = array();

        // Make sure after first run / update the authdb plugin is registered and active
        // it can not be deactivated
        if (!class_exists('Authdb', false)) {
            $plugin = Plugin::model()->findByAttributes(array('name'=>'Authdb'));
            if (!$plugin) {
                $plugin = new Plugin();
                $plugin->name = 'Authdb';
                $plugin->active = 1;
                $plugin->save();
                App()->getPluginManager()->loadPlugin('Authdb', $plugin->id);
            } else {
                $plugin->active = 1;
                $plugin->save();
            }
        }

        $beforeLogin = new PluginEvent('beforeLogin');
        $beforeLogin->set('identity', new LSUserIdentity('', ''));

        App()->getPluginManager()->dispatchEvent($beforeLogin);
        /* @var $identity LSUserIdentity */
        $identity = $beforeLogin->get('identity');

        if (!$beforeLogin->isStopped() && is_null(App()->getRequest()->getPost('login_submit')) )
        {
            if (!is_null($beforeLogin->get('default'))) {
                $aData['defaultAuth'] = $beforeLogin->get('default');
            }
            else {
                if (App()->getPluginManager()->isPluginActive(Yii::app()->getConfig('default_displayed_auth_method'))) {
                        $aData['defaultAuth'] = Yii::app()->getConfig('default_displayed_auth_method');
                    }
                    else {
                        $aData['defaultAuth'] = 'Authdb';
                    }
            }
            $newLoginForm = new PluginEvent('newLoginForm');
            App()->getPluginManager()->dispatchEvent($newLoginForm);
            $aData['summary'] = $this->_getSummary('logout');
            $aData['pluginContent'] = $newLoginForm->getAllContent();
        }
        else
        {
             // Handle getting the post and populating the identity there
            $authMethod = App()->getRequest()->getPost('authMethod', $identity->plugin);
            $identity->plugin = $authMethod;

            $event = new PluginEvent('afterLoginFormSubmit');
            $event->set('identity', $identity);
            App()->getPluginManager()->dispatchEvent($event, array($authMethod));
            $identity = $event->get('identity');

            // Now authenticate
            if ($identity->authenticate())
            {
                FailedLoginAttempt::model()->deleteAttempts();
                App()->user->setState('plugin', $authMethod);
                $this->getController()->_GetSessionUserRights(Yii::app()->session['loginID']);
                Yii::app()->session['just_logged_in'] = true;
                Yii::app()->session['loginsummary'] = $this->_getSummary();

                $event = new PluginEvent('afterSuccessfulLogin');
                App()->getPluginManager()->dispatchEvent($event);

                $this->_doRedirect();

            }
            else
            {
                // Failed
                $event = new PluginEvent('afterFailedLoginAttempt');
                $event->set('identity', $identity);
                App()->getPluginManager()->dispatchEvent($event);

                $message = $identity->errorMessage;
                if (empty($message)) {
                    // If no message, return a default message
                    $message = gT('Incorrect username and/or password!');
                }
                App()->user->setFlash('loginError', $message);
                $this->getController()->redirect(array('/admin/authentication/sa/login'));
            }
        }
        // If for any reason, the plugin bugs, we can't let the user with a blank screen.
        $this->_renderWrappedTemplate('authentication', 'login', $aData);
    }

    /**
    * Logout user
    */
    public function logout()
    {
        /* Adding beforeLogout event */
        $beforeLogout = new PluginEvent('beforeLogout');
        App()->getPluginManager()->dispatchEvent($beforeLogout);
        // Expire the CSRF cookie
        $cookie = new CHttpCookie('YII_CSRF_TOKEN', '');
        $cookie->expire = time()-3600;
        Yii::app()->request->cookies['YII_CSRF_TOKEN'] = $cookie;
        App()->user->logout();
        App()->user->setFlash('loginmessage', gT('Logout successful.'));

        /* Adding afterLogout event */
        $event = new PluginEvent('afterLogout');
        App()->getPluginManager()->dispatchEvent($event);

        $this->getController()->redirect(array('/admin/authentication/sa/login'));
    }

    /**
    * Forgot Password screen
    */
    public function forgotpassword()
    {
        $this->_redirectIfLoggedIn();

        if (!Yii::app()->request->getPost('action'))
        {
            $this->_renderWrappedTemplate('authentication', 'forgotpassword');
        }
        else
        {
            $sUserName = Yii::app()->request->getPost('user');
            $sEmailAddr = Yii::app()->request->getPost('email');

            $aFields = User::model()->findAllByAttributes(array('users_name' => $sUserName, 'email' => $sEmailAddr));

            // Preventing attacker from easily knowing whether the user and email address are valid or not (and slowing down brute force attacks)
            usleep(rand(Yii::app()->getConfig("minforgottenpasswordemaildelay"),Yii::app()->getConfig("maxforgottenpasswordemaildelay")));

            if (count($aFields) < 1 || ($aFields[0]['uid'] != 1 && !Permission::model()->hasGlobalPermission('auth_db','read',$aFields[0]['uid'])))
            {
                // Wrong or unknown username and/or email. For security reasons, we don't show a fail message
                $aData['message'] = '<br>'.gT('If username and email are valid and you are allowed to use internal database authentication a new password has been sent to you').'<br>';
            }
            else
            {
                $aData['message'] = '<br>'.$this->_sendPasswordEmail($sEmailAddr, $aFields).'</br>';
            }
            $this->_renderWrappedTemplate('authentication', 'message', $aData);
        }
    }

    /**
    * Send the forgot password email
    *
    * @param string $sEmailAddr
    * @param array $aFields
    */
    private function _sendPasswordEmail($sEmailAddr, $aFields)
    {
        $sFrom = Yii::app()->getConfig("siteadminname") . " <" . Yii::app()->getConfig("siteadminemail") . ">";
        $sTo = $sEmailAddr;
        $sSubject = gT('User data');
        $sNewPass = createPassword();
        $sSiteName = Yii::app()->getConfig('sitename');
        $sSiteAdminBounce = Yii::app()->getConfig('siteadminbounce');

        $username = sprintf(gT('Username: %s'), $aFields[0]['users_name']);
        $password = sprintf(gT('New password: %s'), $sNewPass);

        $body   = array();
        $body[] = sprintf(gT('Your user data for accessing %s'), Yii::app()->getConfig('sitename'));
        $body[] = $username;
        $body[] = $password;
        $body   = implode("\n", $body);

        if (SendEmailMessage($body, $sSubject, $sTo, $sFrom, $sSiteName, false, $sSiteAdminBounce))
        {
            User::model()->updatePassword($aFields[0]['uid'], $sNewPass);
            // For security reasons, we don't show a successful message
            $sMessage = gT('If username and email are valid and you are allowed to use internal database authentication a new password has been sent to you');
        }
        else
        {
            $sMessage = gT('Email failed');
        }

        return $sMessage;
    }

    /**
    * Get's the summary
    * @param string $sMethod login|logout
    * @param string $sSummary Default summary
    * @return string Summary
    */
    private function _getSummary($sMethod = 'login', $sSummary = '')
    {
        if (!empty($sSummary))
        {
            return $sSummary;
        }

        switch ($sMethod) {
            case 'logout' :
                $sSummary = gT('Please log in first.');
                break;

            case 'login' :
            default :
                $sSummary = '<br />' . sprintf(gT('Welcome %s!'), Yii::app()->session['full_name']) . '<br />&nbsp;';
                if (!empty(Yii::app()->session['redirect_after_login']) && strpos(Yii::app()->session['redirect_after_login'], 'logout') === FALSE)
                {
                    Yii::app()->session['metaHeader'] = '<meta http-equiv="refresh"'
                    . ' content="1;URL=' . Yii::app()->session['redirect_after_login'] . '" />';
                    $sSummary = '<p><font size="1"><i>' . gT('Reloading screen. Please wait.') . '</i></font>';
                    unset(Yii::app()->session['redirect_after_login']);
                }
                break;
        }

        return $sSummary;
    }

    /**
    * Redirects a logged in user to the administration page
    */
    private function _redirectIfLoggedIn()
    {
        if (!Yii::app()->user->getIsGuest())
        {
            $this->getController()->redirect(array('/admin'));
        }
    }

    /**
    * Check if a user can log in
    * @return bool|array
    */
    private function _userCanLogin()
    {
        $failed_login_attempts = FailedLoginAttempt::model();
        $failed_login_attempts->cleanOutOldAttempts();

        if ($failed_login_attempts->isLockedOut())
        {
            return $this->_getAuthenticationFailedErrorMessage();
        }
        else
        {
            return true;
        }
    }

    /**
    * Redirect after login
    */
    private function _doRedirect()
    {
        $returnUrl = App()->user->getReturnUrl(array('/admin'));
        $this->getController()->redirect($returnUrl);
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'authentication', $aViewUrls = array(), $aData = array())
    {
        $aData['display']['menu_bars'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

}
