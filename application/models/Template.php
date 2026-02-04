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

/**
 * Class Template
 *
 * @property string $name Template name
 * @property string $folder Template folder name eg: 'default'
 * @property string $title
 * @property string $creation_date
 * @property string $author
 * @property string $author_email
 * @property string $author_url
 * @property string $copyright
 * @property string $license
 * @property string $version
 * @property string $view_folder
 * @property string $files_folder
 * @property string $description
 * @property string $last_update
 * @property string $api_version
 * @property integer $owner_id
 * @property string $extends
 */
class Template extends LSActiveRecord
{
    /** @var array $aAllTemplatesDir cache for the method getAllTemplatesDirectories */
    public static $aAllTemplatesDir = null;

    /** @var array $aTemplatesFileFolder cache for the method getTemplateFilesFolder */
    public static $aTemplatesFileFolder = null;

    /** @var array $aNamesFiltered cache for the method templateNameFilter */
    public static $aNamesFiltered = null;

    /** @var Template|TemplateManifest - The instance of Template or TemplateManifest as an object */
    private static $instance;

    public static $sTemplateNameIllegalChars = "#$%^&*()+=[]';,./{}|:<>?~";

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{templates}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name', 'checkTemplateName'),
            array('title, creation_date', 'required'),
            array('owner_id', 'numerical', 'integerOnly' => true),
            array('name, author, extends', 'length', 'max' => 150),
            array('folder, version, api_version, view_folder, files_folder', 'length', 'max' => 45),
            array('title', 'length', 'max' => 100),
            array('author_email, author_url', 'length', 'max' => 255),
            array('copyright, license, description, last_update', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('name, folder, title, creation_date, author, author_email, author_url, copyright, license, version, api_version, view_folder, files_folder, description, last_update, owner_id, extends', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Template name rule function.
     */
    public function checkTemplateName($attributes, $params)
    {
        Template::validateTemplateName($this->name);
        return true;
    }

    /**
     * Validate the template name.
     *
     * @param string $templateName The name of the template
     */
    public static function validateTemplateName($templateName)
    {
        if (strpbrk((string) $templateName, Template::$sTemplateNameIllegalChars)) {
            Yii::app()->setFlashMessage(sprintf(gT("The name contains special characters.")), 'error');
            Yii::app()->getController()->redirect(array('themeOptions/index'));
            Yii::app()->end();
        } elseif (strlen((string)$templateName) > 45) {
            Yii::app()->setFlashMessage(sprintf(gT("The name is too long.")), 'error');
            Yii::app()->getController()->redirect(array('themeOptions/index'));
            Yii::app()->end();
        }
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(

        );
    }

    /**
     * @return array customized attribute labels (name=>label)
    */

    public function attributeLabels()
    {
        return array(
            'name' => 'Name',
            'folder' => 'Folder',
            'title' => 'Title',
            'creation_date' => 'Creation Date',
            'author' => 'Author',
            'author_email' => 'Author Email',
            'author_url' => 'Author Url',
            'copyright' => 'Copyright',
            'license' => 'License',
            'version' => 'Version',
            'api_version' => 'Api Version',
            'view_folder' => 'View Folder',
            'files_folder' => 'Files Folder',
            'description' => 'Description',
            'last_update' => 'Last Update',
            'owner_id' => 'Owner',
            'extends' => 'Extends Templates Name',
        );
    }


    /**
     * Returns this table's primary key
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'name';
    }

    /**
     * Filter the template name : test if template exists
     *
     * @param string $sTemplateName
     * @return string existing $sTemplateName
     * @throws Exception
     */
    public static function templateNameFilter($sTemplateName)
    {
        $sTemplateName = sanitize_filename($sTemplateName, false, false, false, true);

        // If the names has already been filtered, we skip the process
        if (!empty(self::$aNamesFiltered[$sTemplateName])) {
            return self::$aNamesFiltered[$sTemplateName];
        }

        $sRequestedTemplate = $sTemplateName;
        $sDefaultTemplate = App()->getConfig('defaulttheme');

        /* Validate if template is OK in user dir, DIRECTORY_SEPARATOR not needed "/" is OK */
        $oTemplate = self::model()->findByPk($sTemplateName);

        if (
            !empty($oTemplate)
            && $oTemplate->checkTemplate()
            && (self::checkTemplateXML($oTemplate))
        ) {
            self::$aNamesFiltered[$sTemplateName] = $sTemplateName;
            return self::$aNamesFiltered[$sTemplateName];
        }

        /* Then try with the global default template */
        if ($sTemplateName != $sDefaultTemplate) {
            return self::templateNameFilter($sDefaultTemplate);
        }

        /* If we're here, then the default survey theme is not installed and must be changed */
        $aTemplateList = self::model()->search()->getData();
        $i = 0;
        while ($sTemplateName == $sRequestedTemplate) {
            if (!empty($aTemplateList[$i])) {
                $sTemplateName = $aTemplateList[$i]->name;
            } else {
                throw new Exception('Could not find a working installed template');
            }
            $i++;
        }

        if (!empty($sTemplateName)) {
            SettingGlobal::setSetting('defaulttheme', $sTemplateName);
            $sDefaultTemplate = App()->getConfig('defaulttheme');

            if (method_exists(Yii::app(), 'setFlashMessage')) {
                Yii::app()->setFlashMessage(sprintf(gT("Default survey theme %s is not installed. Now %s is the new default survey theme"), $sRequestedTemplate, $sTemplateName), 'error');
            }

            self::$aNamesFiltered[$sTemplateName] = $sTemplateName;
            return $sTemplateName;
        } else {
            throw new Exception('No survey theme installed !!!!');
        }
    }

    /**
     * @return boolean
     * @throws Exception if extended template is not installed.
     */
    public function checkTemplate()
    {
        // Check that extended template is installed.
        $this->checkTemplateExtends();

        // A template should not extend it self.
        $this->checkExtendsItSelf();

        return true;
    }

    /**
     * Throws exception if any of the extended templates are not installed; otherwise
     * returns true.
     * @return boolean
     * @throws Exception if extended template is not installed.
     */
    public function checkTemplateExtends()
    {
        /**
         * TODO: the following code needs to be rewritten, currently the only thing it does is return true, that why it is commented out
         */
        return true;
//        if (!empty($this->extends)) {
//            $oRTemplate = self::model()->findByPk($this->extends);
//            if (empty($oRTemplate)) {
//                // Why? it blocks the user at login screen....
//                // It should return false and show a nice warning message.
//
//                /*throw new Exception(
//                    sprintf(
//                        'Extended template "%s" is not installed.',
//                        $this->extends
//                    )
//                );*/
//            }
//        }
//        return true;
    }

    /**
     * @return boolean
     * @throws Exception if name equals extends.
     */
    public function checkExtendsItSelf()
    {
        if ($this->name == $this->extends) {
            throw new Exception(
                sprintf(
                    'Error: The template %s extends it self',
                    $this->name
                )
            );
        }
        return true;
    }

    /**
     * Check if a given Template has a valid XML File
     *
     * @param Template $template The template model
     * @return boolean
     * @throws CDbException
     */
    public static function checkTemplateXML($template)
    {
        // Core templates should never be checked for validity
        // TODO: we can not trust the filestate and need to be
        //  able to check the theme type (core or custom) from the database, this should replace the call to getTemplatesData()
        $templates = array_column(LsDefaultDataSets::getTemplatesData(), 'name');
        if (in_array($template->name, $templates, true)) {
            return true;
        }
        // check if the configuration can be found
        $userThemePath = App()->getConfig("userthemerootdir") . DIRECTORY_SEPARATOR . $template->folder . DIRECTORY_SEPARATOR . 'config.xml';
        $standardThemePath = App()->getConfig("standardthemerootdir") . DIRECTORY_SEPARATOR . $template->folder . DIRECTORY_SEPARATOR . 'config.xml';
        if (is_file($userThemePath)) {
            $currentThemePath = $userThemePath;
        } elseif (is_file($standardThemePath)) {
            $currentThemePath = $standardThemePath;
        } else {
            return false;
        }

        // check compatability with current limesurvey version
        if (!TemplateConfig::validateTheme($template->name, $currentThemePath)) {
            return false;
        }

        // all checks succeeded, continue loading the theme
        return true;
    }

    /**
     * @param string $sTemplateName
     * @return bool
     */
    public static function checkIfTemplateExists($sTemplateName)
    {
        // isset is faster, and we need a value, no need var here
        return isset(self::getTemplateList()[$sTemplateName]);
    }

    /**
     * Get the template path for any template : test if template exists
     *
     * @param string $sTemplateName
     * @return string template path
     * @throws Exception
     */
    public static function getTemplatePath($sTemplateName = "")
    {
        $sTemplateName = self::templateNameFilter($sTemplateName);
        // Make sure template name is valid
        if (!self::checkIfTemplateExists($sTemplateName)) {
            throw new \CException("Invalid {$sTemplateName} template directory");
        }

        static $aTemplatePath = array();
        if (isset($aTemplatePath[$sTemplateName])) {
            return $aTemplatePath[$sTemplateName];
        }

        $oTemplate = self::model()->findByPk($sTemplateName);
        if (empty($oTemplate)) {
            throw new \CException("Survey theme {$sTemplateName} not found.", 1);
        }

        Yii::import('application.helpers.SurveyThemeHelper');
        if (SurveyThemeHelper::isStandardTemplate($sTemplateName)) {
            return $aTemplatePath[$sTemplateName] = Yii::app()->getConfig("standardthemerootdir") . DIRECTORY_SEPARATOR . $oTemplate->folder;
        } else {
            return $aTemplatePath[$sTemplateName] = Yii::app()->getConfig("userthemerootdir") . DIRECTORY_SEPARATOR . $oTemplate->folder;
        }
    }

    /**
     * This method construct a template object, having all the needed configuration datas.
     * It checks if the required template is a core one or a user one.
     * If it's a user template, it will check if it's an old 2.0x template to provide default configuration values corresponding to the old template system
     * If it's not an old template, it will check if it has a configuration file to load its datas.
     * If it's not the case (template probably doesn't exist), it will load the default template configuration
     * TODO : more tests should be done, with a call to private function _is_valid_template(), testing not only if it has a config.xml, but also id this file is correct, if the files refered in css exist, etc.
     *
     * @param string $sTemplateName the name of the template to load. The string come from the template selector in survey settings
     * @param integer $iSurveyId the id of the survey.
     * @param integer $iSurveyId the id of the survey.
     * @param boolean $bForceXML the id of the survey.
     * @return TemplateConfiguration|TemplateManifest
     */
    public static function getTemplateConfiguration($sTemplateName = null, $iSurveyId = null, $iSurveyGroupId = null, $bForceXML = false, $abstractInstance = false)
    {

        // First we try to get a configuration row from DB
        if (!$bForceXML) {
            // The name need to be filtred only for DB version. From TemplateEditor, the template is not installed.
            $sTemplateName = (empty($sTemplateName)) ? null : self::templateNameFilter($sTemplateName);
            $oTemplateConfigurationModel = TemplateConfiguration::getInstance($sTemplateName, $iSurveyGroupId, $iSurveyId, $abstractInstance);
        }


        // If no row found, or if the template folder for this configuration row doesn't exist we load the XML config (which will load the default XML)
        if ($bForceXML || !is_a($oTemplateConfigurationModel, 'TemplateConfiguration') || !$oTemplateConfigurationModel->checkTemplate()) {
            $oTemplateConfigurationModel = new TemplateManifest(null);
            $oTemplateConfigurationModel->setBasics($sTemplateName, $iSurveyId);
        }

        //$oTemplateConfigurationModel->prepareTemplateRendering($sTemplateName, $iSurveyId);
        return $oTemplateConfigurationModel;
    }


    /**
     * Return the list of ALL files present in the file directory
     *
     * @param string $filesDir
     * @return array
     */
    public static function getOtherFiles($filesDir)
    {
        $otherFiles = array();
        if (file_exists($filesDir) && $handle = opendir($filesDir)) {
            while (false !== ($file = readdir($handle))) {
                // The file '..' can mess with open_basedir permissions.
                if ($file == '..' || $file == '.') {
                    continue;
                }
                if (!is_dir($file)) {
                    $otherFiles[] = array("name" => $file);
                }
            }
            closedir($handle);
        }
        return $otherFiles;
    }

    /**
     * This function returns the complete URL path to a given template name
     *
     * @param string $sTemplateName
     * @return string template url
     */
    public static function getTemplateURL($sTemplateName = "")
    {
        $sTemplateName = self::templateNameFilter($sTemplateName);
        // Make sure template name is valid
        if (!self::checkIfTemplateExists($sTemplateName)) {
            throw new \CException("Invalid {$sTemplateName} template directory");
        }

        static $aTemplateUrl = array();
        if (isset($aTemplateUrl[$sTemplateName])) {
            return $aTemplateUrl[$sTemplateName];
        }

        $oTemplate = self::model()->findByPk($sTemplateName);

        if (is_object($oTemplate)) {
            Yii::import('application.helpers.SurveyThemeHelper');
            if (SurveyThemeHelper::isStandardTemplate($sTemplateName)) {
                return $aTemplateUrl[$sTemplateName] = Yii::app()->getConfig("standardthemerooturl") . '/' . $oTemplate->folder . '/';
            } else {
                return $aTemplateUrl[$sTemplateName] = Yii::app()->getConfig("userthemerooturl") . '/' . $oTemplate->folder . '/';
            }
        } else {
            return '';
        }
    }



    /**
     * This function returns the complete URL path to a given template name
     *
     * @param string $sTemplateName
     * @return string template url
     */
    public static function getTemplatesFileFolder($sTemplateName = "")
    {
        static $aTemplatesFileFolder = array();
        if (isset($aTemplatesFileFolder[$sTemplateName])) {
            return $aTemplatesFileFolder[$sTemplateName];
        }

        $oTemplate = self::model()->findByPk($sTemplateName);

        if (is_object($oTemplate)) {
            return $aTemplatesFileFolder[$sTemplateName] = $oTemplate->files_folder;
        } else {
            return '';
        }
    }

    /**
     * Returns an array of all available template names - check if template exist
     * key is template name, value is template folder
     * @return string|array
     */
    public static function getTemplateList()
    {
        static $aTemplateList =  null;
        if (!is_null($aTemplateList)) {
            return $aTemplateList;
        }
        $aTemplateList = [];
        /* Get the template name by TemplateConfiguration and fiolder by template , no need other data */
        $criteria = new CDBCriteria();
        $criteria->select = 'template_name';
        $criteria->condition = 'sid IS NULL AND gsid IS NULL AND template.folder IS NOT NULL';
        $oTemplateList = TemplateConfiguration::model()->with(array(
            'template' => ['select' => 'id, folder'],
        ))->findAll($criteria);
        $aTemplateInStandard = SurveyThemeHelper::getTemplateInStandard();
        $aTemplateInUpload = SurveyThemeHelper::getTemplateInUpload();
        foreach ($oTemplateList as $oTemplate) {
            if (isset($aTemplateInStandard[$oTemplate->template->folder])) {
                $aTemplateList[$oTemplate->template_name] = $aTemplateInStandard[$oTemplate->template->folder];
            } elseif (isset($aTemplateInUpload[$oTemplate->template->folder])) {
                $aTemplateList[$oTemplate->template_name] = $aTemplateInUpload[$oTemplate->template->folder];
            }
        }
        return $aTemplateList;
    }

    /**
     * Return the array of existing and installed template with the preview images
     * @deprecated 2024-04-25 use directly Template::getTemplateList
     * @return array[]
     */
    public static function getTemplateListWithPreviews()
    {
        $criteria = new CDBCriteria();
        $criteria->select = 'template_name';
        $criteria->condition = 'sid IS NULL AND gsid IS NULL';
        $criteria->addInCondition('template_name', array_keys(self::getTemplateList()));

        $oTemplateList = TemplateConfiguration::model()->with(array(
            'template' => ['select' => 'id, name'],
        ))->findAll($criteria);
        $aTemplateList = array();
        foreach ($oTemplateList as $oTemplate) {
            $aTemplateList[$oTemplate->template_name]['preview'] = $oTemplate->preview;
        }
        return $aTemplateList;
    }

    /**
     * isStandardTemplate returns true if a template is a standard template
     * This function does not check if a template actually exists
     *
     * @param mixed $sTemplateName template name to look for
     * @return bool True if standard template, otherwise false
     * @deprecated Use SurveyThemeHelper::getStandardTemplateList() instead.
     */
    public static function isStandardTemplate($sTemplateName)
    {
        // Refactored into SurveyThemeHelper. Replaced the code here
        // by a call to the helper to avoid code duplication while keeping
        // backwards compatibility.
        Yii::import('application.helpers.SurveyThemeHelper');
        return SurveyThemeHelper::isStandardTemplate($sTemplateName);
    }

    /**
     * Get instance of template object.
     * Will instantiate the template object first time it is called.
     *
     * NOTE 1: This function will call prepareTemplateRendering that create/update all the packages needed to render the template, which imply to do the same for all mother templates
     * NOTE 2: So if you just want to access the TemplateConfiguration AR Object, you don't need to use this one. Call it only before rendering anything related to the template.
     * NOTE 3: If you need to get the related configuration to this template, rather use: getTemplateConfiguration()
     * NOTE 4: If you want the lastest generated theme, just call Template::getLastInstance()
     *
     * @param string $sTemplateName
     * @param int|string $iSurveyId
     * @param int|string $iSurveyGroupId
     * @param boolean $bForceXML
     * @param boolean $last if you want to get the last instace without providing template name or sid
     * @return self
     */
    public static function getInstance($sTemplateName = null, $iSurveyId = null, $iSurveyGroupId = null, $bForceXML = null, $abstractInstance = false, $last = false)
    {

        if ($bForceXML === null) {
          // Template developer could prefer to work with XML rather than DB as a first step, for quick and easy changes
            $bForceXML = (App()->getConfig('force_xmlsettings_for_survey_rendering')) ? true : false;
        }
        // The error page from default template can be called when no survey found with a specific ID.
        if ($sTemplateName === null && $iSurveyId === null && $last === false) {
            $sTemplateName = App()->getConfig('defaulttheme');
        }

        // TODO: this probably not use any more. Check and remove it.
        if ($abstractInstance === true) {
            return self::getTemplateConfiguration($sTemplateName, $iSurveyId, $iSurveyGroupId, $bForceXML, true);
        }

        $getNewInstance = false;
        // if we don't have an instance, generate one
        if (empty(self::$instance)) {
            $getNewInstance = true;
        } elseif (!self::$instance instanceof TemplateManifest
            && !(self::$instance->sid === $iSurveyId && self::$instance->gsid === $iSurveyGroupId)) {
            // if the current instance matches the requested surveys sid and gsid, generate a new one
            $getNewInstance = true;
        } elseif (!self::isCorrectInstance($sTemplateName)) {
            // if the current instance name does not match the requested, generate a new one
            $getNewInstance = true;
        }
        if ($getNewInstance) {
            self::$instance = self::getTemplateConfiguration($sTemplateName, $iSurveyId, $iSurveyGroupId, $bForceXML);
            self::$instance->prepareTemplateRendering($sTemplateName, $iSurveyId);
        }

        return self::getLastInstance(false);
    }

    /**
     * Return last instance if it exists, else generate it or throw an exception depending on $bAutoGenerate.
     * @param boolean $bAutoGenerate : should the function try to generate an instance if it doesn't exist?
     * @return self
     */
    public static function getLastInstance($bAutoGenerate = true)
    {
        if (empty(self::$instance)) {
            if ($bAutoGenerate) {
                self::getInstance();
            } else {
                throw new \Exception("No Survey theme was generated", 1);
            }
        }

        return self::$instance;
    }

    /**
     * Check if the current instance is the correct one. Could be more complex in the future
     * @param string $sTemplateName
     * @return boolean
     */
    public static function isCorrectInstance($sTemplateName = null)
    {
        return ( $sTemplateName == null || self::$instance->sTemplateName == $sTemplateName);
    }

    /**
     * Sets self::$instance to null;
     * Needed for unit test.
     */
    public static function resetInstance()
    {
        self::$instance = null;
    }

    /**
    * Alias function for resetAssetVersion()
    * Don't delete this one to maintain updgrade compatibility
    * @return void
    */
    public function forceAssets()
    {
        $this->resetAssetVersion();
    }

    /**
     * Reset assets for this template
     * Using DB only
     * @return void
     */
    public function resetAssetVersion()
    {
        AssetVersion::incrementAssetVersion(self::getTemplatePath($this->name));
    }

    /**
     * Delete asset related to this template
     * Using DB only
     * @return integer (0|1)
     */
    public function deleteAssetVersion()
    {
        return AssetVersion::deleteAssetVersion(self::getTemplatePath($this->name));
    }

    /**
     * Return the standard template list
     * @return string[]
     * @throws Exception
     * @deprecated Use SurveyThemeHelper::getStandardTemplateList() instead.
     */
    public static function getStandardTemplateList()
    {
        // Refactored into SurveyThemeHelper. Replaced the code here
        // by a call to the helper to avoid code duplication while keeping
        // backwards compatibility.
        Yii::import('application.helpers.SurveyThemeHelper');
        return SurveyThemeHelper::getStandardTemplateList();
    }


    public static function hasInheritance($sTemplateName)
    {
        return self::model()->countByAttributes(array('extends' => $sTemplateName));
    }

    public static function getAllTemplatesDirectories()
    {
        if (empty(self::$aAllTemplatesDir)) {
            Yii::import('application.helpers.SurveyThemeHelper');
            $aTemplatesInUpload     = SurveyThemeHelper::getTemplateInUpload();
            $aTemplatesInCore       = SurveyThemeHelper::getTemplateInStandard();
            self::$aAllTemplatesDir = array_merge($aTemplatesInUpload, $aTemplatesInCore);
        }
        return self::$aAllTemplatesDir;
    }

    /**
     * @deprecated Use SurveyThemeHelper::getTemplateInUpload() instead.
     */
    public static function getTemplateInUpload()
    {
        // Refactored into SurveyThemeHelper. Replaced the code here
        // by a call to the helper to avoid code duplication while keeping
        // backwards compatibility.
        Yii::import('application.helpers.SurveyThemeHelper');
        return SurveyThemeHelper::getTemplateInUpload();
    }

    /**
     * @deprecated Use SurveyThemeHelper::getTemplateInStandard() instead.
     */
    public static function getTemplateInStandard()
    {
        // Refactored into SurveyThemeHelper. Replaced the code here
        // by a call to the helper to avoid code duplication while keeping
        // backwards compatibility.
        Yii::import('application.helpers.SurveyThemeHelper');
        return SurveyThemeHelper::getTemplateInStandard();
    }

    /**
     * @deprecated Use SurveyThemeHelper::getTemplateInFolder() instead.
     */
    public static function getTemplateInFolder($sFolder)
    {
        // Refactored into SurveyThemeHelper. Replaced the code here
        // by a call to the helper to avoid code duplication while keeping
        // backwards compatibility.
        Yii::import('application.helpers.SurveyThemeHelper');
        return SurveyThemeHelper::getTemplateInFolder($sFolder);
    }

    /**
     * Change the template name inside DB and the manifest (called from template editor)
     * NOTE: all tests (like template exist, etc) are done from template controller.
     *
     * @param string $sNewName The newname of the template
     */
    public function renameTo($sNewName)
    {
        Yii::import('application.helpers.sanitize_helper', true);
        $this->deleteAssetVersion();
        Survey::model()->updateAll(array('template' => $sNewName), "template = :oldname", array(':oldname' => $this->name));
        SurveysGroupsettings::model()->updateAll(['template' => $sNewName], "template = :oldname", [':oldname' => $this->name]);
        Template::model()->updateAll(array('name' => $sNewName, 'folder' => $sNewName), "name = :oldname", array(':oldname' => $this->name));
        Template::model()->updateAll(array('extends' => $sNewName), "extends = :oldname", array(':oldname' => $this->name));
        TemplateConfiguration::rename($this->name, $sNewName);
        TemplateManifest::rename($this->name, $sNewName);
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new LSDbCriteria();

        $criteria->compare('name', $this->name, true);
        $criteria->compare('folder', $this->folder, true);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('creation_date', $this->creation_date, true);
        $criteria->compare('author', $this->author, true);
        $criteria->compare('author_email', $this->author_email, true);
        $criteria->compare('author_url', $this->author_url, true);
        $criteria->compare('copyright', $this->copyright, true);
        $criteria->compare('license', $this->license, true);
        $criteria->compare('version', $this->version, true);
        $criteria->compare('api_version', $this->api_version, true);
        $criteria->compare('view_folder', $this->view_folder, true);
        $criteria->compare('files_folder', $this->files_folder, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('last_update', $this->last_update, true);
        $criteria->compare('owner_id', $this->owner_id);
        $criteria->compare('extends', $this->extends, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Retrieves a list of deprecated templates (the templates in upload/templates/)
     */
    public static function getDeprecatedTemplates()
    {
        $usertemplaterootdir     = App()->getConfig("uploaddir") . DIRECTORY_SEPARATOR . "templates";
        $aTemplateList = array();

        if ((is_dir($usertemplaterootdir)) && $usertemplaterootdir && $handle = opendir($usertemplaterootdir)) {
            while (false !== ($file = readdir($handle))) {
                if (!is_file("$usertemplaterootdir/$file") && $file != "." && $file != ".." && $file != ".svn") {
                    $aTemplateList[$file]['directory']  = $usertemplaterootdir . DIRECTORY_SEPARATOR . $file;
                    $aTemplateList[$file]['name']       = $file;
                }
            }
            closedir($handle);
        }
        ksort($aTemplateList);

        return $aTemplateList;
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Template the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }
}
