<?php

if (!defined('BASEPATH')) {
    die('No direct script access allowed');
}
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
 * @property integer $owner_id
 * @property string $extends
 */
class Template extends LSActiveRecord
{

    /** @var array $aAllTemplatesDir cache for the method getAllTemplatesDirectories */
    public static $aAllTemplatesDir = null;

    /** @var array $aTemplatesInUploadDir cache for the method getTemplateInUpload */
    public static $aTemplatesInUploadDir = null;

    /** @var array $aTemplatesInStandardDir cache for the method getTemplateInStandard */
    public static $aTemplatesInStandardDir = null;

    /** @var array $aTemplatesFileFolder cache for the method getTemplateFilesFolder */
    public static $aTemplatesFileFolder = null;

    /** @var array $aNamesFiltered cache for the method templateNameFilter */
    public static $aNamesFiltered = null;

    /** @var Template - The instance of template object */
    private static $instance;

    /** @var string[] list of standard template */
    private static $standardTemplates = array();

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
            array('name, title, creation_date', 'required'),
            array('owner_id', 'numerical', 'integerOnly'=>true),
            array('name, author, extends', 'length', 'max'=>150),
            array('folder, version, api_version, view_folder, files_folder', 'length', 'max'=>45),
            array('title', 'length', 'max'=>100),
            array('author_email, author_url', 'length', 'max'=>255),
            array('copyright, license, description, last_update', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('name, folder, title, creation_date, author, author_email, author_url, copyright, license, version, api_version, view_folder, files_folder, description, last_update, owner_id, extends', 'safe', 'on'=>'search'),
        );
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
     * Filter the template name : test if template if exist
     *
     * @param string $sTemplateName
     * @return string existing $sTemplateName
     */
    public static function templateNameFilter($sTemplateName)
    {
        // If the names has already been filtered, we skip the process
        if (!empty(self::$aNamesFiltered[$sTemplateName])) {
            return self::$aNamesFiltered[$sTemplateName];
        }

        $sRequestedTemplate = $sTemplateName;
        $sDefaultTemplate = App()->getConfig('defaulttheme');

        /* Validate if template is OK in user dir, DIRECTORY_SEPARATOR not needed "/" is OK */
        $oTemplate = self::model()->findByPk($sTemplateName);

        if (is_object($oTemplate) && $oTemplate->checkTemplate() && (self::checkTemplateXML($oTemplate->folder))) {
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

            if(method_exists(Yii::app(), 'setFlashMessage'))
                Yii::app()->setFlashMessage(sprintf(gT("Default survey theme %s is not installed. Now %s is the new default survey theme"), $sRequestedTemplate, $sTemplateName), 'error');

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
        if (!empty($this->extends)) {
            $oRTemplate = self::model()->findByPk($this->extends);
            if (empty($oRTemplate)) {

                // Why? it blocks the user at login screen....
                // It should return false and show a nice warning message.

                /*throw new Exception(
                    sprintf(
                        'Extended template "%s" is not installed.',
                        $this->extends
                    )
                );*/
            }
        }
        return true;
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
     * @TODO: check api version
     *
     * @param string $sTemplateFolder the template forder name where to look for the XML
     * @return boolean
     */
    public static function checkTemplateXML($sTemplateFolder)
    {
        return (is_file(Yii::app()->getConfig("userthemerootdir").DIRECTORY_SEPARATOR.$sTemplateFolder.DIRECTORY_SEPARATOR.'config.xml') || is_file(Yii::app()->getConfig("standardthemerootdir").DIRECTORY_SEPARATOR.$sTemplateFolder.DIRECTORY_SEPARATOR.'config.xml'));
    }

    /**
     * @param string $sTemplateName
     * @return bool
     */
    public static function checkIfTemplateExists($sTemplateName)
    {
        $aTemplates = self::getTemplateList();
        if (array_key_exists($sTemplateName, $aTemplates)) {
            return true;
        }
        return false;
    }

    /**
     * Get the template path for any template : test if template if exist
     *
     * @param string $sTemplateName
     * @return string template path
     */
    public static function getTemplatePath($sTemplateName = "")
    {
        static $aTemplatePath = array();
        if (isset($aTemplatePath[$sTemplateName])) {
            return $aTemplatePath[$sTemplateName];
        }

        $oTemplate = self::model()->findByPk($sTemplateName);

        if (self::isStandardTemplate($sTemplateName)) {
            return $aTemplatePath[$sTemplateName] = Yii::app()->getConfig("standardthemerootdir").DIRECTORY_SEPARATOR.$oTemplate->folder;
        } else {
            return $aTemplatePath[$sTemplateName] = Yii::app()->getConfig("userthemerootdir").DIRECTORY_SEPARATOR.$oTemplate->folder;
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
     * @param string $sTemplateName     the name of the template to load. The string come from the template selector in survey settings
     * @param integer $iSurveyId        the id of the survey.
     * @param integer $iSurveyId        the id of the survey.
     * @param boolean $bForceXML        the id of the survey.
     * @return TemplateConfiguration
     */
    public static function getTemplateConfiguration($sTemplateName = null, $iSurveyId = null, $iSurveyGroupId = null, $bForceXML = false, $abstractInstance = false)
    {

        // First we try to get a confifuration row from DB
        if (!$bForceXML) {
            // The name need to be filtred only for DB version. From TemplateEditor, the template is not installed.
            $sTemplateName = (empty($sTemplateName)) ? null : self::templateNameFilter($sTemplateName);
            $oTemplateConfigurationModel = TemplateConfiguration::getInstance($sTemplateName, $iSurveyGroupId, $iSurveyId, $abstractInstance);
        }


        // If no row found, or if the template folder for this configuration row doesn't exist we load the XML config (which will load the default XML)
        if ($bForceXML || !is_a($oTemplateConfigurationModel, 'TemplateConfiguration') || !$oTemplateConfigurationModel->checkTemplate()) {
            $oTemplateConfigurationModel = new TemplateManifest;
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
    static public function getOtherFiles($filesDir)
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
        static $aTemplateUrl = array();
        if (isset($aTemplateUrl[$sTemplateName])) {
            return $aTemplateUrl[$sTemplateName];
        }

        $oTemplate = self::model()->findByPk($sTemplateName);

        if (is_object($oTemplate)) {
            if (self::isStandardTemplate($sTemplateName)) {
                return $aTemplateUrl[$sTemplateName] = Yii::app()->getConfig("standardthemerooturl").'/'.$oTemplate->folder.'/';
            } else {
                return $aTemplateUrl[$sTemplateName] = Yii::app()->getConfig("userthemerooturl").'/'.$oTemplate->folder.'/';
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
     * Returns an array of all available template names - does a basic check if the template might be valid
     *
     * TODO: replace the calls to that function by a data provider based on search
     *
     * @return array
     */
    public static function getTemplateList()
    {


        $aTemplateList = array();

        $oTemplateList = TemplateConfiguration::model()->search();
        $oTemplateList->setPagination(false);

        foreach ($oTemplateList->getData() as $oTemplate) {
            $aTemplateList[$oTemplate->template_name] =  (isStandardTemplate($oTemplate->template_name)) ?  Yii::app()->getConfig("standardthemerootdir") . DIRECTORY_SEPARATOR . $oTemplate->template->folder : Yii::app()->getConfig("userthemerootdir") . DIRECTORY_SEPARATOR . $oTemplate->template->folder;
        }
        return $aTemplateList;

    }

    /**
     * @return array
     * TODO: replace the calls to that function by a data provider based on search
     */
    public static function getTemplateListWithPreviews()
    {

        $aTemplateList = array();

        $oTemplateList = TemplateConfiguration::model()->search();
        $oTemplateList->setPagination(false);

        foreach ($oTemplateList->getData() as $oTemplate) {
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
     */
    public static function isStandardTemplate($sTemplateName)
    {
        $standardTemplates = self::getStandardTemplateList();
        return in_array($sTemplateName, $standardTemplates);
    }

    /**
     * Get instance of template object.
     * Will instantiate the template object first time it is called.
     *
     * NOTE 1: This function will call prepareTemplateRendering that create/update all the packages needed to render the template, which imply to do the same for all mother templates
     * NOTE 2: So if you just want to access the TemplateConfiguration AR Object, you don't need to use this one. Call it only before rendering anything related to the template.
     * NOTE 3: If you need to get the related configuration to this template, rather use: getTemplateConfiguration()
     *
     * @param string $sTemplateName
     * @param int|string $iSurveyId
     * @param int|string $iSurveyGroupId
     * @param boolean $bForceXML
     * @return TemplateConfiguration
     */
    public static function getInstance($sTemplateName = null, $iSurveyId = null, $iSurveyGroupId = null, $bForceXML = null, $abstractInstance = false)
    {
        // The error page from default template can be called when no survey found with a specific ID.
        if ($sTemplateName === null && $iSurveyId === null) {
            $sTemplateName = App()->getConfig('defaulttheme');
        }

        if ($bForceXML === null) {
            // Template developper could prefer to work with XML rather than DB as a first step, for quick and easy changes
            if (App()->getConfig('force_xmlsettings_for_survey_rendering') && YII_DEBUG) {
                $bForceXML = true;
            } elseif (App()->getConfig('force_xmlsettings_for_survey_rendering') && YII_DEBUG) {
                $bForceXML = false;
            }
        }

        if($abstractInstance === true) {
            return self::getTemplateConfiguration($sTemplateName, $iSurveyId, $iSurveyGroupId, $bForceXML, true);
        }

        if (empty(self::$instance)) {
            self::$instance = self::getTemplateConfiguration($sTemplateName, $iSurveyId, $iSurveyGroupId, $bForceXML);
            self::$instance->prepareTemplateRendering($sTemplateName, $iSurveyId);
        }


        return self::$instance;
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
    * Don't delete this one to maintain updgrade compatibilty
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
     */
    public static function getStandardTemplateList()
    {
        return array_keys(self::getTemplateInStandard());
    }


    public static function hasInheritance($sTemplateName)
    {
        return self::model()->countByAttributes(array('extends' => $sTemplateName));
    }

    public static function getAllTemplatesDirectories()
    {
        if (empty(self::$aAllTemplatesDir)) {
            $aTemplatesInUpload     = Template::getTemplateInUpload();
            $aTemplatesInCore       = Template::getTemplateInStandard();
            self::$aAllTemplatesDir = array_merge($aTemplatesInUpload, $aTemplatesInCore);
        }
        return self::$aAllTemplatesDir;
    }

    public static function getTemplateInUpload()
    {
        if (empty(self::$aTemplatesInUploadDir)) {
            $sUserTemplateRootDir        = Yii::app()->getConfig("userthemerootdir");
            self::$aTemplatesInUploadDir = self::getTemplateInFolder($sUserTemplateRootDir);
        }

        return self::$aTemplatesInUploadDir;
    }

    public static function getTemplateInStandard()
    {
        if (empty(self::$aTemplatesInStandardDir)) {
            $standardTemplateRootDir       = Yii::app()->getConfig("standardthemerootdir");
            self::$aTemplatesInStandardDir = self::getTemplateInFolder($standardTemplateRootDir);
        }
        return self::$aTemplatesInStandardDir;
    }

    public static function getTemplateInFolder($sFolder)
    {
        $aTemplateList = array();

        if ($sFolder && $handle = opendir($sFolder)) {
            while (false !== ($sFileName = readdir($handle))) {
                if (!is_file("$sFolder/$sFileName") && $sFileName != "." && $sFileName != ".." && $sFileName != ".svn" && (file_exists("{$sFolder}/{$sFileName}/config.xml"))) {
                    $aTemplateList[$sFileName] = $sFolder.DIRECTORY_SEPARATOR.$sFileName;
                }
            }
            closedir($handle);
        }
        ksort($aTemplateList);
        return  $aTemplateList;
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
        Survey::model()->updateAll(array('template' => $sNewName), "template = :oldname", array(':oldname'=>$this->name));
        Template::model()->updateAll(array('name' => $sNewName, 'folder' => $sNewName), "name = :oldname", array(':oldname'=>$this->name));
        Template::model()->updateAll(array('extends' => $sNewName), "extends = :oldname", array(':oldname'=>$this->name));
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

        $criteria = new CDbCriteria;

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
            'criteria'=>$criteria,
        ));
    }

    /**
     * Retrieves a list of deprecated templates (the templates in upload/templates/)
     */
    static public function getDeprecatedTemplates()
    {
        $usertemplaterootdir     = Yii::app()->getConfig("uploaddir").DIRECTORY_SEPARATOR."templates";
        $aTemplateList = array();

        if ( (is_dir($usertemplaterootdir)) && $usertemplaterootdir && $handle = opendir($usertemplaterootdir)){
            while (false !== ($file = readdir($handle))){
                if (!is_file("$usertemplaterootdir/$file") && $file != "." && $file != ".." && $file!=".svn"){
                    $aTemplateList[$file]['directory']  = $usertemplaterootdir.DIRECTORY_SEPARATOR.$file;
                    $aTemplateList[$file]['name']       = $file;
                }
            }
            closedir($handle);
        }
        ksort($aTemplateList);

        return $aTemplateList;
    }

    /**
     * Retrieves a list of broken themes
     */
    public static function getBrokenThemes($sFolder=null)
    {
        $aBrokenTemplateList = array();
        $sFolder    =  (empty($sFolder))?Yii::app()->getConfig("userthemerootdir"):$sFolder;

        if ($sFolder && $handle = opendir($sFolder)) {
            while (false !== ($sFileName = readdir($handle))) {
                if (!is_file("$sFolder/$sFileName") && $sFileName != "." && $sFileName != ".." && $sFileName != ".svn" && $sFileName != 'generalfiles' ) {

                    try {
                        $oTheme = Template::getTemplateConfiguration($sFileName, null, null, true); // Get the manifest;
                    }catch (Exception $e) {
                        $aBrokenTemplateList[$sFileName] = $e;
                    }
                }
            }
            closedir($handle);
        }
        ksort($aBrokenTemplateList);
        return  $aBrokenTemplateList;
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
