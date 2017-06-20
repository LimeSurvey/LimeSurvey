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

/**
 * Template Configuration Model
 *
 * This model retrieves all the data of template configuration from the configuration file
 *
 * @package       LimeSurvey
 * @subpackage    Backend
 */
class TemplateConfiguration extends CFormModel
{
    /** @var string $sTemplateName The template name */
    public $sTemplateName='';

    /** @var string $sPackageName Name of the asset package of this template*/
    public $sPackageName;

    /** @var  string $siteLogo Name of the logo file (like: logo.png) */
    public $siteLogo;

    /** @var  string $path Path of this template */
    public $path;

    /** @var string[] $sTemplateurl Url to reach the framework */
    public $sTemplateurl;

    /** @var  string $viewPath Path of the views files (twig template) */
    public $viewPath;

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

    public $templateEditor;


    /** @var string $iSurveyId The current Survey Id. It can be void. It's use only to retreive the current template of a given survey */
    private $iSurveyId='';

    /** @var string $hasConfigFile Does it has a config.xml file? */
    private $hasConfigFile='';//

    /** @var stdClass[] $packages Array of package dependencies defined in config.xml*/
    private $packages;

    /** @var string[] $depends List of all dependencies (could be more that just the config.xml packages) */
    private $depends = array();

    /** @var string $xmlFile What xml config file does it use? (config/minimal) */
    private $xmlFile;

    /**  @var integer $apiVersion: Version of the LS API when created. Must be private : disallow update */
    private $apiVersion;


    /**
     * Constructs a template configuration object
     * If any problem (like template doesn't exist), it will load the default template configuration
     *
     * @param  string $sTemplateName the name of the template to load. The string comes from the template selector in survey settings
     * @param  string $iSurveyId the id of the survey. If
     * @return $this
     */
    public function setTemplateConfiguration($sTemplateName='', $iSurveyId='')
    {
        $this->setTemplateName($sTemplateName, $iSurveyId);                     // Check and set template name
        $this->setIsStandard();                                                 // Check if  it is a CORE template
        $this->setPath();                                                       // Check and set path
        $this->readManifest();                                                  // Check and read the manifest to set local params
        $this->setMotherTemplates();                                            // Recursive mother templates configuration
        $this->setThisTemplate();                                               // Set the main config values of this template
        $this->createTemplatePackage($this);                                    // Create an asset package ready to be loaded
        return $this;
    }

    /**
     * Update the configuration file "last update" node.
     * For now, it is called only from template editor
     */
    public function actualizeLastUpdate()
    {
        $date   = date("Y-m-d H:i:s");
        $config = simplexml_load_file(realpath ($this->xmlFile));
        $config->metadatas->last_update = $date;
        $config->asXML( realpath ($this->xmlFile) );                // Belt
        touch ( $this->path );                                      // & Suspenders ;-)
    }


    /**
     * get the template API version
     * @return integer
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
    * This function returns the complete URL path to a given template name
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
     * Create a package for the asset manager.
     * The asset manager will push to tmp/assets/xyxyxy/ the whole template directory (with css, js, files, etc.)
     * And it will publish the CSS and the JS defined in config.xml. So CSS can use relative path for pictures.
     * The publication of the package itself is in LSETwigViewRenderer::renderTemplateFromString()
     *
     */
    private function createTemplatePackage($oTemplate)
    {
        // Each template in the inheritance tree needs a specific alias
        $sPathName  = 'survey.template-'.$oTemplate->sTemplateName.'.path';
        $sViewName  = 'survey.template-'.$oTemplate->sTemplateName.'.viewpath';

        Yii::setPathOfAlias($sPathName, $oTemplate->path);
        Yii::setPathOfAlias($sViewName, $oTemplate->viewPath);

        $aCssFiles   = isset($oTemplate->config->files->css->filename)?(array) $oTemplate->config->files->css->filename:array();        // The CSS files of this template
        $aJsFiles    = isset($oTemplate->config->files->js->filename)? (array) $oTemplate->config->files->js->filename:array();         // The JS files of this template
        $dir         = getLanguageRTL(App()->language) ? 'rtl' : 'ltr';

        // Remove/Replace mother files
        $aCssFiles = $this->changeMotherConfiguration('css', $aCssFiles);
        $aJsFiles  = $this->changeMotherConfiguration('js',  $aJsFiles);

        if (isset($oTemplate->config->files->$dir)) {
            $aCssFilesDir = isset($oTemplate->config->files->$dir->css->filename) ? (array) $oTemplate->config->files->$dir->css->filename : array();
            $aJsFilesDir  = isset($oTemplate->config->files->$dir->js->filename)  ? (array) $oTemplate->config->files->$dir->js->filename : array();
            $aCssFiles    = array_merge($aCssFiles,$aCssFilesDir);
            $aJsFiles     = array_merge($aJsFiles,$aJsFilesDir);
        }

        if (Yii::app()->getConfig('debug') == 0) {
            Yii::app()->clientScript->registerScriptFile( Yii::app()->getConfig("generalscripts"). 'deactivatedebug.js', CClientScript::POS_END);
        }

        $this->sPackageName = 'survey-template-'.$this->sTemplateName;
        $sTemplateurl       = $oTemplate->getTemplateURL();

        // The package "survey-template-{sTemplateName}" will be available from anywhere in the app now.
        // To publish it : Yii::app()->clientScript->registerPackage( 'survey-template-{sTemplateName}' );
        // Depending on settings, it will create the asset directory, and publish the css and js files
        Yii::app()->clientScript->addPackage( $this->sPackageName, array(
            'devBaseUrl'  => $sTemplateurl,                                     // Used when asset manager is off
            'basePath'    => $sPathName,                                        // Used when asset manager is on
            'css'         => $aCssFiles,
            'js'          => $aJsFiles,
            'depends'     => $oTemplate->depends,
        ) );
    }

    /**
     * Change the mother template configuration depending on template settings
     * @var $sType     string   the type of settings to change (css or js)
     * @var $aSettings array    array of local setting
     * @return array
     */
    private function changeMotherConfiguration( $sType, $aSettings )
    {
        foreach( $aSettings as $key => $aSetting){
            if (!empty($aSetting['replace']) || !empty($aSetting['remove'])){
                Yii::app()->clientScript->removeFileFromPackage($this->oMotherTemplate->sPackageName, $sType, $aSetting['replace'] );
                unset($aSettings[$key]);
            }
        }

        return $aSettings;
    }

    /**
     * Read the config.xml file of the template and push its contents to $this->config
     */
    private function readManifest()
    {
        $this->xmlFile         = $this->path.DIRECTORY_SEPARATOR.'config.xml';
        $bOldEntityLoaderState = libxml_disable_entity_loader(true);            // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection
        $sXMLConfigFile        = file_get_contents( realpath ($this->xmlFile)); // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string
        $this->config          = simplexml_load_string($sXMLConfigFile);        // Using PHP >= 5.4 then no need to decode encode + need attributes : then other function if needed :https://secure.php.net/manual/en/book.simplexml.php#108688 for example

        libxml_disable_entity_loader($bOldEntityLoaderState);                   // Put back entity loader to its original state, to avoid contagion to other applications on the server
    }

    /**
     * Configure the mother template (and its mother templates)
     * This is an object recursive call to TemplateConfiguration::setTemplateConfiguration()
     */
    private function setMotherTemplates()
    {
        if (isset($this->config->metadatas->extends)){
            $sMotherTemplateName   = (string) $this->config->metadatas->extends;
            $this->oMotherTemplate = new TemplateConfiguration;
            $this->oMotherTemplate->setTemplateConfiguration($sMotherTemplateName); // Object Recursion
        }
    }

    /**
     * Set the path of the current template
     * It checks if it's a core or a user template, if it exists, and if it has a config file
     */
    private function setPath()
    {
        // If the template is standard, its root is based on standardtemplaterootdir, else, it is a user template, its root is based on usertemplaterootdir
        $this->path = ($this->isStandard)?Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->sTemplateName:Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$this->sTemplateName;

        // If the template directory doesn't exist, we just set Default as the template to use
        // TODO: create a method "setToDefault"
        if (!is_dir($this->path)) {
            $this->sTemplateName = 'default';
            $this->isStandard    = true;
            $this->path = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->sTemplateName;
            if(!$this->iSurveyId){
                setGlobalSetting('defaulttemplate', 'default');
            }
        }

        // If the template doesn't have a config file (maybe it has been deleted, or whatever),
        // then, we load the default template
        $this->hasConfigFile = (string) is_file($this->path.DIRECTORY_SEPARATOR.'config.xml');
        if (!$this->hasConfigFile) {
            $this->path = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->sTemplateName;

        }
    }

    /**
     * Set the template name.
     * If no templateName provided, then a survey id should be given (it will then load the template related to the survey)
     *
     * @var     $sTemplateName  string the name of the template
     * @var     $iSurveyId      int    the id of the survey
      */
    private function setTemplateName($sTemplateName='', $iSurveyId='')
    {
        // If it is called from the template editor, a template name will be provided.
        // If it is called for survey taking, a survey id will be provided
        if ($sTemplateName == '' && $iSurveyId == '') {
            /* Some controller didn't test completely survey id (PrintAnswersController for example), then set to default here */
            $sTemplateName = Template::templateNameFilter(Yii::app()->getConfig('defaulttemplate','default'));
        }

        $this->sTemplateName = $sTemplateName;
        $this->iSurveyId     = (int) $iSurveyId;

        if ($sTemplateName == '') {
            $oSurvey       = Survey::model()->findByPk($iSurveyId);

            if($oSurvey) {
                $this->sTemplateName = $oSurvey->template;
            } else {
                $this->sTemplateName = Template::templateNameFilter(App()->getConfig('defaulttemplate','default'));
            }
        }
    }

    /**
     * Set the default configuration values for the template, and use the motherTemplate value if needed
     */
    private function setThisTemplate()
    {
        // Mandtory setting in config XML (can be not set in inheritance tree, but must be set in mother template (void value is still a setting))
        $this->apiVersion               = (isset($this->config->metadatas->apiVersion))            ? $this->config->metadatas->apiVersion                                                       : $this->oMotherTemplate->apiVersion;

        $this->viewPath                 = (!empty($this->config->xpath("//viewdirectory")))   ? $this->path.DIRECTORY_SEPARATOR.$this->config->engine->viewdirectory.DIRECTORY_SEPARATOR   : $this->oMotherTemplate->viewPath;
        $this->filesPath                = (!empty($this->config->xpath("//filesdirectory")))  ? $this->path.DIRECTORY_SEPARATOR.$this->config->engine->filesdirectory.DIRECTORY_SEPARATOR   : $this->oMotherTemplate->filesPath;
        $this->templateEditor           = (!empty($this->config->xpath("//template_editor"))) ?  $this->config->engine->template_editor : $this->oMotherTemplate->templateEditor;
        $this->siteLogo                 = (!empty($this->config->xpath("//logo")))            ? $this->config->files->logo->filename                                                       : $this->oMotherTemplate->siteLogo;

        // Not mandatory (use package dependances)
        $this->cssFramework             = (!empty($this->config->xpath("//cssframework")))    ? $this->config->engine->cssframework                                                                                  : '';
        $this->packages                 = (!empty($this->config->xpath("//packages")))        ? $this->config->engine->packages                                                                                      : array();

        // Add depend package according to packages
        $this->depends                  = array_merge($this->depends, $this->getDependsPackages($this));
    }


    /**
     * @return bool
     */
    private function setIsStandard()
    {
        $this->isStandard = Template::isStandardTemplate($this->sTemplateName);
    }


    /**
     * Get the depends package
     * @uses self::@package
     * @return string[]
     */
    private function getDependsPackages($oTemplate)
    {

        /* Start by adding cssFramework package */
        $packages = $this->getFrameworkPackages($oTemplate);

        if (!getLanguageRTL(App()->getLanguage())) {
            $packages = array_merge ($packages, $this->getFrameworkPackages($oTemplate, 'ltr'));
        } else {
            $packages = array_merge ($packages, $this->getFrameworkPackages($oTemplate, 'rtl'));
        }

        /* Core package */
        $packages[]='limesurvey-public';

        /* template packages */
        if (!empty($this->packages->package)) {
            $packages = array_merge ($packages, (array)$this->packages->package);
        }

        /* Adding rtl/tl specific package (see https://bugs.limesurvey.org/view.php?id=11970#c42317 ) */
        $dir = getLanguageRTL(App()->language) ? 'rtl' : 'ltr';

        if (!empty($this->packages->$dir->package)) {
            $packages = array_merge ($packages, (array)$this->packages->$dir->package);
        }

        if (isset($this->config->metadatas->extends)){
            $sMotherTemplateName = (string) $this->config->metadatas->extends;
            $packages[]          = 'survey-template-'.$sMotherTemplateName;
        }

        return $packages;
    }

    /**
     * Set the framework package
     * @param string $dir (rtl|ltr|)
     * @use self::@cssFramework
     * @return string[] depends for framework
     */
    private function getFrameworkPackages($oTemplate, $dir="")
    {
        // If current template doesn't have a name for the framework package, we use the mother's one
        $framework = isset($oTemplate->cssFramework->name) ? (string) $oTemplate->cssFramework->name : (string) $oTemplate->oMotherTemplate->cssFramework;
        $framework = $dir ? $framework."-".$dir : $framework;

        if  ( isset(Yii::app()->clientScript->packages[$framework]) ) {

            $frameworkPackages = array();

            /* Theming */
            if ($dir) {
                $cssFrameworkCsss = isset ( $oTemplate->cssFramework->$dir->css ) ? $oTemplate->cssFramework->$dir->css : array();
                $cssFrameworkJss  = isset ( $oTemplate->cssFramework->$dir->js  ) ? $oTemplate->cssFramework->$dir->js  : array();
            } else {
                $cssFrameworkCsss = isset ( $oTemplate->cssFramework->css       ) ? $oTemplate->cssFramework->css       : array();
                $cssFrameworkJss  = isset ( $oTemplate->cssFramework->js        ) ? $oTemplate->cssFramework->js        : array();
            }

            if (empty($cssFrameworkCsss) && empty($cssFrameworkJss)) {
                $frameworkPackages[] = $framework;
            } else {

                $cssFrameworkPackage = Yii::app()->clientScript->packages[$framework];     // Need to create an adapted core framework
                $packageCss          = array();                                            // Need to create an adapted template/theme framework */
                $packageJs           = array();                                            // css file to replace from default package */
                $cssDelete           = array();

                foreach($cssFrameworkCsss as $cssFrameworkCss) {
                    if(isset($cssFrameworkCss['replace'])) {
                        $cssDelete[] = $cssFrameworkCss['replace'];
                    }
                    if((string)$cssFrameworkCss) {
                        $packageCss[] = (string) $cssFrameworkCss;
                    }
                }

                if(isset($cssFrameworkPackage['css'])) {
                    $cssFrameworkPackage['css']=array_diff($cssFrameworkPackage['css'],$cssDelete);
                }

                $jsDelete=array();
                foreach($cssFrameworkJss as $cssFrameworkJs) {
                    if(isset($cssFrameworkJs['replace'])) {
                        $jsDelete[] = $cssFrameworkJs['replace'];
                    }
                    if((string)$cssFrameworkJs) {
                        $packageJs[] = (string)$cssFrameworkJs;
                    }
                }
                if(isset($cssFrameworkPackage['js'])) {
                    $cssFrameworkPackage['js'] = array_diff($cssFrameworkPackage['js'],$cssDelete);
                }

                /* And now : we add : core package fixed + template/theme package */
                Yii::app()->clientScript->packages[$framework] = $cssFrameworkPackage; /* @todo : test if empty css and js : just add depends if yes */
                $aDepends=array(
                    $framework,
                );

                $sTemplateurl = $oTemplate->getTemplateURL();
                $sPathName    = 'survey.template-'.$oTemplate->sTemplateName.'.path';

                Yii::app()->clientScript->addPackage(
                    $framework.'-template', array(
                        'devBaseUrl'  => $sTemplateurl,                        // Don't use asset manager
                        'basePath'    => $sPathName,                            // basePath: the asset manager will be used
                        'css'         => $packageCss,
                        'js'          => $packageJs,
                        'depends'     => $aDepends,
                    )
                );
                $frameworkPackages[]=$framework.'-template';
            }
            return $frameworkPackages;
        }/*elseif($framework){
            throw error ? Only for admin template editor ? disable and reset to default ?
        }*/
        return array();
    }

}
