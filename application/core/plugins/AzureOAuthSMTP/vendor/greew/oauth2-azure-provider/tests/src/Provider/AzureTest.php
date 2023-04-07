<?php

declare(strict_types=1);

namespace Greew\OAuth2\Test\Client\Provider;

use Greew\OAuth2\Client\Provider\Azure;
use League\OAuth2\Client\Tool\QueryBuilderTrait;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class AzureTest extends TestCase
{
    use QueryBuilderTrait;

    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new Azure([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            'tenantId' => 'mock_tenant_id',
        ]);
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testScopes(): void
    {
        $scopeSeparator = ' ';
        $options = ['scope' => [uniqid('', true), uniqid('', true)]];
        $query = ['scope' => implode($scopeSeparator, $options['scope'])];
        $url = $this->provider->getAuthorizationUrl($options);
        $encodedScope = $this->buildQueryString($query);
        $this->assertStringContainsString($encodedScope, $url);
    }

    public function testGetAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/mock_tenant_id/oauth2/v2.0/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl(): void
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/mock_tenant_id/oauth2/v2.0/token', $uri['path']);
    }

    public function testSettingAuthEndpoints(): void
    {
        $customAuthUrl = uniqid('', true);
        $customResourceOwnerUrl = uniqid('', true);
        $customTenantId = uniqid('', true);
        $token = m::mock('League\OAuth2\Client\Token\AccessToken');

        $this->provider = new Azure([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            'tenantId' => $customTenantId,
            'urlAuthorize' => $customAuthUrl,
            'urlResourceOwnerDetails' => $customResourceOwnerUrl
        ]);

        $authUrl = $this->provider->getAuthorizationUrl();
        $this->assertStringContainsString($customAuthUrl, $authUrl);
        $tokenUrl = $this->provider->getBaseAccessTokenUrl([]);
        $this->assertStringContainsString($customAuthUrl, $tokenUrl);
        $resourceOwnerUrl = $this->provider->getResourceOwnerDetailsUrl($token);
        $this->assertStringContainsString($customResourceOwnerUrl, $resourceOwnerUrl);
    }

    public function testGetAccessToken(): void
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn(
            '{"access_token":"mock_access_token","authentication_token":"","code":"","expires_in":3600,' .
            '"refresh_token":"mock_refresh_token","scope":"","state":"","token_type":""}'
        );
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertEquals('mock_refresh_token', $token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testUserData(): void
    {
        $email = uniqid('', true);
        $firstname = uniqid('', true);
        $lastname = uniqid('', true);
        $name = uniqid('', true);
        $userId = rand(1000, 9999);
        $urls = uniqid('', true);

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn(
            '{"access_token":"mock_access_token","authentication_token":"","code":"","expires_in":3600,' .
            '"refresh_token":"mock_refresh_token","scope":"","state":"","token_type":""}'
        );
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn('{"id": ' . $userId . '}');
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertEquals($userId, $user->getId());
        $this->assertEquals($userId, $user->toArray()['id']);
    }

    public function testExceptionThrownWhenErrorObjectReceived(): void
    {
        $this->expectException(\League\OAuth2\Client\Provider\Exception\IdentityProviderException::class);
        $message = uniqid('', true);

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn(
            '{"error": {"code": "request_token_expired", "message": "' . $message . '"}}'
        );
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn(500);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);

        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }
}
