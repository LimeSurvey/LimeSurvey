<?php

namespace LimeSurvey\PluginManager;

use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

abstract class SmtpOauthPluginBase extends PluginBase
{
    protected $cliendId = null;
    protected $clientSecret = null;

    protected function getRedirectUri()
    {
        return $this->api->createUrl('plugins/unsecure', ['plugin' => $this->getName()]);
    }

    /**
     * Returns the OAuth provider object for the specified credentials
     * @param string $clientId
     * @param string $clientSecret
     * @return League\OAuth2\Client\Provider\AbstractProvider
     */
    abstract protected function getProvider($clientId, $clientSecret);

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
            $this->set('email', null);

            // If credentials are complete, we redirect to settings page so the user can
            // retrieve a new token.
            if (!empty($clientId) && !empty($clientSecret)) {
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
     * @param string $clientId
     * @param string $clientSecret
     * @return bool
     */
    protected function validateRefreshToken($refreshToken, $clientId, $clientSecret)
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
            // TODO: Handle token with invalid scope (ie. missing https://mail.google.com/)
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
    protected function getCredentials()
    {
        $clientId = $this->get('clientId');
        $clientSecret = $this->get('clientSecret');
        if (empty($clientId) || empty($clientSecret)) {
            // TODO: Is it ok to check this here? If it's ok, should we use a different exception class?
            throw new \Exception("Incomplete OAuth settings");
        }
        return [$clientId, $clientSecret];
    }

    /**
     * Receives the response from OAuth provider
     */
    protected function receiveOAuthResponse()
    {
        /** @var LSHttpRequest */
        $request = \Yii::app()->getRequest();

        $code = $request->getParam("code");
        if (empty($code)) {
            throw new \Exception("Invalid request");
        }

        $sessionVar = self::getName() . '-state';
        $oauth2state = \Yii::app()->session[$sessionVar];
        if (empty($oauth2state)) {
            throw new \Exception("Invalid state");
        }

        $state = $request->getParam("state");
        if ($state != $oauth2state) {
            unset(\Yii::app()->session[$sessionVar]);
            throw new \Exception("Invalid state");
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
        $authOptions = $this->getAuthorizationOptions();
        /*if (!empty($authOptions)) {
            foreach ($authOptions as $key => $value) {
                if (is_array($value)) {
                    $authOptions[$key] = implode(" ", $value);
                }
            }
        }*/
        $authOptions = array_merge($authOptions ?? [], ['code' => $code]);
        $token = $provider->getAccessToken('authorization_code', $authOptions);
        $refreshToken = $token->getRefreshToken();
        // Store the token and related credentials (so we can later check if the token belongs to the saved settings)
        $this->set('refreshToken', $refreshToken);
        $this->set('refreshTokenMetadata', [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ]);

        $this->afterRefreshTokenRetrieved($provider, $token);

        // Renders a "success" html. It actually doesn't show any message.
        // It just closes the window after sending a message to the opener.
        $this->renderPartial('TokenSuccess', []);
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
     * Redirects to the OAuth provider authorization page
     */
    protected function redirectToAuthPage()
    {
        [$clientId, $clientSecret] = $this->getCredentials();
        $provider = $this->getProvider($clientId, $clientSecret);
        $options = $this->getAuthorizationOptions();
        $authUrl = $provider->getAuthorizationUrl($options);

        // Keep the 'state' in the session so we can later use it for validation.
        \Yii::app()->session[self::getName() . '-state'] = $provider->getState();
        header('Location: ' . $authUrl);
        exit;
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
}
