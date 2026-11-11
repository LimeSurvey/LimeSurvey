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
 * @package    Zend_Captcha
 * @subpackage Adapter
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** @see Zend_Captcha_Base */
require_once 'Zend/Captcha/Base.php';

/** @see Zend_Service_SiteVerify */
require_once 'Zend/Service/SiteVerify.php';

/**
 * Captcha adapter for Google reCAPTCHA (v2 checkbox, v2 invisible, v3),
 * Cloudflare Turnstile and hCaptcha
 *
 * The services share one model: a script renders a widget into a
 * placeholder element, the widget puts a token into the submitted form, and
 * the server verifies the token with a POST to the "siteverify" endpoint.
 *
 * Options:
 *   provider       recaptcha (default), turnstile or hcaptcha; sets verifyUrl,
 *                  responseField, scriptUrl and widgetClass, which can still
 *                  be overridden individually for a compatible service
 *   siteKey        public key of the site (data-sitekey of the widget)
 *   secretKey      private key used for the verification
 *   attribs        extra data-* attributes of the widget, e.g.
 *                  ['theme' => 'dark', 'size' => 'compact']
 *   includeScript  whether render() also emits the <script> tag (default true)
 *   scoreThreshold minimum score for reCAPTCHA v3 (0.0 - 1.0), null = not checked
 *   action         expected action name (reCAPTCHA v3 / Turnstile), null = not checked
 *   hostname       expected hostname the challenge was solved on, null = not checked
 *   remoteIp       IP address sent along with the verification, null = taken from REMOTE_ADDR
 *
 * @category   Zend
 * @package    Zend_Captcha
 * @subpackage Adapter
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Captcha_SiteVerify extends Zend_Captcha_Base
{
    /**#@+
     * Error codes
     */
    public const MISSING_VALUE = 'missingValue';
    public const ERR_CAPTCHA   = 'errCaptcha';
    public const BAD_CAPTCHA   = 'badCaptcha';
    public const LOW_SCORE     = 'lowScore';
    public const BAD_ACTION    = 'badAction';
    public const BAD_HOSTNAME  = 'badHostname';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::MISSING_VALUE => 'Missing captcha response',
        self::ERR_CAPTCHA   => 'Failed to validate captcha',
        self::BAD_CAPTCHA   => 'Captcha value is wrong: %value%',
        self::LOW_SCORE     => 'Captcha score is too low',
        self::BAD_ACTION    => 'Captcha action does not match',
        self::BAD_HOSTNAME  => 'Captcha hostname does not match',
    ];

    /**
     * @var string
     */
    protected $_provider = Zend_Service_SiteVerify::PROVIDER_RECAPTCHA;

    /**
     * @var string
     */
    protected $_siteKey = '';

    /**
     * @var string
     */
    protected $_secretKey = '';

    /**
     * @var string|null
     */
    protected $_verifyUrl = null;

    /**
     * @var string|null
     */
    protected $_responseField = null;

    /**
     * @var string|null
     */
    protected $_scriptUrl = null;

    /**
     * @var string|null
     */
    protected $_widgetClass = null;

    /**
     * @var array
     */
    protected $_attribs = [];

    /**
     * @var boolean
     */
    protected $_includeScript = true;

    /**
     * @var float|null
     */
    protected $_scoreThreshold = null;

    /**
     * @var string|null
     */
    protected $_action = null;

    /**
     * @var string|null
     */
    protected $_hostname = null;

    /**
     * @var string|null
     */
    protected $_remoteIp = null;

    /**
     * @var Zend_Service_SiteVerify|null
     */
    protected $_service = null;

    /**
     * Last response of the verification service
     *
     * @var Zend_Service_SiteVerify_Response|null
     */
    protected $_lastResponse = null;

    /**
     * @param  string $provider
     * @return $this
     * @throws Zend_Service_SiteVerify_Exception for an unknown provider
     */
    public function setProvider($provider)
    {
        Zend_Service_SiteVerify::getProvider($provider);
        $this->_provider = strtolower((string) $provider);
        if ($this->_service !== null) {
            $this->_service->setVerifyUrl($this->getVerifyUrl());
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->_provider;
    }

    /**
     * @param  string $siteKey
     * @return $this
     */
    public function setSiteKey($siteKey)
    {
        $this->_siteKey = (string) $siteKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getSiteKey()
    {
        return $this->_siteKey;
    }

    /**
     * @param  string $secretKey
     * @return $this
     */
    public function setSecretKey($secretKey)
    {
        $this->_secretKey = (string) $secretKey;
        if ($this->_service !== null) {
            $this->_service->setSecretKey($this->_secretKey);
        }
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
     * @param  string|null $verifyUrl
     * @return $this
     */
    public function setVerifyUrl($verifyUrl)
    {
        $this->_verifyUrl = $verifyUrl === null ? null : (string) $verifyUrl;
        if ($this->_service !== null) {
            $this->_service->setVerifyUrl($this->getVerifyUrl());
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getVerifyUrl()
    {
        return $this->_verifyUrl ?? Zend_Service_SiteVerify::getProviderSetting($this->_provider, 'verifyUrl');
    }

    /**
     * @param  string|null $responseField Name of the form field the widget fills in
     * @return $this
     */
    public function setResponseField($responseField)
    {
        $this->_responseField = $responseField === null ? null : (string) $responseField;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponseField()
    {
        return $this->_responseField ?? Zend_Service_SiteVerify::getProviderSetting($this->_provider, 'responseField');
    }

    /**
     * @param  string|null $scriptUrl
     * @return $this
     */
    public function setScriptUrl($scriptUrl)
    {
        $this->_scriptUrl = $scriptUrl === null ? null : (string) $scriptUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getScriptUrl()
    {
        return $this->_scriptUrl ?? Zend_Service_SiteVerify::getProviderSetting($this->_provider, 'scriptUrl');
    }

    /**
     * @param  string|null $widgetClass CSS class the script looks for
     * @return $this
     */
    public function setWidgetClass($widgetClass)
    {
        $this->_widgetClass = $widgetClass === null ? null : (string) $widgetClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getWidgetClass()
    {
        return $this->_widgetClass ?? Zend_Service_SiteVerify::getProviderSetting($this->_provider, 'widgetClass');
    }

    /**
     * Extra data-* attributes of the widget, without the "data-" prefix
     *
     * @param  array $attribs
     * @return $this
     */
    public function setAttribs(array $attribs)
    {
        $this->_attribs = $attribs;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttribs()
    {
        return $this->_attribs;
    }

    /**
     * @param  boolean $includeScript
     * @return $this
     */
    public function setIncludeScript($includeScript)
    {
        $this->_includeScript = (bool) $includeScript;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIncludeScript()
    {
        return $this->_includeScript;
    }

    /**
     * @param  float|null $scoreThreshold
     * @return $this
     */
    public function setScoreThreshold($scoreThreshold)
    {
        $this->_scoreThreshold = $scoreThreshold === null ? null : (float) $scoreThreshold;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getScoreThreshold()
    {
        return $this->_scoreThreshold;
    }

    /**
     * @param  string|null $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->_action = $action === null ? null : (string) $action;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * @param  string|null $hostname
     * @return $this
     */
    public function setHostname($hostname)
    {
        $this->_hostname = $hostname === null ? null : (string) $hostname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHostname()
    {
        return $this->_hostname;
    }

    /**
     * @param  string|null $remoteIp
     * @return $this
     */
    public function setRemoteIp($remoteIp)
    {
        $this->_remoteIp = $remoteIp === null ? null : (string) $remoteIp;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRemoteIp()
    {
        if ($this->_remoteIp === null && isset($_SERVER['REMOTE_ADDR'])) {
            return (string) $_SERVER['REMOTE_ADDR'];
        }

        return $this->_remoteIp;
    }

    /**
     * @param  Zend_Service_SiteVerify $service
     * @return $this
     */
    public function setService(Zend_Service_SiteVerify $service)
    {
        $this->_service = $service;
        return $this;
    }

    /**
     * @return Zend_Service_SiteVerify
     */
    public function getService()
    {
        if ($this->_service === null) {
            $this->_service = new Zend_Service_SiteVerify($this->_secretKey, $this->getVerifyUrl());
        }

        return $this->_service;
    }

    /**
     * Response of the last verification, null before the first one or when the service could not be reached
     *
     * @return Zend_Service_SiteVerify_Response|null
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }

    /**
     * The widget needs no server side state
     *
     * @return string
     */
    public function generate()
    {
        return '';
    }

    /**
     * Validates the token the widget put into the form
     *
     * @param  mixed      $value   The token itself, or the submitted data
     * @param  array|null $context Submitted form data
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $token = $this->_extractToken($value, $context);
        if ($token === '') {
            $this->_error(self::MISSING_VALUE);
            return false;
        }
        $this->_setValue($token);

        $this->_lastResponse = null;
        try {
            $response = $this->getService()->verify($token, $this->getRemoteIp());
        } catch (Zend_Service_SiteVerify_Exception $e) {
            $this->_error(self::ERR_CAPTCHA);
            return false;
        }
        $this->_lastResponse = $response;

        if (!$response->isSuccess()) {
            $this->_error(self::BAD_CAPTCHA, implode(', ', $response->getErrorCodes()));
            return false;
        }

        if ($this->_hostname !== null && strcasecmp((string) $response->getHostname(), $this->_hostname) !== 0) {
            $this->_error(self::BAD_HOSTNAME);
            return false;
        }

        if ($this->_action !== null && $response->getAction() !== $this->_action) {
            $this->_error(self::BAD_ACTION);
            return false;
        }

        if ($this->_scoreThreshold !== null
            && ($response->getScore() === null || $response->getScore() < $this->_scoreThreshold)) {
            $this->_error(self::LOW_SCORE);
            return false;
        }

        return true;
    }

    /**
     * Finds the token in the value or the form data
     *
     * @param  mixed      $value
     * @param  array|null $context
     * @return string
     */
    protected function _extractToken($value, $context)
    {
        $field = $this->getResponseField();

        if (is_array($value) && isset($value[$field])) {
            return trim((string) $value[$field]);
        }
        if (is_array($context) && isset($context[$field])) {
            return trim((string) $context[$field]);
        }
        if (is_string($value) && !is_array($context) && $value !== '') {
            return trim($value);
        }

        return '';
    }

    /**
     * Renders the widget placeholder and, unless disabled, the script tag
     *
     * @param  Zend_View_Interface $view
     * @param  mixed $element
     * @return string
     */
    public function render(?Zend_View_Interface $view = null, $element = null)
    {
        $escape = function ($value) use ($view) {
            if ($view !== null && method_exists($view, 'escape')) {
                return $view->escape((string) $value);
            }
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        };

        $attribs = ['sitekey' => $this->_siteKey];
        if ($this->_action !== null && !isset($this->_attribs['action'])) {
            $attribs['action'] = $this->_action;
        }
        $attribs += $this->_attribs;

        $html = '<div class="' . $escape($this->getWidgetClass()) . '"';
        if ($element instanceof Zend_Form_Element && $element->getId() !== null) {
            $html .= ' id="' . $escape($element->getId() . '-widget') . '"';
        }
        foreach ($attribs as $name => $attribValue) {
            $html .= ' data-' . $escape($name) . '="' . $escape($attribValue) . '"';
        }
        $html .= '></div>';

        if ($this->_includeScript) {
            $html .= "\n" . '<script src="' . $escape($this->getScriptUrl()) . '" async defer></script>';
        }

        return $html;
    }

    /**
     * The generic captcha decorator renders the widget
     *
     * @return string
     */
    public function getDecorator()
    {
        return 'Captcha';
    }
}
