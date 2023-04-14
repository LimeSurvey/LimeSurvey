<?php

namespace LimeSurvey\PluginManager;

use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use LimeMailer;
use LimeSurvey\Datavalueobjects\SmtpOAuthPluginMetadata;

abstract class SmtpOauthPluginBase extends PluginBase
{
    const SETUP_STATUS_INCOMPLETE_CREDENTIALS = 0;
    const SETUP_STATUS_MISSING_REFRESH_TOKEN = 1;
    const SETUP_STATUS_INVALID_REFRESH_TOKEN = 2;
    const SETUP_STATUS_VALID_REFRESH_TOKEN = 3;

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

            // If credentials are complete, we redirect to settings page so the user can
            // retrieve a new token.
            if ($this->validateCredentials($newCredentials)) {
                \Yii::app()->user->setFlash('success', gT('The plugin settings were saved.'));
                $this->redirectToConfig();
            }
        }
    }

    /**
     * Redirects the browser to the plugin settings
     */
    protected function redirectToConfig()
    {
        $url = $this->getConfigUrl();
        \Yii::app()->getController()->redirect($url);
    }

    /**
     * Returns the URL for plugin settings
     * @return string
     */
    protected function getConfigUrl()
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
        $code = $event->get('code');
        $credentials = $this->getCredentials();

        $this->retrieveRefreshToken($code, $credentials);
    }

    /**
     * Retrieve and store the refresh token from google
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
    protected function getAuthorizationOptions()
    {
        return [];
    }

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
     * Handles the afterSelectSMTPOAuthPlugin event, triggered when the plugin
     * is selected as the SMTP OAuth plugin in Global Settings
     */
    public function afterSelectSMTPOAuthPlugin()
    {
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

    /**
     * @inheritdoc
     */
    public function getStatusText()
    {
        if (!(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')) {
            return $this->formatStatusText(gT("OAuth authentication requires the application to be served over HTTPS."), 'danger');
        }

        $getTokenUrl = $this->api->createUrl('smtpOAuth/prepareRefreshTokenRequest', ['plugin' => get_class($this)]);
        $getTokenLink = ' <a href="' . $getTokenUrl . '">' . gT("Get new token") . '</a>';

        $setupStatus = $this->getSetupStatus();
        switch ($setupStatus) {
            case self::SETUP_STATUS_INCOMPLETE_CREDENTIALS:
                return $this->formatStatusText(gT("Currently saved credentials are incomplete."), 'danger');
            case self::SETUP_STATUS_MISSING_REFRESH_TOKEN:
                return $this->formatStatusText(gT("No OAuth token.") . $getTokenLink, 'danger');
            case self::SETUP_STATUS_INVALID_REFRESH_TOKEN:
                return $this->formatStatusText(gT("The saved token isn't valid. You need to get a new one.") . $getTokenLink, 'danger');
            case self::SETUP_STATUS_VALID_REFRESH_TOKEN:
                return $this->formatStatusText(gT("Configuration is complete.") . $getTokenLink, 'success');
        }
    }

    protected function formatStatusText($statusText, $class = 'success')
    {
        switch ($class) {
            case 'success':
                $icon = '<span class="ri-check-fill text-success"></span> ';

                break;
            case 'danger':
                $icon = '<span class="ri-close-circle-line text-danger"></span> ';
                break;
            default:
                $icon = '';
        }
        return $icon . $statusText;
    }

    /**
     * Returns true if the plugin is the currently selected SMTP OAuth plugin
     * @return bool
     */
    protected function isCurrentSMTPOAuthHandler()
    {
        if (LimeMailer::MethodOAuth2Smtp !== \Yii::app()->getConfig('emailmethod')) {
            return false;
        }
        return get_class($this) == \Yii::app()->getConfig('emailoauthplugin');
    }

    /**
     * Returns the setting definition to show the current email address
     * @return array<string,mixed>|null
     */
    protected function getCurrentEmailSetting()
    {
        $emailAddress = $this->get('email');
        if (!empty($emailAddress)) {
            return [
                'type' => 'string',
                'label' => gT('Saved Token Owner'),
                'help' => gT('This is the email address used to create the current authentication token. Please note all emails will be sent from this address.'),
                'htmlOptions' => [
                    'readonly' => true,
                ],
                'current' => $emailAddress,
            ];
        }
    }

    /**
     * Returns the provider's name
     * @return string
     */
    abstract protected function getProviderName();

    /**
     * Returns the plugin's metadata
     * @return SmtpOAuthPluginMetadata
     */
    protected function getOwnMetadata()
    {
        return new SmtpOAuthPluginMetadata($this->getId(), $this->getProviderName(), get_class($this));
    }
}
