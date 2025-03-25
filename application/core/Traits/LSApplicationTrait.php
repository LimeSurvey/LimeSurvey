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
        /* use App()->session and not App()->user fot easiest unit test */
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
     * Function to Throw Exception when error happen
     * @see https://www.php.net/set-error-handler
     * @throws \ErrorException
     * @return null|array : last error handler, null if default
     */
    public function setErrorHandler()
    {
        return set_error_handler(
            function ($errno, $errstr, $errfile = null, $errline = null, $context = null) {
                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
        );
    }

    /**
     * function to reset error handler
     * @see https://www.php.net/restore-error-handler
     * @return void
     */
    public function resetErrorHandler()
    {
        restore_error_handler();
    }
}
