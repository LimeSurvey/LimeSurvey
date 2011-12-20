<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* $Id: authentication.php 11326 2011-11-04 12:33:50Z shnoulle $
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

class Authentication extends CAction
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
            $this->forgotpassword();
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
        redirect('/admin', 'refresh');
    }

    /**
     * Show login screen and parse login data
    */
    public function login()
    {
        if (Yii::app()->user->getIsGuest())
        {
            $sIp = Yii::app()->request->getUserHostAddress();

            $failed_login_attempts = Failed_login_attempts::model();
            $failed_login_attempts->cleanOutOldAttempts();
			
            $bCannotLogin = $failed_login_attempts->isLockedOut($sIp);
            if (!$bCannotLogin)
            {
                if (!empty($_POST['action']))
                {
                    
                    $clang = $this->getController()->lang;

                    $data = $this->_doLogin($_POST['user'], $_POST['password']);

                    if (isset($data['errormsg']))
                        $this->getController()->render('/admin/authentication/error', $data);
                    else
                    {
                        $failed_login_attempts->deleteAttempts($sIp);
                        $loginsummary = '<br />' . sprintf($clang->gT('Welcome %s!'), Yii::app()->session['full_name']) . '<br />&nbsp;';
                        if (!empty(Yii::app()->session['redirect_after_login']) && strpos(Yii::app()->session['redirect_after_login'], 'logout') === FALSE)
                        {
                            Yii::app()->session['metaHeader']  = '<meta http-equiv="refresh"'
                            . ' content="1;URL=' . Yii::app()->session['redirect_after_login'].'" />';
                            $loginsummary = '<p><font size="1"><i>' . $clang->gT('Reloading screen. Please wait.') . '</i></font>\n';
                            unset(Yii::app()->session['redirect_after_login']);
                        }
                        $this->getController()->_GetSessionUserRights(Yii::app()->session['loginID']);
                        Yii::app()->session['just_logged_in'] = true;
                        Yii::app()->session['loginsummary'] = $loginsummary;
                        $this->_doRedirect();
                    }
                }
                else
                    $this->_showLoginForm();
            }
            else
            {
                // wrong or unknown username
                $data['errormsg']="";
                $data['maxattempts'] = sprintf(
                $this->getController()->lang->gT('You have exceeded you maximum login attempts. Please wait %d minutes before trying again'),
                (Yii::app()->getConfig("timeOutTime")/60)) . '<br />';

                $this->_renderTemplateWrappedInHeaderAndFooter('/admin/authentication/error', $data);
            }
        }
        else
        {
        	
            Yii::app()->request->redirect($this->getController()->createUrl('/admin'));
        }
    }

    private function _doRedirect()
    {
        if (strlen(Yii::app()->session['redirectopage']) > 1) {
            Yii::app()->request->redirect(Yii::app()->session['redirectopage']);
        } else {
            Yii::app()->request->redirect($this->getController()->createUrl('/admin'));
        }
    }

    /**
    * Logout user
    */
    public function logout()
    {
        Yii::app()->user->logout();
        $this->_showLoginForm('<p>'.$this->getController()->lang->gT('Logout successful.'));
    }

    /**
    * Forgot Password screen
    */
    public function forgotpassword()
    {
        $clang = $this->getController()->lang;
        if(!CHttpRequest::getPost('action'))
        {
            $this->_renderTemplateWrappedInHeaderAndFooter("/admin/authentication/forgotpassword");
        }
        else
        {
            $postuser = CHttpRequest::getPost('user');
            $emailaddr = CHttpRequest::getPost('email');

            //$query = "SELECT users_name, password, uid FROM ".db_table_name('users')." WHERE users_name=".$connect->qstr($postuser)." AND email=".$connect->qstr($emailaddr);
            //$result = db_select_limit_assoc($query, 1) or safe_die ($query."<br />".$connect->ErrorMsg());  // Checked
            $query = User::model()->getSomeRecords(array("users_name, password, uid"),array("users_name"=>$postuser,"email"=>$emailaddr));

            if (count($query)  < 1)
            {
                // wrong or unknown username and/or email
                $data['errormsg'] = $this->getController()->lang->gT("User name and/or email not found!");
                $data['maxattempts']="";
                $this->_renderTemplateWrappedInHeaderAndFooter("/admin/authentication/error", $data);

            } else {
                //$fields = $result->FetchRow();
                $fields = $query;

                // send Mail
                $new_pass = createPassword();
                $body = sprintf($clang->gT("Your user data for accessing %s"),Yii::app()->getConfig("sitename")). "<br />\n";;
                $body .= $clang->gT("Username") . ": " . $fields[0]['users_name'] . "<br />\n";
                $body .= $clang->gT("New password") . ": " . $new_pass . "<br />\n";

               // $this->load->config("email");
                $subject = $clang->gT("User data","unescaped");
                $to = $emailaddr;
                $from = Yii::app()->getConfig("siteadminemail");
                $sitename = Yii::app()->getConfig("siteadminname");
                if(SendEmailMessage($body, $subject, $to, $from, Yii::app()->getConfig("sitename"), false,Yii::app()->getConfig("siteadminbounce")))
                {
                    //$query = "UPDATE ".db_table_name('users')." SET password='".SHA256::hashing($new_pass)."' WHERE uid={$fields['uid']}";
                    //$connect->Execute($query); //Checked
                    User::model()->updatePassword($fields[0]['uid'], hash('sha256', $new_pass));
                    $data['message'] = '<br />' . $clang->gT("Username") . ': ' . $fields[0]['users_name'] . '<br />' . $clang->gT("Email") . ': ' . $emailaddr . '<br />
                    <br />' . $clang->gT('An email with your login data was sent to you.');
                    $this->_renderTemplateWrappedInHeaderAndFooter('/admin/authentication/message', $data);
                }
                else
                {
                    $tmp = str_replace("{NAME}", "<strong>" . $fields[0]['users_name'] . "</strong>", $clang->gT("Email to {NAME} ({EMAIL}) failed."));
                    $data['message'] = '<br />' . str_replace("{EMAIL}", $emailaddr, $tmp) . '<br />';
                    $this->_renderTemplateWrappedInHeaderAndFooter('/admin/authentication/message', $data);
                }
            }
        }
    }

    /**
    * Show login screen
    * @param optional message
    */
    protected function _showLoginForm( $logoutSummary = '' )
    {
        if ($logoutSummary === '' )
        {
            $data['summary'] = $this->getController()->lang->gT('You have to login first.');
        }
        else
        {
            $data['summary'] = $logoutSummary;
        }
        $this->_renderTemplateWrappedInHeaderAndFooter('/admin/authentication/login', $data);
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

        if (!$identity->authenticate()) {
            return $this->_getAuthenticationFailedErrorMessage();
        }

        return $this->_setLoginSessions($identity);
    }

    private function _setLoginSessions($identity)
    {
        $user = $identity->getUser();

        Yii::app()->user->login($identity);
        $this->_checkForUsageOfDefaultPassword();
        $this->_setSessionData($user);
        $this->_setLanguageSettings($user);

        return true;
    }

    private function _setSessionData($user)
    {
        Yii::app()->session['loginID'] = intval($user->uid);
        Yii::app()->session['user'] = $user->users_name;
        Yii::app()->session['full_name'] = $user->full_name;
        Yii::app()->session['htmleditormode'] = $user->htmleditormode;
        Yii::app()->session['templateeditormode'] = $user->templateeditormode;
        Yii::app()->session['questionselectormode'] = $user->questionselectormode;
        Yii::app()->session['dateformat'] = $user->dateformat;
        Yii::app()->session['checksessionpost'] = sRandomChars(10);
    }

    private function _setLanguageSettings($user)
    {
        if (isset($_POST['loginlang']) && $_POST['loginlang'] !== 'default')
        {
            $user->lang = sanitize_languagecode($_POST['loginlang']);
            $user->save();
        }

        Yii::app()->session['adminlang'] = $user->lang;
        $this->getController()->lang->limesurvey_lang(array('langcode' => $user->lang));
    }

    private function _checkForUsageOfDefaultPassword()
    {
        $clang = $this->getController()->lang;
        if (strtolower($_POST['password']) === 'password') {
            Yii::app()->session['pw_notify'] = true;
            Yii::app()->session['flashmessage'] = $clang->gT('Warning: You are still using the default password (\'password\'). Please change your password and re-login again.');
        }
        else
        {
            Yii::app()->session['pw_notify'] = false;
        }
    }

    private function _getAuthenticationFailedErrorMessage()
    {
        $clang = $this->getController()->lang;
        $data = array();
        $userHostAddress = Yii::app()->request->getUserHostAddress();
        $isUserNotFound = Failed_login_attempts::model()->addAttempt($userHostAddress);

        if ( $isUserNotFound )
        {
            $data['errormsg'] = $clang->gT('Incorrect username and/or password!');
            $data['maxattempts'] = '';

            $isLockedOut = Failed_login_attempts::model()->isLockedOut($userHostAddress);

            if ( $isLockedOut )
            {
                $data['maxattempts'] = sprintf(
                    $clang->gT('You have exceeded you maximum login attempts. Please wait %d minutes before trying again'),
                    Yii::app()->getConfig('timeOutTime') / 60
                );
            }
        }

        return $data;
    }

    private function _renderTemplateWrappedInHeaderAndFooter($szViewUrl, $data = NULL)
    {
        $clang = $this->getController()->lang;
        $data['clang'] = $clang;
        $this->getController()->_getAdminHeader();
        $this->getController()->render($szViewUrl, $data);
        $this->getController()->_getAdminFooter('http://docs.limesurvey.org', $clang->gT('LimeSurvey online manual'));
    }
}
