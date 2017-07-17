<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey
* Copyright (C) 2007-2015 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/*
 * Common methods for TemplateConfiguration and TemplateManifest
 */

 class TemplateConfig extends CActiveRecord
 {
    /** @var string $sTemplateName The template name */
    public $sTemplateName='';

    /** @var string $sPackageName Name of the asset package of this template*/
    public $sPackageName;

    /** @var  string $path Path of this template */
    public $path;

    /** @var string[] $sTemplateurl Url to reach the framework */
    public $sTemplateurl;

    /** @var  string $viewPath Path of the views files (twig template) */
    public $viewPath;

    /** @var  string $sFilesDirectory name of the file directory */
    public $sFilesDirectory;

    /** @var  string $filesPath Path of the tmeplate's files */
    public $filesPath;

    /** @var string[] $cssFramework What framework css is used */
    public $cssFramework;

    /** @var boolean $isStandard Is this template a core one? */
    public $isStandard;

    /** @var SimpleXMLElement $config Will contain the config.xml */
    public $config;

    /** @var TemplateConfiguration $oMotherTemplate The template name */
    public $oMotherTemplate;

    /** @var array $oOptions The template options */
    public $oOptions;


    /** @var string[] $depends List of all dependencies (could be more that just the config.xml packages) */
    protected $depends = array();

    /**  @var integer $apiVersion: Version of the LS API when created. Must be private : disallow update */
    protected $apiVersion;

    /** @var string $iSurveyId The current Survey Id. It can be void. It's use only to retreive the current template of a given survey */
    protected $iSurveyId='';

    /** @var string $hasConfigFile Does it has a config.xml file? */
    protected $hasConfigFile='';//

    /** @var stdClass[] $packages Array of package dependencies defined in config.xml*/
    protected $packages;

    /** @var string $xmlFile What xml config file does it use? (config/minimal) */
    protected $xmlFile;



     /**
      * get the template API version
      * @return integer
      */
     public function getApiVersion()
     {
         return $this->apiVersion;
     }

     /**
     * Returns the complete URL path to a given template name
     *
     * @param string $sTemplateName
     * @return string template url
     */
     public function getTemplateURL()
     {
         if(!isset($this->sTemplateurl)){
             $this->sTemplateurl = Template::getTemplateURL($this->sTemplateName);
         }
         return $this->sTemplateurl;
     }


     /**
     * Get the template for a given file. It checks if a file exist in the current template or in one of its mother templates
     *
     * @param  string $sFile      the  file to look for (must contain relative path, unless it's a view file)
     * @param string $oRTemplate template from which the recurrence should start
     * @return TemplateManifest
     */
     public function getTemplateForFile($sFile, $oRTemplate)
     {
         while (!file_exists($oRTemplate->path.'/'.$sFile) && !file_exists($oRTemplate->viewPath.$sFile)){
             $oMotherTemplate = $oRTemplate->oMotherTemplate;
             if(!($oMotherTemplate instanceof TemplateConfiguration)){
                 throw new Exception("no template found for  $sFile!");
                 break;
             }
             $oRTemplate = $oMotherTemplate;
         }

         return $oRTemplate;
     }


     /**
      * Create a package for the asset manager.
      * The asset manager will push to tmp/assets/xyxyxy/ the whole template directory (with css, js, files, etc.)
      * And it will publish the CSS and the JS defined in config.xml. So CSS can use relative path for pictures.
      * The publication of the package itself is in LSETwigViewRenderer::renderTemplateFromString()
      *
      * @param $oTemplate TemplateManifest
      */
     protected function createTemplatePackage($oTemplate)
     {
         // Each template in the inheritance tree needs a specific alias
         $sPathName  = 'survey.template-'.$oTemplate->sTemplateName.'.path';
         $sViewName  = 'survey.template-'.$oTemplate->sTemplateName.'.viewpath';

         Yii::setPathOfAlias($sPathName, $oTemplate->path);
         Yii::setPathOfAlias($sViewName, $oTemplate->viewPath);

         $aCssFiles  = $aJsFiles = array();

         // First we add the framework replacement (bootstrap.css must be loaded before template.css)
         $aCssFiles  = $this->getFrameworkAssetsToReplace('css');
         $aJsFiles   = $this->getFrameworkAssetsToReplace('js');

         // Then we add the template config files
         $aTCssFiles = $this->getFilesToLoad($oTemplate, 'css');
         $aTJsFiles  = $this->getFilesToLoad($oTemplate, 'js');

         $aCssFiles  = array_merge($aCssFiles, $aTCssFiles);
         $aJsFiles  = array_merge($aJsFiles, $aTJsFiles);

         $dir        = getLanguageRTL(App()->language) ? 'rtl' : 'ltr';

         // Remove/Replace mother template files
         $aCssFiles = $this->changeMotherConfiguration('css', $aCssFiles);
         $aJsFiles  = $this->changeMotherConfiguration('js',  $aJsFiles);

         // Then we add the direction files if they exist
         // TODO: attribute system rather than specific fields for RTL

         $this->sPackageName = 'survey-template-'.$this->sTemplateName;
         $sTemplateurl       = $oTemplate->getTemplateURL();

         $aDepends          = empty($oTemplate->depends)?array():$oTemplate->depends;


         // The package "survey-template-{sTemplateName}" will be available from anywhere in the app now.
         // To publish it : Yii::app()->clientScript->registerPackage( 'survey-template-{sTemplateName}' );
         // Depending on settings, it will create the asset directory, and publish the css and js files
         Yii::app()->clientScript->addPackage( $this->sPackageName, array(
             'devBaseUrl'  => $sTemplateurl,                                     // Used when asset manager is off
             'basePath'    => $sPathName,                                        // Used when asset manager is on
             'css'         => $aCssFiles,
             'js'          => $aJsFiles,
             'depends'     => $aDepends,
         ) );
     }

     /**
      * Get the file path for a given template.
      * It will check if css/js (relative to path), or view (view path)
      * It will search for current template and mother templates
      *
      * @param   string  $sFile          relative path to the file
      * @param   string  $oTemplate      the template where to look for (and its mother templates)
      */
     protected function getFilePath($sFile, $oTemplate)
     {
         // Remove relative path
         $sFile = trim($sFile, '.');
         $sFile = trim($sFile, '/');

         // Retreive the correct template for this file (can be a mother template)
         $oTemplate = $this->getTemplateForFile($sFile, $oTemplate);

         if($oTemplate instanceof TemplateConfiguration){
             if(file_exists($oTemplate->path.'/'.$sFile)){
                 return $oTemplate->path.'/'.$sFile;
             }elseif(file_exists($oTemplate->viewPath.$sFile)){
                 return $oTemplate->viewPath.$sFile;
             }
         }
         return false;
     }

     /**
      * Get the depends package
      * @uses self::@package
      * @return string[]
      */
     protected function getDependsPackages($oTemplate)
     {
         $dir = (getLanguageRTL(App()->getLanguage()))?'rtl':'ltr';

         /* Core package */
         $packages[] = 'limesurvey-public';
         $packages[] = 'template-core';
         $packages[] = ( $dir == "ltr")? 'template-core-ltr' : 'template-core-rtl'; // Awesome Bootstrap Checkboxes

         /* bootstrap */
         if(!empty($this->cssFramework)){

             // Basic bootstrap package
             if((string)$this->cssFramework->name == "bootstrap"){
                 $packages[] = 'bootstrap';
             }

             // Rtl version of bootstrap
             if ($dir == "rtl"){
                 $packages[] = 'bootstrap-rtl';
             }

             // Remove unwanted bootstrap stuff
             foreach( $this->getFrameworkAssetsToReplace('css', true) as $toReplace){
                 Yii::app()->clientScript->removeFileFromPackage('bootstrap', 'css', $toReplace );
             }

             foreach( $this->getFrameworkAssetsToReplace('js', true) as $toReplace){
                 Yii::app()->clientScript->removeFileFromPackage('bootstrap', 'js', $toReplace );
             }
         }

         // Moter Template Package
         $packages = $this->addMotherTemplatePackage($packages);

         return $packages;
     }



     /**
     * @return bool
     */
     protected function setIsStandard()
     {
        $this->isStandard = Template::isStandardTemplate($this->sTemplateName);
     }

    // TODO: try to refactore most of those methods in TemplateConfiguration and TemplateManifest so we can define their body here.
    // It will consist in adding private methods to get the values of variables... See what has been done for createTemplatePackage
    // Then, the lonely differences between TemplateManifest and TemplateConfiguration should be how to retreive and format the data
    // Note: signature are already the same

    public function setTemplateConfiguration($sTemplateName='', $iSurveyId=''){}
    public function addFileReplacement($sFile, $sType){}

    protected function getFilesToLoad($oTemplate, $sType){}
    protected function changeMotherConfiguration( $sType, $aSettings ){}
    protected function getFrameworkAssetsToReplace( $sType, $bInlcudeRemove = false){}
    protected function removeFileFromPackage( $sPackageName, $sType, $aSettings ){}
    protected function setMotherTemplates(){}
    protected function setThisTemplate(){}
 }
