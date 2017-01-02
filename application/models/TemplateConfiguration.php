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
    public $sTemplateName='';                   // The template name
    public $iSurveyId='';                       // The current Survey Id. It can be void. It's use only to retreive the current template of a given survey
    public $config;                             // Will contain the config.xml
    /**
     * @var integer : the actual api version. Must be private : disallow update
     */
    private $apiVersion;                        // Version of the LS API when created

    public $pstplPath;                          // Path of the pstpl files
    public $viewPath;                           // Path of the views files (php files to replace existing core views)
    public $siteLogo;                           // Name of the logo file (like: logo.png)
    public $filesPath;                          // Path of the uploaded files
    public $cssFramework;                       // What framework css is used @see getFrameworkPackages()
    public $packages;                           // Array of package dependencies defined in config.xml
    public $depends;                            // List of all dependencies (could be more that just the config.xml packages)
    public $otherFiles;                         // Array of files in the file directory

    public $oSurvey;                            // The survey object
    public $isStandard;                         // Is this template a core one?
    public $path;                               // Path of this template
    public $hasConfigFile='';                   // Does it has a config.xml file?
    public $isOldTemplate;                      // Is it a 2.06 template?

    public $overwrite_question_views=false;     // Does it overwrites the question rendering from quanda.php? Must have a valid viewPath too.

    public $xmlFile;                            // What xml config file does it use? (config/minimal)

    /**
     * This method construct a template object, having all the needed configuration datas.
     * It checks if the required template is a core one or a user one.
     * If it's a user template, it will check if it's an old 2.0x template to provide default configuration values corresponding to the old template system
     * If it's not an old template, it will check if it has a configuration file to load its datas.
     * If it's not the case (template probably doesn't exist), it will load the default template configuration
     * TODO : more tests should be done, with a call to private function _is_valid_template(), testing not only if it has a config.xml, but also id this file is correct, if it has the needed pstpl files, if the files refered in css exist, etc.
     *
     * @param string $sTemplateName     the name of the template to load. The string come from the template selector in survey settings
     * @param string $iSurveyId        the id of the survey. If
     */
    public function setTemplateConfiguration($sTemplateName='', $iSurveyId='')
    {
        // If it's called from template editor, a template name will be provided.
        // If it's called for survey taking, a survey id will be provided
        if ($sTemplateName == '' && $iSurveyId == '')
        {
            /* Some controller didn't test completely survey id (PrintAnswersController for example), then set to default here */
            $sTemplateName=Template::templateNameFilter(Yii::app()->getConfig('defaulttemplate','default'));
            //throw new TemplateException("Template needs either template name or survey id");
        }
        $this->sTemplateName = $sTemplateName;
        $this->iSurveyId     = (int) $iSurveyId;

        if ($sTemplateName=='')
        {
            $this->oSurvey       = Survey::model()->findByPk($iSurveyId);
            if($this->oSurvey){
                $this->sTemplateName = $this->oSurvey->template;
            }else{
                $this->sTemplateName = Template::templateNameFilter(App()->getConfig('defaulttemplate','default'));
            }
        }
        // We check if  it's a CORE template
        $this->isStandard = $this->setIsStandard();
        // If the template is standard, its root is based on standardtemplaterootdir, else, it's a user template, its root is based on usertemplaterootdir
        $this->path = ($this->isStandard)?Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->sTemplateName:Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$this->sTemplateName;

        // If the template directory doesn't exist, it can be that:
        // - user deleted a custom theme
        // In any case, we just set Default as the template to use
        if (!is_dir($this->path))
        {
            $this->sTemplateName = 'default';
            $this->isStandard    = true;
            $this->path = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->sTemplateName;
            if(!$this->iSurveyId){
                setGlobalSetting('defaulttemplate', 'default');
            }
        }

        // If the template don't have a config file (maybe it has been deleted, or whatever),
        // then, we load the default template
        $this->hasConfigFile = (string) is_file($this->path.DIRECTORY_SEPARATOR.'config.xml');
        $this->isOldTemplate = ( !$this->hasConfigFile && is_file($this->path.DIRECTORY_SEPARATOR.'startpage.pstpl')); // TODO: more complex checks

        if (!$this->hasConfigFile)
        {
            // If it's an imported template from 2.06, we return default values
            if ( $this->isOldTemplate )
            {
                /* Must review: maybe some package ?*/
                $this->xmlFile = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.'minimal-config.xml';
            }
            else
            {
                $this->path = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->sTemplateName;
                $this->xmlFile = $this->path.DIRECTORY_SEPARATOR.'config.xml';
            }
        }
        else
        {
            $this->xmlFile = $this->path.DIRECTORY_SEPARATOR.'config.xml';
        }



        //////////////////////
        // Config file loading

        $bOldEntityLoaderState = libxml_disable_entity_loader(true);             // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection
        $sXMLConfigFile        = file_get_contents( realpath ($this->xmlFile));  // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string

        // Using PHP >= 5.4 then no need to decode encode + need attributes : then other function if needed :https://secure.php.net/manual/en/book.simplexml.php#108688 for example
        $this->config  = simplexml_load_string($sXMLConfigFile);

        // Template configuration
        // Ternary operators test if configuration entry exists in the config file (to avoid PHP notice in user custom templates)
        $this->apiVersion               = (isset($this->config->metadatas->apiVersion)) ? $this->config->metadatas->apiVersion:0;

        $this->pstplPath                = (isset($this->config->engine->pstpldirectory))           ? $this->path.DIRECTORY_SEPARATOR.$this->config->engine->pstpldirectory.DIRECTORY_SEPARATOR                            : $this->path;
        $this->viewPath                 = (isset($this->config->engine->viewdirectory))            ? $this->path.DIRECTORY_SEPARATOR.$this->config->engine->viewdirectory.DIRECTORY_SEPARATOR                            : '';

        $this->siteLogo                 = (isset($this->config->files->logo))                      ? $this->config->files->logo->filename                                                                                 : '';
        $this->filesPath                = (isset($this->config->engine->filesdirectory))           ? $this->path.DIRECTORY_SEPARATOR.$this->config->engine->filesdirectory.DIRECTORY_SEPARATOR                            : $this->path . '/files/';
        $this->cssFramework             = (isset($this->config->engine->cssframework))             ? $this->config->engine->cssframework                                                                                  : '';
        $this->cssFramework->name       = (isset($this->config->engine->cssframework->name))       ? $this->config->engine->cssframework->name                                                                            : (string)$this->config->engine->cssframework;
        $this->packages                 = (isset($this->config->engine->packages))                 ? $this->config->engine->packages                                                                             : array();

        /* Add options/package according to apiVersion */
        $this->fixTemplateByApi();

        /* Add depend package according to packages */
        $this->depends                  = $this->getDependsPackages();

        // overwrite_question_views accept different values : "true" or "yes"
        $this->overwrite_question_views = (isset($this->config->engine->overwrite_question_views)) ? ($this->config->engine->overwrite_question_views=='true' || $this->config->engine->overwrite_question_views=='yes' ) : false;

        $this->otherFiles               = $this->setOtherFiles();

        // Package creation
        $this->createTemplatePackage();

        libxml_disable_entity_loader($bOldEntityLoaderState);                   // Put back entity loader to its original state, to avoid contagion to other applications on the server
        return $this;
    }

    /**
     * Update the configuration file "last update" node.
     * For now, it's called only from template editor
     */
    public function actualizeLastUpdate()
    {
        $date = date("Y-m-d H:i:s");
        $config = simplexml_load_file(realpath ($this->xmlFile));
        $config->metadatas->last_update = $date;
        $config->asXML( realpath ($this->xmlFile) );                // Belt
        touch ( $this->path );                                      // & Suspenders ;-)
    }

    /**
     * Create a package for the asset manager.
     * The asset manager will push to tmp/assets/xyxyxy/ the whole template directory (with css, js, files, etc.)
     * And it will publish the CSS and the JS defined in config.xml. So CSS can use relative path for pictures.
     * The publication of the package itself is done for now in replacements_helper, to respect the old logic of {TEMPLATECSS} replacement keyword
     *
     * NOTE 1 : To refresh the assets, the base directory of the template must be updated.
     *
     * NOTE 2: By default, Asset Manager is off when debug mode is on.
     * Developers should then think about :
     * 1. refreshing their brower's cache (ctrl + F5) to see their changes
     * 2. update the config.xml last_update before pushing, to be sure that end users will have the new version
     *
     *
     * For more detail, see :
     *  http://www.yiiframework.com/doc/api/1.1/CClientScript#addPackage-detail
     *  http://www.yiiframework.com/doc/api/1.1/YiiBase#setPathOfAlias-detail
     *
     */
    private function createTemplatePackage()
    {
        Yii::setPathOfAlias('survey.template.path', $this->path);                                   // The package creation/publication need an alias
        Yii::setPathOfAlias('survey.template.viewpath', $this->viewPath);

        $aCssFiles   = (array)$this->config->files->css->filename;                                 // The CSS files of this template
        $aJsFiles    = (array)$this->config->files->js->filename;                                  // The JS files of this template
        $dir=getLanguageRTL(App()->language) ? 'rtl' : 'ltr';
        if (isset($this->config->files->$dir)){
            $aCssFilesDir = isset($this->config->files->$dir->css->filename) ? (array)$this->config->files->$dir->css->filename : array();
            $aJsFilesDir  = isset($this->config->files->$dir->js->filename) ? (array)$this->config->files->$dir->js->filename : array();
            $aCssFiles=array_merge($aCssFiles,$aCssFilesDir);
            $aJsFiles=array_merge($aJsFiles,$aJsFilesDir);
        }

        if (Yii::app()->getConfig('debug') == 0)
        {
            Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/scripts/deactivatedebug.js', CClientScript::POS_END);
        }

        // The package "survey-template" will be available from anywhere in the app now.
        // To publish it : Yii::app()->clientScript->registerPackage( 'survey-template' );
        // It will create the asset directory, and publish the css and js files
        /* @todo : using assets directory  ? */
        Yii::app()->clientScript->addPackage( 'survey-template', array(
            'basePath'    => 'survey.template.path',
            'css'         => $aCssFiles,
            'js'          => $aJsFiles,
            'depends'     => $this->depends,
        ) );
    }

    /**
     * Return the list of ALL files present in the file directory
     */
    private function setOtherFiles()
    {
        $otherfiles = array();
        if ( file_exists($this->filesPath) && $handle = opendir($this->filesPath))
        {
            while (false !== ($file = readdir($handle)))
            {
                if($file!='.' && $file!='..')
                {
                    if (!is_dir($file))
                    {
                        $otherfiles[] = array("name" => $file);
                    }
                }
            }
            closedir($handle);
        }
        return $otherfiles;
    }

    public function getName()
    {
        return $this->sTemplateName;
    }


    private function setIsStandard()
    {
        return Template::isStandardTemplate($this->sTemplateName);
    }

    /**
     * Fix template accorfing to apiVersion
     */
    private function fixTemplateByApi()
    {
        if($this->apiVersion<3){
            if(!is_file($this->pstplPath.DIRECTORY_SEPARATOR."message.pstpl")){
                $messagePstpl  =  "<div id='{MESSAGEID}-wrapper'>\n"
                                . "    {ERROR}\n"
                                . "    <div class='{MESSAGEID}-text'>{MESSAGE}</div>\n"
                                . "    {URL}"
                                . "</div>";
                file_put_contents($this->pstplPath.DIRECTORY_SEPARATOR."message.pstpl",$messagePstpl);
            }
            if(!is_file($this->pstplPath.DIRECTORY_SEPARATOR."form.pstpl")){
                $formTemplate  =  "<div class='{FORMID}-page'>\n"
                                . "    <div class='form-heading'>{FORMHEADING}</div>\n"
                                . "    {FORMMESSAGE}\n"
                                . "    {FORMERROR}\n"
                                . "    <div class='form-{FORMID}'>{FORM}</div>\n"
                                . "</div>";
                file_put_contents($this->pstplPath.DIRECTORY_SEPARATOR."form.pstpl",$formTemplate);
            }
            if(getLanguageRTL(App()->language)){
                unset($this->config->files->css);
                unset($this->config->files->js);
                unset($this->config->files->print_css);
            }
            $name=(isset($this->config->metadatas->name)) ? (string)$this->config->metadatas->name:null;
            if(in_array($name,array("Default","News Paper","Ubuntu Orange"))){/* LimeSurvey template only updated via GUI */
                $packages=new stdClass();
                $packages->package="template-default";
                $packages->ltr=new stdClass();
                $packages->ltr->package="template-default-ltr";
                $packages->rtl=new stdClass();
                $packages->rtl->package="template-default-rtl";
                $this->packages=$packages;
                Yii::app()->getClientScript()->registerMetaTag('width=device-width, initial-scale=1.0', 'viewport');
            }
        }
    }

    /**
     * Get the depends package
     * @uses self::@package
     */
    private function getDependsPackages()
    {

        /* Start by adding cssFramework package */
        $packages=$this->getFrameworkPackages();
        if(!getLanguageRTL(App()->getLanguage())){
            $packages=array_merge ($packages,$this->getFrameworkPackages('ltr'));
        }else{
            $packages=array_merge ($packages,$this->getFrameworkPackages('rtl'));
        }

        /* Core package */
        $packages[]='limesurvey-public';

        /* template packages */
        if(!empty($this->packages->package)){
            $packages=array_merge ($packages,(array)$this->packages->package);
        }
        /* Adding rtl/tl specific package (see https://bugs.limesurvey.org/view.php?id=11970#c42317 ) */
        $dir=getLanguageRTL(App()->language) ? 'rtl' : 'ltr';
        if(!empty($this->packages->$dir->package)){
            $packages=array_merge ($packages,(array)$this->packages->$dir->package);
        }

        return $packages;
    }
    /**
     * Set the framework package
     * @param string : dir (rtl|ltr|)
     * @use self::@cssFramework
     * @return string[] depends for framework
     */
    private function getFrameworkPackages($dir="")
    {
        $framework=isset($this->cssFramework->name)? (string)$this->cssFramework->name : (string)$this->cssFramework;
        $framework=$dir ? $framework."-".$dir : $framework;
        if(isset(Yii::app()->clientScript->packages[$framework])){
            $frameworkPackages=array();
            /* Theming */
            if($dir){
                $cssFrameworkCsss=isset($this->cssFramework->$dir->css) ? $this->cssFramework->$dir->css : array();
                $cssFrameworkJss=isset($this->cssFramework->$dir->js) ? $this->cssFramework->$dir->js : array();
            }else{
                $cssFrameworkCsss=isset($this->cssFramework->css) ? $this->cssFramework->css : array();
                $cssFrameworkJss=isset($this->cssFramework->js) ? $this->cssFramework->js : array();
            }
            if(empty($cssFrameworkCsss) && empty($cssFrameworkJss)){
                $frameworkPackages[]=$framework;
            }else{
                /* Need to create an adapted core framework */
                $cssFrameworkPackage=Yii::app()->clientScript->packages[$framework];
                /* Need to create an adapted template/theme framework */
                $packageCss=array();
                $packageJs=array();
                /* css file to replace from default package */
                $cssDelete=array();
                foreach($cssFrameworkCsss as $cssFrameworkCss){
                    if(isset($cssFrameworkCss['replace'])){
                        $cssDelete[]=$cssFrameworkCss['replace'];
                    }
                    if((string)$cssFrameworkCss){
                        $packageCss[]=(string)$cssFrameworkCss;
                    }
                }
                if(isset($cssFrameworkPackage['css'])){
                    $cssFrameworkPackage['css']=array_diff($cssFrameworkPackage['css'],$cssDelete);
                }
                $jsDelete=array();
                foreach($cssFrameworkJss as $cssFrameworkJs){
                    if(isset($cssFrameworkJs['replace'])){
                        $jsDelete[]=$cssFrameworkJs['replace'];
                    }
                    if((string)$cssFrameworkJs){
                        $packageJs[]=(string)$cssFrameworkJs;
                    }
                }
                if(isset($cssFrameworkPackage['js'])){
                    $cssFrameworkPackage['js']=array_diff($cssFrameworkPackage['js'],$cssDelete);
                }
                /* And now : we add : core package fixed + template/theme package */
                Yii::app()->clientScript->packages[$framework]=$cssFrameworkPackage; /* @todo : test if empty css and js : just add depends if yes */
                $aDepends=array(
                    $framework,
                );

                Yii::app()->clientScript->addPackage( $framework.'-template', array(
                    'basePath'    => 'survey.template.path',
                    'css'         => $packageCss,
                    'js'          => $packageJs,
                    'depends'     => $aDepends,
                ));
                $frameworkPackages[]=$framework.'-template';
            }
            return $frameworkPackages;
        }/*elseif($framework){
            throw error ? Only for admin template editor ? disable and reset to default ?
        }*/
        return array();
    }
    /**
     * get the template API version
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }
}
