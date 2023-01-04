<?php
require_once __DIR__ . '/vendor/autoload.php';

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Grant\RefreshToken;

class GoogleOAuthSMTP extends PluginBase
{
    protected $storage = 'DbStorage';
    protected static $description = 'Core: Adds Google OAuth support for email sending';
    protected static $name = 'GoogleOAuthSMTP';

    protected $settings = [
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
    }

    /**
     * @inheritdoc
     * Update the information content
     */
    public function getPluginSettings($getValues = true)
    {
        $this->settings['information']['content'] = $this->getRefreshTokenInfo();
        return parent::getPluginSettings($getValues);
    }

    private function getRefreshTokenInfo()
    {
        $this->subscribe('getPluginTwigPath');

        $clientId = $this->get('clientId');
        $clientSecret = $this->get('clientSecret');
        if (empty($clientId) || empty($clientSecret)) {
            return Yii::app()->twigRenderer->renderPartial('/IncompleteSettingsMessage.twig', []);
        }

        $refreshToken = $this->get('refreshToken');
        if (empty($refreshToken)) {
            $class = "warning";
            $message = gT("Get token for currently saved Client ID and Secret.");
        } elseif (!$this->validateRefreshToken($refreshToken, $clientId, $clientSecret)) {
            $class = "danger";
            $message = gT("The saved token isn't valid. You need to get a new one.");
        } else {
            return Yii::app()->twigRenderer->renderPartial('/ValidTokenMessage.twig', []);
        }
        return Yii::app()->twigRenderer->renderPartial('/GetTokenMessage.twig', [
            'class' => $class,
            'message' => $message,
            'tokenUrl' => $this->api->createUrl('plugins/direct', ['plugin' => $this->getName()])
        ]);

        // Translations here just so the translations bot can pick them up.
        $lang = [
            gT("Currently saved settings are incomplete. After saving both 'Client ID' and 'Client Secret' you will be able to validate the credentials."),
            gT("Get token")
        ];
    }

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

        if (!empty($refreshTokenMetadata['expires']) && $refreshTokenMetadata['expires'] < time()) {
            return false;
        }

        $provider = $this->getProvider($clientId, $clientSecret);
        $token = $provider->getAccessToken(
            new RefreshToken(),
            ['refresh_token' => $refreshToken]
        );
        return !empty($token);
    }

    public function redirectToGoogle()
    {
        $oEvent = $this->event;
        if ($oEvent->get('target') != $this->getName()) return;

        $clientId = $this->get('clientId');
        $clientSecret = $this->get('clientSecret');
        if (empty($clientId) || empty($clientSecret)) {
            throw new Exception("Invalid Google OAuth settings");
        }

        $provider = $this->getProvider($clientId, $clientSecret);
        $options = [
            'scope' => [
                'https://mail.google.com/'
            ]
        ];
        $authUrl = $provider->getAuthorizationUrl($options);
        $this->setSession('oauth2state', $provider->getState());
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Receives the response after Google redirection
     */
    public function receiveGoogleResponse()
    {
        $oEvent = $this->event;
        if ($oEvent->get('target') != $this->getName()) return;

        /** @var LSHttpRequest */
        $request = Yii::app()->getRequest();

        $oauth2state = $this->getSession('oauth2state');
        if (empty($oauth2state)) {
            //Yii::app()->setFlashMessage(gT("Invalid request."), 'error');
            $this->redirectToConfig();
        }

        $clientId = $this->get('clientId');
        $clientSecret = $this->get('clientSecret');
        if (empty($clientId) || empty($clientSecret)) {
            throw new Exception("Invalid Google OAuth settings");
        }

        $code = $request->getParam("code");
        $state = $request->getParam("state");
        if ($code) {
            if ($state == $oauth2state) {
                $this->retrieveRefreshToken($code, $clientId, $clientSecret);
            } else {
                $this->cleanupSession();
                throw new Exception("Invalid state");
            }
        }
    }

    private function retrieveRefreshToken($code, $clientId, $clientSecret)
    {
        $provider = $this->getProvider($clientId, $clientSecret);
        // Get an access token (using the authorization code grant)
        $token = $provider->getAccessToken('authorization_code', ['code' => $code]);
        $refreshToken = $token->getRefreshToken();
        $this->set('refreshToken', $refreshToken);
        $this->set('refreshTokenMetadata', [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'expires' => $token->getExpires(),
        ]);

        Yii::app()->setFlashMessage(gT("Refresh Token saved."), 'success');
        $this->redirectToConfig();
    }

    private function cleanupSession()
    {
        unset($_SESSION['googleOAuth']);
    }

    private function getSession($key, $default = null) {
        return $_SESSION['googleOAuth'][$key] ?? $default;
    }

    private function setSession($key, $value) {
        $_SESSION['googleOAuth'][$key] = $value;
    }

    private function redirectToConfig()
    {
        $url = $this->getConfigUrl();
        Yii::app()->getController()->redirect($url);
    }

    private function getConfigUrl()
    {
        return $this->api->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'configure',
                'id' => $this->id,
                'tab' => 'settings'
            ]
        );
    }

    private function getProvider($clientId, $clientSecret)
    {
        $redirectUri = $this->api->createUrl('plugins/unsecure', ['plugin' => $this->getName()]);
        $params = [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
            'accessType' => 'offline'
        ];
        $provider = new Google($params);
        return $provider;
    }

    /**
     * Add some views for this and other plugin
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
        $oldClientId = $this->get('clientId');
        $oldClientSecret = $this->get('clientSecret');

        parent::saveSettings($settings);

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
}
