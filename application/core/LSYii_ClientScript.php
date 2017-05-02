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
     * @var $name of the package to remove
     */
    public function unregisterPackage($sName)
    {
        if(!empty($this->coreScripts[$sName])){
            unset($this->coreScripts[$sName]);
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
        $aUrlDatas = $this->analyzeUrl($url);

        // If possible, we publish the asset: it moves the file to the tmp/asset directory and return the url to access it
        if ( ( !YII_DEBUG || Yii::app()->getConfig('use_asset_manager')) && $aUrlDatas['toPublish'] ){
            $url = App()->getAssetManager()->publish( $aUrlDatas['sPathToFile']);
        }

        parent::registerScriptFile($url,$position,$htmlOptions);                    // We publish the script
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

        switch($aUrlParams[1]) {

            // The file to register is already published via asset manager (it's in root/tmp/assets/...)
            case 'tmp':
                $sType = 'published';
                break;

            // The file to publish is inside the assets directory
            case 'assets':
                $sType = 'toPublish';
                $sPath = $sFilePath;
                break;

            default:
                $sType = 'unknown';
                break;
        }

        // We check if the file to register exists in the local file system
        if($sType=='unknown'){
            if ( file_exists($sFilePath) ) {
                $sType = 'toPublish';
                $sPath = $sFilePath;
            }else{
                $sType = 'cantPublish';
            }
        }

        return array('toPublish'=>($sType=='toPublish'), 'sPathToFile' => $sPath );
    }
}
