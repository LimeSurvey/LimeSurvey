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
* @package		LimeSurvey
* @subpackage	Backend
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
                        $loginsummary = "<br />".sprintf($clang->gT("Welcome %s!"), Yii::app()->session['full_name'])."<br />&nbsp;";
                        if (!empty(Yii::app()->session['redirect_after_login']) && strpos(Yii::app()->session['redirect_after_login'], "logout") === FALSE)
                        {
                            Yii::app()->session['metaHeader']  = "<meta http-equiv=\"refresh\""
                            . " content=\"1;URL=".Yii::app()->session['redirect_after_login']."\" />";
                            $loginsummary = "<p><font size='1'><i>".$clang->gT("Reloading screen. Please wait.")."</i></font>\n";
                            unset(Yii::app()->session['redirect_after_login']);
                        }
                        $this->getController()->_GetSessionUserRights(Yii::app()->session['loginID']);
                    	Yii::app()->session['just_logged_in'] = true;
                        Yii::app()->session['loginsummary'] = $loginsummary;
                        $this->getController()->redirect($this->getController()->createUrl('/admin'));
                    }
                }
                else
                    $this->_showLoginForm();
            }
            else
            {
                // wrong or unknown username
                $data['errormsg']="";
                $data['maxattempts'] = sprintf($this->getController()->lang->gT("You have exceeded you maximum login attempts. Please wait %d minutes before trying again"),(Yii::app()->getConfig("timeOutTime")/60))."<br />";
                $data['clang'] = $this->getController()->lang;

            	$this->getController()->_getAdminHeader();
            	$this->getController()->render('/admin/authentication/error', $data);
                $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $data['clang']->gT("LimeSurvey online manual"));
            }
        }
        else
        {
            $this->getController()->redirect($this->getController()->createUrl('/admin'));
        }
    }

    /**
    * Logout user
    */
    public function logout()
    {
        Yii::app()->user->logout();
        $this->_showLoginForm('<p>'.$this->getController()->lang->gT("Logout successful."));
    }

    /**
    * Forgot Password screen
    */
    public function forgotpassword()
    {
        $clang = $this->getController()->lang;
        if(!$this->input->post("action"))
        {
            $data['clang'] = $this->limesurvey_lang;
            parent::_getAdminHeader();
            $this->load->view('admin/authentication/forgotpassword', $data);
            parent::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
        }
        else
        {
            $postuser = $this->input->post("user");
            $emailaddr = $this->input->post("email");
            //$query = "SELECT users_name, password, uid FROM ".db_table_name('users')." WHERE users_name=".$connect->qstr($postuser)." AND email=".$connect->qstr($emailaddr);
            //$result = db_select_limit_assoc($query, 1) or safe_die ($query."<br />".$connect->ErrorMsg());  // Checked
            $this->load->model("Users_model");
            $query = $this->Users_model->getSomeRecords(array("users_name, password, uid"),array("users_name"=>$postuser,"email"=>$emailaddr));

            if ($query->num_rows()  < 1)
            {
                // wrong or unknown username and/or email
                $data['errormsg']=$this->limesurvey_lang->gT("User name and/or email not found!");
                $data['maxattempts']="";
                $data['clang']=$this->limesurvey_lang;

                parent::_getAdminHeader();
                $this->load->view('admin/authentication/error', $data);
                parent::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

            }
            else
            {
                //$fields = $result->FetchRow();
                $fields = $query->row_array();

                // send Mail
                $new_pass = createPassword();
                $body = sprintf($clang->gT("Your user data for accessing %s"),$this->config->item("sitename")). "<br />\n";;
                $body .= $clang->gT("Username") . ": " . $fields['users_name'] . "<br />\n";
                $body .= $clang->gT("New password") . ": " . $new_pass . "<br />\n";

                $this->load->config("email");
                $subject = $clang->gT("User data","unescaped");
                $to = $emailaddr;
                $from = $this->config->item("siteadminemail");
                $sitename = $this->config->item("siteadminname");

                if(SendEmailMessage($body, $subject, $to, $from, $this->config->item("sitename"), false,$this->config->item("siteadminbounce")))
                {
                    //$query = "UPDATE ".db_table_name('users')." SET password='".SHA256::hashing($new_pass)."' WHERE uid={$fields['uid']}";
                    //$connect->Execute($query); //Checked
                    $this->Users_model->updatePassword($fields['uid'], $this->sha256->hashing($new_pass));

                    $data['clang'] = $clang;
                    $data['message'] = "<br />".$clang->gT("Username").": {$fields['users_name']}<br />".$clang->gT("Email").": {$emailaddr}<br />
                    <br />".$clang->gT("An email with your login data was sent to you.");
                    parent::_getAdminHeader();
                    $this->load->view('admin/authentication/message', $data);
                    parent::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
                }
                else
                {
                    $tmp = str_replace("{NAME}", "<strong>".$fields['users_name']."</strong>", $clang->gT("Email to {NAME} ({EMAIL}) failed."));
                    $data['clang'] = $clang;
                    $data['message'] = "<br />".str_replace("{EMAIL}", $emailaddr, $tmp) . "<br />";

                    $this->getController()->_getAdminHeader();
                    $this->load->view('admin/authentication/message', $data);
                    parent::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
                }
            }
        }

    }

    /**
    * Show login screen
    * @param optional message
    */
    protected function _showLoginForm($logoutsummary="")
    {
        $data['clang'] = $this->getController()->lang;

        if ($logoutsummary=="")
        {
            $data['summary'] = $this->getController()->lang->gT("You have to login first.");
        }
        else
        {
            $data['summary'] = $logoutsummary;
        }

    	$this->getController()->_getAdminHeader();
    	$this->getController()->render('/admin/authentication/login', $data);
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));

    }

    /**
    * Do the actual login work
    * Note: This function is replicated in parts in remotecontrol.php controller - if you change this don't forget to make according changes there, too
    * @param string $sUsername The username to login with
    * @param string $sPassword The password to login with
    */
    protected function _doLogin($sUsername, $sPassword)
    {
        $clang = $this->getController()->lang;
        $sUsername = sanitize_user($sUsername);

        $identity = new UserIdentity($sUsername, $sPassword);

        if (!$identity->authenticate())
        {
            $query = Failed_login_attempts::model()->addAttempt(Yii::app()->request->getUserHostAddress());

            if ($query)
            {
                // wrong or unknown username
                $data['errormsg']=$clang->gT("Incorrect username and/or password!");
                $data['maxattempts']="";
                if (Failed_login_attempts::model()->isLockedOut(Yii::app()->request->getUserHostAddress()))
                    $data['maxattempts']=sprintf($clang->gT("You have exceeded you maximum login attempts. Please wait %d minutes before trying again"),(Yii::app()->getConfig("timeOutTime")/60))."<br />";

                $data['clang']=$clang;
                return $data;
            }
        }
    	// Log the user in
        else
        {
        	$user = $identity->getUser();

        	Yii::app()->user->login($identity);

            // Check if the user has changed his default password
            if (strtolower($_POST['password']) == 'password')
            {
                Yii::app()->session['pw_notify'] = true;
                Yii::app()->session['flashmessage'] = $clang->gT("Warning: You are still using the default password ('password'). Please change your password and re-login again.");
            }
            else
                Yii::app()->session['pw_notify'] = false;

            $session_data = array(
            	'loginID' => intval($user->uid),
            	'user' => $user->users_name,
           		'full_name' => $user->full_name,
            	'htmleditormode' => $user->htmleditormode,
            	'templateeditormode' => $user->templateeditormode,
            	'questionselectormode' => $user->questionselectormode,
            	'dateformat' => $user->dateformat,
            	// Compute a checksession random number to test POSTs
            	'checksessionpost' => sRandomChars(10)
            );

            foreach ($session_data as $k => $v)
            	Yii::app()->session[$k] = $v;

            $postloginlang = sanitize_languagecode($_POST['loginlang']);
            if (isset($postloginlang) && $postloginlang != 'default')
            {
                Yii::app()->session['adminlang'] = $postloginlang;
                $this->getController()->lang->limesurvey_lang(array("langcode"=>$postloginlang));
                $clang = $this->getController()->lang;

            	$user->lang = $postloginlang;
            	$user->save();
            }
            else
            {
                Yii::app()->session['adminlang'] = $user->lang;

            	$this->getController()->lang->limesurvey_lang(array("langcode"=>$user->lang));
                $clang = $this->getController()->lang;
            }
            return true;
        }
    }
}
