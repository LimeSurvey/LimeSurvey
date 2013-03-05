<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
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
*/

/**
* User Controller
*
* This controller performs user actions
*
* @package        LimeSurvey
* @subpackage    Backend
*/
class UserAction extends Survey_Common_Action
{

    function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        Yii::app()->loadHelper('database');
    }

    /**
    * Show users table
    */
    public function index()
    {
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.tablesorter.min.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts').'users.js');

        $userlist = getUserList();
        $usrhimself = $userlist[0];
        unset($userlist[0]);

        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) {
            $noofsurveys = Survey::model()->countByAttributes(array("owner_id" => $usrhimself['uid']));
            $aData['noofsurveys'] = $noofsurveys;
        }

        $aData['row'] = 0;
        if (isset($usrhimself['parent_id']) && $usrhimself['parent_id'] != 0)
        {
            $aData['row'] = User::model()->findByAttributes(array('uid' => $usrhimself['parent_id']))->users_name;
        }


        $aData['usrhimself'] = $usrhimself;
        // other users
        $aData['usr_arr'] = $userlist;
        $noofsurveyslist = array();

        //This loops through for each user and checks the amount of surveys against them.
        for ($i = 1; $i <= count($userlist); $i++)
            $noofsurveyslist[$i] = $this->_getSurveyCountForUser($userlist[$i]);

        $aData['imageurl'] = Yii::app()->getConfig("adminimageurl");
        $aData['noofsurveyslist'] = $noofsurveyslist;

        $this->_renderWrappedTemplate('user', 'editusers', $aData);
    }

    private function _getSurveyCountForUser(array $user)
    {
        return Survey::model()->countByAttributes(array('owner_id' => $user['uid']));
    }

    function adduser()
    {
        if (!Yii::app()->session['USER_RIGHT_CREATE_USER']) {
            die(accessDenied('adduser'));
        }

        $clang = Yii::app()->lang;
        $new_user = flattenText(Yii::app()->request->getPost('new_user'), false, true);
        $new_email = flattenText(Yii::app()->request->getPost('new_email'), false, true);
        $new_full_name = flattenText(Yii::app()->request->getPost('new_full_name'), false, true);
        $aViewUrls = array();
        $valid_email = true;
        if (!validateEmailAddress($new_email)) {
            $valid_email = false;
            $aViewUrls['message'] = array('title' => $clang->gT("Failed to add user"), 'message' => $clang->gT("The email address is not valid."), 'class'=> 'warningheader');
        }
        if (empty($new_user)) {
            $aViewUrls['message'] = array('title' => $clang->gT("Failed to add user"), 'message' => $clang->gT("A username was not supplied or the username is invalid."), 'class'=> 'warningheader');
        }
        elseif (User::model()->find("users_name='$new_user'")) {
            $aViewUrls['message'] = array('title' => $clang->gT("Failed to add user"), 'message' => $clang->gT("The username already exists."), 'class'=> 'warningheader');
        }
        elseif ($valid_email)
        {
            $new_pass = createPassword();
            $iNewUID = User::model()->insertUser($new_user, $new_pass, $new_full_name, Yii::app()->session['loginID'], $new_email);

            if ($iNewUID) {
                // add default template to template rights for user
                Templates_rights::model()->insertRecords(array('uid' => $iNewUID, 'folder' => Yii::app()->getConfig("defaulttemplate"), 'use' => '1'));

                // add new user to userlist
                $sresult = User::model()->getAllRecords(array('uid' => $iNewUID));
                $srow = count($sresult);

                $userlist = getUserList();
                array_push($userlist, array("user" => $srow['users_name'], "uid" => $srow['uid'], "email" => $srow['email'],
                "password" => $srow["password"], "parent_id" => $srow['parent_id'], // "level"=>$level,
                "create_survey" => $srow['create_survey'], "participant_panel" => $srow['participant_panel'], "configurator" => $srow['configurator'], "create_user" => $srow['create_user'],
                "delete_user" => $srow['delete_user'], "superadmin" => $srow['superadmin'], "manage_template" => $srow['manage_template'],
                "manage_label" => $srow['manage_label']));

                // send Mail
                $body = sprintf($clang->gT("Hello %s,"), $new_full_name) . "<br /><br />\n";
                $body .= sprintf($clang->gT("this is an automated email to notify that a user has been created for you on the site '%s'."), Yii::app()->getConfig("sitename")) . "<br /><br />\n";
                $body .= $clang->gT("You can use now the following credentials to log into the site:") . "<br />\n";
                $body .= $clang->gT("Username") . ": " . $new_user . "<br />\n";
                if (Yii::app()->getConfig("useWebserverAuth") === false) { // authent is not delegated to web server
                    // send password (if authorized by config)
                    if (Yii::app()->getConfig("display_user_password_in_email") === true) {
                        $body .= $clang->gT("Password") . ": " . $new_pass . "<br />\n";
                    }
                    else
                    {
                        $body .= $clang->gT("Password") . ": " . $clang->gT("Please contact your LimeSurvey administrator for your password.") . "<br />\n";
                    }
                }

                $body .= "<a href='" . $this->getController()->createAbsoluteUrl("/admin") . "'>" . $clang->gT("Click here to log in.") . "</a><br /><br />\n";
                $body .= sprintf($clang->gT('If you have any questions regarding this mail please do not hesitate to contact the site administrator at %s. Thank you!'), Yii::app()->getConfig("siteadminemail")) . "<br />\n";

                $subject = sprintf($clang->gT("User registration at '%s'", "unescaped"), Yii::app()->getConfig("sitename"));
                $to = $new_user . " <$new_email>";
                $from = Yii::app()->getConfig("siteadminname") . " <" . Yii::app()->getConfig("siteadminemail") . ">";
                $extra = '';
                $classMsg = '';
                if (SendEmailMessage($body, $subject, $to, $from, Yii::app()->getConfig("sitename"), true, Yii::app()->getConfig("siteadminbounce"))) {
                    $extra .= "<br />" . $clang->gT("Username") . ": $new_user<br />" . $clang->gT("Email") . ": $new_email<br />";
                    $extra .= "<br />" . $clang->gT("An email with a generated password was sent to the user.");
                    $classMsg = 'successheader';
                    $sHeader= $clang->gT("Success");
                }
                else
                {
                    // has to be sent again or no other way
                    $tmp = str_replace("{NAME}", "<strong>" . $new_user . "</strong>", $clang->gT("Email to {NAME} ({EMAIL}) failed."));
                    $extra .= "<br />" . str_replace("{EMAIL}", $new_email, $tmp) . "<br />";
                    $classMsg = 'warningheader';
                    $sHeader= $clang->gT("Warning");
                }

                $aViewUrls['mboxwithredirect'][] = $this->_messageBoxWithRedirect($clang->gT("Add user"), $sHeader, $classMsg, $extra,
                $this->getController()->createUrl("admin/user/sa/setUserRights"), $clang->gT("Set user permissions"),
                array('action' => 'setUserRights', 'user' => $new_user, 'uid' => $iNewUID));
            }
            else
            {
                $aViewUrls['mboxwithredirect'][] = $this->_messageBoxWithRedirect($clang->gT("Failed to add user"), $clang->gT("The user name already exists."), 'warningheader');
            }
        }

        $this->_renderWrappedTemplate('user', $aViewUrls);
    }

    /**
    * Delete user
    */
    function deluser()
    {
        if (!(Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || Yii::app()->session['USER_RIGHT_DELETE_USER'])) {
            die(accessDenied('deluser'));
        }
        $clang = Yii::app()->lang;
        $action = Yii::app()->request->getPost("action");
        $aViewUrls = array();

        // CAN'T DELETE ORIGINAL SUPERADMIN
        // Initial SuperAdmin has parent_id == 0
        $row = User::model()->findByAttributes(array('parent_id' => 0));

        $postuserid = Yii::app()->request->getPost("uid");
        $postuser = Yii::app()->request->getPost("user");
        if ($row['uid'] == $postuserid) // it's the original superadmin !!!
        {
            $aViewUrls['message'] = array('title' => $clang->gT('Initial Superadmin cannot be deleted!'), 'class' => 'warningheader');
        }
        else
        {
            if (isset($_POST['uid'])) {
                $sresultcount = 0; // 1 if I am parent of $postuserid
                if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1) {
                    $sresult = User::model()->findAllByAttributes(array('parent_id' => $postuserid, 'parent_id' => Yii::app()->session['loginID']));
                    $sresultcount = count($sresult);
                }

                if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || $sresultcount > 0 || $postuserid == Yii::app()->session['loginID']) {
                    $transfer_surveys_to = 0;
                    $ownerUser = User::model()->findAll();
                    $aData['users'] = $ownerUser;

                    $current_user = Yii::app()->session['loginID'];
                    if (count($ownerUser) == 2) {

                        $action = "finaldeluser";
                        foreach ($ownerUser as &$user)
                        {
                            if ($postuserid != $user['uid'])
                                $transfer_surveys_to = $user['uid'];
                        }
                    }

                    $ownerUser = Survey::model()->findAllByAttributes(array('owner_id' => $postuserid));
                    if (count($ownerUser) == 0) {
                        $action = "finaldeluser";
                    }

                    if ($action == "finaldeluser") {
                        $aViewUrls=$this->deleteFinalUser($ownerUser, $transfer_surveys_to);
                    }
                    else
                    {
                        $aData['postuserid'] = $postuserid;
                        $aData['postuser'] = $postuser;
                        $aData['current_user'] = $current_user;

                        $aViewUrls['deluser'][] = $aData;
                        $this->_renderWrappedTemplate('user', $aViewUrls);
                        
                    }
                }
                else
                {
                    echo accessDenied('deluser');
                    die();
                }
            }
            else
            {
                $aViewUrls['mboxwithredirect'][] = $this->_messageBoxWithRedirect("", $clang->gT("Could not delete user. User was not supplied."), "warningheader");
            }
        }

        return $aViewUrls;
    }

    function deleteFinalUser($result, $transfer_surveys_to)
    {
        $clang = Yii::app()->lang;
        $postuserid = Yii::app()->request->getPost("uid");
        $postuser = Yii::app()->request->getPost("user");

        if (isset($_POST['transfer_surveys_to'])) {
            $transfer_surveys_to = sanitize_int($_POST['transfer_surveys_to']);
        }
        if ($transfer_surveys_to > 0) {
            $iSurveysTransferred = Survey::model()->updateAll(array('owner_id' => $transfer_surveys_to), 'owner_id='.$postuserid);
        }
        $sresult = User::model()->findByAttributes(array('uid' => $postuserid));
        $fields = $sresult;
        if (isset($fields['parent_id'])) {
            $uresult = User::model()->updateAll(array('parent_id' => $fields['parent_id']), 'parent_id='.$postuserid);
        }

        //DELETE USER FROM TABLE
        $dresult = User::model()->deleteUser($postuserid);

        // Delete user rights
        $dresult = Survey_permissions::model()->deleteAllByAttributes(array('uid' => $postuserid));

        if ($postuserid == Yii::app()->session['loginID'])
        {
            session_destroy();    // user deleted himself
            $this->getController()->redirect($this->getController()->createUrl("admin/authentication/sa/logout"));
            die();
        }

        $extra = "<br />" . sprintf($clang->gT("User '%s' was successfully deleted."),$postuser)."<br /><br />\n";
        if ($transfer_surveys_to > 0 && $iSurveysTransferred>0) {
            $user = User::model()->findByPk($transfer_surveys_to);
            $sTransferred_to = $user->users_name;
            //$sTransferred_to = $this->getController()->_getUserNameFromUid($transfer_surveys_to);
            $extra = sprintf($clang->gT("All of the user's surveys were transferred to %s."), $sTransferred_to);
        }

        $aViewUrls['mboxwithredirect'][] = $this->_messageBoxWithRedirect("", $clang->gT("Success!"), "successheader", $extra);
        $this->_renderWrappedTemplate('user', $aViewUrls);
    }

    /**
    * Modify User
    */
    function modifyuser()
    {
        if (isset($_POST['uid'])) {
            $postuserid = sanitize_int($_POST['uid']);
            $sresult = User::model()->findAllByAttributes(array('uid' => $postuserid, 'parent_id' => Yii::app()->session['loginID']));
            $sresultcount = count($sresult);

            if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || Yii::app()->session['loginID'] == $postuserid ||
            (Yii::app()->session['USER_RIGHT_CREATE_USER'] && $sresultcount > 0) )
            {
                $sresult = User::model()->parentAndUser($postuserid);
                $aData['mur'] = $sresult;

                $this->_renderWrappedTemplate('user', 'modifyuser', $aData);
            }
            return;
        }
        echo accessDenied('modifyuser');
        die();
    }

    /**
    * Modify User POST
    */
    function moduser()
    {
        $clang = Yii::app()->lang;
        $postuser = Yii::app()->request->getPost("user");
        $postemail = Yii::app()->request->getPost("email");
        $postuserid = Yii::app()->request->getPost("uid");
        $postfull_name = Yii::app()->request->getPost("full_name");
        $display_user_password_in_html = Yii::app()->getConfig("display_user_password_in_html");
        $addsummary = '';
        $aViewUrls = array();

        $sresult = User::model()->findAllByAttributes(array('uid' => $postuserid, 'parent_id' => Yii::app()->session['loginID']));
        $sresultcount = count($sresult);

        if ((Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || $postuserid == Yii::app()->session['loginID'] ||
        ($sresultcount > 0 && Yii::app()->session['USER_RIGHT_CREATE_USER'])) && !(Yii::app()->getConfig("demoMode") == true && $postuserid == 1)
        ) {
            $users_name = html_entity_decode($postuser, ENT_QUOTES, 'UTF-8');
            $email = html_entity_decode($postemail, ENT_QUOTES, 'UTF-8');
            $sPassword = html_entity_decode(Yii::app()->request->getPost('pass'), ENT_QUOTES, 'UTF-8');
            if ($sPassword == '%%unchanged%%')
                $sPassword = '';
            $full_name = html_entity_decode($postfull_name, ENT_QUOTES, 'UTF-8');

            if (!validateEmailAddress($email)) {
                $aViewUrls['mboxwithredirect'][] = $this->_messageBoxWithRedirect($clang->gT("Editing user"), $clang->gT("Could not modify user data."), "warningheader", $clang->gT("Email address is not valid."),
                $this->getController()->createUrl('admin/user/modifyuser'), $clang->gT("Back"), array('uid' => $postuserid));
            }
            else
            {
                if (empty($sPassword))
                {
                    $uresult = User::model()->updateByPk($postuserid, array('email' => $this->escape($email), 'full_name' => $this->escape($full_name)));
                }
                else
                {
                    $uresult = User::model()->updateByPk($postuserid, array('email' => $this->escape($email), 'full_name' => $this->escape($full_name), 'password' => hash('sha256', $sPassword)));
                }

                if (empty($sPassword)) {
                    $extra = $clang->gT("Username") . ": $users_name<br />" . $clang->gT("Password") . ": (" . $clang->gT("Unchanged") . ")<br />\n";
                    $aViewUrls['mboxwithredirect'][] = $this->_messageBoxWithRedirect($clang->gT("Editing user"), $clang->gT("Success!"), "successheader", $extra);
                }
                elseif ($uresult && !empty($sPassword))
                {
                    if ($sPassword != 'password')
                        Yii::app()->session['pw_notify'] = FALSE;
                    if ($sPassword == 'password')
                        Yii::app()->session['pw_notify'] = TRUE;

                    if ($display_user_password_in_html === true) {
                        $displayedPwd = $sPassword;
                    }
                    else
                    {
                        $displayedPwd = preg_replace('/./', '*', $sPassword);
                    }

                    $extra = $clang->gT("Username") . ": {$users_name}<br />" . $clang->gT("Password") . ": {$displayedPwd}<br />\n";
                    $aViewUrls['mboxwithredirect'][] = $this->_messageBoxWithRedirect($clang->gT("Editing user"), $clang->gT("Success!"), "successheader", $extra);
                }
                else
                {
                    // Username and/or email adress already exists.
                    $aViewUrls['mboxwithredirect'][] = $this->_messageBoxWithRedirect($clang->gT("Editing user"), $clang->gT("Could not modify user data. Email address already exists."), 'warningheader');
                }
            }
        }

        $this->_renderWrappedTemplate('user', $aViewUrls);
    }

    function setUserRights()
    {
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.tablesorter.min.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . 'users.js');
        $postuser = Yii::app()->request->getPost('user');
        $postemail = Yii::app()->request->getPost('email');
        $postuserid = Yii::app()->request->getPost('uid');
        $postfull_name = Yii::app()->request->getPost('full_name');
        if (isset($_POST['uid'])) {
            $sresult = User::model()->findAllByAttributes(array('uid' => $postuserid, 'parent_id' => Yii::app()->session['loginID']));
            $sresultcount = count($sresult);
        }
        else
        {
            echo accessDenied('setUserRights');
            die();
        }
        // RELIABLY CHECK MY RIGHTS
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || (Yii::app()->session['USER_RIGHT_CREATE_USER'] && $sresultcount > 0 && Yii::app()->session['loginID'] != $postuserid)
        ) //	if(Yii::app()->session['loginID'] != $postuserid)
        {
            $aData['postuserid'] = $postuserid;
            $this->_renderWrappedTemplate('user', 'setuserrights', $aData);
        } // if
        else
        {
            echo accessDenied('setUserRights');
            die();
        }
    }

    /**
    * User Rights POST
    */
    function userrights()
    {
        $clang = Yii::app()->lang;
        $postuserid = Yii::app()->request->getPost("uid");
        $aViewUrls = array();

        // A user can't modify his own rights
        if ($postuserid != Yii::app()->session['loginID']) {
            $sresult = User::model()->findAllByAttributes(array('uid' => $postuserid, 'parent_id' => Yii::app()->session['loginID']));
            $sresultcount = count($sresult);

            if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1 && $sresultcount > 0) { // Not Admin, just a user with childs
                $rights = array();

                $rights['create_survey'] = (isset($_POST['create_survey']) && Yii::app()->session['USER_RIGHT_CREATE_SURVEY'])
                ? 1 : 0;
                $rights['configurator'] = (isset($_POST['configurator']) && Yii::app()->session['USER_RIGHT_CONFIGURATOR'])
                ? 1 : 0;
                $rights['create_user'] = (isset($_POST['create_user']) && Yii::app()->session['USER_RIGHT_CREATE_USER'])
                ? 1 : 0;
                $rights['participant_panel'] = (isset($_POST['participant_panel']) && Yii::app()->session['USER_RIGHT_PARTICIPANT_PANEL'])
                ? 1 : 0;
                $rights['delete_user'] = (isset($_POST['delete_user']) && Yii::app()->session['USER_RIGHT_DELETE_USER'])
                ? 1 : 0;
                $rights['manage_template'] = (isset($_POST['manage_template']) && Yii::app()->session['USER_RIGHT_MANAGE_TEMPLATE'])
                ? 1 : 0;
                $rights['manage_label'] = (isset($_POST['manage_label']) && Yii::app()->session['USER_RIGHT_MANAGE_LABEL'])
                ? 1 : 0;

                $rights['superadmin'] = 0; // ONLY Initial Superadmin can give this right

                if ($postuserid != 1)
                    setUserRights($postuserid, $rights);
            }
            elseif (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
            {
                $rights = array();

                // Only Initial Superadmin can give this right
                if (isset($_POST['superadmin'])) {
                    // Am I original Superadmin ?
                    // Initial SuperAdmin has parent_id == 0
                    $adminresult = User::model()->getuidfromparentid('0');
                    $row = $adminresult;
                    if ($row['uid'] == Yii::app()->session['loginID']) // it's the original superadmin !!!
                    {
                        $rights['superadmin'] = 1;
                    }
                    else
                    {
                        $rights['superadmin'] = 0;
                    }
                }
                else
                {
                    $rights['superadmin'] = 0;
                }

                $rights['create_survey'] = (isset($_POST['create_survey']) || $rights['superadmin']) ? 1 : 0;
                $rights['configurator'] = (isset($_POST['configurator']) || $rights['superadmin']) ? 1 : 0;
                $rights['create_user'] = (isset($_POST['create_user']) || $rights['superadmin']) ? 1 : 0;
                $rights['participant_panel'] = (isset($_POST['participant_panel']) || $rights['superadmin']) ? 1 : 0;
                $rights['delete_user'] = (isset($_POST['delete_user']) || $rights['superadmin']) ? 1 : 0;
                $rights['manage_template'] = (isset($_POST['manage_template']) || $rights['superadmin']) ? 1 : 0;
                $rights['manage_label'] = (isset($_POST['manage_label']) || $rights['superadmin']) ? 1 : 0;

                setUserRights($postuserid, $rights);
            }
            else
            {
                echo accessDenied('userrights');
                die();
            }
            $aViewUrls['mboxwithredirect'][] = $this->_messageBoxWithRedirect($clang->gT("Set user permissions"), $clang->gT("User permissions were updated successfully."), 'successheader');
        }
        else
        {
            $aViewUrls['mboxwithredirect'][] = $this->_messageBoxWithRedirect($clang->gT("Set user permissions"), $clang->gT("You are not allowed to change your own permissions!"), 'warningheader');
        }

        $this->_renderWrappedTemplate('user', $aViewUrls);
    }

    function setusertemplates()
    {
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.tablesorter.min.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . 'users.js');
        $aData['postuser']  = Yii::app()->request->getPost("user");
        $aData['postemail'] = Yii::app()->request->getPost("email");
        $postuserid = Yii::app()->request->getPost("uid");
        $aData['postuserid'] = $postuserid;
        $aData['postfull_name'] = Yii::app()->request->getPost("full_name");
        $this->_refreshtemplates();
        foreach (getUserList() as $usr)
        {
            if ($usr['uid'] == $postuserid)
            {
                $trights = Templates_rights::model()->findAllByAttributes(array('uid' => $usr['uid']));
                foreach ($trights as $srow)
                {
                    $templaterights[$srow["folder"]] = array("use"=>$srow["use"]);
                }
                $templates = Template::model()->findAll();
                $aData['list'][] = array('templaterights'=>$templaterights,'templates'=>$templates);
            }
        }
        $this->_renderWrappedTemplate('user', 'setusertemplates', $aData);
    }

    function usertemplates()
    {
        $clang = Yii::app()->lang;
        $postuserid = Yii::app()->request->getPost('uid');

        // SUPERADMINS AND MANAGE_TEMPLATE USERS CAN SET THESE RIGHTS
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || Yii::app()->session['USER_RIGHT_MANAGE_TEMPLATE'] == 1) {
            $templaterights = array();
            $tresult = Template::model()->findAll();
            $postvalue= array_flip($_POST);
            foreach ($tresult as $trow)
            {
                if (isset($postvalue[$trow["folder"] . "_use"]))
                    $templaterights[$trow["folder"]] = 1;
                else
                    $templaterights[$trow["folder"]] = 0;
            }
            foreach ($templaterights as $key => $value)
            {
                $rights = Templates_rights::model()->findByPk(array('folder' => $key, 'uid' => $postuserid));
                if (empty($rights))
                {
                    $rights = new Templates_rights;
                    $rights->uid = $postuserid;
                    $rights->folder = $key;
                }
                $rights->use = $value;
                $uresult = $rights->save();
            }
            if ($uresult !== false) {
                $aViewUrls['mboxwithredirect'][] = $this->_messageBoxWithRedirect($clang->gT("Set template permissions"), $clang->gT("Template permissions were updated successfully."), "successheader");
            }
            else
            {
                $aViewUrls['mboxwithredirect'][] = $this->_messageBoxWithRedirect($clang->gT("Set template permissions"), $clang->gT("Error while updating usertemplates."), "warningheader");
            }
        }
        else
        {
            die('access denied');
        }

        $this->_renderWrappedTemplate('user', $aViewUrls);
    }

    /**
    * Manage user personal settings
    */
    function personalsettings()
    {

        // Save Data
        if (Yii::app()->request->getPost("action")) {
            $aData = array(
            'lang' => Yii::app()->request->getPost('lang'),
            'dateformat' => Yii::app()->request->getPost('dateformat'),
            'htmleditormode' => Yii::app()->request->getPost('htmleditormode'),
            'questionselectormode' => Yii::app()->request->getPost('questionselectormode'),
            'templateeditormode' => Yii::app()->request->getPost('templateeditormode')
            );

            $uresult = User::model()->updateByPk(Yii::app()->session['loginID'], $aData);

            if (Yii::app()->request->getPost('lang')=='auto')
            {
                $sLanguage= getBrowserLanguage();
            }
            else
            {
                $sLanguage=Yii::app()->request->getPost('lang');
            }

            Yii::app()->session['adminlang'] = $sLanguage;
            Yii::app()->lang=new limesurvey_lang($sLanguage);
            $clang = Yii::app()->lang;

            Yii::app()->session['htmleditormode'] = Yii::app()->request->getPost('htmleditormode');
            Yii::app()->session['questionselectormode'] = Yii::app()->request->getPost('questionselectormode');
            Yii::app()->session['templateeditormode'] = Yii::app()->request->getPost('templateeditormode');
            Yii::app()->session['dateformat'] = Yii::app()->request->getPost('dateformat');
            Yii::app()->session['flashmessage'] = $clang->gT("Your personal settings were successfully saved.");
        }

        // Get user lang
        $user = User::model()->findByPk(Yii::app()->session['loginID']);
        $aData['sSavedLanguage'] = $user->lang;

        // Render personal settings view
        $this->_renderWrappedTemplate('user', 'personalsettings', $aData);
    }

    private function _getUserNameFromUid($uid)
    {
        $uid = sanitize_int($uid);
        $result = User::model()->findByPk($uid);

        if (!empty($result)) {
            return $result->users_name;
        }
        else
        {
            return false;
        }
    }

    private function _refreshtemplates()
    {
        $template_a = getTemplateList();
        foreach ($template_a as $tp => $fullpath)
        {
            // check for each folder if there is already an entry in the database
            // if not create it with current user as creator (user with rights "create user" can assign template rights)
            $result = Template::model()->findByPk($tp);

            if (count($result) == 0) {
                $post = new Template;
                $post->folder = $tp;
                $post->creator = Yii::app()->session['loginID'];
                $post->save();
            }
        }
        return true;
    }

    private function escape($str)
    {
        if (is_string($str)) {
            $str = $this->escape_str($str);
        }
        elseif (is_bool($str))
        {
            $str = ($str === true) ? 1 : 0;
        }
        elseif (is_null($str))
        {
            $str = 'NULL';
        }

        return $str;
    }

    private function escape_str($str, $like = FALSE)
    {
        if (is_array($str)) {
            foreach ($str as $key => $val)
            {
                $str[$key] = $this->escape_str($val, $like);
            }

            return $str;
        }

        // Escape single quotes
        $str = str_replace("'", "''", $this->remove_invisible_characters($str));

        return $str;
    }

    private function remove_invisible_characters($str, $url_encoded = TRUE)
    {
        $non_displayables = array();

        // every control character except newline (dec 10)
        // carriage return (dec 13), and horizontal tab (dec 09)

        if ($url_encoded) {
            $non_displayables[] = '/%0[0-8bcef]/'; // url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/'; // url encoded 16-31
        }

        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'; // 00-08, 11, 12, 14-31, 127

        do
        {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        } while ($count);

        return $str;
    }

    private function _messageBoxWithRedirect($title, $message, $classMsg, $extra = "", $url = "", $urlText = "", $hiddenVars = array(), $classMbTitle = "header ui-widget-header")
    {
        $clang = Yii::app()->lang;
        $url = (!empty($url)) ? $url : $this->getController()->createUrl('admin/user/index');
        $urlText = (!empty($urlText)) ? $urlText : $clang->gT("Continue");

        $aData['title'] = $title;
        $aData['message'] = $message;
        $aData['url'] = $url;
        $aData['urlText'] = $urlText;
        $aData['classMsg'] = $classMsg;
        $aData['classMbTitle'] = $classMbTitle;
        $aData['extra'] = $extra;
        $aData['hiddenVars'] = $hiddenVars;

        return $aData;
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'user', $aViewUrls = array(), $aData = array())
    {
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

}
