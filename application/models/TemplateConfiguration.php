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

    public $viewPath;                           // Path of the pstpl files
    public $siteLogo;                           // Name of the logo file (like: logo.png)
    public $filesPath;                          // Path of the uploaded files
    public $cssFramework;                       // What framework css is used (for now, this parameter is used only to deactive bootstrap for retrocompatibility)
    public $packages;                           // Array of package dependencies defined in config.xml
    public $depends;                            // List of all dependencies (could be more that just the config.xml packages)
    public $otherFiles;                         // Array of files in the file directory

    public $oSurvey;                            // The survey object
    public $isStandard;                         // Is this template a core one?
    public $path;                               // Path of this template
    public $hasConfigFile='';                   // Does it has a config.xml file?
    public $isOldTemplate;                      // Is it a 2.06 template?

    public $overwrite_question_views=false;     // Does it overwrites the question rendering from quanda.php?

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
            throw new TemplateException("Template needs either template name or survey id");
        }

        $this->sTemplateName = $sTemplateName;
        $this->iSurveyId     = (int) $iSurveyId;

        if ($sTemplateName=='')
        {
            $this->oSurvey       = Survey::model()->findByPk($iSurveyId);
            $this->sTemplateName = $this->oSurvey->template;
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
            setGlobalSetting('defaulttemplate', 'default');
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

        // Simple Xml is buggy on PHP < 5.4. The [ array -> json_encode -> json_decode ] workaround seems to be the most used one.
        // @see: http://php.net/manual/de/book.simplexml.php#105330 (top comment on PHP doc for simplexml)
        $this->config  = json_decode( json_encode ( ( array ) simplexml_load_string($sXMLConfigFile), 1));

        // Template configuration
        // Ternary operators test if configuration entry exists in the config file (to avoid PHP notice in user custom templates)
        $this->viewPath                 = (isset($this->config->engine->pstpldirectory))           ? $this->path.DIRECTORY_SEPARATOR.$this->config->engine->pstpldirectory.DIRECTORY_SEPARATOR                            : $this->path;
        $this->siteLogo                 = (isset($this->config->files->logo))                      ? $this->config->files->logo->filename                                                                                 : '';
        $this->filesPath                = (isset($this->config->engine->filesdirectory))           ? $this->path.DIRECTORY_SEPARATOR.$this->config->engine->filesdirectory.DIRECTORY_SEPARATOR                            : $this->path . '/files/';
        $this->cssFramework             = (isset($this->config->engine->cssframework))             ? $this->config->engine->cssframework                                                                                  : '';
        $this->packages                 = (isset($this->config->engine->packages->package))        ? $this->config->engine->packages->package                                                                             : array();

        // overwrite_question_views accept different values : "true" or "yes"
        $this->overwrite_question_views = (isset($this->config->engine->overwrite_question_views)) ? ($this->config->engine->overwrite_question_views=='true' || $this->config->engine->overwrite_question_views=='yes' ) : false;

        $this->otherFiles               = $this->setOtherFiles();
        $this->depends                  = $this->packages;  // TODO: remove

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

        $oCssFiles   = $this->config->files->css->filename;                                 // The CSS files of this template
        $oJsFiles    = $this->config->files->js->filename;                                  // The JS files of this template

        $jsDeactivateConsole = "
            <script> var dummyConsole = {
                log : function(){},
                error : function(){}
            };
            console = dummyConsole;
            window.console = dummyConsole;
        </script>";

        if (getLanguageRTL(App()->language))
        {
            $oCssFiles = $this->config->files->rtl->css->filename; // In RTL mode, original CSS files should not be loaded, else padding-left could be added to padding-right.)
            $oJsFiles  = $this->config->files->rtl->js->filename;   // In RTL mode,
        }

        if (Yii::app()->getConfig('debug') == 0)
        {
            Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/scripts/deactivatedebug.js', CClientScript::POS_END);
        }

        $aCssFiles = (array) $oCssFiles;
        $aJsFiles  = (array) $oJsFiles;


        // The package "survey-template" will be available from anywhere in the app now.
        // To publish it : Yii::app()->clientScript->registerPackage( 'survey-template' );
        // It will create the asset directory, and publish the css and js files
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
        return in_array($this->sTemplateName,
            array(
                'default',
                'news_paper',
                'ubuntu_orange',
            )
        );
    }

}
