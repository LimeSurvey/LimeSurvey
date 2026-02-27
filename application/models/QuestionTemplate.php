<?php

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


class QuestionTemplate extends CFormModel
{
    /**
     * The question associated with this template.
     *
     * @var Question
     */
    public $oQuestion;

    /**
     * Whether this question has a template applied.
     *
     * @var bool
     */
    public $bHasTemplate;

    /**
     * The folder name of the template applied to this question.
     *
     * Returns the template folder name or false if no custom template is used.
     *
     * @var string|false
     */
    public $sTemplateFolderName;

    /**
     * List of views supported by this template.
     * Example: $aViews['path/to/view'] === true
     *
     * @var array
     */
    public $aViews;

    /**
     *  XML configuration of the template.
     *
     * @var SimpleXMLElement
     */
    public $oConfig;

    /**
     * Whether the template provides custom attributes.
     *
     * @var bool
     */
    public $bHasCustomAttributes;

    /**
     *  Custom attributes defined by the template.
     * Format: ['attributeName' => 'value']
     *
     * @var array
     */
    public $aCustomAttributes;

    /**
     * Filesystem path to the template directory.
     *
     * @var string
     */
    private $sTemplatePath;

    /**
     * Public URL pointing to the template directory.
     *
     * @var string
     */
    private $sTemplateUrl;

    /**
     *  Filesystem path to the folder corresponding to the current question type.
     *
     * @var string
     */
    private $sTemplateQuestionPath;

    /**
     * Whether a configuration file exists for this template.
     *
     * @var bool
     */
    private $bHasConfigFile;

    /**
     * Filesystem path to the XML configuration file.
     *
     * @var string
     */
    private $xmlFile;

    /**
     * Whether the core JavaScript for this question should be rendered.
     * (Scripts are registered in qanda.)
     *
     * @var bool
     */
    private $bLoadCoreJs;

    /**
     * Whether the core CSS for this question should be rendered.
     * (Styles are registered in qanda.)
     *
     * @var bool
     */
    private $bLoadCoreCss;

    /**
     * Whether the core asset packages should be loaded.
     *
     * @var bool
     */
    private $bLoadCorePackage;

    /**
     * Singleton instance of the question template.
     *
     * @var QuestionTemplate|null
     */
    private static $instance;



    /**
     * Get a new instance of the template object
     * Each question on the page could have a different template.
     * So each question must have a new instance
     *
     * @param Question $oQuestion Question
     *
     * @return QuestionTemplate
     */
    public static function getNewInstance($oQuestion)
    {
        self::$instance = new QuestionTemplate();
        self::$instance->oQuestion = $oQuestion;
        self::$instance->aViews    = array();
        self::$instance->getQuestionTemplateFolderName(); // Will initiate $sTemplateFolderName and $bHasTemplate.
        self::$instance->setConfig();
        return self::$instance;
    }

    /**
     * Returns the current QuestionTemplate instance.
     *
     * If no instance exists yet and a Question object is provided,
     * a new instance will be created. If no Question is given and
     * no instance exists, null is returned.
     *
     * @param Question|null $oQuestion Optional question used to initialize a new instance.
     *
     * @return QuestionTemplate|null  The existing or newly created template instance, or null.
     *
     * @inheritdoc
     */
    public static function getInstance($oQuestion = null)
    {
        if (empty(self::$instance) && $oQuestion != null) {
            self::getNewInstance($oQuestion);
        }
        return self::$instance;
    }


    /**
     * Check if the question template offer a specific replacement for that view file.
     *
     * @param string $sView View File
     *
     * @return mixed
     */
    public function checkIfTemplateHasView($sView)
    {
        if (!isset($this->aViews[$sView])) {
            $sTemplatePath = $this->getTemplatePath();
            if (!empty($sTemplatePath) && is_file("$sTemplatePath/$sView.twig")) {
                $this->aViews[$sView] = true;
            } else {
                $this->aViews[$sView] = false;
            }
        }
        return $this->aViews[$sView];
    }

    /**
     * Retrieve the template base path if exist
     *
     * @return null|string
     */
    public function getTemplatePath()
    {
        if (!isset($this->sTemplatePath)) {
            $sTemplateFolderName    = $this->getQuestionTemplateFolderName();
            $sCoreQTemplateRootDir  = Yii::app()->getConfig("corequestionthemerootdir");
            $sUserQTemplateRootDir  = Yii::app()->getConfig("userquestionthemerootdir");

            // Core templates come first
            if (is_dir("$sCoreQTemplateRootDir/$sTemplateFolderName")) {
                $this->sTemplatePath = "$sCoreQTemplateRootDir/$sTemplateFolderName";
            } elseif (is_dir("$sUserQTemplateRootDir/$sTemplateFolderName")) {
                $this->sTemplatePath = "$sUserQTemplateRootDir/$sTemplateFolderName";
            }
        }
        return $this->sTemplatePath;
    }

    /**
     * Returns the template folder name for the current question.
     *
     * Returns the explicit question theme name, or false if the question
     * uses the core template or no custom theme is defined.
     *
     * @return string|false  The folder name or false if no custom template is used.
     *
     * @inheritdoc
     *
     * @todo Consider returning an empty string instead of `false` to maintain a
     *       consistent return type (string). Currently this method returns either
     *       a string or false, which makes usage and type checking less predictable.
     */
    public function getQuestionTemplateFolderName()
    {
        if ($this->sTemplateFolderName === null) {
            $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($this->oQuestion->qid);
            /**
             * Name of the question theme
             *
             * @var string|null
             */
            $questionThemeName = $this->oQuestion->question_theme_name;
            $this->sTemplateFolderName = (!empty($questionThemeName) && $questionThemeName != 'core') ? $questionThemeName : false;
        }
        $this->bHasTemplate = ($this->sTemplateFolderName != false);
        return $this->sTemplateFolderName;
    }

    /**
     * Register a core script file
     *
     * @param string $sFile Name of the file
     * @param int    $pos   Position
     *
     * @return void
     */
    public function registerScriptFile($sFile, $pos = CClientScript::POS_BEGIN)
    {
        if ($this->templateLoadsCoreJs) {
            Yii::app()->getClientScript()->registerScriptFile($sFile, $pos);
        }
    }

    /**
     * Register a core script
     *
     * @param string $sScript Name of the script
     * @param int    $pos     Position
     *
     * @return void
     */
    public function registerScript($sScript, $pos = CClientScript::POS_BEGIN)
    {
        if ($this->templateLoadsCoreJs) {
            Yii::app()->getClientScript()->registerScript($sScript, $pos);
        }
    }

    /**
     * Register a core css file
     *
     * @param string $sCssFile Name of the CSS File
     * @param string $media    Media
     *
     * @return void
     */
    public function registerCssFile($sCssFile, $media = '')
    {
        if ($this->templateLoadsCoreCss) {
            Yii::app()->getClientScript()->registerCssFile($sCssFile, $media);
        }
    }

    /**
     * Register a core package file
     *
     * @param string $sPackage Name of the package
     *
     * @return void
     */
    public function registerPackage($sPackage)
    {
        if ($this->templateLoadsCorePackage) {
            Yii::app()->getClientScript()->registerPackage($sPackage);
        }
    }

    /**
     * Return true if the core css should be loaded.
     *
     * @return boolean
     */
    public function templateLoadsCoreJs()
    {
        if (!isset($this->bLoadCoreJs)) {
            if ($this->bHasTemplate) {
                // Init config ($this->bHasConfigFile and $this->bLoadCoreJs )
                $this->setConfig();
                if ($this->bHasConfigFile) {
                    return $this->bLoadCoreJs;
                }
            }
            $this->bLoadCoreJs = true;
        }
        return $this->bLoadCoreJs;
    }

    /**
     * Return true if the core css should be loaded.
     *
     * @return boolean
     */
    public function templateLoadsCoreCss()
    {
        if (!isset($this->bLoadCoreCss)) {
            if ($this->bHasTemplate) {
                // Init config ($this->bHasConfigFile and $this->bLoadCoreCss )
                $this->setConfig();
                if ($this->bHasConfigFile) {
                    return $this->bLoadCoreCss;
                }
            }
            $this->bLoadCoreCss = true;
        }
        return $this->bLoadCoreCss;
    }

    /**
     * Return true if the core packages should be loaded.
     *
     * @return null|boolean
     */
    public function templateLoadsCorePackage()
    {
        if (!isset($this->bLoadCorePackage)) {
            if ($this->bHasTemplate) {
                // Init config ($this->bHasConfigFile and $this->bLoadCorePackage )
                $this->setConfig();
                if ($this->bHasConfigFile) {
                    return $this->bLoadCorePackage;
                }
            }
            $this->bLoadCoreCss = true;
        }
        return $this->bLoadCoreCss;
    }


    /**
     * In the future, could retrieve data from DB
     *
     * @return void
     */
    public function setConfig()
    {
        if (!isset($this->oConfig)) {
            $oQuestion                    = $this->oQuestion;
            $sTemplatePath                = $this->getTemplatePath();
            if (empty($sTemplatePath)) {
                return;
            }
            $sFolderName                  = self::getFolderName($oQuestion->type);
            $this->sTemplateQuestionPath  = $sTemplatePath . '/survey/questions/answer/' . $sFolderName;
            $xmlFile                      = $this->sTemplateQuestionPath . '/config.xml';
            $this->bHasConfigFile         = is_file($xmlFile);

            if ($this->bHasConfigFile) {
                $sXMLConfigFile               = file_get_contents(realpath($xmlFile)); // Entity loader is disabled, so we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string
                $this->xmlFile                = $xmlFile;
                $this->oConfig                 = simplexml_load_string($sXMLConfigFile);

                $this->bLoadCoreJs             = $this->oConfig->engine->load_core_js;
                $this->bLoadCoreCss            = $this->oConfig->engine->load_core_css;
                $this->bLoadCorePackage        = $this->oConfig->engine->load_core_package;
                $this->bHasCustomAttributes    = !empty($this->oConfig->attributes);

                // Set the custom attributes
                // In QuestionTheme set at a complete array using json_decode(json_encode((array)$xml_config->attributes), true);
                if ($this->bHasCustomAttributes) {
                    $this->aCustomAttributes = array();
                    foreach ($this->oConfig->attributes->attribute as $oCustomAttribute) {
                        $attribute_name = (string) $oCustomAttribute->name;
                        if (isset($oCustomAttribute->i18n) && strval($oCustomAttribute->i18n)) {
                            $sLang = App()->language;
                            $oAttributeValue = QuestionAttribute::model()->find("qid=:qid and attribute=:custom_attribute and language =:language", array('qid' => $oQuestion->qid, 'custom_attribute' => $attribute_name, 'language' => $sLang));
                        } else {
                            $oAttributeValue = QuestionAttribute::model()->find("qid=:qid and attribute=:custom_attribute", array('qid' => $oQuestion->qid, 'custom_attribute' => $attribute_name));
                        }
                        if (is_object($oAttributeValue)) {
                            $this->aCustomAttributes[$attribute_name] = $oAttributeValue->value;
                        } else {
                            $this->aCustomAttributes[$attribute_name] = (string) $oCustomAttribute->default;
                        }
                    }
                }
            }
        }
    }

    /**
     * Registers the assets.
     *
     * @return void
     */
    public function registerAssets()
    {
        if ($this->bHasConfigFile) {
            // Load the custom JS/CSS
            $aCssFiles   = (array) $this->oConfig->files->css->filename; // The CSS files of this template
            $aJsFiles    = (array) $this->oConfig->files->js->filename; // The JS files of this template

            if (!empty($aCssFiles) || !empty($aJsFiles)) {
                // It will create the asset directory, and publish the css and js files
                $questionTemplatePath = 'question.' . $this->oQuestion->qid . '.template.path';
                $package              = 'question-template_' . $this->oQuestion->qid;

                Yii::setPathOfAlias($questionTemplatePath, $this->sTemplateQuestionPath . '/assets'); // The package creation/publication need an alias
                Yii::app()->clientScript->addPackage(
                    $package,
                    array(
                    'basePath'    => $questionTemplatePath,
                    'css'         => $aCssFiles,
                    'js'          => $aJsFiles,
                    'position'    => LSYii_ClientScript::POS_BEGIN
                    )
                );

                if (!YII_DEBUG || Yii::app()->getConfig('use_asset_manager')) {
                    Yii::app()->clientScript->registerPackage($package);
                } else {
                    $templateurl = $this->getTemplateUrl();
                    foreach ($aCssFiles as $sCssFile) {
                        Yii::app()->getClientScript()->registerCssFile("{$templateurl}$sCssFile");
                    }
                    foreach ($aJsFiles as $sJsFile) {
                        Yii::app()->getClientScript()->registerScriptFile("{$templateurl}$sJsFile", LSYii_ClientScript::POS_BEGIN);
                    }
                }
            }
        }
    }

    /**
     * Returns the template URL.
     *
     * @return string
     */
    public function getTemplateUrl()
    {
        if (!isset($this->sTemplateUrl)) {
            $sBaseUrl               = Yii::app()->getBaseUrl(true);
            $sFolderName            = self::getFolderName($this->oQuestion->type);
            $sTemplateFolderName    = $this->getQuestionTemplateFolderName();
            $sCoreQTemplateRootDir  = Yii::app()->getConfig("corequestionthemerootdir");
            $sUserQTemplateRootDir  = Yii::app()->getConfig("userquestionthemerootdir");

            $sCoreQTemplateDir = Yii::app()->getConfig("corequestionthemedir");

            // Core templates come first
            if (is_dir("$sCoreQTemplateRootDir/$sTemplateFolderName/")) {
                $this->sTemplateUrl = "$sBaseUrl/$sCoreQTemplateDir/$sTemplateFolderName/survey/questions/answer/$sFolderName/assets/";
            } elseif (is_dir("$sUserQTemplateRootDir/$sTemplateFolderName/")) {
                $this->sTemplateUrl = "$sBaseUrl/$sUserQTemplateRootDir/$sTemplateFolderName/survey/questions/answer/$sFolderName/assets/";
            }
        }
        return $this->sTemplateUrl;
    }

    /**
     * Returns the custom attributes.
     *
     * @return array|null
     */
    public function getCustomAttributes()
    {
        if ($this->bHasCustomAttributes) {
            return $this->aCustomAttributes;
        }
        return null;
    }

    /**
     * Called from admin, to generate the template list for a given question type
     *
     * @param string $type Question Type
     *
     * @return array
     * @todo   Move to QuestionTheme?
     * @todo   This is not the same as QuestionTheme::findQuestionMetaDataForAllTypes() which is the database layer
     * @todo   this should check the filestructure instead of the database as this is the filestructure layer
     */
    public static function getQuestionTemplateList($type)
    {
        // todo: incorrect, this should check the filestructure instead of the database as this is the filestructure layer
        /**
         * QuestionTheme
         *
         * @var QuestionTheme[]
         */
        $questionThemes = QuestionTheme::model()->findAllByAttributes(
            [],
            'question_type = :question_type',
            ['question_type' => $type]
        );
        $aQuestionTemplates = [];

        foreach ($questionThemes as $questionTheme) {
            if ($questionTheme->core_theme == true && empty($questionTheme->extends)) {
                $aQuestionTemplates['core'] = [
                    'title' => gT('Default'),
                    'preview' => $questionTheme->image_path
                ];
            } else {
                $aQuestionTemplates[$questionTheme->name] = [
                    'title' => $questionTheme->title,
                    'preview' => $questionTheme->image_path
                ];
            }
        }
        return $aQuestionTemplates;
    }

    /**
     * Returns the folder name.
     *
     * @param string $type Type
     *
     * @return     string|null
     * @deprecated use QuestionTheme::getQuestionXMLPathForBaseType
     */
    public static function getFolderName($type)
    {
        if ($type) {
            $aTypeToFolder  = self::getTypeToFolder();
            $sFolderName    = $aTypeToFolder[$type];
            return $sFolderName;
        }
        return null;
    }

    /**
     * Correspondence between question type and the view folder name
     * Rem: should be in question model. We keep it here for easy access
     *
     * @return     array
     * @deprecated
     */
    public static function getTypeToFolder()
    {
        return array(
            "1" => 'arrays/dualscale',
            "5" => '5pointchoice',
            "A" => 'arrays/5point',
            "B" => 'arrays/10point',
            "C" => 'arrays/yesnouncertain',
            "D" => 'date',
            "E" => 'arrays/increasesamedecrease',
            "F" => 'arrays/array',
            "G" => 'gender',
            "H" => 'arrays/column',
            "I" => 'language',
            "K" => 'multiplenumeric',
            "L" => 'listradio',
            "M" => 'multiplechoice',
            "N" => 'numerical',
            "O" => 'list_with_comment',
            "P" => 'multiplechoice_with_comments',
            "Q" => 'multipleshorttext',
            "R" => 'ranking',
            "S" => 'shortfreetext',
            "T" => 'longfreetext',
            "U" => 'hugefreetext',
            "X" => 'boilerplate',
            "Y" => 'yesno',
            "!" => 'list_dropdown',
            ":" => 'arrays/multiflexi',
            ";" => 'arrays/texts',
            "|" => 'file_upload',
            "*" => 'equation',
        );
    }
}
