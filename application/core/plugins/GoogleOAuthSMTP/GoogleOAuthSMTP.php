<?php
require_once __DIR__ . '/vendor/autoload.php';

use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use PHPMailer\PHPMailer\OAuth;

class GoogleOAuthSMTP extends PluginBase
{
    protected $storage = 'DbStorage';
    protected static $description = 'Core: Adds Google OAuth support for email sending';
    protected static $name = 'GoogleOAuthSMTP';

    /** @inheritdoc, this plugin doesn't have any public method */
    public $allowedPublicMethods = array();

    protected $settings = [
        'enable' => [
            'type' => 'boolean',
            'label' => 'Enable',
        ],
        'clientId' => [
            'type' => 'string',
            'label' => 'Client ID',
        ],
        'clientSecret' => [
            'type' => 'string',
            'label' => 'Client Secret',
        ],
        'information' => [
            'type' => 'info',
            'content' => '',
        ],
    ];

    public function init()
    {
        $this->subscribe('newUnsecureRequest', 'receiveGoogleResponse');
        $this->subscribe('newDirectRequest', 'redirectToGoogle');

        $this->subscribe('beforeEmail', 'beforeEmail');
        $this->subscribe('beforeSurveyEmail', 'beforeEmail');
        $this->subscribe('beforeTokenEmail', 'beforeEmail');

        $this->subscribe('beforeControllerAction');
    }

    /**
     * @inheritdoc
     * Update the information content
     */
    public function getPluginSettings($getValues = true)
    {
        $this->settings['enable']['label'] = gT("Enable");
        $this->settings['enable']['help'] = gT("Use this plugin for SMTP authentication");
        $this->settings['clientId']['label'] = gT("Client ID");
        $this->settings['clientSecret']['label'] = gT("Client Secret");
        $this->settings['information']['content'] = $this->getRefreshTokenInfo();
        return parent::getPluginSettings($getValues);
    }

    /**
     * Renders the contents of the "information" setting depending on
     * settings and token validity.
     * @return string
     */
    private function getRefreshTokenInfo()
    {
        $this->subscribe('getPluginTwigPath');

        $clientId = $this->get('clientId');
        $clientSecret = $this->get('clientSecret');
        // If either "Client ID" or "Client Secret" is missing, show the "incomplete settings" message.
        if (empty($clientId) || empty($clientSecret)) {
            return Yii::app()->twigRenderer->renderPartial('/IncompleteSettingsMessage.twig', []);
        }

        $refreshToken = $this->get('refreshToken');
        $googleRedirectionUrl = $this->api->createUrl('plugins/direct', ['plugin' => $this->getName()]);
        $reloadSettingsUrl = $this->getConfigUrl();

        // If there is no refresh token stored, ask the user to get one.
        if (empty($refreshToken)) {
            return Yii::app()->twigRenderer->renderPartial('/GetTokenMessage.twig', [
                'class' => "warning",
                'message' => gT("Get token for currently saved Client ID and Secret."),
                'tokenUrl' => $googleRedirectionUrl,
                'reloadUrl' => $reloadSettingsUrl,
            ]);
        }

        // Check if the refresh token is still valid. If it's not, ask the user to get a new one.
        if (!$this->validateRefreshToken($refreshToken, $clientId, $clientSecret)) {
            return Yii::app()->twigRenderer->renderPartial('/GetTokenMessage.twig', [
                'class' => "danger",
                'message' => gT("The saved token isn't valid. You need to get a new one."),
                'tokenUrl' => $googleRedirectionUrl,
                'reloadUrl' => $reloadSettingsUrl,
            ]);
        }

        // If we got here, inform the user everything is Ok.
        return Yii::app()->twigRenderer->renderPartial('/ValidTokenMessage.twig', []);

        // Translations here just so the translations bot can pick them up.
        $lang = [
            gT("Currently saved settings are incomplete. After saving both 'Client ID' and 'Client Secret' you will be able to validate the credentials."),
            gT("Configuration is complete. If 'Client ID' or 'Client Secret' is changed, you will need to re-validate the credentials."),
            gT("Get token")
        ];
    }

    /**
     * Returns true if the specified refresh token is valid
     * @param string $refreshToken
     * @param string $clientId
     * @param string $clientSecret
     * @return bool
     */
    private function validateRefreshToken($refreshToken, $clientId, $clientSecret)
    {
        $refreshTokenMetadata = $this->get('refreshTokenMetadata') ?? [];
        if (
            empty($refreshTokenMetadata['clientId'])
            || empty($refreshTokenMetadata['clientSecret'])
            || $refreshTokenMetadata['clientId'] != $clientId
            || $refreshTokenMetadata['clientSecret'] != $clientSecret
        ) {
            return false;
        }

        $provider = $this->getProvider($clientId, $clientSecret);
        try {
            $token = $provider->getAccessToken(
                new RefreshToken(),
                ['refresh_token' => $refreshToken]
            );
            // TODO: Handle token with invalid scope (missing https://mail.google.com/)
        } catch (IdentityProviderException $ex) {
            // Don't do anything. Just leave $token unset.
        }
        return !empty($token);
    }

    /**
     * Returns the Client ID and Client Secret settings
     * @return string[] The credentials in the form [clientId, clientSecret]
     * @throws Exception if credentials are incomplete.
     */
    private function getCredentials()
    {
        $clientId = $this->get('clientId');
        $clientSecret = $this->get('clientSecret');
        if (empty($clientId) || empty($clientSecret)) {
            throw new Exception("Invalid Google OAuth settings");
        }
        return [$clientId, $clientSecret];
    }

    /**
     * Redirects to the Google's authorization page
     */
    public function redirectToGoogle()
    {
        $oEvent = $this->event;
        if ($oEvent->get('target') != $this->getName()) return;

        [$clientId, $clientSecret] = $this->getCredentials();

        $provider = $this->getProvider($clientId, $clientSecret);
        $options = [
            'scope' => [
                'https://mail.google.com/'
            ]
        ];
        $authUrl = $provider->getAuthorizationUrl($options);

        // Keep the 'state' in the session so we can later use it for validation.
        Yii::app()->session['googleOAuth-state'] = $provider->getState();
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Receives the response from Google
     */
    public function receiveGoogleResponse()
    {
        $oEvent = $this->event;
        if ($oEvent->get('target') != $this->getName()) return;

        /** @var LSHttpRequest */
        $request = Yii::app()->getRequest();

        $code = $request->getParam("code");
        if (empty($code)) {
            throw new Exception("Invalid request");
        }

        $oauth2state = Yii::app()->session['googleOAuth-state'];
        if (empty($oauth2state)) {
            throw new Exception("Invalid state");
        }

        $state = $request->getParam("state");
        if ($state != $oauth2state) {
            unset(Yii::app()->session['googleOAuth-state']);
            throw new Exception("Invalid state");
        }

        [$clientId, $clientSecret] = $this->getCredentials();

        // If all checks are Ok, try to retrieve the refresh token
        $this->retrieveRefreshToken($code, $clientId, $clientSecret);
    }

    /**
     * Retrieve and store the refresh token from google
     */
    private function retrieveRefreshToken($code, $clientId, $clientSecret)
    {
        $provider = $this->getProvider($clientId, $clientSecret);
        // Get an access token (using the authorization code grant)
        $token = $provider->getAccessToken('authorization_code', ['code' => $code]);
        $refreshToken = $token->getRefreshToken();
        // Store the token and related credentials (so we can later check if the token belongs to the saved settings)
        $this->set('refreshToken', $refreshToken);
        $this->set('refreshTokenMetadata', [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ]);
        // Renders a "success" html. It actually doesn't show any message.
        // It just closes the window after sending a message to the opener.
        $this->renderPartial('TokenSuccess', []);
    }

    /**
     * Redirects the browser to the plugin settings
     */
    private function redirectToConfig()
    {
        $url = $this->getConfigUrl();
        Yii::app()->getController()->redirect($url);
    }

    /**
     * Returns the URL for plugin settings
     * @return string
     */
    private function getConfigUrl()
    {
        return $this->api->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'configure',
                'id' => $this->id,
                'tab' => 'settings' // Used in JS to switch tabs
            ]
        );
    }

    /**
     * Returns the OAuth provider object for the specified credentials
     * @param string $clientId
     * @param string $clientSecret
     * @return League\OAuth2\Client\Provider\Google
     */
    private function getProvider($clientId, $clientSecret)
    {
        $redirectUri = $this->api->createUrl('plugins/unsecure', ['plugin' => $this->getName()]);
        $params = [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
            'accessType' => 'offline',
            'prompt' => 'consent',
        ];
        return new Google($params);
    }

    /**
     * Adds view's path to twig system
     */
    public function getPluginTwigPath()
    {
        $viewPath = dirname(__FILE__) . "/views";
        $this->getEvent()->append('add', array($viewPath));
    }

    /**
     * @inheritdoc
     * Handle changes in credentials
     */
    public function saveSettings($settings)
    {
        // Get the current credentials (before saving new settings)
        $oldClientId = $this->get('clientId');
        $oldClientSecret = $this->get('clientSecret');

        // Save settings
        parent::saveSettings($settings);

        // Get new credentials
        $clientId = $this->get('clientId');
        $clientSecret = $this->get('clientSecret');

        // If credentials changed, we need to clear the stored refresh token
        if ($clientId != $oldClientId || $clientSecret != $oldClientSecret) {
            $this->set('refreshToken', null);
            $this->set('refreshTokenMetadata', []);

            // If credentials are complete, we redirect to settings page so the user can
            // retrieve the token from Google.
            if (!empty($clientId) && !empty($clientSecret)) {
                Yii::app()->user->setFlash('success', gT('The plugin settings were saved.'));
                $this->redirectToConfig();
            }
        }
    }

    /**
     * Override mailer
     */
    public function beforeEmail()
    {
        if (!$this->get('enable')) {
            return;
        }

        $emailmethod = Yii::app()->getConfig('emailmethod');
        if ($emailmethod != "smtp") {
            return;
        }

        $emailsmtpuser = Yii::app()->getConfig('emailsmtpuser');
        if (empty($emailsmtpuser)) {
            return;
        }

        try {
            [$clientId, $clientSecret] = $this->getCredentials();
        } catch (Exception $ex) {
            $this->log("GoogleOAuthSMTP is enabled but credentials are incomplete.", CLogger::LEVEL_WARNING);
            return;
        }

        $refreshToken = $this->get("refreshToken");
        if (empty($refreshToken)) {
            $this->log("GoogleOAuthSMTP is enabled but there is no refresh token stored.", CLogger::LEVEL_WARNING);
            return;
        }

        $limeMailer = $this->getEvent()->get('mailer');
        $provider = $this->getProvider($clientId, $clientSecret);
        $limeMailer->setOAuth(new OAuth(
            [
                'provider' => $provider,
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'refreshToken' => $refreshToken,
                'userName' => $emailsmtpuser,
            ]
        ));

        $limeMailer->Username = null;
        $limeMailer->Password = null;
        $limeMailer->AuthType = 'XOAUTH2';

        // TODO: Override Host, Port and SMTPSecure (encryption mechanism)? Not sure if it makes sense to use the global settings. Seems more error prone.

        // Set "Reply To" because Gmail overrides the From/Sender with the logged user.
        $limeMailer->AddReplyTo($limeMailer->From, $limeMailer->FromName);
    }

    public function beforeControllerAction()
    {
        if (!$this->get('enable')) {
            return;
        }

        $controller = $this->getEvent()->get('controller');
        $action = $this->getEvent()->get('action');

        if ($controller == 'admin' && $action == 'globalsettings') {
            $assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__). '/assets/js');
            Yii::app()->clientScript->registerScriptFile($assetsUrl . '/globalSettingsOverride.js');
        }
    }
}
