<?php

    class LSYii_UrlManager extends CUrlManager
    {
        
        
        public function parseUrl($request) {
            $result = parent::parseUrl($request);
            if ($result == '' && $this->urlFormat == CUrlManager::PATH_FORMAT && isset($_GET[$this->routeVar]))
            {
                return $_GET[$this->routeVar];
            }
            else
            {
                return $result;
            }
        }
        
        public function setShowScriptName($showScriptName)
        {
            $this->showScriptName = $showScriptName;
            $this->setBaseUrl(null);
            
        }
        public function createUrl($route, $params = array(), $ampersand = '&') {
            
            return parent::createUrl($route, $params, $ampersand);
            
            
        }
    }
?>
