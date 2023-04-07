<?php
require_once __DIR__ . '/vendor/autoload.php';

use Greew\OAuth2\Client\Provider\Azure;
use LimeSurvey\PluginManager\SmtpOauthPluginBase;
use PHPMailer\PHPMailer\PHPMailer;

class AzureOAuthSMTP extends SmtpOauthPluginBase
{
    protected $storage = 'DbStorage';
    protected static $description = 'Core: Adds Azure OAuth support for email sending';
    protected static $name = 'AzureOAuthSMTP';

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
        'tenantId' => [
            'type' => 'string',
            'label' => 'Tenant ID',
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
        $this->subscribe('newUnsecureRequest', 'receiveAzureResponse');
        $this->subscribe('newDirectRequest', 'redirectToAzure');

        $this->subscribe('listSMTPOauthPlugins');
        $this->subscribe('afterSelectSMTPOAuthPlugin');
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
        $settings['tenantId']['label'] = gT("Tenant ID");
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
                'message' => gT("Azure OAuth authentication requires the application to be served over HTTPS.")
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
            gT("Currently saved settings are incomplete. After saving 'Client ID', 'Client Secret' and 'Tenant ID' you will be able to validate the credentials.")
        ];
    }

    /**
     * Redirects to the Azure's authorization page
     */
    public function redirectToAzure()
    {
        $oEvent = $this->event;
        if ($oEvent->get('target') != $this->getName()) return;

        $this->redirectToAuthPage();
    }

    /**
     * Receives the response from Azure
     */
    public function receiveAzureResponse()
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
        [$clientId, $clientSecret] = $this->getCredentials();
        $tenantId = $this->get('tenantId');
        $redirectUri = $this->getRedirectUri();
        $params = [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'tenantId' => $tenantId,
            'redirectUri' => $redirectUri,
            'accessType' => 'offline',
            'prompt' => 'consent',
        ];
        return new Azure($params);
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
    public function listSMTPOauthPlugins()
    {
        $event = $this->getEvent();
        $event->append('oauthplugins', [
            get_class($this) => gT("Azure")
        ]);
    }

    public function newSMTPOAuthConfiguration()
    {
        try {
            [$clientId, $clientSecret] = $this->getCredentials();
        } catch (Exception $ex) {
            $this->log("AzureOAuthSMTP is enabled but credentials are incomplete.", CLogger::LEVEL_WARNING);
            return;
        }

        $refreshToken = $this->get("refreshToken");
        if (empty($refreshToken)) {
            $this->log("AzureOAuthSMTP is enabled but there is no refresh token stored.", CLogger::LEVEL_WARNING);
            return;
        }

        $emailAddress = $this->get("email");
        if (empty($emailAddress)) {
            $this->log("AzureOAuthSMTP is enabled but there is no email address. Please generate a new token.", CLogger::LEVEL_WARNING);
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
        $limeMailer->Host = 'smtp.office365.com';
        $limeMailer->Port = 587;
        $limeMailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }

    /**
     * @inheritdoc
     */
    protected function getAuthorizationOptions()
    {
        return [
            'scope' => [
                'https://outlook.office.com/SMTP.Send openid email offline_access ',
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeEmail()
    {
        $event = $this->getEvent();
        $limeMailer = $event->get('mailer');
        // Set "Reply To" because we need to override the From/Sender with the logged user.
        $limeMailer->AddReplyTo($limeMailer->From, $limeMailer->FromName);

        // Override the sender to avoid to match OAuth credentials.
        $from = $this->get('email');
        $event->set('from', $from);
    }

    /**
     * Override getRedirectUri to integrate parameters in the path.
     * Azure doesn't support query parameters in the redirect URI.
     */
    protected function getRedirectUri()
    {
        return $this->api->createUrl("plugins/unsecure/plugin/{$this->getName()}", []);
    }

    protected function afterRefreshTokenRetrieved($provider, $token)
    {
        $tokenValues = $token->getValues();
        if (empty($tokenValues['id_token'])) {
            throw new Exception("The token doesn't contain an id_token. This is required to get the user's email address.");
        }
        $idToken = $tokenValues['id_token'];
        // TODO: Decode the token using the public key from the provider. Could use firebase/php-jwt.
        // That would require getting the public key from the provider. See: https://github.com/TheNetworg/oauth2-azure/blob/dc095e5a6ae485be8a0c8b88a0d07616c18d484b/src/Provider/Azure.php#L376
        $idTokenParts = explode('.', $idToken);
        $idTokenPayload = $idTokenParts[1];
        $decodedToken = json_decode(base64_decode($idTokenPayload));
        $email = $decodedToken->email;
        $this->set('email', $email);
    }
}
