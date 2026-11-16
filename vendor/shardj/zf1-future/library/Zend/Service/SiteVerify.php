<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage SiteVerify
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** @see Zend_Service_SiteVerify_Response */
require_once 'Zend/Service/SiteVerify/Response.php';

/**
 * Client for the "siteverify" API shared by Google reCAPTCHA (v2 and v3),
 * Cloudflare Turnstile and hCaptcha
 *
 * The widget of the service puts a token into the submitted form; this
 * class posts the token together with the secret key to the verification
 * endpoint and returns the decoded answer.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage SiteVerify
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_SiteVerify
{
    public const PROVIDER_RECAPTCHA = 'recaptcha';
    public const PROVIDER_TURNSTILE = 'turnstile';
    public const PROVIDER_HCAPTCHA  = 'hcaptcha';

    /**
     * Endpoints, form field names, script and widget markup of the known services
     *
     * @var array
     */
    protected static $_providers = [
        self::PROVIDER_RECAPTCHA => [
            'verifyUrl'     => 'https://www.google.com/recaptcha/api/siteverify',
            'responseField' => 'g-recaptcha-response',
            'scriptUrl'     => 'https://www.google.com/recaptcha/api.js',
            'widgetClass'   => 'g-recaptcha',
        ],
        self::PROVIDER_TURNSTILE => [
            'verifyUrl'     => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            'responseField' => 'cf-turnstile-response',
            'scriptUrl'     => 'https://challenges.cloudflare.com/turnstile/v0/api.js',
            'widgetClass'   => 'cf-turnstile',
        ],
        self::PROVIDER_HCAPTCHA => [
            'verifyUrl'     => 'https://api.hcaptcha.com/siteverify',
            'responseField' => 'h-captcha-response',
            'scriptUrl'     => 'https://js.hcaptcha.com/1/api.js',
            'widgetClass'   => 'h-captcha',
        ],
    ];

    /**
     * @var string
     */
    protected $_secretKey = '';

    /**
     * @var string
     */
    protected $_verifyUrl = '';

    /**
     * @var Zend_Http_Client|null
     */
    protected $_httpClient = null;

    /**
     * @param string                $secretKey
     * @param string                $provider  One of the PROVIDER_* constants or a verification URL
     * @param Zend_Http_Client|null $httpClient
     */
    public function __construct($secretKey, $provider = self::PROVIDER_RECAPTCHA, ?Zend_Http_Client $httpClient = null)
    {
        $this->setSecretKey($secretKey);
        $this->setVerifyUrl(self::getProviderSetting($provider, 'verifyUrl'));
        if ($httpClient !== null) {
            $this->setHttpClient($httpClient);
        }
    }

    /**
     * Returns the settings of a known service
     *
     * @param  string $provider One of the PROVIDER_* constants
     * @return array verifyUrl, responseField, scriptUrl, widgetClass
     * @throws Zend_Service_SiteVerify_Exception for an unknown provider
     */
    public static function getProvider($provider)
    {
        $provider = strtolower((string) $provider);
        if (!isset(self::$_providers[$provider])) {
            require_once 'Zend/Service/SiteVerify/Exception.php';
            throw new Zend_Service_SiteVerify_Exception("Unknown captcha provider '$provider', expected one of "
                . implode(', ', array_keys(self::$_providers)));
        }

        return self::$_providers[$provider];
    }

    /**
     * Returns one setting of a known service; a value that is not a
     * provider name is returned as it is (a custom URL, field name, ...)
     *
     * @param  string $provider
     * @param  string $setting
     * @return string
     */
    public static function getProviderSetting($provider, $setting)
    {
        $key = strtolower((string) $provider);
        if (isset(self::$_providers[$key])) {
            return self::$_providers[$key][$setting];
        }

        return (string) $provider;
    }

    /**
     * @param  string $secretKey
     * @return $this
     */
    public function setSecretKey($secretKey)
    {
        $this->_secretKey = (string) $secretKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->_secretKey;
    }

    /**
     * @param  string $verifyUrl
     * @return $this
     */
    public function setVerifyUrl($verifyUrl)
    {
        $this->_verifyUrl = (string) $verifyUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getVerifyUrl()
    {
        return $this->_verifyUrl;
    }

    /**
     * @param  Zend_Http_Client $httpClient
     * @return $this
     */
    public function setHttpClient(Zend_Http_Client $httpClient)
    {
        $this->_httpClient = $httpClient;
        return $this;
    }

    /**
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        if ($this->_httpClient === null) {
            require_once 'Zend/Http/Client.php';
            $this->_httpClient = new Zend_Http_Client(null, ['timeout' => 10]);
        }

        return $this->_httpClient;
    }

    /**
     * Verifies the token the widget put into the form
     *
     * @param  string      $token    Value of the response field (g-recaptcha-response, cf-turnstile-response, ...)
     * @param  string|null $remoteIp IP address of the user, optional
     * @return Zend_Service_SiteVerify_Response
     * @throws Zend_Service_SiteVerify_Exception when the service cannot be reached or answers with an error status
     */
    public function verify($token, $remoteIp = null)
    {
        if ($this->_secretKey === '') {
            require_once 'Zend/Service/SiteVerify/Exception.php';
            throw new Zend_Service_SiteVerify_Exception('Missing secret key');
        }

        $client = $this->getHttpClient();
        $client->resetParameters(true);
        $client->setUri($this->_verifyUrl);
        $client->setParameterPost('secret', $this->_secretKey);
        $client->setParameterPost('response', (string) $token);
        if ($remoteIp !== null && $remoteIp !== '') {
            $client->setParameterPost('remoteip', (string) $remoteIp);
        }

        try {
            $httpResponse = $client->request(Zend_Http_Client::POST);
        } catch (Zend_Http_Client_Exception $e) {
            require_once 'Zend/Service/SiteVerify/Exception.php';
            throw new Zend_Service_SiteVerify_Exception('Could not reach the verification service: ' . $e->getMessage(), 0, $e);
        }

        if (!$httpResponse->isSuccessful()) {
            require_once 'Zend/Service/SiteVerify/Exception.php';
            throw new Zend_Service_SiteVerify_Exception('The verification service answered with HTTP status '
                . $httpResponse->getStatus());
        }

        return Zend_Service_SiteVerify_Response::fromJson($httpResponse->getBody());
    }
}
