<?php

use LimeSurvey\PluginManager\SmtpOAuthPluginBase;
use PHPMailer\PHPMailer\PHPMailer;

class AzureOAuthSMTP extends SmtpOAuthPluginBase
{
    protected $storage = 'DbStorage';
    protected static $description = 'Core: Adds Azure OAuth support for email sending';
    protected static $name = 'AzureOAuthSMTP';

    /** @inheritdoc this plugin doesn't have any public method */
    public $allowedPublicMethods = [];

    /** @inheritdoc */
    protected $credentialAttributes = ['clientId', 'clientSecret', 'tenantId'];

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
    ];

    public function init()
    {
        // This plugin is only compatible with PHP 7.3 and above,
        // while LimeSurvey is still compatible with PHP 7.2, so
        // we need to make sure we only load composer's autoload
        // on PHP 7.3 and above. We also need to prevent activation
        // of the plugin on PHP 7.2 and below.
        // TODO: Remove this once we drop support for PHP 7.2
        $this->subscribe('beforeActivate');
        if (!(PHP_VERSION_ID >= 70300)) {
            return;
        }
        require_once __DIR__ . '/vendor/autoload.php';

        $this->subscribe('listEmailPlugins');
        $this->subscribe('afterSelectEmailPlugin');
        $this->subscribe('MailerConstruct');    // Handler defined in SmtpOAuthPluginBase
        $this->subscribe('beforePrepareRedirectToAuthPage');
        $this->subscribe('beforeRedirectToAuthPage');   // Handler defined in SmtpOAuthPluginBase
        $this->subscribe('afterReceiveOAuthResponse');  // Handler defined in SmtpOAuthPluginBase

        $this->subscribe('beforeEmailDispatch');
    }

    /**
     * TODO: Remove this once we drop support for PHP 7.2
     */
    public function beforeActivate()
    {
        if (!(PHP_VERSION_ID >= 70300)) {
            $event = $this->getEvent();
            $event->set('success', false);
            $event->set('message', gT("This plugin requires PHP version 7.3 or higher."));
        }
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

        return $settings;
    }

    private function getHelpContent()
    {
        $this->subscribe('getPluginTwigPath');
        $data = [
            'redirectUri' => $this->getRedirectUri(),
            'isHttps' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
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
     * @inheritdoc
     */
    protected function getProvider($credentials)
    {
        $redirectUri = $this->getRedirectUri();
        $params = [
            'clientId' => $credentials['clientId'],
            'clientSecret' => $credentials['clientSecret'],
            'tenantId' => $credentials['tenantId'],
            'redirectUri' => $redirectUri,
            'accessType' => 'offline',
            'prompt' => 'consent',
        ];
        /**
         * @psalm-suppress UndefinedClass
         */
        return new Greew\OAuth2\Client\Provider\Azure($params);
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
     * Adds the plugin to the list of email plugins
     */
    public function listEmailPlugins()
    {
        $event = $this->getEvent();
        $event->append('plugins', [
            'azure' => $this->getEmailPluginInfo()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getOAuthConfigForMailer()
    {
        try {
            $credentials = $this->getCredentials();
            $clientId = $credentials['clientId'];
            $clientSecret = $credentials['clientSecret'];
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

        $provider = $this->getProvider($credentials);
        $config = [
            'provider' => $provider,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'refreshToken' => $refreshToken,
            'userName' => $emailAddress,
        ];

        return $config;
    }

    /**
     * @inheritdoc
     * Add Azure specific settings to the mailer
     */
    protected function setupMailer($mailer)
    {
        parent::setupMailer($mailer);
        $mailer->Host = 'smtp.office365.com';
        $mailer->Port = 587;
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }

    /**
     * Handles the afterSelectEmailPlugin event, triggered when the plugin
     * is selected as email plugin in Global Settings
     */
    public function afterSelectEmailPlugin()
    {
        $setupStatus = $this->getSetupStatus();
        if ($setupStatus !== self::SETUP_STATUS_VALID_REFRESH_TOKEN) {
            $event = $this->getEvent();
            $event->set('warning', sprintf(gT("The %s plugin is not configured correctly. Please check the plugin settings."), self::getName()));
        }
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
     * Handles the beforeEmailDispatch event, triggered right before an email is sent.
     * This is used to override the sender with the logged user, and to set the "Reply To" header,
     * because Azure doesn't accept the sender to be different from the logged user.
     */
    public function beforeEmailDispatch()
    {
        // Don't do anything if the current plugin is not the one selected.
        if (!$this->isCurrentEmailPlugin()) {
            return;
        }

        $event = $this->getEvent();

        /** @var LimeMailer */
        $limeMailer = $event->get('mailer');
        // Set "Reply To" because we need to override the From/Sender with the logged user.
        $limeMailer->AddReplyTo($limeMailer->From, $limeMailer->FromName);

        // Override the sender to avoid to match OAuth credentials.
        $from = $this->get('email');
        $limeMailer->setFrom($from);
    }

    /**
     * @inheritdoc
     */
    protected function afterRefreshTokenRetrieved($provider, $token)
    {
        $tokenValues = $token->getValues();
        if (empty($tokenValues['id_token'])) {
            throw new Exception("The token doesn't contain an id_token. This is required to get the user's email address.");
        }
        $idToken = $tokenValues['id_token'];
        $idTokenParts = explode('.', $idToken);
        $idTokenPayload = $idTokenParts[1];
        $decodedToken = json_decode(base64_decode($idTokenPayload));
        $email = $decodedToken->email;
        $this->set('email', $email);
    }

     /**
     * @inheritdoc
     */
    protected function getDisplayName()
    {
        return 'Azure';
    }

    /**
     * Handles the beforePrepareRedirectToAuthPage event, triggered before the
     * page with the "Get Token" button is rendered.
     */
    public function beforePrepareRedirectToAuthPage()
    {
        $event = $this->getEvent();
        $event->set('width', 600);
        $event->set('height', 800);
        $event->set('providerName', $this->getDisplayName());

        $setupStatus = $this->getSetupStatus();
        $description = $this->getSetupStatusDescription($setupStatus);
        $event->setContent($this, $description);
    }

    /**
     * @inheritdoc
     */
    protected function getSetupStatusAlert()
    {
        if (Yii::app()->getUrlManager()->getUrlFormat() == CUrlManager::GET_FORMAT) {
            return "<div class=\"alert alert-danger\">" . gT("Azure doesn't accept redirect URIs with query parameters when using personal accounts. This plugin will not work properly with the current URL manager configuration.") . "</div>";
        }

        return parent::getSetupStatusAlert();
    }
}
