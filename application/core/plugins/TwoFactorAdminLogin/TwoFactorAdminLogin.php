<?php

/**
 * Plugin to enable two factor authentication for LimeSurvey Admin Backend
 * @author LimeSurvey GmbH <info@limesurvey.org>
 * @license GPL 2.0 or later
 */

//Get necessary libraries and component plugins
require_once(__DIR__ . '/vendor/autoload.php');
spl_autoload_register(function ($class_name) {
    if (preg_match("/^TFA.*/", $class_name)) {
        if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $class_name . '.php')) {
            include __DIR__ . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $class_name . '.php';
        } elseif (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . $class_name . '.php')) {
            include __DIR__ . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . $class_name . '.php';
        } elseif (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'installer' . DIRECTORY_SEPARATOR . $class_name . '.php')) {
            include __DIR__ . DIRECTORY_SEPARATOR . 'installer' . DIRECTORY_SEPARATOR . $class_name . '.php';
        }
    }
});


class TwoFactorAdminLogin extends AuthPluginBase
{
    protected static $description = 'Add two-factor authentication to your admin login';
    protected static $name = 'TwoFactorAdminLogin';

    private $o2FA = null;

    /** @inheritdoc **/
    public $allowedPublicMethods = [
        'userindex',
        'index'
    ];

    protected $storage = 'DbStorage';
    protected $settings = array(
        'issuer' => array(
            'type' => 'string',
            'label' => 'Issuer',
            'default' => 'LimeSurvey Survey Software',
            'help' => 'This will be displayed in the 2FA app as issuer name.'
        ),
        'digits' => array(
            'type' => 'string',
            'label' => 'Code length',
            'default' => '6',
            'help' => 'The number of digits the resulting codes will be. Leave it at 6 for best compatibility.'
        ),
        'period' => array(
            'type' => 'string',
            'label' => 'Time period',
            'default' => '30',
            'help' => 'The number of seconds a code will be valid. Please leave it at 30 for best compatibility.'
        ),
        'leeway' => array(
            'type' => 'string',
            'label' => 'Discrepancy',
            'default' => '5',
            'help' => 'What amount of discrepancy in seconds is allowed for the client.'
        ),
        'algorithm' => array(
            'type' => 'select',
            'label' => 'Algorithm',
            'default' => 'sha1',
            'options' => [
                'sha1' => 'SHA1 (Default)',
                'sha256 ' => 'SHA256',
                'md5' => 'MD5',
            ],
            'help' => 'Please keep in mind, that most tools only work with SHA1 hashing.'
        ),
        'yubiClientId' => array(
            'type' => 'string',
            'label' => 'YubiCloud - Client ID',
            'default' => '',
            'help' => 'Your YubiCloud Client ID.'
        ),
        'yubiSecretKey' => array(
            'type' => 'password',
            'label' => 'YubiCloud - Secret Key',
            'default' => '',
            'help' => 'Your YubiCloud Secret Key. If not set, the YubiCloud responses authenticity will not be verified. Verifying authenticity is recommended for increased security.'
        ),
        'force2fa' => array(
            'type' => 'select',
            'label' => 'Prompt to activate 2FA on login',
            'default' => '0',
            'options' => [
                '0 ' => 'No',
                '1' => 'Only prompt, no enforcement',
                '2' => 'Always enforce 2FA activation',
            ],
            'help' => 'Upon login, users who have not enabled two-factor authentication (2FA) will be prompted to set it up.'
        ),

    );

    public function init()
    {
        //System events
        $this->subscribe('direct');
        $this->subscribe('newDirectRequest');
        $this->subscribe('beforeActivate');
        $this->subscribe('beforeDeactivate');
        $this->subscribe('beforeAdminMenuRender');
        $this->subscribe('afterSuccessfulLogin');

        //Login events
        $this->subscribe('newLoginForm');
        $this->subscribe('afterLoginFormSubmit');
        $this->subscribe('newUserSession');
    }

    //##############  Plugin event handlers ##############//
    /**
     * Listen to direct requests
     * Necessary for the getMetadata function
     *
     * @return void
     */
    public function newDirectRequest()
    {
        $request = $this->api->getRequest();
        $oEvent = $this->getEvent();
        if ($oEvent->get('target') != 'TwoFactorAdminLogin') {
            return;
        }

        $action = $oEvent->get('function');
        if (method_exists($this, $action)) {
            call_user_func([$this, $action], $oEvent, $request);
        }
    }

    /**
     * Event direct happen
     * Usage : index --target=value [--function=] [--option=]
     * @return void
     */
    public function direct()
    {
        $oEvent = $this->getEvent();
        if ($oEvent->get('target') != 'TwoFactorAdminLogin') {
            return;
        }
        $option = $this->event->get("option");
        $action = $oEvent->get('function');
        if (method_exists($this, $action)) {
            call_user_func([$this, $action], $oEvent, $option);
        }
    }
    /**
     * Register new table and populate it.
     *
     * @return void
     */
    public function beforeActivate()
    {
        TFAPluginInstaller::instance()->install();
    }

    /**
     * Delete the created tables again
     *
     * @return void
     */
    public function beforeDeactivate()
    {
        TFAPluginInstaller::instance()->uninstall();
    }


    /**
     * Add Two-Factor field to login page.
     *
     * @return void
     */
    public function newLoginForm()
    {
        $oEvent = $this->getEvent();
        $extraLine = ""
            . "<span>"
            . "<label for='twofactor'>"  . gT("2FA key (optional)") . "</label>
            <input class='form-control' name='twofactor' id='twofactor' type='text' value='' />"
            . "</span>";

        $oEvent->getContent('Authdb')->addContent($extraLine, 'append');
    }

    /**
     * Control if login is successful by checking the transmitted 2FA-token value
     *
     * @return void
     */
    public function newUserSession()
    {
        $oEvent = $this->getEvent();
        $onepass = App()->request->getParam('onepass');

        // skip 2fa when theres an active and verified onetimepassword used (verification is allready done before getting here)
        if (App()->getConfig('use_one_time_passwords') && isset($onepass)) {
            return;
        }

        $oIdentity = $oEvent->get('identity');
        $oTFAModel =  TFAUserKey::model()->findByPk($oIdentity->id);

        if ($oTFAModel != null) {
            if (!in_array($oTFAModel->authType, ['totp', 'yubi'])) {
                $this->setAuthFailure(null, 'Authentication method not supported');
                return;
            }
            $oTFAModel->decrypt();
            $authenticationKey = Yii::app()->getRequest()->getPost('twofactor', false);
            if (!$authenticationKey || !$this->confirmKey($oTFAModel, $authenticationKey)) {
                $this->setAuthFailure(null, 'Authentication key invalid');
            }
        }
        return;
    }

    /**
     * Add menue to the top bar
     * @return void
     */
    public function beforeAdminMenuRender()
    {
        $oEvent = $this->getEvent();
        $aMenuItems = [];

        $aMenuItemUserOptions = [
            'isDivider' => false,
            'isSmallText' => false,
            'label' => gT('General'),
            'href' => $this->api->createUrl('admin/pluginhelper/sa/fullpagewrapper/plugin/TwoFactorAdminLogin/method/userindex', []),
            'iconClass' => 'ri-spy-fill',
        ];

        $aMenuItems[] = (new \LimeSurvey\Menu\MenuItem($aMenuItemUserOptions));

        if (Permission::model()->hasGlobalPermission('users', 'update')) {
            $aMenuItemAdminOptions = [
                'isDivider' => false,
                'isSmallText' => false,
                'label' => gT('Administration'),
                'href' => $this->api->createUrl('admin/pluginhelper/sa/fullpagewrapper/plugin/TwoFactorAdminLogin/method/index', []),
                'iconClass' => 'ri-group-fill',
            ];
            $aMenuItems[] = (new \LimeSurvey\Menu\MenuItem($aMenuItemAdminOptions));
        }

        $aNewMenuOptions = [
            'isDropDown' => true,
            'label' => gT('2FA settings'),
            'href' => '#',
            'menuItems' => $aMenuItems,
            'iconClass' => 'ri-lock-fill',
            'isInMiddleSection' => true,
            'isPrepended' => false,
        ];
        $oNewMenu = new TFAMenuClass($aNewMenuOptions);

        //enable menu only if plugin is active
        if (TFAHelper::isPluginActive()) {
            $oEvent->append('extraMenus', [$oNewMenu]);
        }

        /* Admin GUI : redirect if needed (force2fa >=2) */
        if ($this->get('force2fa', null, null, 0) < 2) {
            return;
        }
        /* Already set */
        if (TFAUserKey::model()->findByPk(App()->user->id)) {
            return;
        }
        /* Page to set (already redirected) */
        if (
            App()->getController() && App()->getController()->getId() == 'admin'
            && App()->getController()->getAction() && App()->getController()->getAction()->getId() == 'pluginhelper'
            && App()->getRequest()->getQuery('plugin') == 'TwoFactorAdminLogin'
            && App()->getRequest()->getQuery('method') == 'userindex'
        ) {
            return;
        }
        Yii::app()->getController()->redirect(
            [
                'admin/pluginhelper',
                'sa' => 'fullpagewrapper',
                'plugin' => 'TwoFactorAdminLogin',
                'method' => 'userindex'
            ]
        );
    }

    /**
     * If force 2FA login is enabled, redirect to the 2FA page
     * @return void
     */
    public function afterSuccessfulLogin()
    {
        $oEvent = $this->getEvent();
        $oTFAModel =  TFAUserKey::model()->findByPk(App()->user->id);

        if ($oTFAModel == null && $this->get('force2fa', null, null, 0) >= 1) {
            Yii::app()->getController()->redirect(
                [
                    'admin/pluginhelper',
                    'sa' => 'fullpagewrapper',
                    'plugin' => 'TwoFactorAdminLogin',
                    'method' => 'userindex'
                ]
            );
        }
    }

    //################# View rendering ###################

    /**
     * Renders a list of users including their 2FA settings
     * To be called by fullpagewrapper
     *
     * @return string
     */
    public function index()
    {
        if (Yii::app()->getRequest()->getQuery('pageSize')) {
            Yii::app()->user->setState('pageSize', (int) Yii::app()->getRequest()->getQuery('pageSize'));
        }

        $model = new TFAUser('search');
        $model->setAttributes(Yii::app()->getRequest()->getParam('TFAUser'));

        $iPageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);

        $aData = [
            'model' => $model,
            'pageSize' => $iPageSize,
        ];
        $this->pageScripts();


        return $this->renderPartial('index', $aData, true);
    }

    /**
     * Renders the user configuration page
     * To be called by fullpagewrapper
     *
     * @return string
     */
    public function userindex()
    {
        $iUserId = Yii::app()->getRequest()->getPost('iUserId', Yii::app()->user->id);
        $oTFAModel =  TFAUserKey::model()->findByPk($iUserId);
        if ($oTFAModel != null) {
            $oTFAModel->decrypt();
        }

        $aData = [
            'oTFAModel' => $oTFAModel,
            'force2FA' => $this->get('force2fa', null, null, 0)
        ];

        $this->pageScripts();
        return $this->renderPartial('userindex', $aData, true);
    }

    //################ Direct access methods ###############

    /**
     * Renders the content of the modal to create a 2FA key registration
     *
     * @param PluginEvent $oEvent
     * @param CHttpRequest $oRequest
     * @return string
     */
    public function directCallCreateNewKey($oEvent, $oRequest)
    {
        $iUserId = Yii::app()->getRequest()->getParam('uid', Yii::app()->user->id);

        if (!Permission::model()->hasGlobalPermission('users', 'update') && $iUserId !== Yii::app()->user->id) {
            return $this->renderPartial('_partial.error', [
                'errors' => ["No permission"]
            ]);
        }

        $oTFAModel = TFAUserKey::model()->findByPk($iUserId);

        if ($oTFAModel == null) {
            $oTFAModel = new TFAUserKey();
        }

        $o2FA = $this->get2FAObject();

        $oTFAModel->uid = $iUserId;
        $oTFAModel->secretKey = $o2FA->createSecret();
        $sQRCodeContent = '<img src="' . $o2FA->getQRCodeImageAsDataUri('LimeSurvey - User ID: ' . Yii::app()->user->id, $oTFAModel->secretKey) . '">';

        return $this->renderPartial('_partial/create', [
            'model' => $oTFAModel,
            'sQRCodeContent' => $sQRCodeContent,
        ]);
    }

    /**
     * Checks a submitted authentication code and stores the underlaying secret key into the Database.
     * Returns a JSON document
     *
     * @param PluginEvent $oEvent
     * @param CHttpRequest $oRequest
     * @return boolean
     */
    public function directCallConfirmKey($oEvent, $oRequest)
    {
        $aTFAUserKey = Yii::app()->getRequest()->getPost('TFAUserKey', []);
        $uid = $aTFAUserKey['uid'];
        if (!(Permission::model()->hasGlobalPermission('users', 'update') || $uid == Yii::app()->user->id)) {
            return $this->createJSONResponse(false, "No permission");
        }

        $authType = $aTFAUserKey['authType'];

        switch ($authType) {
            case 'totp':
                $confirmationKey = Yii::app()->getRequest()->getPost('confirmationKey', '');
                return $this->setupTotp($aTFAUserKey, $confirmationKey);
                break;
            case 'yubi':
                $yubiOtp = Yii::app()->getRequest()->getPost('yubikeyOtp', '');
                return $this->setupYubi($aTFAUserKey, $yubiOtp);
                break;
            default:
                return $this->createJSONResponse(false, gT("Invalid auth type"));
        }
    }

    private function setupTotp($tfaUserKey, $confirmationKey)
    {
        if ($confirmationKey == '') {
            return $this->createJSONResponse(false, "Please enter a confirmation key");
        }

        $o2FA = $this->get2FAObject();
        $result = $o2FA->verifyCode($tfaUserKey['secretKey'], $confirmationKey);
        if (!$result) {
            return $this->createJSONResponse(false, gT("The confirmation key is not correct."));
        }

        TFAUserKey::model()->deleteAllByAttributes(['uid' => $tfaUserKey['uid']]);

        $oTFAModel = new TFAUserKey();
        $oTFAModel->setAttributes($tfaUserKey, false);
        $oTFAModel->firstLogin = 0;
        if (!$oTFAModel->encryptSave()) {
            return $this->createJSONResponse(false, gT("The two-factor authentication key could not be stored."));
        }
        return $this->createJSONResponse(true, "Two-factor method successfully stored", ['reload' => true]);
    }

    private function setupYubi($tfaUserKey, $yubiOtp)
    {
        if (empty($yubiOtp)) {
            return $this->createJSONResponse(false, "Please enter a YubiKey OTP");
        }

        $yubikeyOtpHelper = $this->getYubikeyOtpHelper($yubiOtp);
        if (!$yubikeyOtpHelper->verifyOtp()) {
            $error = $yubikeyOtpHelper->getLastError();
            if (empty($error)) {
                $error = gT("The YubiKey OTP is not correct.");
            }
            return $this->createJSONResponse(false, $error);
        }
        $tfaUserKey['secretKey'] = $yubikeyOtpHelper->getPublicId();

        TFAUserKey::model()->deleteAllByAttributes(['uid' => $tfaUserKey['uid']]);

        $oTFAModel = new TFAUserKey();
        $oTFAModel->setAttributes($tfaUserKey, false);
        $oTFAModel->firstLogin = 0;
        if (!$oTFAModel->encryptSave()) {
            return $this->createJSONResponse(false, gT("The two-factor authentication key could not be stored."));
        }
        return $this->createJSONResponse(true, "Two-factor method successfully stored", ['reload' => true]);
    }

    /**
     * Deletes a users secret, effectively ending the 2FA login mechanism for that user.
     * Returns a JSON document
     *
     * @param PluginEvent $oEvent
     * @param CHttpRequest $oRequest
     * @return boolean
     */
    public function directCallDeleteKey($oEvent, $oRequest)
    {
        $uid = $oRequest->getPost('uid', null);

        if (!Permission::model()->hasGlobalPermission('users', 'update') && $uid !== Yii::app()->user->id) {
            return $this->createJSONResponse(false, gT('No permission'));
        }
        $oTFAModel =  TFAUserKey::model()->findByPk($uid);
        $success = $oTFAModel->delete();
        return $this->createJSONResponse($success, ($success ? gT('Successfully deleted') : gT('Deletion failed')));
    }

    /**
     * Deletes a users secret, effectively ending the 2FA login mechanism for that user CLI version.
     * Returns a String
     *
     * @param PluginEvent $oEvent
     * @param string $sOption
     * @return string
     */
    public function deleteKeyForUserId($oEvent, $iUserId)
    {
        $uid = (int) $iUserId;
        $oTFAModel =  TFAUserKey::model()->findByPk($uid);
        if ($oTFAModel == null) {
            printf(gT("No 2FA key set for user ID %s"), $iUserId);
            return;
        }
        $success = $oTFAModel->delete();
        echo ($success ? gT('Successfully deleted') : gT('Deletion failed'));
    }

    /**
     * Deletes a users secret, effectively ending the 2FA login mechanism for that user CLI version.
     * Returns a String
     *
     * @param PluginEvent $oEvent
     * @param string $sOption
     * @return string
     */
    public function deleteKeyForUserName($oEvent, $sUserName)
    {
        $oUser = User::model()->findByAttributes(['users_name' => $sUserName]);
        $oTFAModel =  TFAUserKey::model()->findByPk($oUser->uid);
        if ($oTFAModel == null) {
            echo "No 2FA key set for user " . $sUserName;
            return;
        }
        $success = $oTFAModel->delete();
        echo ($success ? 'Successfully deleted' : 'Deleting failed');
    }


    //################### Utility methods ##################

    /**
     * Checks a 2FA OTP authentication code against the stored secret
     *
     * @param TFAUserKey $tfaModel
     * @param string $authenticationCode
     * @return boolean true on authentication code matching stored secret key
     */
    private function confirmKey($tfaModel, $authenticationCode)
    {
        $authType = $tfaModel->authType;

        if ($authType == 'totp') {
            $o2FA = $this->get2FAObject();
            return $o2FA->verifyCode($tfaModel->secretKey, $authenticationCode);
        } elseif ($authType == 'yubi') {
            $yubikeyOtpHelper = $this->getYubikeyOtpHelper($authenticationCode);

            // For YubiKey OTP we need to validate two things:
            // 1. That the OTP matches the stored key ID
            // 2. That the OTP is valid

            // Check the key ID
            if ($yubikeyOtpHelper->getPublicId() != $tfaModel->secretKey) {
                return false;
            }

            // Validate the OTP
            return $yubikeyOtpHelper->verifyOtp();
        } else {
            return false;
        }
    }

    /**
     * Creates or returns a 2FA-library object
     * Using Rob Janssen TwoFactorAuth - Library https://github.com/RobThree/TwoFactorAuth
     *
     * @return TwoFactorAuth
     */
    private function get2FAObject()
    {
        if ($this->o2FA == null) {
            $mp = new TFAQrCodeGenerator();
            $this->o2FA = new RobThree\Auth\TwoFactorAuth(
                $this->get('issuer', null, null, 'LimeSurvey - survey software'),
                ((int) $this->get('digits', null, null, 6)),
                ((int) $this->get('period', null, null, 30)),
                $this->get('algorithm', null, null, 'sha1'),
                $mp
            );
        }
        return $this->o2FA;
    }

    /**
     * Returns a TFAYubikeyOtpHelper instance for the given YubiKey OTP code.
     * @param string $otpCode the YubiKey OTP code
     * @return TFAYubikeyOtpHelper the YubiKey OTP helper
     */
    private function getYubikeyOtpHelper($otpCode)
    {
        $clientId = $this->get('yubiClientId', null, null, null);
        $clientSecret = $this->get('yubiSecretKey', null, null, null);
        return new TFAYubikeyOtpHelper($otpCode, $clientId, $clientSecret);
    }

    /**
     * Generates a printed JSON-Response
     *
     * @param bool $success
     * @param string $message
     * @param array $data
     * @return boolean $success
     */
    protected function createJSONResponse($success, $message, $data = [])
    {
        header('Content-Type: application/json');

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        return $success;
    }

    /**
     * Applies the necessary page scripts to the page through CClientScript derivate
     *
     * @return void
     */
    protected function pageScripts()
    {

        $assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/assets');
        Yii::app()->clientScript->registerScriptFile($assetsUrl . '/tfaScripts.js', LSYii_ClientScript::POS_HEAD);
        Yii::app()->clientScript->registerCssFile($assetsUrl . '/tfaStyles.css');
    }
}
