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
 * 	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
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
     * Executes the action based on given input
     *
     * @access public
     * @return void
     */
    public function run()
    {
        if (isset($_GET['login']))
            $this->login();
        elseif (isset($_GET['logout']))
            $this->logout();
        elseif (isset($_GET['forgotpassword']))
            $this->forgotPassword();
        else
            $this->index();
    }

    /**
     * Default Controller Action
     *
     * @access public
     * @return void
     */
    public function index()
    {
        $this->_doRedirect();
    }

    /**
     * Show login screen and parse login data
     */
    public function login()
    {
        $this->_redirectIfLoggedIn();
        $sIp = Yii::app()->request->getUserHostAddress();
        $canLogin = $this->_userCanLogin($sIp);

        if ($canLogin && !is_array($canLogin))
        {
            if (CHttpRequest::getPost('action'))
            {
                $aData = $this->_doLogin(CHttpRequest::getPost('user'), CHttpRequest::getPost('password'));

                if (!isset($aData['errormsg']))
                {
                    Failed_login_attempts::model()->deleteAttempts($sIp);

                    $this->getController()->_GetSessionUserRights(Yii::app()->session['loginID']);
                    Yii::app()->session['just_logged_in'] = true;
                    Yii::app()->session['loginsummary'] = $this->_getSummary();
                    $this->_doRedirect();
                    die();
                }
                else {
                    $this->_renderWrappedTemplate('error', $aData);
                }
            }
            else
            {
                $this->_showLoginForm();
            }
        }
        else
        {
            $this->_renderWrappedTemplate('error', $canLogin);
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
    public function forgotPassword()
    {
        $this->_redirectIfLoggedIn();

        if (!CHttpRequest::getPost('action'))
        {
            $this->_renderWrappedTemplate('forgotpassword');
        }
        else
        {
            $postuser = CHttpRequest::getPost('user');
            $sEmailAddr = CHttpRequest::getPost('email');

            $aFields = User::model()->getSomeRecords(array('users_name, password, uid'), array('users_name' => $postuser, 'email' => $sEmailAddr));

            if (count($aFields) < 1)
            {
                // wrong or unknown username and/or email
                $aData['errormsg'] = $this->getController()->lang->gT('User name and/or email not found!');
                $aData['maxattempts'] = '';
                $this->_renderWrappedTemplate('error', $aData);
            }
            else
            {
                $aData['message'] = $this->_sendPasswordEmail($sEmailAddr, $aFields);
                $this->_renderWrappedTemplate('message', $aData);
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
            $tmp = str_replace("{NAME}", '<strong>' . $aFields[0]['users_name'] . '</strong>', $clang->gT("Email to {NAME} ({EMAIL}) failed."));
            $sMessage = str_replace("{EMAIL}", $sEmailAddr, $tmp) . '<br />';
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
        $this->_renderWrappedTemplate('login', $aData);
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
                $sSummary = $clang->gT('You have to login first.');
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
     * @param string $sIp IP Address
     * @return bool|array
     */
    private function _userCanLogin($sIp = '')
    {
        if (empty($sIp))
        {
            $sIp = Yii::app()->request->getUserHostAddress();
        }

        $failed_login_attempts = Failed_login_attempts::model();
        $failed_login_attempts->cleanOutOldAttempts();

        if ($failed_login_attempts->isLockedOut($sIp))
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
    private function _doLogin($sUsername, $sPassword)
    {
        $identity = new UserIdentity(sanitize_user($sUsername), $sPassword);

        if (!$identity->authenticate())
        {
            return $this->_getAuthenticationFailedErrorMessage();
        }

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
        Yii::app()->session['checksessionpost'] = sRandomChars(10);
    }

    /**
     * Sets the language settings for the user
     * @param CActiveRecord $user
     */
    private function _setLanguageSettings($user)
    {
        if (CHttpRequest::getPost('loginlang') !== 'default')
        {
            $user->lang = sanitize_languagecode(CHttpRequest::getPost('loginlang'));
            $user->save();
        }

        Yii::app()->session['adminlang'] = $user->lang;
        $this->getController()->lang->limesurvey_lang(array('langcode' => $user->lang));
    }

    /**
     * Checks if the user is using default password
     */
    private function _checkForUsageOfDefaultPassword()
    {
        $clang = $this->getController()->lang;
        if (strtolower($_POST['password']) === 'password')
        {
            Yii::app()->session['pw_notify'] = true;
            Yii::app()->session['flashmessage'] = $clang->gT('Warning: You are still using the default password (\'password\'). Please change your password and re-login again.');
        }
        else
        {
            Yii::app()->session['pw_notify'] = false;
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
                    $clang->gT('You have exceeded you maximum login attempts. Please wait %d minutes before trying again'),
                    Yii::app()->getConfig('timeOutTime') / 60
            );
        }

        return $aData;
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    function _renderWrappedTemplate($aViewUrls = array(), $aData = array())
    {
        parent::_renderWrappedTemplate('authentication', $aViewUrls, $aData);
    }

}
