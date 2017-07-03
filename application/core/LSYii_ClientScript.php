<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

 /*
 * NOTE 1 : To refresh the assets, the base directory of the template must be updated.
 * NOTE 2: By default, Asset Manager is off when debug mode is on.
 *
 * Developers should then think about :
 * 1. refreshing their brower's cache (ctrl + F5) to see their changes
 * 2. update the config.xml last_update before pushing, to be sure that end users will have the new version
 *
 *
 * For more detail, see :
 *  http://www.yiiframework.com/doc/api/1.1/CClientScript#addPackage-detail
 *  http://www.yiiframework.com/doc/api/1.1/YiiBase#setPathOfAlias-detail
 */

class LSYii_ClientScript extends CClientScript {

    /**
     * cssFiles is protected on CClientScript. It can be useful to access it for debugin purpose
     * @return array
     */
    public function getCssFiles()
    {
        return $this->cssFiles;
    }

    /**
     * cssFicoreScripts is protected on CClientScript. It can be useful to access it for debugin purpose
     * @return array
     */
    public function getCoreScripts()
    {
        return $this->coreScripts;
    }

    /**
     * Remove a package from coreScript.
     * It can be useful when mixing backend/frontend rendering (see: template editor)
     *
     * @var string $sName of the package to remove
     */
    public function unregisterPackage($sName)
    {
        if(!empty($this->coreScripts[$sName])){
            unset($this->coreScripts[$sName]);
        }
    }

    /**
     * Remove a file from a given package
     *
     * @var $sPackageName   string  name of the package
     * @var $sType          string  css/js
     * @var $sFileName      string name of the file to remove
     */
    public function removeFileFromPackage($sPackageName, $sType, $sFileName )
    {
        if (!empty(Yii::app()->clientScript->packages[$sPackageName])){
            if (!empty(Yii::app()->clientScript->packages[$sPackageName][$sType])){
                $key = array_search( $sFileName,Yii::app()->clientScript->packages[$sPackageName][$sType]);
                unset(Yii::app()->clientScript->packages[$sPackageName][$sType][$key]);
            }
        }
    }

    /**
     * In LimeSurvey, if debug mode is OFF we use the asset manager (so participants never needs to update their webbrowser cache).
     * If debug mode is ON, we don't use the asset manager, so developpers just have to refresh their browser cache to reload the new scripts.
     * To make developper life easier, if they want to register a single script file, they can use App()->getClientScript()->registerScriptFile({url to script file})
     * if the file exist in local file system and debug mode is off, it will find the path to the file, and it will publish it via the asset manager
     */
    public function registerScriptFile($url, $position=null, array $htmlOptions=array())
    {
        // If possible, we publish the asset: it moves the file to the tmp/asset directory and return the url to access it
        if ( ( !YII_DEBUG || Yii::app()->getConfig('use_asset_manager')) ){
            $aUrlDatas = $this->analyzeUrl($url);
            if ( $aUrlDatas['toPublish']){
                $url = App()->getAssetManager()->publish( $aUrlDatas['sPathToFile']);
            }
        }

        parent::registerScriptFile($url,$position,$htmlOptions);                    // We publish the script
    }


    public function registerCssFile($url,$media='')
    {
        // If possible, we publish the asset: it moves the file to the tmp/asset directory and return the url to access it
        if ( ( !YII_DEBUG || Yii::app()->getConfig('use_asset_manager')) ){
            $aUrlDatas = $this->analyzeUrl($url);
            if ( $aUrlDatas['toPublish']){
                $url = App()->getAssetManager()->publish( $aUrlDatas['sPathToFile']);
            }
        }
        parent::registerCssFile($url,$media);                    // We publish the script
    }

    /**
     * The method will first check if a devbaseUrl parameter is provided,
     * so when debug mode is on, it doens't use the asset manager
     */
    public function registerPackage($name)
    {
        if(!YII_DEBUG ||  Yii::app()->getConfig('use_asset_manager')){
            parent::registerPackage( $name );
        }else{

            // We first convert the current package to devBaseUrl
            $this->convertDevBaseUrl($name);

            // Then we do the same for all its dependencies
            $aDepends = $this->getRecursiveDependencies($name);
            foreach($aDepends as $package){
                $this->convertDevBaseUrl($package);
            }

            parent::registerPackage( $name );
        }
    }

    /**
     * Return a list of all the recursive dependencies of a packages
     * eg: If a package A depends on B, and B depends on C, getRecursiveDependencies('A') will return {B,C}
     */
    public function getRecursiveDependencies($sPackageName)
    {
        $aPackages     = Yii::app()->clientScript->packages;
        if ( array_key_exists('depends', $aPackages[$sPackageName]) ){
            $aDependencies = $aPackages[$sPackageName]['depends'];

            foreach ($aDependencies as $sDpackageName){
                if($aPackages[$sPackageName]['depends']){
                    $aRDependencies = $this->getRecursiveDependencies($sDpackageName);                  // Recursive call
                    if (is_array($aRDependencies)){
                        $aDependencies = array_unique(array_merge($aDependencies, $aRDependencies));
                    }
                }
            }
            return $aDependencies;
        }
        return array();
    }


    /**
     * Convert one package to baseUrl 
     */
    private function convertDevBaseUrl($package)
    {
        $aOldPackageDefinition = Yii::app()->clientScript->packages[$package];

        // This will overwrite the package definition using a base url instead of a base path
        // The package must have a devBaseUrl, else it will remain unchanged (for core/external package)
        if( array_key_exists('devBaseUrl', $aOldPackageDefinition ) ){
            Yii::app()->clientScript->addPackage( $package, array(
                'baseUrl'   => $aOldPackageDefinition['devBaseUrl'],                                 // Don't use asset manager
                'css'       => array_key_exists('css', $aOldPackageDefinition)?$aOldPackageDefinition['css']:array(),
                'js'        => array_key_exists('js', $aOldPackageDefinition)?$aOldPackageDefinition['js']:array(),
                'depends'   => array_key_exists('depends', $aOldPackageDefinition)?$aOldPackageDefinition['depends']:array(),
            ) );
        }
    }

    /**
     * This function will analyze the url of a file (css/js) to register
     * It will check if it can be published via the asset manager and if so will retreive its path
     */
    private function analyzeUrl($sUrl)
    {
        $sCleanUrl  = str_replace(Yii::app()->baseUrl, '', $sUrl);              // we remove the base url to be sure that the first parameter is the one we want
        $aUrlParams = explode('/', $sCleanUrl);
        $sFilePath  = Yii::app()->getConfig('rootdir') . $sCleanUrl;
        $sPath = '';

        // TODO: check if tmp directory can be named differently via config
        if ($aUrlParams[1]=='tmp'){
            $sType = 'published';
        }else{
            if ( file_exists($sFilePath) ) {
                $sType = 'toPublish';
                $sPath = $sFilePath;
            }else{
                $sType = 'cantPublish';
            }
        }

        return array('toPublish'=>($sType=='toPublish'), 'sPathToFile' => $sPath );
    }


	/**
	 * Renders the specified core javascript library.
	 */
	public function renderCoreScripts()
	{
		if($this->coreScripts===null)
			return;
		$cssFiles=array();
		$jsFiles=array();
		$jsFilesPositioned=array();
		foreach($this->coreScripts as $name=>$package)
		{
			$baseUrl=$this->getPackageBaseUrl($name);
			if(!empty($package['js']))
			{
				foreach($package['js'] as $js){
					if(isset($package['position'])){
                        $jsFilesPositioned[$package['position']][$baseUrl.'/'.$js]=$baseUrl.'/'.$js;
                    } else {
                        $jsFiles[$baseUrl.'/'.$js]=$baseUrl.'/'.$js;
                    }
                }
			}
			if(!empty($package['css']))
			{
				foreach($package['css'] as $css)
					$cssFiles[$baseUrl.'/'.$css]='';
			}
		}
		// merge in place
		if($cssFiles!==array())
		{
			foreach($this->cssFiles as $cssFile=>$media)
				$cssFiles[$cssFile]=$media;
			$this->cssFiles=$cssFiles;
		}
		if($jsFiles!==array())
		{
			if(isset($this->scriptFiles[$this->coreScriptPosition]))
			{
				foreach($this->scriptFiles[$this->coreScriptPosition] as $url => $value)
					$jsFiles[$url]=$value;
			}
			$this->scriptFiles[$this->coreScriptPosition]=$jsFiles;
		}
		if($jsFilesPositioned!==array())
		{
            foreach($jsFilesPositioned as $position=>$fileArray){
    			if(isset($this->scriptFiles[$position]))
                    foreach($this->scriptFiles[$position] as $url => $value)
					    $fileArray[$url]=$value;
			    $this->scriptFiles[$position]=$fileArray;
            }
		}
	}
}
