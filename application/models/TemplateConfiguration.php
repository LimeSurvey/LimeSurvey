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
        return $this;
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
