<?php
Yii::import('application.extensions.bootstrap.components.Bootstrap', true);
class LSBootstrap extends Bootstrap
{
    /**
	 * Returns the URL to the published assets folder.
     * 
     * Modified version, to prevent republish (and slowness) when debug is on
     * 
	 * @return string the URL
	 */
    public function getAssetsUrl() {
        $republish = false; // Change to republish, not needed for now.
        if (isset($this->_assetsUrl))
			return $this->_assetsUrl;
		else
		{
			$assetsPath = Yii::getPathOfAlias('bootstrap.assets');
			$assetsUrl = Yii::app()->assetManager->publish($assetsPath, false, -1, $republish);
			return $this->_assetsUrl = $assetsUrl;
		}
    }
}