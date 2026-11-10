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

/**
 * Result of a "siteverify" call of reCAPTCHA, Cloudflare Turnstile or hCaptcha
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage SiteVerify
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_SiteVerify_Response
{
    /**
     * @var boolean
     */
    protected $_success = false;

    /**
     * Error codes reported by the service ("error-codes")
     *
     * @var array
     */
    protected $_errorCodes = [];

    /**
     * Hostname of the site the challenge was solved on
     *
     * @var string|null
     */
    protected $_hostname = null;

    /**
     * Score of reCAPTCHA v3 (0.0 - 1.0), null for other services
     *
     * @var float|null
     */
    protected $_score = null;

    /**
     * Action name of reCAPTCHA v3 / Turnstile, null when not reported
     *
     * @var string|null
     */
    protected $_action = null;

    /**
     * Timestamp of the challenge ("challenge_ts"), ISO 8601
     *
     * @var string|null
     */
    protected $_challengeTs = null;

    /**
     * Decoded response as returned by the service
     *
     * @var array
     */
    protected $_data = [];

    /**
     * @param array $data Decoded JSON response of the service
     */
    public function __construct(array $data)
    {
        $this->_data = $data;

        $this->_success = !empty($data['success']) && $data['success'] === true;

        if (!empty($data['error-codes']) && is_array($data['error-codes'])) {
            $this->_errorCodes = array_values(array_map('strval', $data['error-codes']));
        }
        if (isset($data['hostname'])) {
            $this->_hostname = (string) $data['hostname'];
        }
        if (isset($data['score']) && is_numeric($data['score'])) {
            $this->_score = (float) $data['score'];
        }
        if (isset($data['action'])) {
            $this->_action = (string) $data['action'];
        }
        if (isset($data['challenge_ts'])) {
            $this->_challengeTs = (string) $data['challenge_ts'];
        }
    }

    /**
     * Creates a response from the JSON body of the service
     *
     * @param  string $json
     * @return Zend_Service_SiteVerify_Response
     * @throws Zend_Service_SiteVerify_Exception when the body is not a JSON object
     */
    public static function fromJson($json)
    {
        $data = json_decode((string) $json, true);
        if (!is_array($data)) {
            require_once 'Zend/Service/SiteVerify/Exception.php';
            throw new Zend_Service_SiteVerify_Exception('The verification service did not return a JSON object');
        }

        return new self($data);
    }

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->_success;
    }

    /**
     * @return array
     */
    public function getErrorCodes()
    {
        return $this->_errorCodes;
    }

    /**
     * @return string|null
     */
    public function getHostname()
    {
        return $this->_hostname;
    }

    /**
     * @return float|null
     */
    public function getScore()
    {
        return $this->_score;
    }

    /**
     * @return string|null
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * @return string|null
     */
    public function getChallengeTs()
    {
        return $this->_challengeTs;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->_data;
    }
}
