<?php

/**
 * Trait for ConsoleApplication and LSYii_Application
 *
 * @version 0.1.0
 */

trait LSApplicationTrait
{
    /* @var integer| null the current userId for all action */
    private $currentUserId;
    /**
     * get the current id of connected user,
     * check if user exist before return for security
     * @return int|null user id, 0 mean invalid user
     */
    public function getCurrentUserId()
    {
        if (empty(App()->session['loginID'])) {
            /**
             * NULL for guest,
             * null by default for CConsoleapplication, but Permission always return true for console
             * Test can update only App()->session['loginID'] to set the user
             */
            return App()->session['loginID'];
        }
        if (!is_null($this->currentUserId) && $this->currentUserId == App()->session['loginID']) {
            return $this->currentUserId;
        }
        /* use App()->session and not App()->user for easiest unit test */
        $this->currentUserId = App()->session['loginID'];
        if ($this->currentUserId && !User::model()->notexpired()->active()->findByPk($this->currentUserId)) {
            $this->currentUserId = 0;
        }
        return $this->currentUserId;
    }

    /**
     * Creates an absolute URL based on the given controller and action information.
     * @param string $route the URL route. This should be in the format of 'ControllerID/ActionID'.
     * @param array $params additional GET parameters (name=>value). Both the name and value will be URL-encoded.
     * @param string $schema schema to use (e.g. http, https). If empty, the schema used for the current request will be used.
     * @param string $ampersand the token separating name-value pairs in the URL.
     * @return string the constructed URL
     */
    public function createPublicUrl($route, $params = array(), $schema = '', $ampersand = '&')
    {
        $sPublicUrl = $this->getPublicBaseUrl(true);
        $sActualBaseUrl = $this->getBaseUrl(true);
        if ($sPublicUrl !== $sActualBaseUrl) {
            $url = $this->createAbsoluteUrl($route, $params, $schema, $ampersand);
            if (substr((string)$url, 0, strlen((string)$sActualBaseUrl)) == $sActualBaseUrl) {
                $url = substr((string)$url, strlen((string)$sActualBaseUrl));
            }
            return trim((string)$sPublicUrl, "/") . $url;
        } else {
            return $this->createAbsoluteUrl($route, $params, $schema, $ampersand);
        }
    }

    /**
     * Returns the relative URL for the application while
     * considering if a "publicurl" config parameter is set to a valid url
     * @param boolean $absolute whether to return an absolute URL. Defaults to false, meaning returning a relative one.
     * @return string the relative or the configured public URL for the application
     */
    public function getPublicBaseUrl($absolute = false)
    {
        $sPublicUrl = Yii::app()->getConfig("publicurl");
        $aPublicUrl = parse_url($sPublicUrl);
        $baseUrl = $this->getBaseUrl($absolute);
        if (isset($aPublicUrl['scheme']) && isset($aPublicUrl['host'])) {
            $baseUrl = $sPublicUrl;
        }
        return $baseUrl;
    }

    /**
     * Creates an absolute URL that is validated against allowed hosts.
     * This prevents host header injection attacks by ensuring the generated URL
     * uses a trusted host from allowed_hosts.php or the configured publicurl.
     *
     * @param string $route the URL route.
     * @param array $params additional GET parameters (name=>value).
     * @param string $schema schema to use (e.g. http, https).
     * @param string $ampersand the token separating name-value pairs in the URL.
     * @return string|false the constructed URL with a validated host, or false if no trusted host is available.
     */
    public function createValidatedAbsoluteUrl($route, $params = array(), $schema = '', $ampersand = '&')
    {
        $validatedHostName = $this->getValidatedHost();
        if ($validatedHostName === false) {
            return false;
        }

        // Generate the URL using createAbsoluteUrl (which may use the untrusted Host header)
        $url = $this->createAbsoluteUrl($route, $params, $schema, $ampersand);

        // Replace just the host portion with the validated host, keeping scheme/path/query intact
        $parsedUrl = parse_url($url);

        if (!isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
            return false;
        }

        // Build the original base (scheme://host[:port]) to find where the path starts
        $generatedBase = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        if (isset($parsedUrl['port'])) {
            $generatedBase .= ':' . $parsedUrl['port'];
        }

        // Rebuild URL with the validated host, keeping original scheme, port, and path/query
        $remainder = substr($url, strlen($generatedBase));
        $portPart = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        return $parsedUrl['scheme'] . '://' . $validatedHostName . $portPart . $remainder;
    }

    /**
     * Gets a validated host name from allowed_hosts.php or publicurl config.
     *
     * @return string|false A validated domain name (no protocol, no port), or false if none available.
     */
    public function getValidatedHost()
    {
        // First try: allowed_hosts.php
        $allowedHosts = $this->loadAllowedHosts();
        if (!empty($allowedHosts)) {
            // Return the first allowed host as the canonical one
            return $allowedHosts[0];
        }

        // Second try: publicurl config
        $publicUrl = Yii::app()->getConfig('publicurl');
        if (!empty($publicUrl)) {
            $parsed = parse_url($publicUrl);
            if (isset($parsed['host'])) {
                return $parsed['host'];
            }
        }

        return false;
    }

    /**
     * Loads the allowed hosts from /application/config/allowed_hosts.php
     *
     * The file should return an array of allowed domain names (no protocol, no port), e.g.:
     * <?php return ['example.com', 'www.example.com'];
     *
     * @return array List of allowed host names, or empty array if file doesn't exist or is empty.
     */
    public function loadAllowedHosts()
    {
        $filePath = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'allowed_hosts.php';
        if (file_exists($filePath)) {
            $hosts = require($filePath);
            if (is_array($hosts) && !empty($hosts)) {
                return $hosts;
            }
        }
        return [];
    }

    /**
     * Writes the allowed_hosts.php config file with the given hosts array.
     *
     * @param array $hosts Array of allowed domain names (no protocol, no port).
     * @return bool True on success, false on failure.
     */
    public function writeAllowedHosts(array $hosts)
    {
        $filePath = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'allowed_hosts.php';
        $content = "<?php\n"
            . "/**\n"
            . " * Allowed Hosts Configuration\n"
            . " *\n"
            . " * This file contains the list of trusted host names that LimeSurvey is allowed\n"
            . " * to use when generating absolute URLs in emails (e.g. password reset links,\n"
            . " * survey registration links). This prevents host header injection attacks where\n"
            . " * an attacker manipulates the HTTP Host header to poison emailed URLs.\n"
            . " *\n"
            . " * Each entry should be a domain name only.\n"
            . " * Do NOT include the protocol/scheme (http:// or https://) or port numbers.\n"
            . " *\n"
            . " * Examples:\n"
            . " *   'example.com'\n"
            . " *   'surveys.example.com'\n"
            . " *   'localhost'\n"
            . " *\n"
            . " * This file is auto-generated on first admin login if it does not exist.\n"
            . " * You may edit it manually to add or change allowed hosts.\n"
            . " */\n"
            . "return [\n";
        foreach ($hosts as $host) {
            $content .= "    " . var_export($host, true) . ",\n";
        }
        $content .= "];\n";
        return @file_put_contents($filePath, $content) !== false;
    }
}
