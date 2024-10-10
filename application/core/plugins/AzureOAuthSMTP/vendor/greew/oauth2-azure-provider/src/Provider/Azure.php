<?php

namespace Greew\OAuth2\Client\Provider;

use GuzzleHttp\Psr7\Uri;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Azure extends AbstractProvider
{
    /** @var string */
    protected $tenantId = '';

    /**
     * Default scopes
     *
     * @var array
     */
    public $defaultScopes = [
        'https://outlook.office.com/SMTP.Send',
        'offline_access'
    ];

    /**
     * Base url for authorization.
     *
     * @var string
     */
    protected $urlAuthorize = 'https://login.microsoftonline.com';

    /** @var string */
    protected $urlResourceOwnerDetails = 'https://graph.microsoft.com/v1.0/me';

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->urlAuthorize . '/' . $this->tenantId . '/oauth2/v2.0/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->urlAuthorize . '/' . $this->tenantId . '/oauth2/v2.0/token';
    }

    /**
     * @param array $options
     * @return array|string[]
     */
    protected function getAuthorizationParameters(array $options)
    {
        $options += [
            'response_mode' => 'query',
        ];
        return parent::getAuthorizationParameters($options);
    }

    /**
     * Get default scopes
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return $this->defaultScopes;
    }

    /**
     * Check a provider response for errors.
     *
     * @param ResponseInterface $response
     * @param $data
     * @return void
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                (isset($data['error']['message']) ? $data['error']['message'] : $response->getReasonPhrase()),
                $response->getStatusCode(),
                (string)$response->getBody()
            );
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return AzureResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new AzureResourceOwner($response);
    }

    /**
     * Get provider url to fetch user details
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $uri = new Uri($this->urlResourceOwnerDetails);

        return (string)Uri::withQueryValue($uri, 'access_token', (string)$token);
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }
}
