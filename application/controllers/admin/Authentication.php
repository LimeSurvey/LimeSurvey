<?php

// see: https://scrutinizer-ci.com/g/LimeSurvey/LimeSurvey/issues/master/files/application/controllers/admin/authentication.php?selectedSeverities[0]=10&orderField=path&order=asc&honorSelectedPaths=0
// use LimeSurvey\PluginManager\PluginEvent;

/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
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
class Authentication extends SurveyCommonAction
{
    /** Session key holding a login that waits for its second authentication step */
    const SECOND_FACTOR_SESSION_KEY = 'pendingSecondFactorLogin';

    /** Seconds the user has to complete the second authentication step */
    const SECOND_FACTOR_TIMEOUT = 300;

    /** Wrong second-factor keys allowed before the user has to log in again */
    const SECOND_FACTOR_MAX_ATTEMPTS = 5;

    /**
     * Show login screen and parse login data
     * Will redirect or echo json depending on ajax call
     * This function is called while accessing the login page: index.php/admin/authentication/sa/login
     */
    public function index()
    {
        // if the session is not readable clear browser cookies
        if (!session_id()) {
            App()->request->cookies->clear();
        }
        /* Set adminlang to the one set in dropdown */
        if (Yii::app()->request->getParam('loginlang', 'default') != 'default') {
            Yii::app()->session['adminlang'] = Yii::app()->request->getParam('loginlang', 'default');
            Yii::app()->setLanguage(Yii::app()->session["adminlang"]);
        }
        // The page should be shown only for non logged in users
        $this->redirectIfLoggedIn();

        // Result can be success, fail or data for template
        $result = self::prepareLogin();

        $isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;
        $succeeded = isset($result[0]) && $result[0] == 'success';
        $failed = isset($result[0]) && $result[0] == 'failed';
        $needsSecondFactor = isset($result[0]) && $result[0] == 'secondfactor';

        // If Ajax, echo success or failure json
        if ($isAjax) {
            Yii::import('application.helpers.admin.ajax_helper', true);
            if ($succeeded) {
                ls\ajax\AjaxHelper::outputSuccess(gT('Successful login'));
                return;
            } elseif ($failed) {
                ls\ajax\AjaxHelper::outputError(gT('Incorrect or expired username and/or password!'));
                return;
            } elseif ($needsSecondFactor) {
                // The login modal can't show the second step
                self::clearPendingSecondFactor();
                ls\ajax\AjaxHelper::outputError(gT('This account uses two-factor authentication. Please reload the page and log in again.'));
                return;
            }
        } else {
            // If not ajax, redirect to admin startpage or again to login form
            if ($succeeded) {
                self::doRedirect();
            } elseif ($needsSecondFactor) {
                App()->getController()->redirect(array('/admin/authentication/sa/secondfactor'));
            } elseif ($failed) {
                $message = $result[1];
                App()->user->setFlash('error', $message);
                App()->getController()->redirect(array('/admin/authentication/sa/login'));
            }
        }

        // Neither success nor failure, meaning no form submission - result = template data from plugin
        $aData = $result;

        // If for any reason, the plugin bugs, we can't let the user with a blank screen.
        $this->renderWrappedTemplate('authentication', 'login', $aData);
    }

    /**
     * Prepare login and return result
     * It checks if the authdb plugin is registered and active
     * @return array Either success, failure, second factor needed or plugin data (used in login form)
     */
    public static function prepareLogin()
    {
        $aData = array();

        if (!class_exists('Authdb', false)) {
            $plugin = Plugin::model()->findByAttributes(array('name' => 'Authdb'));
            if (!$plugin) {
                // TODO: Should not be possible to get here after LS4. See LsDefaultDataSets::getDefaultPluginsData().
                $plugin = new Plugin();
                $plugin->name = 'Authdb';
                $plugin->active = 1;
                $plugin->plugin_type = 'core';
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
            // Showing the login form again abandons a login waiting for its second step
            self::clearPendingSecondFactor();

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
            App()->getPluginManager()->dispatchEvent($newLoginForm); // inject the HTML of the form inside the private variable "_content" of the plugin
            $aData['summary'] = self::getSummary('logout');
            $aData['pluginContent'] = $newLoginForm->getAllContent(); // Retrieves the private variable "_content" , and parse it to $aData['pluginContent'], which will be  rendered in application/views/admin/authentication/login.php
        } else {
            // The form has been submitted, or the plugin has been stopped (so normally, the value of login/password are available)

                // Handle getting the post and populating the identity there
            $authMethod = App()->getRequest()->getPost('authMethod', $identity->plugin); // If form has been submitted, $_POST['authMethod'] is set, else  $identity->plugin should be set, ELSE: TODO error
            $identity->plugin = $authMethod;

            // Call the function afterLoginFormSubmit of the plugin.
            // For Authdb, it calls AuthPluginBase::afterLoginFormSubmit()
            // which set the plugin's private variables _username and _password with the POST information if it's a POST request else it does nothing
            $event = new PluginEvent('afterLoginFormSubmit');
            $event->set('identity', $identity);
            App()->getPluginManager()->dispatchEvent($event, array($authMethod));
            $identity = $event->get('identity');
            // The login form can show a second authentication step (e.g. 2FA) if a plugin requires it
            $identity->secondFactorSupported = true;

            // Now authenticate
            // This call LSUserIdentity::authenticate() (application/core/LSUserIdentity.php))
            // which will call the plugin function newUserSession() (eg: Authdb::newUserSession() )
            // TODO: for sake of clarity, the plugin function should be renamed to authenticate().
            if ($identity->authenticate()) {
                return self::finishLogin($identity, $authMethod);
            } elseif ($identity->errorCode == LSUserIdentity::ERROR_SECOND_FACTOR_REQUIRED) {
                self::startSecondFactor($identity, $authMethod);
                return array('secondfactor');
            } else {
                // Failed
                $event = new PluginEvent('afterFailedLoginAttempt');
                $event->set('identity', $identity);
                App()->getPluginManager()->dispatchEvent($event);

                $message = $identity->errorMessage;
                if (empty($message)) {
                    // If no message, return a default message
                    $message = gT('Incorrect or expired username and/or password!');
                }
                return array('failed', $message);
            }
        }

        return $aData;
    }

    /**
     * Last steps of a successful login, after the session was created
     *
     * @param LSUserIdentity $identity The logged in identity
     * @param string $authMethod The auth plugin used for the login
     * @return array
     */
    private static function finishLogin($identity, $authMethod)
    {
        FailedLoginAttempt::model()->deleteAttempts(FailedLoginAttempt::TYPE_LOGIN);
        App()->user->setState('plugin', $authMethod);

        Yii::app()->session['just_logged_in'] = true;
        Yii::app()->session['loginsummary'] = self::getSummary();

        $event = new PluginEvent('afterSuccessfulLogin');
        $event->set('identity', $identity);
        App()->getPluginManager()->dispatchEvent($event);

        // If allowed_hosts.php does not exist, write the current host as valid
        $allowedHosts = App()->loadAllowedHosts();
        if (empty($allowedHosts)) {
            $currentHost = App()->request->getServerName();
            if (App()->writeAllowedHosts([$currentHost])) {
                Yii::app()->setFlashMessage(
                    sprintf(
                        gT('The allowed hosts file (application/config/allowed_hosts.php) has been created with "%s" as trusted host. For security reasons, LimeSurvey can only be accessed through that domain. If you need additional hosts, please edit the allowed hosts file directly.'),
                        htmlspecialchars($currentHost)
                    ),
                    'info'
                );
            } else {
                Yii::app()->setFlashMessage(
                    gT('The allowed hosts file (application/config/allowed_hosts.php) could not be created because the application/config directory is not writable. No trusted host restriction is currently enforced. Please make the directory writable, then login again, to enable host header protection.'),
                    'warning'
                );
            }
        }

        return array('success');
    }

    /**
     * Remember a login whose credentials were accepted but which needs a second authentication step
     *
     * @param LSUserIdentity $identity The identity returned by authenticate()
     * @param string $authMethod The auth plugin used for the login
     * @return void
     */
    private static function startSecondFactor($identity, $authMethod)
    {
        // New session id: the pending login must not be reachable through a session id known before
        App()->session->regenerateID(true);
        App()->session[self::SECOND_FACTOR_SESSION_KEY] = array(
            'uid' => (int) $identity->id,
            'authMethod' => $authMethod,
            'plugin' => $identity->secondFactorPlugin,
            'expires' => time() + self::SECOND_FACTOR_TIMEOUT,
            'attempts' => 0,
            // Language chosen on the login form, saved by LSUserIdentity::postLogin() once logged in
            'loginlang' => App()->getRequest()->getPost('loginlang', 'default'),
        );
    }

    /**
     * Returns the login waiting for its second authentication step, if not expired
     *
     * @return array|null
     */
    private static function getPendingSecondFactor()
    {
        $pending = App()->session[self::SECOND_FACTOR_SESSION_KEY];
        if (!is_array($pending)) {
            return null;
        }
        if ($pending['expires'] < time()) {
            self::clearPendingSecondFactor();
            return null;
        }
        return $pending;
    }

    /**
     * Forget the login waiting for its second authentication step
     *
     * @return void
     */
    private static function clearPendingSecondFactor()
    {
        unset(App()->session[self::SECOND_FACTOR_SESSION_KEY]);
    }

    /**
     * Second login step: show and check the form of the plugin requiring a second factor (e.g. 2FA key)
     * The user's credentials were already accepted by prepareLogin().
     * This function is called while accessing: index.php/admin/authentication/sa/secondfactor
     *
     * @return void
     */
    public function secondFactor()
    {
        $this->redirectIfLoggedIn();

        $pending = self::getPendingSecondFactor();
        $user = $pending ? User::model()->findByPk($pending['uid']) : null;
        if (!$user || !$user->canLogin() || !App()->getPluginManager()->isPluginActive($pending['plugin'])) {
            self::clearPendingSecondFactor();
            App()->user->setFlash('error', gT('Your login session has expired. Please log in again.'));
            App()->getController()->redirect(array('/admin/authentication/sa/login'));
        }

        if (!is_null(App()->getRequest()->getPost('login_submit'))) {
            if (FailedLoginAttempt::model()->isLockedOut(FailedLoginAttempt::TYPE_LOGIN)) {
                self::clearPendingSecondFactor();
                App()->user->setFlash('error', sprintf(gT('You have exceeded the number of maximum login attempts. Please wait %d minutes before trying again.'), App()->getConfig('timeOutTime') / 60));
                App()->getController()->redirect(array('/admin/authentication/sa/login'));
            }

            $event = new PluginEvent('verifySecondFactor');
            $event->set('user', $user);
            App()->getPluginManager()->dispatchEvent($event, array($pending['plugin']));
            $result = $event->get('result');

            $identity = new LSUserIdentity($user->users_name, '');
            $identity->plugin = $pending['authMethod'];
            if ($result instanceof LSAuthResult && $result->isValid()) {
                self::clearPendingSecondFactor();
                $identity->id = $user->uid;
                $identity->user = $user;
                $identity->completeLogin();
                self::finishLogin($identity, $pending['authMethod']);
                self::doRedirect();
            }

            FailedLoginAttempt::model()->addAttempt(FailedLoginAttempt::TYPE_LOGIN);
            $event = new PluginEvent('afterFailedLoginAttempt');
            $event->set('identity', $identity);
            App()->getPluginManager()->dispatchEvent($event);

            $pending['attempts']++;
            if ($pending['attempts'] >= self::SECOND_FACTOR_MAX_ATTEMPTS) {
                self::clearPendingSecondFactor();
                App()->user->setFlash('error', gT('Too many failed attempts. Please log in again.'));
                App()->getController()->redirect(array('/admin/authentication/sa/login'));
            }
            App()->session[self::SECOND_FACTOR_SESSION_KEY] = $pending;
            $message = $result instanceof LSAuthResult ? $result->getMessage() : '';
            App()->user->setFlash('error', !empty($message) ? $message : gT('Incorrect 2FA key'));
            App()->getController()->redirect(array('/admin/authentication/sa/secondfactor'));
        }

        $newSecondFactorForm = new PluginEvent('newSecondFactorForm');
        $newSecondFactorForm->set('user', $user);
        App()->getPluginManager()->dispatchEvent($newSecondFactorForm, array($pending['plugin']));
        $aData = array();
        $aData['pluginContent'] = $newSecondFactorForm->getContent($pending['plugin']);
        $aData['loginlang'] = $pending['loginlang'];
        $this->renderWrappedTemplate('authentication', 'secondFactor', $aData);
    }

    /**
     * This action sets a password for new user or resets a password for an existing user.
     * If validation time is expired, no password will be changed.
     * After password has been changed successfully it redirects to LogIn-Page.
     * If the current user is already logged in, it shows a warning and redirects to the
     * administration page instead of showing the form.
     *
     * @return void
     */
    public function newPassword()
    {
        $this->redirectIfLoggedIn(gT('You cannot reset the password for a user while being logged in as someone else.'));

        //validation key could be a GET- or a POST-PARAM
        $validation_key = Yii::app()->request->getParam('param'); //as link from email
        if ($validation_key === null) {
            $usedLink = false;
            $validation_key = Yii::app()->request->getPost('validation_key'); //coming from form (user set password)
        } else {
            $usedLink = true;
        }

        $errorExists = false;
        $errorMsg = '';
        $user = User::model()->findByAttributes([], 'validation_key=:validation_key', ['validation_key' => $validation_key]);
        if ($user === null) {
            $errorExists = true;
            $errorMsg = gT('The validation key is invalid. Please contact the administrator.');
        } else {
            //check if validation time is expired
            $dateNow = new DateTime();
            $expirationDate = new DateTime($user->validation_key_expiration);
            $dateDiff = $expirationDate->diff($dateNow);
            $differenceDays = $dateDiff->format('%d');
            $differenceHours = $dateDiff->format('%h');
            $differenceCompleteInHours = ((int)$differenceDays * 24) + (int)$differenceHours;
            if ($differenceCompleteInHours > User::MAX_EXPIRATION_TIME_IN_HOURS) {
                $errorExists = true;
                $errorMsg = gT("The validation key expired. Please contact the administrator.");
            }
        }

        if (!$errorExists && !$usedLink) {
            //check if password is set correctly
            $password = Yii::app()->request->getPost('password', '');
            $passwordRepeat = Yii::app()->request->getPost('password_repeat', '');
            $passwordStrengthError = $user->checkPasswordStrength($passwordRepeat);
            if (($password !== null && $passwordRepeat !== null) && ($password === $passwordRepeat) && $passwordStrengthError == '') {
                //now everything is ok, save password
                $user->setPassword($password, true);
                // And remove validation_key
                $user->unsetAttributes(['validation_key', 'validation_key_expiration']);
                $user->save(false, ['validation_key', 'validation_key_expiration']);
                App()->getController()->redirect(array('/admin/authentication/sa/login'));
            } else {
                Yii::app()->setFlashMessage(sprintf(gT('Password cannot be blank and must fulfill minimum requirements: %s'), $passwordStrengthError), 'error');
            }
        }

        $randomPassword = \LimeSurvey\Models\Services\PasswordManagement::getRandomPassword();

        $aData = [
            'errorExists' => $errorExists,
            'errorMsg' => $errorMsg,
            'randomPassword' => $randomPassword,
            'validationKey' => $validation_key
        ];

        $this->renderWrappedTemplate('authentication', 'newPassword', $aData);
    }

    /**
     * Logout user
     * @return void
     */
    public function logout()
    {
        App()->user->logout();
        App()->user->setFlash('loginmessage', gT('Logout successful.'));
        $this->getController()->redirect(array('/admin/authentication/sa/login'));
    }

    /**
     * Forgot Password screen
     * @return void
     */
    public function forgotpassword()
    {
        $this->redirectIfLoggedIn();

        if (!Yii::app()->request->getPost('action')) {
            $this->renderWrappedTemplate('authentication', 'forgotpassword');
        } else {
            $sUserName = Yii::app()->request->getPost('user');
            $sEmailAddr = Yii::app()->request->getPost('email');

            $user = User::model()->findByAttributes(
                [],
                'users_name=:users_name and email=:email',
                ['users_name' => $sUserName, 'email' => $sEmailAddr]
            );

            // Preventing attacker from easily knowing whether the user and email address are valid or not (and slowing down brute force attacks)
            usleep(rand(Yii::app()->getConfig("minforgottenpasswordemaildelay"), Yii::app()->getConfig("maxforgottenpasswordemaildelay")));
            $aData = [];
            if (($user === null) || ($user->uid != 1 && !Permission::model()->hasGlobalPermission('auth_db', 'read', $user->uid))) {
                // Wrong or unknown username and/or email. For security reasons, we don't show a fail message
                $aData['message'] = '<br>' . sprintf(gT('If the username and email address is valid a password reminder email has been sent to you. This email can only be requested once in %d minutes.'), \LimeSurvey\Models\Services\PasswordManagement::MIN_TIME_NEXT_FORGOT_PW_EMAIL) . '<br>';
            } else {
                $passwordManagement = new \LimeSurvey\Models\Services\PasswordManagement($user);
                $aData['message'] = $passwordManagement->sendForgotPasswordEmailLink();
            }
            Yii::app()->setFlashMessage($aData['message'], 'success');
            Yii::app()->getController()->redirect(array('/admin'));
        }
    }

    /**
     * Check if db update is necessary and does the update
     *
     * @return void
     * @throws Exception
     */
    public static function runDbUpgrade()
    {
        // Check if the DB is up to date
        if (Yii::app()->db->schema->getTable('{{surveys}}')) {
            $sDBVersion = Yii::app()->getConfig('DBVersion');
            if ((int) $sDBVersion < Yii::app()->getConfig('dbversionnumber')) {
                // Try a silent update first
                Yii::app()->loadHelper('update.updatedb');
                if (!db_upgrade_all(intval($sDBVersion), true)) {
                    Yii::app()->getController()->redirect(array('/admin/databaseupdate/sa/db'));
                }
            }
        }
    }

    /**
     * Gets the summary
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
            case 'logout':
                $sSummary = gT('Please log in first.');
                break;

            case 'login':
            default:
                $sSummary = '<br />' . sprintf(gT('Welcome %s!'), Yii::app()->session['full_name']) . '<br />&nbsp;';
                if (!empty(Yii::app()->session['redirect_after_login']) && strpos((string) Yii::app()->session['redirect_after_login'], 'logout') === false) {
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
     *
     * @param string|null $flashMessage optional warning message to show after redirecting
     * @return void
     */
    private function redirectIfLoggedIn(?string $flashMessage = null)
    {
        if (!Yii::app()->user->getIsGuest()) {
            if ($flashMessage !== null) {
                Yii::app()->setFlashMessage($flashMessage, 'warning');
            }
            $this->runDbUpgrade();
            Yii::app()->getController()->redirect(array('/admin'));
        }
    }

    /**
     * Redirect after login.
     * Do a db update if any exists.
     * Clean failed_emails table (delete entries older then 30days)
     *
     * @return void
     */
    private static function doRedirect()
    {
        self::runDbUpgrade();
        self::cleanFailedEmailTable();
        self::createNewFailedEmailsNotification();
        $returnUrl = App()->user->getReturnUrl(array('/admin'));
        Yii::app()->getController()->redirect($returnUrl);
    }

    /**
     * Delete all entries from failed_emails table which are older then 30days
     *
     * @return void
     */
    private static function cleanFailedEmailTable()
    {
        $criteria = new CDbCriteria();

        //filter for 'created' date comparison
        $dateNow = new DateTime();

        //minus 30days
        $dateNow = $dateNow->sub(new DateInterval('P30D'));
        $dateNowFormatted = $dateNow->format('Y-m-d H:i');

        $criteria->addCondition('created < \'' . $dateNowFormatted . '\'');

        FailedEmail::model()->deleteAll($criteria);
    }

    /**
     * Checks failed_emails table for entries for this user and creates a UniqueNotification
     *
     * @return void
     */
    private static function createNewFailedEmailsNotification()
    {
        $failedEmailModel = new FailedEmail();
        $failedEmailSurveyTitles = $failedEmailModel->getFailedEmailSurveyTitles();
        if (!empty($failedEmailSurveyTitles)) {
            $uniqueNotification = new UniqueNotification(
                array(
                    'user_id' => App()->user->id,
                    'title' => gT('Failed email notifications'),
                    'markAsNew' => false,
                    'importance' => Notification::NORMAL_IMPORTANCE,
                    'message' => Yii::app()->getController()->renderPartial('//failedEmail/notification_message/_notification_message', [
                        'failedEmailSurveyTitles' => $failedEmailSurveyTitles
                    ], true)
                )
            );

            $uniqueNotification->save();
        }
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     * @return void
     */
    protected function renderWrappedTemplate($sAction = 'authentication', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        $aData['display']['menu_bars'] = false;
        $aData['language'] = Yii::app()->getLanguage() != Yii::app()->getConfig("defaultlang") ? Yii::app()->getLanguage() : 'default';
        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
