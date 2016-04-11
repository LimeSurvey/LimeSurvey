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
    public $sTemplateName='';
    public $iSurveyId='';
    public $config;

    public $viewPath;
    public $siteLogo;
    public $filesPath;
    public $cssFramework;
    public $packages;
    public $depends;
    public $otherFiles;

    public $oSurvey;
    public $isStandard;
    public $name;
    public $path;
    public $hasConfigFile='';
    public $isOldTemplate;

    public $xmlFile;

    /**
     * This method construct a template object, having all the needed configuration datas.
     * It checks if the required template is a core one or a user one.
     * If it's a user template, it will check if it's an old 2.0x template to provide default configuration values corresponding to the old template system
     * If it's not an old template, it will check if it has a configuration file to load its datas.
     * If it's not the case (template probably doesn't exist), it will load the default template configuration
     * TODO : more tests should be done, with a call to private function _is_valid_template(), testing not only if it has a config.xml, but also id this file is correct, if it has the needed pstpl files, if the files refered in css exist, etc.
     *
     * @param string $sTemplateName     the name of the template to load. The string come from the template selector in survey settings
     * @param integer $iSurveyId        the id of the survey. If
     */
    public function setTemplateConfiguration($sTemplateName='', $iSurveyId='')
    {
        if ($sTemplateName == '' && $iSurveyId == '')
        {
            throw new TemplateException("Template needs either template name or survey id");
        }

        $this->sTemplateName = $sTemplateName;
        $this->iSurveyId = $iSurveyId;

        if ($sTemplateName=='')
        {
            $this->oSurvey = Survey::model()->findByPk($iSurveyId);
            $this->sTemplateName = $this->oSurvey->template;
        }

        $this->isStandard = $this->setIsStandard();

        // If the template is standard, its root is based on standardtemplaterootdir
        if($this->isStandard)
        {
            $this->path = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->sTemplateName;
        }
        // Else, it's a user template, its root is based on usertemplaterootdir
        else
        {
            $this->path = Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$this->sTemplateName;
        }




        // If the template don't have a config file (maybe it has been deleted, or whatever),
        // then, we load the default template
        $this->hasConfigFile = is_file($this->path.DIRECTORY_SEPARATOR.'config.xml');
        $this->isOldTemplate = ( !$this->hasConfigFile && is_file($this->path.DIRECTORY_SEPARATOR.'startpage.pstpl'));

        if(!$this->hasConfigFile)
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
        //var_dump(realpath ($this->xmlFile)); die();
        $this->config = simplexml_load_file(realpath ($this->xmlFile));

        // The template configuration.
        $this->viewPath = $this->path.DIRECTORY_SEPARATOR.$this->config->engine->pstpldirectory.DIRECTORY_SEPARATOR;
        $this->siteLogo = (isset($this->config->files->logo))?$this->config->files->logo->filename:'';

        // condition for user's template prior to 160219
        $this->filesPath    = (isset($this->config->engine->filesdirectory))? $this->path.DIRECTORY_SEPARATOR.$this->config->engine->filesdirectory.DIRECTORY_SEPARATOR : $this->path . '/files/';
        $this->cssFramework = $this->config->engine->cssframework;
        $this->packages     = (array) $this->config->engine->packages->package;
        $this->otherFiles   = $this->setOtherFiles();
        $this->depends      = $this->packages;
        $this->depends[]    = (string) $this->cssFramework;

        $this->createTemplatePackage();

        return $this;
    }

    /**
     * Update the configuration file "last update" node.
     * It forces the asset manager to republish all the assets
     * So after a modification of the CSS or the JS, end user will not have to refresh the cache of their browser.
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
     * And it will publish the CSS and the JS defined. So CSS can use relative path for pictures.
     * The publication of the package itself is done for now in replacements_helper, to respect the old logic of {TEMPLATECSS} replacement keyword
     *
     * NOTE 1 : To refresh the assets, the base directory of the template must be updated.
     * The best way to do it, is to change a file in its root directory
     * (then the new directory date will also be handled by Git, Ftp, SSH, etc. whereas a directory touch could not work in every case)
     * That's why now, the config.xml file provide a field "last_update".
     * When this filed is changed, you can be sure that all the CSS/JS/FILES will be reload by the final user browser
     *
     * NOTE 2: the process describe above works fine for publishing changes on template via Git, ComfortUpdates and manual update
     * because pulling/copying a new file in a directory changes its date. BUT, when working on a local installation, it can happen that
     * just changing/saving the XML file is not enough to update the directory's modification date (in Linux system, you can even have: "unknow modification date")
     * The date of the directory must then been changed manually.
     * To avoid them to do it each time, Asset Manager is now off when debug mode is on (see: {TEMPLATECSS} replacement in replacements_helper).
     * Developpers should then think about :
     * 1. refreshing their brower's cache (ctrl + F5) to see their changes
     * 2. update the config.xml last_update before pushing, to be sure that end users will have the new version
     *
     * For more detail, see :
     *  http://www.yiiframework.com/doc/api/1.1/CClientScript#addPackage-detail
     *  http://www.yiiframework.com/doc/api/1.1/YiiBase#setPathOfAlias-detail
     *
     */
    private function createTemplatePackage()
    {
        Yii::setPathOfAlias('survey.template.path', $this->path);                           // The package creation/publication need an alias

        $aCssFiles   = (array) $this->config->files->css->filename;                                 // The CSS files of this template
        $aJsFiles    = (array) $this->config->files->js->filename;                                  // The JS files of this template

        if (getLanguageRTL(App()->language))
        {
            $aCssFiles = array_merge($aCssFiles, (array) $this->config->files->rtl->css->filename); // In RTL mode, more CSS files can be necessary
            $aJsFiles  = array_merge($aJsFiles, (array) $this->config->files->rtl->js->filename);   // In RTL mode, more JS files can be necessary
        }

        // The package "survey-template" will be available from anywhere in the app now.
        // To publish it : Yii::app()->clientScript->registerPackage( 'survey-template' );
        // It will create the asset directory, and publish the css and js files
        Yii::app()->clientScript->addPackage( 'survey-template', array(
            'basePath'    => 'survey.template.path',
            'css'         => $this->config->files->css->filename,
            'js'          => $this->config->files->js->filename,
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
                if (!is_dir($file))
                {
                    $otherfiles[] = array("name" => $file);
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
