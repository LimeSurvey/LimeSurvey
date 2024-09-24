<?php

namespace LimeSurvey\PluginManager;

use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use PHPMailer\PHPMailer\OAuth;

abstract class SmtpOAuthPluginBase extends EmailPluginBase
{
    const SETUP_STATUS_VALID_REFRESH_TOKEN = 1;
    const SETUP_STATUS_REQUIREMENT_UNMET = 2;
    const SETUP_STATUS_INCOMPLETE_CREDENTIALS = 3;
    const SETUP_STATUS_MISSING_REFRESH_TOKEN = 4;
    const SETUP_STATUS_INVALID_REFRESH_TOKEN = 5;

    /** @var string[] The names of attributes that form part of the credentials set. Example: ['clientId', 'clientSecret'] */
    protected $credentialAttributes = [];

    protected function getRedirectUri()
    {
        return $this->api->createUrl('smtpOAuth/receiveOAuthResponse', []);
    }

    /**
     * Returns the OAuth provider object for the specified credentials
     * @param array<string,mixed> $credentials
     * @return League\OAuth2\Client\Provider\AbstractProvider
     */
    abstract protected function getProvider($credentials);

    /**
     * Returns true if the credentials have changed
     */
    protected function haveCredentialsChanged($oldCredentials, $newCredentials)
    {
        foreach ($this->credentialAttributes as $attribute) {
            if ($oldCredentials[$attribute] != $newCredentials[$attribute]) {
                return true;
            }
        }
        return false;
    }

    /**
     * Clears the stored refresh token
     */
    protected function clearRefreshToken()
    {
        $this->set('refreshToken', null);
        $this->set('refreshTokenMetadata', []);
        $this->set('email', null);
    }

    /**
     * Saves the specified refresh token and associated credentials
     */
    protected function saveRefreshToken($refreshToken, $credentials)
    {
        $this->set('refreshToken', $refreshToken);
        $this->set('refreshTokenMetadata', $credentials);
    }

    /**
     * @inheritdoc
     * Handle changes in credentials
     */
    public function saveSettings($settings)
    {
        // Get the current credentials (before saving new settings)
        $oldCredentials = $this->getCredentials();

        // Save settings
        parent::saveSettings($settings);

        // Get new credentials
        $newCredentials = $this->getCredentials();

        // If credentials changed, we need to clear the stored refresh token
        if ($this->haveCredentialsChanged($oldCredentials, $newCredentials)) {
            $this->clearRefreshToken();
        }
    }

    /**
     * Returns true if the specified refresh token is valid
     * @param string $refreshToken
     * @param array<string,mixed> $credentials
     * @return bool
     */
    protected function validateRefreshToken($refreshToken, $credentials)
    {
        $refreshTokenMetadata = $this->get('refreshTokenMetadata') ?? [];

        // Check that the credentials match the ones used to get the refresh token
        foreach ($this->credentialAttributes as $attribute) {
            if (empty($refreshTokenMetadata[$attribute])) {
                return false;
            }
            if ($credentials[$attribute] != $refreshTokenMetadata[$attribute]) {
                return false;
            }
        }

        $provider = $this->getProvider($credentials);
        try {
            $token = $provider->getAccessToken(
                new RefreshToken(),
                ['refresh_token' => $refreshToken]
            );
            // TODO: Handle token with invalid scope (ie. missing https://mail.google.com/)
        } catch (IdentityProviderException $ex) {
            // Don't do anything. Just leave $token unset.
        }
        return !empty($token);
    }

    /**
     * Returns the credentials according to the plugin specified credential attributes
     * @return array<string,mixed> The credentials array
     * @throws Exception if credentials are incomplete.
     */
    protected function getCredentials()
    {
        $credentials = [];
        foreach ($this->credentialAttributes as $attribute) {
            $credentials[$attribute] = $this->get($attribute);
        }
        //if (!$this->validateCredentials($credentials)) {
        //    // TODO: Should we use a different exception class?
        //    throw new \Exception("Incomplete OAuth settings");
        //}
        return $credentials;
    }

    /**
     * Checks if the credentials are valid.
     * By default, it checks if the credentials are not empty.
     * @param array<string,mixed> $credentials
     * @return bool True if the credentials are valid, false otherwise.
     */
    protected function validateCredentials($credentials)
    {
        $incomplete = false;
        foreach ($this->credentialAttributes as $attribute) {
            if (empty($credentials[$attribute])) {
                $incomplete = true;
                break;
            }
        }
        return !$incomplete;
    }

    /**
     * Default handler for the afterReceiveOAuthResponse event
     */
    public function afterReceiveOAuthResponse()
    {
        $event = $this->getEvent();
        if (empty($event)) {
            throw new \CHttpException(403);
        }
        $code = $event->get('code');
        $credentials = $this->getCredentials();

        $this->retrieveRefreshToken($code, $credentials);
    }

    /**
     * Retrieve and store the refresh token
     */
    private function retrieveRefreshToken($code, $credentials)
    {
        $provider = $this->getProvider($credentials);

        // Get an access token (using the authorization code grant)
        $authOptions = $this->getAuthorizationOptions();
        $authOptions = array_merge($authOptions ?? [], ['code' => $code]);
        $token = $provider->getAccessToken('authorization_code', $authOptions);
        $refreshToken = $token->getRefreshToken();

        // Store the token and related credentials (so we can later check if the token belongs to the saved settings)
        $this->saveRefreshToken($refreshToken, $credentials);

        // Do additional processing
        $this->afterRefreshTokenRetrieved($provider, $token);
    }

    /**
     * This method is invoked after the refresh token is retrieved.
     * The default implementation tries to retrieve the email address of the authenticated user.
     * You may override this method to do additional processing.
     * @param AbstractProvider $provider
     * @param AccessTokenInterface $token
     */
    protected function afterRefreshTokenRetrieved($provider, $token)
    {
        $owner = $provider->getResourceOwner($token);
        $this->set('email', $owner->getEmail());
    }

    /**
     * Default handler for beforeRedirectToAuthPage
     */
    public function beforeRedirectToAuthPage()
    {
        $event = $this->getEvent();
        if (empty($event)) {
            throw new \CHttpException(403);
        }
        $credentials = $this->getCredentials();
        $provider = $this->getProvider($credentials);
        $options = $this->getAuthorizationOptions();
        $authUrl = $provider->getAuthorizationUrl($options);
        $event->set('authUrl', $authUrl);
        $event->set('state', $provider->getState());
    }

    /**
     * Returns the OAuth options for authorization (like the scope)
     * @return array<string,mixed>
     */
    abstract protected function getAuthorizationOptions();

    /**
     * Returns true if the plugin is active
     * @return bool
     */
    protected function isActive()
    {
        $pluginModel = \Plugin::model()->findByPk($this->id);
        return $pluginModel->active;
    }

    /**
     * Returns the setup status of the plugin.
     */
    protected function getSetupStatus()
    {
        $credentials = $this->getCredentials();
        if (!$this->validateCredentials($credentials)) {
            return self::SETUP_STATUS_INCOMPLETE_CREDENTIALS;
        }

        $refreshToken = $this->get('refreshToken');
        if (empty($refreshToken)) {
            return self::SETUP_STATUS_MISSING_REFRESH_TOKEN;
        }

        if (!$this->validateRefreshToken($refreshToken, $credentials)) {
            return self::SETUP_STATUS_INVALID_REFRESH_TOKEN;
        }

        return self::SETUP_STATUS_VALID_REFRESH_TOKEN;
    }

    protected function getSetupStatusDescription($setupStatus)
    {
        switch ($setupStatus) {
            case self::SETUP_STATUS_INCOMPLETE_CREDENTIALS:
                return gT("Currently saved credentials are incomplete.");
            case self::SETUP_STATUS_MISSING_REFRESH_TOKEN:
                return gT("No OAuth token.");
            case self::SETUP_STATUS_INVALID_REFRESH_TOKEN:
                return gT("The saved token isn't valid. You need to get a new one.");
            case self::SETUP_STATUS_VALID_REFRESH_TOKEN:
                return gT("Configuration is complete.");
            default:
                return '';
        }
    }

    protected function getHealthStatusClass($setupStatus)
    {
        switch ($setupStatus) {
            case self::SETUP_STATUS_INCOMPLETE_CREDENTIALS:
            case self::SETUP_STATUS_MISSING_REFRESH_TOKEN:
            case self::SETUP_STATUS_INVALID_REFRESH_TOKEN:
                return 'danger';
            case self::SETUP_STATUS_VALID_REFRESH_TOKEN:
                return 'success';
            default:
                return '';
        }
    }

    protected function getHealthStatusIcon($statusClass)
    {
        $icon = '';
        switch ($statusClass) {
            case 'danger':
                $icon = "ri-close-fill text-{$statusClass}";
                break;
            case 'success':
                $icon = "ri-check-fill text-{$statusClass}";
                break;
            case 'warning':
                $icon = "ri-alert-fill text-{$statusClass}";
                break;
        }

        return !empty($icon) ? "<span class=\"{$icon}\"></span>" : '';
    }

    /**
     * @inheritdoc
     */
    public function getHealthStatusText()
    {
        // Check prerequisites
        if (!(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')) {
            return $this->getHealthStatusIcon('danger') . " " . gT("OAuth authentication requires the application to be served over HTTPS.");
        }

        $setupStatus = $this->getSetupStatus();
        $statusClass = $this->getHealthStatusClass($setupStatus);
        $statusText = $this->getHealthStatusIcon($statusClass) . " " . $this->getSetupStatusDescription($setupStatus);

        if (
            $setupStatus === self::SETUP_STATUS_MISSING_REFRESH_TOKEN
            || $setupStatus === self::SETUP_STATUS_INVALID_REFRESH_TOKEN
            || $setupStatus === self::SETUP_STATUS_VALID_REFRESH_TOKEN
        ) {
            $getTokenUrl = $this->api->createUrl('smtpOAuth/prepareRefreshTokenRequest', ['plugin' => get_class($this)]);
            $getTokenLink = ' <a href="' . $getTokenUrl . '">' . gT("Get new token") . '</a>';
            $statusText .= $getTokenLink;
        }

        return $statusText;
    }

    /**
     * @inheritdoc
     */
    public function getPluginSettings($getValues = true)
    {
        $settings = parent::getPluginSettings($getValues);

        // Add the "current email" setting if the email for the current token is set
        $emailAddress = $this->get('email');
        if (!empty($emailAddress)) {
            $settings['currentEmail'] = [
                'type' => 'string',
                'label' => gT('Token owner email address'),
                'help' => gT('This is the email address used to create the current authentication token. Please note that all emails will be sent from this address.'),
                'htmlOptions' => [
                    'readonly' => true,
                ],
                'current' => $emailAddress,
            ];
        }

        // Add the "information" setting
        $statusAlert = $this->getSetupStatusAlert();
        if (!empty($statusAlert)) {
            $settings['information'] = [
                'type' => 'info',
                'content' => $statusAlert,
            ];
        }

        return $settings;
    }

    /**
     * Renders the contents of the "information" setting depending on
     * settings and token validity.
     * @return string
     */
    protected function getSetupStatusAlert()
    {
        $setupStatus = $this->getSetupStatus();

        // Don't show alert for successful setup
        if ($setupStatus === self::SETUP_STATUS_VALID_REFRESH_TOKEN) {
            return '';
        }

        $statusClass = $this->getHealthStatusClass($setupStatus);
        $statusText = $this->getHealthStatusIcon($statusClass) . " " . $this->getSetupStatusDescription($setupStatus);

        return "<div class=\"alert alert-{$statusClass}\">{$statusText}</div>";
    }

    /**
     * Default handler for the MailerConstruct event, triggered during LimeMailer initialization.
     * The event is expected to be dispatched specifically for the selected email plugin.
     */
    public function MailerConstruct()
    {
        $event = $this->getEvent();
        $mailer = $event->get('mailer');
        $this->setupMailer($mailer);
    }

    /**
     * Applies the basic OAuth configuration to the given LimeMailer
     * @param LimeMailer $mailer
     */
    protected function setupMailer($mailer)
    {
        $mailer->IsSMTP();
        $mailer->SMTPAuth = true;
        $mailer->Username = null;
        $mailer->Password = null;
        $mailer->AuthType = 'XOAUTH2';
        $config = $this->getOAuthConfigForMailer();
        if (empty($config)) {
            throw new \Exception('Invalid OAuth configuration');
        }
        $mailer->setOAuth(new OAuth($config));
    }

    /**
     * Returns the OAuth configuration for PHPMailer
     * @return array<string,mixed>|null
     */
    abstract protected function getOAuthConfigForMailer();
}
