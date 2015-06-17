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

    /**
	 * Registers the Bootstrap JavaScript.
	 * @param int $position the position of the JavaScript code.
	 * @see CClientScript::registerScriptFile
	 */
	public function registerJS($position = CClientScript::POS_HEAD)
	{
		/** @var CClientScript $cs */
		$cs = Yii::app()->getClientScript();
        $cs->packages['bootstrap'] = array(
            'baseUrl' => $this->getAssetsUrl(),
            'js' => array(
                'js/bootstrap.min.js'
            ),
            'depends' => array('jquery')
        );
		$cs->registerPackage('bootstrap');
		/** enable bootboxJS? */
		if($this->enableBootboxJS)
		{
			$cs->registerScriptFile($this->getAssetsUrl() . '/js/bootstrap.bootbox.min.js', $position);
		}
	}
  
}