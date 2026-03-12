<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
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
 * 2. update the config.xml lastUpdate before pushing, to be sure that end users will have the new version
 *
 *
 * For more detail, see :
 *  http://www.yiiframework.com/doc/api/1.1/CClientScript#addPackage-detail
 *  http://www.yiiframework.com/doc/api/1.1/YiiBase#setPathOfAlias-detail
 */

class LSYii_ClientScript extends CClientScript
{

    /**
     * The script is rendered at the end of the body section.
     * only for scripts not script files
     */
    const POS_POSTSCRIPT = 5;
    const POS_PREBEGIN = 6;
    /**
     * cssFiles is protected on CClientScript. It can be useful to access it for debugin purpose
     * @return array
     */
    public function getCssFiles()
    {
        return $this->cssFiles;
    }


    public function recordCachingAction($context, $method, $params)
    {
        if (($controller = Yii::app()->getController()) !== null && (get_class($controller) !== 'ConsoleApplication' )) {
            $controller->recordCachingAction($context, $method, $params);
        }
    }

    public function getScriptFiles()
    {
        return $this->scriptFiles;
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
     *
     * @return array
     */
    public function getFontPackages()
    {
        $aPackages = array();
        foreach ($this->packages as $key => $package) {
            if (strpos((string) $key, 'font-') === 0) {
                $key = str_replace('font-', '', (string) $key);
                $aPackages[$package['type']][$key] = $package;
            }
        }
        unset($aPackages['core']['websafe']);
        return $aPackages;
    }

    /**
     * Remove a package from coreScript.
     * It can be useful when mixing backend/frontend rendering (see: template editor)
     *
     * @var string $sName of the package to remove
     */
    public function unregisterPackage($sName)
    {
        if (!empty($this->coreScripts[$sName])) {
            unset($this->coreScripts[$sName]);
        }
    }

    public function unregisterScriptFile($sName)
    {
        if (!empty($this->scriptFiles[0]["$sName"])) {
            unset($this->scriptFiles[0]["$sName"]);
        }
    }

    /**
     * Check if a file is in a given package
     * @var $sPackageName   string  name of the package
     * @var $sType          string  css/js
     * @var $sFileName      string name of the file to remove
     * @return boolean
     */
    public function IsFileInPackage($sPackageName, $sType, $sFileName)
    {
        if (!empty(Yii::app()->clientScript->packages[$sPackageName])) {
            if (!empty(Yii::app()->clientScript->packages[$sPackageName][$sType])) {
                $key = array_search($sFileName, Yii::app()->clientScript->packages[$sPackageName][$sType]);
                return $key !== false;
            }
        }
        return false;
    }


    /**
     * Add a file to a given package
     *
     * @var $sPackageName   string  name of the package
     * @var $sType          string  css/js
     * @var $sFileName      string name of the file to add
     */
    public function addFileToPackage($sPackageName, $sType, $sFileName)
    {
        if (!empty(Yii::app()->clientScript->packages[$sPackageName])) {
            if (empty(Yii::app()->clientScript->packages[$sPackageName][$sType])) {
                Yii::app()->clientScript->packages[$sPackageName][$sType] = array();
            }

            $sFilePath = Yii::getPathOfAlias(Yii::app()->clientScript->packages[$sPackageName]["basePath"]) . DIRECTORY_SEPARATOR . $sFileName;
            Yii::app()->clientScript->packages[$sPackageName][$sType][] = $sFileName;
        }
    }



    /**
     * Remove a file from a given package
     *
     * @var $sPackageName   string  name of the package
     * @var $sType          string  css/js
     * @var $sFileName      string name of the file to remove
     */
    public function removeFileFromPackage($sPackageName, $sType, $sFileName)
    {
        if (!empty(Yii::app()->clientScript->packages[$sPackageName])) {
            if (!empty(Yii::app()->clientScript->packages[$sPackageName][$sType])) {
                $key = array_search($sFileName, Yii::app()->clientScript->packages[$sPackageName][$sType]);
                if ($key !== false) {
                    unset(Yii::app()->clientScript->packages[$sPackageName][$sType][$key]);
                }
            }
        }
    }

    /**
     * In LimeSurvey, if debug mode is OFF we use the asset manager (so participants never needs to update their webbrowser cache).
     * If debug mode is ON, we don't use the asset manager, so developpers just have to refresh their browser cache to reload the new scripts.
     * To make developer life easier, if they want to register a single script file, they can use App()->getClientScript()->registerScriptFile({url to script file})
     * if the file exist in local file system and debug mode is off, it will find the path to the file, and it will publish it via the asset manager
     * @param string $url
     * @param string $position
     * @param array $htmlOptions
     * @return void|static
     */
    public function registerScriptFile($url, $position = null, array $htmlOptions = array())
    {
        // If possible, we publish the asset: it moves the file to the tmp/asset directory and return the url to access it
        if ((!YII_DEBUG || Yii::app()->getConfig('use_asset_manager'))) {
            $aUrlDatas = $this->analyzeUrl($url);
            if ($aUrlDatas['toPublish']) {
                $url = App()->assetManager->publish($aUrlDatas['sPathToFile']);
            }
        }

        parent::registerScriptFile($url, $position, $htmlOptions); // We publish the script
    }


    public function registerCssFile($url, $media = '')
    {
        // If possible, we publish the asset: it moves the file to the tmp/asset directory and return the url to access it
        if ((!YII_DEBUG || Yii::app()->getConfig('use_asset_manager'))) {
            $aUrlDatas = $this->analyzeUrl($url);
            if ($aUrlDatas['toPublish']) {
                $url = App()->assetManager->publish($aUrlDatas['sPathToFile']);
            }
        }
        parent::registerCssFile($url, $media); // We publish the script
    }

    /**
     * The method will first check if a devbaseUrl parameter is provided,
     * so when debug mode is on, it doens't use the asset manager
     * @param string $name
     * @return void|static
     */
    public function registerPackage($name)
    {
        if (!YII_DEBUG || Yii::app()->getConfig('use_asset_manager')) {
            parent::registerPackage($name);
        } else {
            // We first convert the current package to devBaseUrl
            $this->convertDevBaseUrl($name);

            // Then we do the same for all its dependencies
            $aDepends = $this->getRecursiveDependencies($name);
            foreach ($aDepends as $package) {
                $this->convertDevBaseUrl($package);
            }

            parent::registerPackage($name);
        }
    }

    /**
     * Return a list of all the recursive dependencies of a packages
     * eg: If a package A depends on B, and B depends on C, getRecursiveDependencies('A') will return {B,C}
     * @param string $sPackageName
     */
    public function getRecursiveDependencies($sPackageName)
    {
        $aPackages = Yii::app()->clientScript->packages;
        if (isset($aPackages[$sPackageName]['depends'])) {
            $aDependencies = $aPackages[$sPackageName]['depends'];

            foreach ($aDependencies as $sDpackageName) {
                if ($aPackages[$sPackageName]['depends']) {
                    $aRDependencies = $this->getRecursiveDependencies($sDpackageName); // Recursive call
                    if (is_array($aRDependencies)) {
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
     * Overwrite the package definition using a base url instead of a base path
     * The package must have a devBaseUrl, else it will remain unchanged (for core/external package); so third party package are not concerned
     * @param string $package
     */
    private function convertDevBaseUrl($package)
    {
        // We retrieve the old package
        $aOldPackageDefinition = Yii::app()->clientScript->packages[$package];

        // If it has an entry 'devBaseUrl', we use it to replace basePath (it will turn off asset manager for this package)
        if (is_array($aOldPackageDefinition) && array_key_exists('devBaseUrl', $aOldPackageDefinition)) {
            $aNewPackageDefinition = array();

            // Take all the values of the oldPackage to add it to the new one
            foreach ($aOldPackageDefinition as $key => $value) {
                // Remove basePath
                if ($key != 'basePath') {
                    // Convert devBaseUrl
                    if ($key == 'devBaseUrl') {
                        $aNewPackageDefinition['baseUrl'] = $value;
                    } else {
                        $aNewPackageDefinition[$key] = $value;
                    }
                }
            }
            Yii::app()->clientScript->addPackage($package, $aNewPackageDefinition);
        }
    }

    /**
     * This function will analyze the url of a file (css/js) to register
     * It will check if it can be published via the asset manager and if so will retrieve its path
     * @param $sUrl
     * @return array
     */
    private function analyzeUrl($sUrl)
    {
        $sCleanUrl  = str_replace(Yii::app()->baseUrl, '', (string) $sUrl); // we remove the base url to be sure that the first parameter is the one we want
        $aUrlParams = explode('/', $sCleanUrl);
        $sFilePath  = Yii::app()->getConfig('rootdir') . $sCleanUrl;
        $sPath = '';

        // TODO: check if tmp directory can be named differently via config
        if (isset($aUrlParams[1]) && $aUrlParams[1] == 'tmp') {
            $sType = 'published';
        } else {
            if (file_exists($sFilePath)) {
                $sType = 'toPublish';
                $sPath = $sFilePath;
            } else {
                $sType = 'cantPublish';
            }
        }

        return array('toPublish' => ($sType == 'toPublish'), 'sPathToFile' => $sPath);
    }

    /**
     * Registers a script package that is listed in {@link packages}.
     * @param string $name the name of the script package.
     * @return static the CClientScript object itself (to support method chaining, available since version 1.1.5).
     * @see renderCoreScript
     * @throws CException
     */
    public function registerPackageScriptOnPosition($name, $position)
    {
        if (isset($this->coreScripts[$name])) {
            $this->coreScripts[$name]['position'] = $position;
            return $this;
        }

        if (isset($this->packages[$name])) {
                    $package = $this->packages[$name];
        } else {
            if ($this->corePackages === null) {
                            $this->corePackages = require(YII_PATH . '/web/js/packages.php');
            }
            if (isset($this->corePackages[$name])) {
                            $package = $this->corePackages[$name];
            }
        }

        if (isset($package)) {
            $package['position'] = $position;

            if (!empty($package['depends'])) {
                foreach ($package['depends'] as $p) {
                                    $this->registerPackageScriptOnPosition($p, $position);
                }
            }

            $this->coreScripts[$name] = $package;
            $this->hasScripts = true;
            $params = func_get_args();
            $this->recordCachingAction('clientScript', 'registerPackageScriptOnPosition', $params);
        } elseif (YII_DEBUG) {
                    throw new CException('There is no LSYii_ClientScript package: ' . $name);
        } else {
                    Yii::log('There is no LSYii_ClientScript package: ' . $name, CLogger::LEVEL_WARNING, 'system.web.LSYii_ClientScript');
        }

        return $this;
    }

    /**
     * Renders the specified core javascript library.
     */
    public function renderCoreScripts()
    {
        if ($this->coreScripts === null) {
                    return;
        }

        $cssFiles = array();
        $jsFiles = array();
        $jsFilesPositioned = array();

        foreach ($this->coreScripts as $name => $package) {
            $baseUrl = $this->getPackageBaseUrl($name);
            if (!empty($package['js'])) {
                foreach ($package['js'] as $js) {
                    if (isset($package['position'])) {
                        $jsFilesPositioned[$package['position']][$baseUrl . '/' . $js] = $baseUrl . '/' . $js;
                    } else {
                        $jsFiles[$baseUrl . '/' . $js] = $baseUrl . '/' . $js;
                    }
                }
            }
            if (!empty($package['css'])) {
                foreach ($package['css'] as $css) {
                                    $cssFiles[$baseUrl . '/' . $css] = '';
                }
            }
        }
        // merge in place
        if ($cssFiles !== array()) {
            foreach ($this->cssFiles as $cssFile => $media) {
                            $cssFiles[$cssFile] = $media;
            }
            $this->cssFiles = $cssFiles;
        }
        if ($jsFiles !== array()) {
            if (isset($this->scriptFiles[$this->coreScriptPosition])) {
                foreach ($this->scriptFiles[$this->coreScriptPosition] as $url => $value) {
                                    $jsFiles[$url] = $value;
                }
            }
            $this->scriptFiles[$this->coreScriptPosition] = $jsFiles;
        }
        if ($jsFilesPositioned !== array()) {
            foreach ($jsFilesPositioned as $position => $fileArray) {
                if (isset($this->scriptFiles[$position])) {
                    foreach ($this->scriptFiles[$position] as $url => $value) {
                                            $fileArray[$url] = $value;
                    }
                }
                $this->scriptFiles[$position] = $fileArray;
            }
        }
    }

    /**
     * Inserts the scripts in the head section.
     * @param string $output the output to be inserted with scripts.
     */
    public function renderHead(&$output)
    {
        $html = '';

        foreach ($this->metaTags as $meta) {
                    $html .= CHtml::metaTag($meta['content'], null, null, $meta) . "\n";
        }
        foreach ($this->linkTags as $link) {
                    $html .= CHtml::linkTag(null, null, null, null, $link) . "\n";
        }
        foreach ($this->cssFiles as $url => $media) {
                    $html .= CHtml::cssFile($url, $media) . "\n";
        }

        //Propagate our debug settings into the javascript realm
        if (function_exists('getGlobalSetting')) {
            $debugFrontend = (int) getGlobalSetting('javascriptdebugfrntnd');
            $debugBackend  = (int) getGlobalSetting('javascriptdebugbcknd');
        } else {
            $debugFrontend = 0;
            $debugBackend  = 0;
        }

        $html .= "<script type='text/javascript'>window.debugState = {frontend : (" . $debugFrontend . " === 1), backend : (" . $debugBackend . " === 1)};</script>";

        if ($this->enableJavaScript) {
            if (isset($this->scriptFiles[self::POS_HEAD])) {
                foreach ($this->scriptFiles[self::POS_HEAD] as $scriptFileValueUrl => $scriptFileValue) {
                    if (is_array($scriptFileValue)) {
                        $scriptFileValue['class'] = isset($scriptFileValue['class']) ? $scriptFileValue['class'] . " headScriptTag" : "headScriptTag";
                        $html .= CHtml::scriptFile($scriptFileValueUrl, $scriptFileValue) . "\n";
                    } else {
                        $html .= CHtml::scriptFile($scriptFileValueUrl, array('class' => 'headScriptTag')) . "\n";
                    }
                }
            }

            if (isset($this->scripts[self::POS_HEAD])) {
                $html .= $this->renderScriptBatch($this->scripts[self::POS_HEAD]);
            }
        }

        if ($html !== '') {
            $count = 0;
            $output = preg_replace('/(<title\b[^>]*>|<\\/head\s*>)/is', '<###head###>$1', $output, 1, $count);
            if ($count) {
                            $output = str_replace('<###head###>', $html, $output);
            } else {
                            $output = $html . $output;
            }
        }
    }

    /**
     * Inserts the scripts at the beginning of the body section.
     * This is overwriting the core method and is exactly the same except the marked parts
     * @param string $output the output to be inserted with scripts.
     */
    public function renderBodyBegin(&$output)
    {
        $html = '';

        if (isset($this->scriptFiles[self::POS_PREBEGIN])) {
            foreach ($this->scriptFiles[self::POS_PREBEGIN] as $scriptFileUrl => $scriptFileValue) {
                if (is_array($scriptFileValue)) {
                                    $html .= CHtml::scriptFile($scriptFileUrl, $scriptFileValue) . "\n";
                } else {
                                    $html .= CHtml::scriptFile($scriptFileUrl) . "\n";
                }
            }
        }
        if (isset($this->scripts[self::POS_PREBEGIN])) {
            $html .= $this->renderScriptBatch($this->scripts[self::POS_PREBEGIN]);
        }
        if (isset($this->scriptFiles[self::POS_BEGIN])) {
            foreach ($this->scriptFiles[self::POS_BEGIN] as $scriptFileUrl => $scriptFileValue) {
                if (is_array($scriptFileValue)) {
                                    $html .= CHtml::scriptFile($scriptFileUrl, $scriptFileValue) . "\n";
                } else {
                                    $html .= CHtml::scriptFile($scriptFileUrl) . "\n";
                }
            }
        }
        if (isset($this->scripts[self::POS_BEGIN])) {
            $html .= $this->renderScriptBatch($this->scripts[self::POS_BEGIN]);
        }

        if ($html !== '') {
            $count = 0;
            if (preg_match('/<###begin###>/', $output)) {
                $count = 1;
            } else {
                $output = preg_replace('/(<body\b[^>]*>)/is', '$1<###begin###>', $output, 1, $count);
            }
            if ($count) {
                $output = str_replace('<###begin###>', $html, $output);
            } else {
                $output = $html . $output;
            }
        } else {
            $output = preg_replace('/<###begin###>/', '', $output, 1);
        }
    }

    /**
     * Inserts the scripts at the end of the body section.
     * This is overwriting the core method and is exactly the same except the marked parts
     * @param string $output the output to be inserted with scripts.
     */
    public function renderBodyEnd(&$output)
    {
        if (
            !isset($this->scriptFiles[self::POS_END]) && !isset($this->scripts[self::POS_END]) && !isset($this->scripts[self::POS_READY])
            && !isset($this->scripts[self::POS_LOAD]) && !isset($this->scripts[self::POS_POSTSCRIPT])
        ) {
            str_replace('<###end###>', '', $output);
            return;
        }

        $fullPage = 0;
        if (preg_match('/<###end###>/', $output)) {
                    $fullPage = 1;
        } else {
                    $output = preg_replace('/(<\\/body\s*>)/is', '<###end###>$1', $output, 1, $fullPage);
        }

        $html = '';
        if (isset($this->scriptFiles[self::POS_END])) {
            foreach ($this->scriptFiles[self::POS_END] as $scriptFileUrl => $scriptFileValue) {
                if (is_array($scriptFileValue)) {
                                    $html .= CHtml::scriptFile($scriptFileUrl, $scriptFileValue) . "\n";
                } else {
                                    $html .= CHtml::scriptFile($scriptFileUrl) . "\n";
                }
            }
        }
        $scripts = $this->scripts[self::POS_END] ?? array();

        if (isset($this->scripts[self::POS_READY])) {
            if ($fullPage) {
                            $scripts[] = "jQuery(function($) {\n" . implode("\n", $this->scripts[self::POS_READY]) . "\n});";
            } else {
                            $scripts[] = implode("\n", $this->scripts[self::POS_READY]);
            }
        }
        if (isset($this->scripts[self::POS_LOAD])) {
            if ($fullPage) {
                //This part is different to reflect the changes needed in the backend by the pjax loading of pages


                $scripts[] = "jQuery(document).on('ready pjax:complete',function() {\n" . implode("\n", $this->scripts[self::POS_LOAD]) . "\n});";
            } else {
                            $scripts[] = implode("\n", $this->scripts[self::POS_LOAD]);
            }
        }

        if (isset($this->scripts[self::POS_POSTSCRIPT])) {
            if ($fullPage) {
                //This part is different to reflect the changes needed in the backend by the pjax loading of pages
                $scripts[] = "jQuery(document).off('pjax:scriptcomplete.mainBottom').on('ready pjax:scriptcomplete.mainBottom', function() {\n" . implode("\n", $this->scripts[self::POS_POSTSCRIPT]) . "\n});";
            } else {
                $scripts[] = implode("\n", $this->scripts[self::POS_POSTSCRIPT]);
            }
        }
        if (App()->getConfig('debug') > 0) {
            $scripts[] = "jQuery(document).off('pjax:scriptsuccess.debugger').on('pjax:scriptsuccess.debugger',function(e) { console.ls.log('PJAX scriptsuccess', e); });";
            $scripts[] = "jQuery(document).off('pjax:scripterror.debugger').on('pjax:scripterror.debugger',function(e) { console.ls.log('PJAX scripterror', e); });";
            $scripts[] = "jQuery(document).off('pjax:scripttimeout.debugger').on('pjax:scripttimeout.debugger',function(e) { console.ls.log('PJAX scripttimeout', e); });";
            $scripts[] = "jQuery(document).off('pjax:success.debugger').on('pjax:success.debugger',function(e) { console.ls.log('PJAX success', e);});";
            $scripts[] = "jQuery(document).off('pjax:error.debugger').on('pjax:error.debugger',function(e) { console.ls.log('PJAX error', e);});";
        }

        //All scripts are wrapped into a section to be able to reload them accordingly
        if (!empty($scripts)) {
            $html .= $this->renderScriptBatch($scripts);
        }

        if ($fullPage) {
            $output = preg_replace('/<###end###>/', $html, $output, 1);
        } else {
            $output = $output . $html;
        }
    }

    /**
     * Renders the registered scripts.
     * This method is called in {@link CController::render} when it finishes
     * rendering content. CClientScript thus gets a chance to insert script tags
     * at <code>head</code> and <code>body</code> sections in the HTML output.
     * @param string $output the existing output that needs to be inserted with script tags
     */
    public function render(&$output)
    {
        /**
         * beforeCloseHtml event @see https://www.limesurvey.org/manual/BeforeCloseHtml
         * Set it before all other action allow registerScript by plugin
         * Allowlisting available controller (public plugin not happen for PluginsController using actionDirect, actionUnsecure event)
         */
        $publicControllers = array('option','optout','printanswers','register','statistics_user','survey','surveys','uploader');
        if (Yii::app()->getController() && in_array(Yii::app()->getController()->getId(), $publicControllers) && strpos($output, '</body>')) {
            $event = new PluginEvent('beforeCloseHtml');
            $surveyId = Yii::app()->getRequest()->getParam('surveyid', Yii::app()->getRequest()->getParam('sid', Yii::app()->getConfig('surveyid')));
            $event->set('surveyId', $surveyId); // Set to null if not set by param
            App()->getPluginManager()->dispatchEvent($event);
            $pluginHtml = $event->get('html');
            if (!empty($pluginHtml) && is_string($pluginHtml)) {
                $output = preg_replace('/(<\\/body\s*>)/is', "{$pluginHtml}$1", $output, 1);
            }
        }
        if (!$this->hasScripts) {
            return;
        }

        $this->renderCoreScripts();

        if (!empty($this->scriptMap)) {
            $this->remapScripts();
        }

        $this->unifyScripts();

        $this->renderHead($output);
        if ($this->enableJavaScript) {
            $this->renderBodyBegin($output);
            $this->renderBodyEnd($output);
        }
    }
}
