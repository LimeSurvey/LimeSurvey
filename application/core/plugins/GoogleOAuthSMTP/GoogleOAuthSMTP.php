<?php

use League\OAuth2\Client\Provider\Google;
use LimeSurvey\Datavalueobjects\SmtpOAuthPluginOption;
use LimeSurvey\PluginManager\SmtpOauthPluginBase;
use PHPMailer\PHPMailer\PHPMailer;

class GoogleOAuthSMTP extends SmtpOauthPluginBase
{
    protected $storage = 'DbStorage';
    protected static $description = 'Core: Adds Google OAuth support for email sending';
    protected static $name = 'GoogleOAuthSMTP';

    /** @inheritdoc, this plugin doesn't have any public method */
    public $allowedPublicMethods = array();

    protected $settings = [
        'help' => [
            'type' => 'info',
            'content' => '',
        ],
        'clientId' => [
            'type' => 'string',
            'label' => 'Client ID',
        ],
        'clientSecret' => [
            'type' => 'string',
            'label' => 'Client Secret',
        ],
        'currentEmail' => [
            'type' => 'string',
            'label' => 'Saved Token Owner',
            'htmlOptions' => [
                'readonly' => true,
            ],
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

        $this->subscribe('listSMTPOAuthPlugins');
        //$this->subscribe('afterSelectSMTPOAuthPlugin');
        $this->subscribe('newSMTPOAuthConfiguration');

        $this->subscribe('beforeEmail', 'beforeEmail');
        $this->subscribe('beforeSurveyEmail', 'beforeEmail');
        $this->subscribe('beforeTokenEmail', 'beforeEmail');
    }

    /**
     * @inheritdoc
     * Update the information content
     */
    public function getPluginSettings($getValues = true)
    {
        $settings = parent::getPluginSettings($getValues);
        $settings['help']['content'] = $this->getHelpContent();
        $settings['clientId']['label'] = gT("Client ID");
        $settings['clientSecret']['label'] = gT("Client Secret");
        $settings['information']['content'] = $this->getRefreshTokenInfo();

        $emailAddress = $this->get('email');
        if (!empty($emailAddress)) {
            $settings['currentEmail']['label'] = gT('Saved Token Owner');
            $settings['currentEmail']['help'] = gT('This is the email address used to create the current authentication token. Please note all emails will be sent from this address.');
            $settings['currentEmail']['current'] = $emailAddress;
        } else {
            unset($settings['currentEmail']);
        }

        return $settings;
    }

    private function getHelpContent()
    {
        $this->subscribe('getPluginTwigPath');
        $data = [
            'redirectUri' => $this->getRedirectUri(),
            'isHttp' => !(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
        ];
        return Yii::app()->twigRenderer->renderPartial('/Help.twig', $data);

        // Translations here just so the translations bot can pick them up.
        $lang = [
            gT("Help"),
            gT("Prerequisites:"),
            gT("Access LimeSurvey over HTTPS."),
            gT("Currently not served over HTTPS"),
            gT("Instructions:"),
            gT("Setup the OAuth 2.0 Web Application in %s."),
            gT("Google Cloud Platform Console"),
            gT("Redirect URI:"),
            gT("You can find more details %s."),
            gT("here"),
            gT("Activate the plugin."),
            gT("Set the 'Client ID' and 'Client Secret' below and save the settings."),
            gT("Click the 'Get Token' button to open Google's consent screen in a new window."),
            gT("Follow the steps in the consent screen and check the requested permissions."),
            gT("Switch the 'Enabled' setting to 'On' and save."),
        ];
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

        if (!$this->isActive()) {
            return Yii::app()->twigRenderer->renderPartial('/ErrorMessage.twig', [
                'message' => gT("The plugin must be activated before finishing the configuration.")
            ]);
        }

        if (!(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')) {
            return Yii::app()->twigRenderer->renderPartial('/ErrorMessage.twig', [
                'message' => gT("Google OAuth authentication requires the application to be served over HTTPS.")
            ]);
        }

        $refreshToken = $this->get('refreshToken');

        $data = [
            'tokenUrl' => $this->api->createUrl('plugins/direct', ['plugin' => $this->getName()]),
            'reloadUrl' => $this->getConfigUrl(),
            'buttonCaption' => gT("Get token"),
        ];

        // If there is no refresh token stored, ask the user to get one.
        if (empty($refreshToken)) {
            $data['class'] = "warning";
            $data['message'] = gT("Get token for currently saved Client ID and Secret.");
            return Yii::app()->twigRenderer->renderPartial('/GetTokenMessage.twig', $data);
        }

        // Check if the refresh token is still valid. If it's not, ask the user to get a new one.
        if (!$this->validateRefreshToken($refreshToken, $clientId, $clientSecret)) {
            $data['class'] = "danger";
            $data['message'] = gT("The saved token isn't valid. You need to get a new one.");
            return Yii::app()->twigRenderer->renderPartial('/GetTokenMessage.twig', $data);
        }

        // If we got here, inform the user everything is Ok.
        $data['class'] = "success";
        $data['message'] = gT("Configuration is complete. If settings are changed, you will need to re-validate the credentials.");
        $data['buttonCaption'] = gT("Replace token");
        return Yii::app()->twigRenderer->renderPartial('/GetTokenMessage.twig', $data);

        // Translations here just so the translations bot can pick them up.
        $lang = [
            gT("Currently saved settings are incomplete. After saving both 'Client ID' and 'Client Secret' you will be able to validate the credentials.")
        ];
    }

    /**
     * Redirects to the Google's authorization page
     */
    public function redirectToGoogle()
    {
        $oEvent = $this->event;
        if ($oEvent->get('target') != $this->getName()) return;

        $this->redirectToAuthPage();
    }

    /**
     * Receives the response from Google
     */
    public function receiveGoogleResponse()
    {
        $event = $this->event;
        if ($event->get('target') != $this->getName()) return;

        $this->receiveOAuthResponse();
    }

    /**
     * @inheritdoc
     */
    protected function getProvider($clientId, $clientSecret)
    {
        $redirectUri = $this->getRedirectUri();
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
     * Adds the plugin to the list of SMTP OAuth plugins
     */
    public function listSMTPOAuthPlugins()
    {
        $event = $this->getEvent();
        $event->append('oauthplugins', [
            'google' => new SmtpOAuthPluginOption($this->getId(), gT("Google"), get_class($this))
        ]);
    }

    /**
     * Handles the afterSelectSMTPOAuthPlugin event, triggered when the plugin
     * is selected as the SMTP OAuth plugin in Global Settings
     */
    //public function afterSelectSMTPOAuthPlugin()
    //{
    //    
    //}

    public function newSMTPOAuthConfiguration()
    {
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

        $emailAddress = $this->get("email");
        if (empty($emailAddress)) {
            $this->log("GoogleOAuthSMTP is enabled but there is no email address. Please generate a new token.", CLogger::LEVEL_WARNING);
            return;
        }

        $event = $this->getEvent();

        $provider = $this->getProvider($clientId, $clientSecret);
        $config = [
            'provider' => $provider,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'refreshToken' => $refreshToken,
            'userName' => $emailAddress,
        ];

        $event->set('oauthconfig', $config);

        $limeMailer = $event->get('mailer');
        $limeMailer->Host = 'smtp.gmail.com';
        $limeMailer->Port = 465;
        $limeMailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    }

    /**
     * @inheritdoc
     */
    protected function getAuthorizationOptions()
    {
        return [
            'scope' => [
                'https://mail.google.com/'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeEmail()
    {
        $limeMailer = $this->getEvent()->get('mailer');
        // Set "Reply To" because Gmail overrides the From/Sender with the logged user.
        $limeMailer->AddReplyTo($limeMailer->From, $limeMailer->FromName);
    }
}
