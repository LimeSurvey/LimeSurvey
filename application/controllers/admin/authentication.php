<?php
// see: https://scrutinizer-ci.com/g/LimeSurvey/LimeSurvey/issues/master/files/application/controllers/admin/authentication.php?selectedSeverities[0]=10&orderField=path&order=asc&honorSelectedPaths=0
// use LimeSurvey\PluginManager\PluginEvent;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
* 
* @method void redirect(string|array $url, boolean $terminate, integer $statusCode)
 */
class Authentication extends Survey_Common_Action
{

    /**
     * Show login screen and parse login data
     * Will redirect or echo json depending on ajax call
     * This function is called while accessing the login page: index.php/admin/authentication/sa/login
     */
    public function index()
    {
        /* Set adminlang to the one set in dropdown */
        if (Yii::app()->request->getPost('loginlang', 'default') != 'default') {
            Yii::app()->session['adminlang'] = Yii::app()->request->getPost('loginlang', 'default');
            Yii::app()->setLanguage(Yii::app()->session["adminlang"]);
        }
        // The page should be shown only for non logged in users
        $this->_redirectIfLoggedIn();

        // Result can be success, fail or data for template
        $result = self::prepareLogin();

        $isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;
        $succeeded = isset($result[0]) && $result[0] == 'success';
        $failed = isset($result[0]) && $result[0] == 'failed';

        // If Ajax, echo success or failure json
        if ($isAjax) {
            Yii::import('application.helpers.admin.ajax_helper', true);
            if ($succeeded) {
                ls\ajax\AjaxHelper::outputSuccess(gT('Successful login'));
                return;
            } else if ($failed) {
                ls\ajax\AjaxHelper::outputError(gT('Incorrect username and/or password!'));
                return;
            }
        }
        // If not ajax, redirect to admin startpage or again to login form
        else {
            if ($succeeded) {
                self::doRedirect();
            } else if ($failed) {
                $message = $result[1];
                App()->user->setFlash('error', $message);
                App()->getController()->redirect(array('/admin/authentication/sa/login'));
            }
        }

        // Neither success nor failure, meaning no form submission - result = template data from plugin
        $aData = $result;

        // If for any reason, the plugin bugs, we can't let the user with a blank screen.
        $this->_renderWrappedTemplate('authentication', 'login', $aData);
    }

    /**
     * Prepare login and return result
     * It checks if the authdb plugin is registered and active
     * @return array Either success, failure or plugin data (used in login form)
     */
    public static function prepareLogin()
    {
        $aData = array();

        // Plugins, include core plugins, can't be activated by default.
        // So after a fresh installation, core plugins are not activated
        // They need to be manually loaded.
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

        // In Authdb, the plugin event "beforeLogin" checks if the url param "onepass" is set
        // if yes, it will call  AuthPluginBase::setAuthPlugin to set to true the plugin private parameter "_stop", so the form will not be displayed
        // @see: application/core/plugins/Authdb/Authdb.php: function beforeLogin()
        $beforeLogin = new PluginEvent('beforeLogin');
        $beforeLogin->set('identity', new LSUserIdentity('', ''));
        App()->getPluginManager()->dispatchEvent($beforeLogin);

        /* @var $identity LSUserIdentity */
        $identity = $beforeLogin->get('identity'); // Why here?

        // If the plugin private parameter "_stop" is false and the login form has not been submitted: render the login form
        if (!$beforeLogin->isStopped() && is_null(App()->getRequest()->getPost('login_submit'))) {
            // First step: set the value of $aData['defaultAuth']
            // This variable will be used to select the default value of the Authentication method selector
            // which is shown only if there is more than one plugin auth on...
            // @see application/views/admin/authentication/login.php

            // First it checks if the current plugin force the authentication default value...
            // NB: A plugin SHOULD NOT be able to over pass the configuration file
            // @see: http://img.memecdn.com/knees-weak-arms-are-heavy_c_3011277.jpg
            if (!is_null($beforeLogin->get('default'))) {
                $aData['defaultAuth'] = $beforeLogin->get('default');
            } else {
                // THen, it checks if the the user set a different default plugin auth in application/config/config.php
                // eg: 'config'=>array()'debug'=>2,'debugsql'=>0, 'default_displayed_auth_method'=>'muh_auth_method')
                if (App()->getPluginManager()->isPluginActive(Yii::app()->getConfig('default_displayed_auth_method'))) {
                        $aData['defaultAuth'] = Yii::app()->getConfig('default_displayed_auth_method');
                    } else {
                        $aData['defaultAuth'] = 'Authdb';
                    }
            }

            // Call the plugin method newLoginForm
            // For Authdb:  @see: application/core/plugins/Authdb/Authdb.php: function newLoginForm()
            $newLoginForm = new PluginEvent('newLoginForm');
            App()->getPluginManager()->dispatchEvent($newLoginForm); // inject the HTML of the form inside the private varibale "_content" of the plugin
            $aData['summary'] = self::getSummary('logout');
            $aData['pluginContent'] = $newLoginForm->getAllContent(); // Retreives the private varibale "_content" , and parse it to $aData['pluginContent'], which will be  rendered in application/views/admin/authentication/login.php
        } else {
            // The form has been submited, or the plugin has been stoped (so normally, the value of login/password are available)

                // Handle getting the post and populating the identity there
            $authMethod = App()->getRequest()->getPost('authMethod', $identity->plugin); // If form has been submitted, $_POST['authMethod'] is set, else  $identity->plugin should be set, ELSE: TODO error
            $identity->plugin = $authMethod;

            // Call the function afterLoginFormSubmit of the plugin.
            // For Authdb, it calls AuthPluginBase::afterLoginFormSubmit()
            // which set the plugin's private variables _username and _password with the POST informations if it's a POST request else it does nothing
            $event = new PluginEvent('afterLoginFormSubmit');
            $event->set('identity', $identity);
            App()->getPluginManager()->dispatchEvent($event, array($authMethod));
            $identity = $event->get('identity');

            // Now authenticate
            // This call LSUserIdentity::authenticate() (application/core/LSUserIdentity.php))
            // which will call the plugin function newUserSession() (eg: Authdb::newUserSession() )
            // TODO: for sake of clarity, the plugin function should be renamed to authenticate().
            if ($identity->authenticate()) {
                FailedLoginAttempt::model()->deleteAttempts();
                App()->user->setState('plugin', $authMethod);

                Yii::app()->session['just_logged_in'] = true;
                Yii::app()->session['loginsummary'] = self::getSummary();

                $event = new PluginEvent('afterSuccessfulLogin');
                App()->getPluginManager()->dispatchEvent($event);

                return array('success');
            } else {
                // Failed
                $event = new PluginEvent('afterFailedLoginAttempt');
                $event->set('identity', $identity);
                App()->getPluginManager()->dispatchEvent($event);

                $message = $identity->errorMessage;
                if (empty($message)) {
                    // If no message, return a default message
                    $message = gT('Incorrect username and/or password!');
                }
                return array('failed', $message);
            }
        }

        return $aData;
    }

    /**
     * Logout user
     * @return void
     */
    public function logout()
    {
        /* Adding beforeLogout event */
        $beforeLogout = new PluginEvent('beforeLogout');
        App()->getPluginManager()->dispatchEvent($beforeLogout);
        regenerateCSRFToken();
        App()->user->logout();
        App()->user->setFlash('loginmessage', gT('Logout successful.'));

        /* Adding afterLogout event */
        $event = new PluginEvent('afterLogout');
        App()->getPluginManager()->dispatchEvent($event);

        $this->getController()->redirect(array('/admin/authentication/sa/login'));
    }

    /**
     * Forgot Password screen
     * @return void
     */
    public function forgotpassword()
    {
        $this->_redirectIfLoggedIn();

        if (!Yii::app()->request->getPost('action')) {
            $this->_renderWrappedTemplate('authentication', 'forgotpassword');
        } else {
            $sUserName = Yii::app()->request->getPost('user');
            $sEmailAddr = Yii::app()->request->getPost('email');

            $aFields = User::model()->findAllByAttributes(array('users_name' => $sUserName, 'email' => $sEmailAddr));

            // Preventing attacker from easily knowing whether the user and email address are valid or not (and slowing down brute force attacks)
            usleep(rand(Yii::app()->getConfig("minforgottenpasswordemaildelay"), Yii::app()->getConfig("maxforgottenpasswordemaildelay")));
            $aData = [];
            if (count($aFields) < 1 || ($aFields[0]['uid'] != 1 && !Permission::model()->hasGlobalPermission('auth_db', 'read', $aFields[0]['uid']))) {
                // Wrong or unknown username and/or email. For security reasons, we don't show a fail message
                $aData['message'] = '<br>'.gT('If the username and email address is valid and you are allowed to use the internal database authentication a new password has been sent to you.').'<br>';
            } else {
                $aData['message'] = '<br>'.$this->_sendPasswordEmail($aFields[0]).'</br>';
            }
            $this->_renderWrappedTemplate('authentication', 'message', $aData);
        }
    }

    public static function runDbUpgrade()
    {
        // Check if the DB is up to date
        if (Yii::app()->db->schema->getTable('{{surveys}}')) {
            $sDBVersion = getGlobalSetting('DBVersion');
            if ((int) $sDBVersion < Yii::app()->getConfig('dbversionnumber')) {
                // Try a silent update first
                Yii::app()->loadHelper('update/updatedb');
                if (!db_upgrade_all(intval($sDBVersion), true)) {
                    Yii::app()->getController()->redirect(array('/admin/databaseupdate/sa/db'));
                }
            }
        }
    }

    /**
     * Send the forgot password email
     *
     * @param CActiveRecord User 
     */
    private function _sendPasswordEmail( $arUser)
    {
        $sFrom = Yii::app()->getConfig("siteadminname")." <".Yii::app()->getConfig("siteadminemail").">";
        $sTo = $arUser->email;
        $sSubject = gT('User data');
        $sNewPass = createPassword();
        $sSiteName = Yii::app()->getConfig('sitename');
        $sSiteAdminBounce = Yii::app()->getConfig('siteadminbounce');

        $username = sprintf(gT('Username: %s'), $arUser['users_name']);
        $password = sprintf(gT('New password: %s'), $sNewPass);

        $body   = array();
        $body[] = sprintf(gT('Your user data for accessing %s'), Yii::app()->getConfig('sitename'));
        $body[] = $username;
        $body[] = $password;
        $body   = implode("\n", $body);

        if (SendEmailMessage($body, $sSubject, $sTo, $sFrom, $sSiteName, false, $sSiteAdminBounce)) {
            User::updatePassword($arUser['uid'], $sNewPass);
            // For security reasons, we don't show a successful message
            $sMessage = gT('If the username and email address is valid and you are allowed to use the internal database authentication a new password has been sent to you.');
        } else {
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
    private static function getSummary($sMethod = 'login', $sSummary = '')
    {
        if (!empty($sSummary)) {
            return $sSummary;
        }

        switch ($sMethod) {
            case 'logout' :
                $sSummary = gT('Please log in first.');
                break;

            case 'login' :
            default :
                $sSummary = '<br />'.sprintf(gT('Welcome %s!'), Yii::app()->session['full_name']).'<br />&nbsp;';
                if (!empty(Yii::app()->session['redirect_after_login']) && strpos(Yii::app()->session['redirect_after_login'], 'logout') === false) {
                    Yii::app()->session['metaHeader'] = '<meta http-equiv="refresh"'
                    . ' content="1;URL='.Yii::app()->session['redirect_after_login'].'" />';
                    $sSummary = '<p><font size="1"><i>'.gT('Reloading screen. Please wait.').'</i></font>';
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
        if (!Yii::app()->user->getIsGuest()) {
            $this->runDbUpgrade();
            Yii::app()->getController()->redirect(array('/admin'));
        }
    }

    /**
     * Redirect after login
     * @return void
     */
    private static function doRedirect()
    {
        self::runDbUpgrade();
        $returnUrl = App()->user->getReturnUrl(array('/admin'));
        Yii::app()->getController()->redirect($returnUrl);
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     * @return void
     */
    protected function _renderWrappedTemplate($sAction = 'authentication', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        $aData['display']['menu_bars'] = false;
        $aData['language'] = Yii::app()->getLanguage() != Yii::app()->getConfig("defaultlang") ? Yii::app()->getLanguage() : 'default';
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

}
