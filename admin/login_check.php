<?php
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
 * $Id$
 */


if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}
if (!isset($action)) {$action=returnglobal('action');}




/*
 * New feature since version 1.81: One time passwords
 * The user can call the limesurvey login at /limesurvey/admin and pass username and
 * a one time password which was previously written into the users table (column one_time_pw) by
 * an external application.
 * Furthermore there is a setting in config-defaults which has to be turned on (default = off)
 * to enable the usage of one time passwords.
 */

//check if data was passed by URL
if(isset($_GET['user']) && isset($_GET['onepass']))
{
    //take care of passed data
    $user = sanitize_user($_GET['user']);
    $pw = sanitize_paranoid_string(md5($_GET['onepass']));

    //check if setting $use_one_time_passwords exists in config file
    if(isset($use_one_time_passwords))
    {
        //$use_one_time_passwords switched OFF but data was passed by URL: Show error message
        if($use_one_time_passwords === false)
        {
            //create an error message
            $loginsummary = "<br />".$clang->gT("Data for username and one time password was received but the usage of one time passwords is disabled at your configuration settings. Please add the following line to config.php to enable one time passwords: ")."<br />";
            $loginsummary .= '<br /><em>$use_one_time_passwords = true;</em><br />';
            $loginsummary .= "<br /><br /><a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        //Data was passed, using one time passwords is enabled
        else
        {
            //check if user exists in DB
            $query = "SELECT uid, users_name, password, one_time_pw, dateformat, full_name, htmleditormode FROM ".db_table_name('users')." WHERE users_name=".$connect->qstr($user);
            $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC; //Checked
            $result = $connect->SelectLimit($query, 1) or safe_die ($query."<br />".$connect->ErrorMsg());
            if(!$result)
            {
                echo "<br />".$connect->ErrorMsg();
            }
            if ($result->RecordCount() < 1)
            {
                // wrong or unknown username
                $loginsummary = sprintf($clang->gT("No one-time password found for user %s"),htmlspecialchars($user))."<br />";
                if ($sessionhandler=='db')
                {
                    adodb_session_regenerate_id();
                }
                else
                {
                    session_regenerate_id();
                }
            }
            else
            {
                //get one time pw from db
                $srow = $result->FetchRow();
                $otpw = $srow['one_time_pw'];

                //check if passed password and one time password from database DON'T match
                if($pw != $otpw)
                {
                    //no match -> warning
                    $loginsummary = "<br />".sprintf($clang->gT("Passed one time password doesn't match one time password for user %s")," <em>".htmlspecialchars($user)."</em>")."<br />";
                    $loginsummary .= "<br /><br /><a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
                }
                //both passwords match
                else
                {

                    //delete one time password in database
                    $uquery = "UPDATE ".db_table_name('users')."
					SET one_time_pw=''
					WHERE users_name='".db_quote($user)."'";	

                    $uresult = $connect->Execute($uquery);

                    //data necessary for following functions
                    $_SESSION['user'] = $srow['users_name'];
                    $_SESSION['checksessionpost'] = randomkey(10);
                    $_SESSION['loginID'] = $srow['uid'];
                    $_SESSION['dateformat'] = $srow['dateformat'];
                    $_SESSION['htmleditormode'] = $srow['htmleditormode'];
                    $_SESSION['full_name'] = $srow['full_name'];
                    GetSessionUserRights($_SESSION['loginID']);

                    // Check if the user has changed his default password
                    if (strtolower($srow['password'])=='password')
                    {
                        $_SESSION['pw_notify']=true;
                    }
                    else
                    {
                        $_SESSION['pw_notify']=false;
                    }

                    //delete passed information
                    unset($_GET['user']);
                    unset($_GET['onepass']);

                }	//else -> passwords match

            }	//else -> password found

        }	//else -> one time passwords enabled

    }	//else -> one time passwords set

}	//else -> data was passed by URL





// check data for login
if( isset($_POST['user']) && isset($_POST['password']) ||
($action == "forgotpass") || ($action == "login") ||
($action == "logout") ||
($useWebserverAuth === true && !isset($_SESSION['loginID'])) )
{
    include("usercontrol.php");
}




// login form
if(!isset($_SESSION['loginID']) && $action != "forgotpass" && ($action != "logout" || ($action == "logout" && !isset($_SESSION['loginID'])))) // && $action != "login")	// added by Dennis
{
    if($action == "forgotpassword")
    {
        $loginsummary = '
			<form name="forgot" id="forgot" method="post" action="'.$homeurl.'/admin.php" >
				<p><strong>'.$clang->gT('You have to enter user name and email.').'</strong></p>

				<table>
					<tbody>
					<tr>
						<td><label for="user">'.$clang->gT('Username').'</label></td>
						<td><input name="user" id="user" type="text" size="60" maxlength="60" value="" /></td>
					</tr>
					<tr>
						<td><label for="email">'.$clang->gT('Email').'</label></td>
						<td><input name="email" id="email" type="text" size="60" maxlength="60" value="" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><input type="hidden" name="action" value="forgotpass" />
						<input class="action" type="submit" value="'.$clang->gT('Check Data').'" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><a href="'.$scriptname.'">'.$clang->gT('Main Admin Screen').'</a></td>
					</tr>
				</table>
			</form>
';
    }
    elseif (!isset($loginsummary))
    { // could be at login or after logout
        $refererargs=''; // If this is a direct access to admin.php, no args are given
        // If we are called from a link with action and other args set, get them
        if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'])
        {
            $refererargs = html_escape($_SERVER['QUERY_STRING']);
        }



        



        //include("database.php");

        $sIp=   $_SERVER['REMOTE_ADDR'];
        $query = "SELECT * FROM ".db_table_name('failed_login_attempts'). " WHERE ip='$sIp';";
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $result = $connect->query($query) or safe_die ($query."<br />".$connect->ErrorMsg());
        $bCannotLogin = false;
        $intNthAttempt = 0;
        if ($result->RecordCount() >= 1)
        {
            $field = $result->FetchRow();
            $intNthAttempt = $field['number_attempts'];
            if ($intNthAttempt>=$maxLoginAttempt){
                $bCannotLogin = true;
            }

            $iLastAttempt = strtotime($field['last_attempt']);

            if (time() > $iLastAttempt + $timeOutTime){
                $bCannotLogin = false;
                $query = "DELETE FROM ".db_table_name('failed_login_attempts'). " WHERE ip='$sIp';";
                $result = $connect->query($query) or safe_die ($query."<br />".$connect->ErrorMsg());
            }

        }
        $loginsummary ="";
        if (!$bCannotLogin)
        {
            if (!isset($logoutsummary))
            {
                $loginsummary = "<form name='loginform' id='loginform' method='post' action='$homeurl/admin.php' ><p><strong>".$clang->gT("You have to login first.")."</strong><br />	<br />";
            }
            else
            {
                $loginsummary = "<form name='loginform' id='loginform' method='post' action='$homeurl/admin.php' ><br /><strong>".$logoutsummary."</strong><br />	<br />";
            }
            
            $loginsummary .= "
                                                            <ul>
                                                                            <li><label for='user'>".$clang->gT("Username")."</label>
                                                                            <input name='user' id='user' type='text' size='40' maxlength='40' value='' /></li>
                                                                            <li><label for='password'>".$clang->gT("Password")."</label>
                                                                            <input name='password' id='password' type='password' size='40' maxlength='40' /></li>
                                        <li><label for='loginlang'>".$clang->gT("Language")."</label>
                                        <select id='loginlang' name='loginlang' style='width:216px;'>\n";
            $loginsummary .='<option value="default">'.$clang->gT('Default').'</option>';
            foreach (getlanguagedata(true) as $langkey=>$languagekind)
            {
                $loginsummary .= "\t\t\t\t<option value='$langkey'>".$languagekind['nativedescription']." - ".$languagekind['description']."</option>\n";
            }
            $loginsummary .= "\t\t\t</select>\n"
            . "</li>
                                    </ul>
                                                                            <p><input type='hidden' name='action' value='login' />
                                                                            <input type='hidden' name='refererargs' value='".$refererargs."' />
                                                                            <input class='action' type='submit' value='".$clang->gT("Login")."' /><br />&nbsp;\n<br/>";
        }
        else{
            $loginsummary .= "<p>".sprintf($clang->gT("You have exceeded you maximum login attempts. Please wait %d minutes before trying again"),($timeOutTime/60))."<br /></p>";
        }

        $loginsummary .= "<p><a href='$scriptname?action=forgotpassword'>".$clang->gT("Forgot Your Password?")."</a><br />&nbsp;\n
						</form><br /></p>";
        $loginsummary .= "                                                <script type='text/javascript'>\n";
        $loginsummary .= "                                                  document.getElementById('user').focus();\n";
        $loginsummary .= "                                                </script>\n";
    }
}
