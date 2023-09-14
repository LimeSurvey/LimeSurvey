<?php

trait LSApplicationTrait
{
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
}
