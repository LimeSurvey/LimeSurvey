<?php
namespace ls\components;

/**
 * Class AssetManager
 * This asset manager adds better base URL resolution when the entry script might be in a number of
 * different locations.
 * @package ls\components
 */
class AssetManager extends \CAssetManager
{

    public $relativeUrl = 'assets';

    private $_baseUrl;

    /**
     * @return string the root directory storing the published asset files. Defaults to 'WebRoot/assets'.
     */
    public function getBaseUrl()
    {
        if(!isset($this->_baseUrl))
        {
            /** @var \CHttpRequest $request */
            $request = \Yii::app()->getRequest();
            $baseUrl = $request->getBaseUrl();
            if (basename($baseUrl) != 'public' && !preg_match('-^.*public/index.php$-', $request->scriptFile)) {
                $baseUrl .= '/public';
            }
            $this->_baseUrl = $baseUrl . DIRECTORY_SEPARATOR . $this->relativeUrl;
        }

        return $this->_baseUrl;
    }

    /**
     * @param string $value the base url that the published asset files can be accessed
     */
    public function setBaseUrl($value)
    {
        $this->_baseUrl=rtrim($value,'/');
    }


}