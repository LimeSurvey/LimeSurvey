<?php

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
* 	$Id$
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
        $bCanLogin = $this->_userCanLogin();

        if ($bCanLogin && !is_array($bCanLogin))
        {
            if (Yii::app()->request->getPost('action') ||  !is_null(Yii::app()->request->getQuery('onepass')) || Yii::app()->getConfig('auth_webserver') === true)
            {

                $aData = $this->_doLogin(Yii::app()->request->getParam('user'), Yii::app()->request->getPost('password'),Yii::app()->request->getQuery('onepass',''));

                if (!isset($aData['errormsg']))
                {
                    Failed_login_attempts::model()->deleteAttempts();

                    $this->getController()->_GetSessionUserRights(Yii::app()->session['loginID']);
                    Yii::app()->session['just_logged_in'] = true;
                    Yii::app()->session['loginsummary'] = $this->_getSummary();
                    $this->_doRedirect();
                    die();
                }
                else
                {
                    $this->_renderWrappedTemplate('authentication', 'error', $aData);
                }
            }
            else
            {
                $this->_showLoginForm();
            }
        }
        else
        {
            $this->_renderWrappedTemplate('authentication', 'error', $bCanLogin);
        }
    }

    /**
    * Logout user
    */
    public function logout()
    {
        Yii::app()->user->logout();
        $this->_showLoginForm($this->getController()->lang->gT('Logout successful.'));
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

            if (count($aFields) < 1)
            {
                // wrong or unknown username and/or email
                $aData['errormsg'] = $this->getController()->lang->gT('User name and/or email not found!');
                $aData['maxattempts'] = '';
                $this->_renderWrappedTemplate('authentication', 'error', $aData);
            }
            else
            {
                $aData['message'] = $this->_sendPasswordEmail($sEmailAddr, $aFields);
                $this->_renderWrappedTemplate('authentication', 'message', $aData);
            }
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
        $clang = $this->getController()->lang;
        $sFrom = Yii::app()->getConfig('siteadminemail');
        $sTo = $sEmailAddr;
        $sSubject = $clang->gT('User data');
        $sNewPass = createPassword();
        $sSiteName = Yii::app()->getConfig('sitename');
        $sSiteAdminBounce = Yii::app()->getConfig('siteadminbounce');

        $username = sprintf($clang->gT('Username: %s'), $aFields[0]['users_name']);
        $email    = sprintf($clang->gT('Email: %s'), $sEmailAddr);
        $password = sprintf($clang->gT('New password: %s'), $sNewPass);

        $body   = array();
        $body[] = sprintf($clang->gT('Your user data for accessing %s'), Yii::app()->getConfig('sitename'));
        $body[] = $username;
        $body[] = $password;
        $body   = implode("\n", $body);

        if (SendEmailMessage($body, $sSubject, $sTo, $sFrom, $sSiteName, false, $sSiteAdminBounce))
        {
            User::model()->updatePassword($aFields[0]['uid'], hash('sha256', $sNewPass));
            $sMessage = $username . '<br />' . $email . '<br /><br />' . $clang->gT('An email with your login data was sent to you.');
        }
        else
        {
            $sTmp = str_replace("{NAME}", '<strong>' . $aFields[0]['users_name'] . '</strong>', $clang->gT("Email to {NAME} ({EMAIL}) failed."));
            $sMessage = str_replace("{EMAIL}", $sEmailAddr, $sTmp) . '<br />';
        }

        return $sMessage;
    }

    /**
    * Show login screen
    * @param optional message
    */
    protected function _showLoginForm($sLogoutSummary = '')
    {
        $aData['summary'] = $this->_getSummary('logout', $sLogoutSummary);
        $this->_renderWrappedTemplate('authentication', 'login', $aData);
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

        $clang = $this->getController()->lang;

        switch ($sMethod) {
            case 'logout' :
                $sSummary = $clang->gT('Please log in first.');
                break;

            case 'login' :
            default :
                $sSummary = '<br />' . sprintf($clang->gT('Welcome %s!'), Yii::app()->session['full_name']) . '<br />&nbsp;';
                if (!empty(Yii::app()->session['redirect_after_login']) && strpos(Yii::app()->session['redirect_after_login'], 'logout') === FALSE)
                {
                    Yii::app()->session['metaHeader'] = '<meta http-equiv="refresh"'
                    . ' content="1;URL=' . Yii::app()->session['redirect_after_login'] . '" />';
                    $sSummary = '<p><font size="1"><i>' . $clang->gT('Reloading screen. Please wait.') . '</i></font>';
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
            Yii::app()->request->redirect($this->getController()->createUrl('/admin'));
        }
    }

    /**
    * Check if a user can log in
    * @return bool|array
    */
    private function _userCanLogin()
    {
        $failed_login_attempts = Failed_login_attempts::model();
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
        if (strlen(Yii::app()->session['redirectopage']) > 1)
        {
            Yii::app()->request->redirect(Yii::app()->session['redirectopage']);
        }
        else
        {
            Yii::app()->request->redirect($this->getController()->createUrl('/admin'));
        }
    }

    /**
    * Do the actual login work
    * Note: This function is replicated in parts in remotecontrol.php controller - if you change this don't forget to make according changes there, too (which is why we should make a login helper)
    * @param string $sUsername The username to login with
    * @param string $sPassword The password to login with
    * @return Array of data containing errors for the view
    */
    private function _doLogin($sUsername, $sPassword, $sOneTimePassword)
    {
        $identity = new UserIdentity(sanitize_user($sUsername), $sPassword);

        if (!$identity->authenticate($sOneTimePassword))
        {
            return $this->_getAuthenticationFailedErrorMessage();
        }
        @session_regenerate_id(); // Prevent session fixation
        return $this->_setLoginSessions($identity);
    }

    /**
    * Sets the login sessions
    * @param UserIdentity $identity
    * @return bool True
    */
    private function _setLoginSessions($identity)
    {
        $user = $identity->getUser();

        Yii::app()->user->login($identity);
        $this->_checkForUsageOfDefaultPassword();
        $this->_setSessionData($user);
        $this->_setLanguageSettings($user);

        return true;
    }

    /**
    * Sets the session data
    * @param CActiveRecord $user
    */
    private function _setSessionData($user)
    {
        Yii::app()->session['loginID'] = (int) $user->uid;
        Yii::app()->session['user'] = $user->users_name;
        Yii::app()->session['full_name'] = $user->full_name;
        Yii::app()->session['htmleditormode'] = $user->htmleditormode;
        Yii::app()->session['templateeditormode'] = $user->templateeditormode;
        Yii::app()->session['questionselectormode'] = $user->questionselectormode;
        Yii::app()->session['dateformat'] = $user->dateformat;
        Yii::app()->session['session_hash'] = hash('sha256',getGlobalSetting('SessionName').$user->users_name.$user->uid);
    }

    /**
    * Sets the language settings for the user
    * @param CActiveRecord $user
    */
    private function _setLanguageSettings($user)
    {
        if (Yii::app()->request->getPost('loginlang','default') != 'default')
        {
            $user->lang = sanitize_languagecode(Yii::app()->request->getPost('loginlang'));
            $user->save();
            $sLanguage=$user->lang;
        }
        else if ($user->lang=='auto' || $user->lang=='')
        {
            $sLanguage= getBrowserLanguage();
        }
        else
        {
            $sLanguage=$user->lang;
        }

        Yii::app()->session['adminlang'] = $sLanguage;
        $this->getController()->lang= new limesurvey_lang($sLanguage);
    }

    /**
    * Checks if the user is using default password
    */
    private function _checkForUsageOfDefaultPassword()
    {
        $clang = $this->getController()->lang;
        Yii::app()->session['pw_notify'] = false;
        if (strtolower(Yii::app()->request->getPost('password','') ) === 'password')
        {
            Yii::app()->session['pw_notify'] = true;
            Yii::app()->session['flashmessage'] = $clang->gT('Warning: You are still using the default password (\'password\'). Please change your password and re-login again.');
        }
    }

    /**
    * Get the authentication failed error messages
    * @return array Data
    */
    private function _getAuthenticationFailedErrorMessage()
    {
        $clang = $this->getController()->lang;
        $aData = array();

        $userHostAddress = Yii::app()->request->getUserHostAddress();
        $bUserNotFound = Failed_login_attempts::model()->addAttempt($userHostAddress);

        if ($bUserNotFound)
        {
            $aData['errormsg'] = $clang->gT('Incorrect username and/or password!');
            $aData['maxattempts'] = '';
        }

        $bLockedOut = Failed_login_attempts::model()->isLockedOut($userHostAddress);

        if ($bLockedOut)
        {
            $aData['maxattempts'] = sprintf(
            $clang->gT('You have exceeded the number of maximum login attempts. Please wait %d minutes before trying again.'),
            Yii::app()->getConfig('timeOutTime') / 60
            );
        }

        return $aData;
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
