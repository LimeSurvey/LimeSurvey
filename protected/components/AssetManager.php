<?php

class AssetManager extends CAssetManager {

    public $relativeUrl;

    private $_baseUrl;

    public function getBaseUrl() {

        if($this->_baseUrl===null)
        {
            $request = Yii::app()->getRequest();
            $this->setBaseUrl($request->getBaseUrl() .'/'. $this->relativeUrl);
        }
        return $this->_baseUrl;
    }

    /**
     * @param string $value the base url that the published asset files can be accessed
     */
    public function setBaseUrl($value)
    {
        $this->_baseUrl = rtrim($value,'/');
    }


}