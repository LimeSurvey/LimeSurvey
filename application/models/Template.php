<?php

if (!defined('BASEPATH'))
    die('No direct script access allowed');
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

//TODO separate class file (add exceptions folder?)
class TemplateException extends Exception {}

/**
 * Class Template
 *
 * @property string $folder Template folder name eg: 'default'
 * @property integer $creator User ID of Template creator
 */
class Template extends LSActiveRecord
{

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
            array('name, author, extends_templates_name', 'length', 'max'=>150),
            array('folder, version, api_version, view_folder, files_folder', 'length', 'max'=>45),
            array('title', 'length', 'max'=>100),
            array('author_email, author_url', 'length', 'max'=>255),
            array('copyright, license, description, last_update', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('name, folder, title, creation_date, author, author_email, author_url, copyright, license, version, api_version, view_folder, files_folder, description, last_update, owner_id, extends_templates_name', 'safe', 'on'=>'search'),
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
            'extends_templates_name' => 'Extends Templates Name',
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
        $sDefaultTemplate = Yii::app()->getConfig('defaulttemplate','Default template');
        $sTemplateName    = empty($sTemplateName) ? $sDefaultTemplate : $sTemplateName;

        /* Standard Template return it without testing */
        if(self::isStandardTemplate($sTemplateName)) {
            return $sTemplateName;
        }

        /* Validate if template is OK in user dir, DIRECTORY_SEPARATOR not needed "/" is OK */
        $oTemplate  = self::model()->findByPk($sTemplateName);

        if( is_object($oTemplate) && is_file(Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$oTemplate->folder.DIRECTORY_SEPARATOR.'config.xml')) {
            return $sTemplateName;
        }

        /* Then try with the global default template */
        if($sTemplateName != $sDefaultTemplate) {
            return self::templateNameFilter($sDefaultTemplate);
        }

        /* Last solution : default */
        return 'default';
    }


    /**
     * @param string $sTemplateName
     * @return bool
     */
    public static function checkIfTemplateExists($sTemplateName)
    {
        $aTemplates=self::getTemplateList();
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
        if(isset($aTemplatePath[$sTemplateName])) {
            return $aTemplatePath[$sTemplateName];
        }

        $oTemplate  = self::model()->findByPk($sTemplateName);

        if (self::isStandardTemplate($sTemplateName)) {
            return $aTemplatePath[$sTemplateName] = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$oTemplate->folder;
        }
        else {
            return $aTemplatePath[$sTemplateName] = Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$oTemplate->folder;
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
     * @param integer $bForceXML        the id of the survey. 
     * @return StdClass
     */
    public static function getTemplateConfiguration($sTemplateName=null, $iSurveyId=null, $iSurveyGroupId=null, $bForceXML=false)
    {

        // First we try to get a confifuration row from DB
        if (!$bForceXML){

            if ($sTemplateName!=null){
                $oTemplateConfigurationModel = TemplateConfiguration::model()->find(
                    'templates_name=:templates_name AND sid IS NULL AND gsid IS NULL', 
                    array(':templates_name'=>$sTemplateName)
                );
            } 
            
            if($iSurveyGroupId!=null) {
                $oTemplateConfigurationModel = TemplateConfiguration::model()->find(
                    'gsid=:gsid', 
                    array(':gsid' => $iSurveyGroupId )
                    );

                // No specific template configuration for this survey
                if (!is_a($oTemplateConfigurationModel, 'TemplateConfiguration')){
                    $sTemplateName = SurveysGroups::model()->findByPk($iSurveyGroupId)->template;
                    $oTemplateConfigurationModel = TemplateConfiguration::model()->find('templates_name=:templates_name AND sid IS NULL AND gsid IS NULL', array(':templates_name'=>$sTemplateName));
                    $oTemplateConfigurationModel->setToInherit();
                }
            } 
            
            if($iSurveyId!=null) {
                $oTemplateConfigurationModel = TemplateConfiguration::model()->find(
                    'sid=:sid', 
                    array(':sid' => $iSurveyId )
                    );

                // No specific template configuration for this survey
                if (!is_a($oTemplateConfigurationModel, 'TemplateConfiguration')){
                    $sTemplateName = Survey::model()->findByPk($iSurveyId)->template;
                    $oTemplateConfigurationModel = TemplateConfiguration::model()->find('templates_name=:templates_name AND sid IS NULL AND gsid IS NULL', array(':templates_name'=>$sTemplateName));
                    $oTemplateConfigurationModel->setToInherit();
                }
            }
        }

        //If a survey group id is set, but there is no survey specific template configuration create one!
        if(($iSurveyGroupId != null) && !($iSurveyGroupId == $oTemplateConfigurationModel->gsid)){
            $oTemplateConfigurationModel->id = null;
            $oTemplateConfigurationModel->isNewRecord = true;
            $oTemplateConfigurationModel->gsid = $iSurveyGroupId;
            $sTemplateName = SurveyGroups::model()->findByPk($iSurveyGroupId)->template;
            $result = $oTemplateConfigurationModel->save();
            return $oTemplateConfigurationModel;
        }

        //If a survey id is set, but there is no survey specific template configuration create one!
        if(($iSurveyId != null) && !($iSurveyId == $oTemplateConfigurationModel->sid)){
            $oTemplateConfigurationModel->id = null;
            $oTemplateConfigurationModel->isNewRecord = true;
            $oTemplateConfigurationModel->sid = $iSurveyId;
            $sTemplateName = Survey::model()->findByPk($iSurveyId)->template;
            $result = $oTemplateConfigurationModel->save();
            return $oTemplateConfigurationModel;
        }

        // If no row found, or if the template folder for this configuration row doesn't exist we load the XML config (which will load the default XML)
        if ( $bForceXML || !is_a($oTemplateConfigurationModel, 'TemplateConfiguration') || ! $oTemplateConfigurationModel->checkTemplate()){
            $oTemplateConfigurationModel = new TemplateManifest;
        }

        //$oTemplateConfigurationModel->setTemplateConfiguration($sTemplateName, $iSurveyId);
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
        if ( file_exists($filesDir) && $handle = opendir($filesDir)) {
            while (false !== ($file = readdir($handle))) {
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
    public static function getTemplateURL($sTemplateName="")
    {
        static $aTemplateUrl=array();
        if(isset($aTemplateUrl[$sTemplateName])){
            return $aTemplateUrl[$sTemplateName];
        }

        $oTemplate  = self::model()->findByPk($sTemplateName);

        if (is_object($oTemplate)){
            if (self::isStandardTemplate($sTemplateName)) {
                return $aTemplateUrl[$sTemplateName]=Yii::app()->getConfig("standardtemplaterooturl").'/'.$oTemplate->folder.'/';
            }
            else {
                return $aTemplateUrl[$sTemplateName]=Yii::app()->getConfig("usertemplaterooturl").'/'.$oTemplate->folder.'/';
            }
        }else{
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
        $sUserTemplateRootDir    = Yii::app()->getConfig("usertemplaterootdir");
        $standardTemplateRootDir = Yii::app()->getConfig("standardtemplaterootdir");

        $aTemplateList=array();
        $aStandardTemplates = self::getStandardTemplateList();

        foreach ($aStandardTemplates as $sTemplateName){
            $oTemplate  = self::model()->findByPk($sTemplateName);

            if (is_object($oTemplate)){
                $aTemplateList[$sTemplateName] = $standardTemplateRootDir.DIRECTORY_SEPARATOR.$oTemplate->folder;
            }
        }

        if ($sUserTemplateRootDir && $handle = opendir($sUserTemplateRootDir)) {
            while (false !== ($sTemplatePath = readdir($handle))) {
                // Maybe $file[0] != "." to hide Linux hidden directory
                if (!is_file("$sUserTemplateRootDir/$sTemplatePath")
                    && $sTemplatePath != "."
                    && $sTemplatePath != ".." && $sTemplatePath!=".svn"
                    && (file_exists("{$sUserTemplateRootDir}/{$sTemplatePath}/config.xml"))) {

                    $oTemplate = self::getTemplateConfiguration($sTemplatePath,null,null,true);

                    if (is_object($oTemplate)){
                        $aTemplateList[$sTemplatePath] = $sUserTemplateRootDir.DIRECTORY_SEPARATOR.$sTemplatePath;
                    }
                }
            }
            closedir($handle);
        }
        ksort($aTemplateList);

        return $aTemplateList;
    }

    /**
     * @return array
     * TODO: replace the calls to that function by a data provider based on search
     */
    public static function getTemplateListWithPreviews()
    {
        $sUserTemplateRootDir     = Yii::app()->getConfig("usertemplaterootdir");
        $standardtemplaterootdir = Yii::app()->getConfig("standardtemplaterootdir");
        $usertemplaterooturl     = Yii::app()->getConfig("usertemplaterooturl");
        $standardtemplaterooturl = Yii::app()->getConfig("standardtemplaterooturl");

        $aTemplateList = array();
        $aStandardTemplates = self::getStandardTemplateList();

        foreach ($aStandardTemplates as $sTemplateName){
            $oTemplate  = self::model()->findByPk($sTemplateName);

            if (is_object($oTemplate)) {
                $aTemplateList[$sTemplateName]['directory'] = $standardtemplaterootdir.DIRECTORY_SEPARATOR.$oTemplate->folder;
                $aTemplateList[$sTemplateName]['preview']   = $standardtemplaterooturl.'/'.$oTemplate->folder.'/preview.png';
            }
        }

        if ($sUserTemplateRootDir && $handle = opendir($sUserTemplateRootDir)) {
            while (false !== ($sTemplatePath = readdir($handle))) {
                // Maybe $file[0] != "." to hide Linux hidden directory
                if (!is_file("$sUserTemplateRootDir/$sTemplatePath") && $sTemplatePath != "." && $sTemplatePath != ".." && $sTemplatePath!=".svn") {


                    $oTemplate  = self::model()->find('folder=:folder', array(':folder'=>$sTemplatePath));

                    if (is_object($oTemplate)){
                        $aTemplateList[$oTemplate->name]['directory'] = $sUserTemplateRootDir.DIRECTORY_SEPARATOR.$sTemplatePath;
                        $aTemplateList[$oTemplate->name]['preview'] = $sUserTemplateRootDir.DIRECTORY_SEPARATOR.$sTemplatePath.'/'.'preview.png';
                    }
                }
            }
            closedir($handle);
        }
        ksort($aTemplateList);

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
        $standardTemplates=self::getStandardTemplateList();
        return in_array($sTemplateName,$standardTemplates);
    }

    /**
     * Get instance of template object.
     * Will instantiate the template object first time it is called.
     * Please use this instead of global variable.
     *
     * @param string $sTemplateName
     * @param int|string $iSurveyId
     * @param int|string $iSurveyGroupId
     * @return TemplateConfiguration
     */
    public static function getInstance($sTemplateName='', $iSurveyId='', $iSurveyGroupId='', $bForceXML=false)
    {

        // Template developper could prefer to work with XML rather than DB as a first step, for quick and easy changes
        if (App()->getConfig('force_xmlsettings_for_survey_rendering') && YII_DEBUG){
            $bForceXML=true;
        }elseif( App()->getConfig('force_xmlsettings_for_survey_rendering') && YII_DEBUG){
            $bForceXML=false;
        }

        if (empty(self::$instance)) {
            self::$instance = self::getTemplateConfiguration($sTemplateName, $iSurveyId, $iSurveyGroupId, $bForceXML);
            self::$instance->setTemplateConfiguration($sTemplateName, $iSurveyId);
        }
        return self::$instance;
    }

    /**
     * Touch each directory in standard template directory to force assset manager to republish them
     */
    public static function forceAssets()
    {
        // Don't touch symlinked assets because it won't work
        if (App()->getAssetManager()->linkAssets) return;

        $standardTemplatesPath = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR;
        $Resource    = opendir($standardTemplatesPath);
        while ($Item = readdir($Resource)) {
            if (is_dir($standardTemplatesPath . $Item) && $Item != "." && $Item != "..") {
                touch($standardTemplatesPath . $Item);
            }
        }
    }

    /**
     * Return the standard template list
     * @return string[]
     * @throws Exception
     */
    public static function getStandardTemplateList()
    {

        $standardTemplates = array('default', 'minimal', 'material');
        return $standardTemplates;

        /*
        $standardTemplates=self::$standardTemplates;
        if(empty($standardTemplates)){
            $standardTemplates = array();
            $sStandardTemplateRootDir=Yii::app()->getConfig("standardtemplaterootdir");
            if ($sStandardTemplateRootDir && $handle = opendir($sStandardTemplateRootDir)) {
                while (false !== ($sFileName = readdir($handle))) {
                    // Maybe $file[0] != "." to hide Linux hidden directory
                    if (!is_file("$sStandardTemplateRootDir/$sFileName") && $sFileName[0] != "." && file_exists("{$sStandardTemplateRootDir}/{$sFileName}/config.xml")) {
                        $standardTemplates[$sFileName] = $sFileName;
                    }
                }
                closedir($handle);
            }
            ksort($standardTemplates);
            if(!in_array("default",$standardTemplates)){
                throw new Exception('There are no default template in stantard template root dir.');
            }
            self::$standardTemplates = $standardTemplates;
        }
*/
    //    return self::$standardTemplates;
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

        $criteria=new CDbCriteria;

        $criteria->compare('name',$this->name,true);
        $criteria->compare('folder',$this->folder,true);
        $criteria->compare('title',$this->title,true);
        $criteria->compare('creation_date',$this->creation_date,true);
        $criteria->compare('author',$this->author,true);
        $criteria->compare('author_email',$this->author_email,true);
        $criteria->compare('author_url',$this->author_url,true);
        $criteria->compare('copyright',$this->copyright,true);
        $criteria->compare('license',$this->license,true);
        $criteria->compare('version',$this->version,true);
        $criteria->compare('api_version',$this->api_version,true);
        $criteria->compare('view_folder',$this->view_folder,true);
        $criteria->compare('files_folder',$this->files_folder,true);
        $criteria->compare('description',$this->description,true);
        $criteria->compare('last_update',$this->last_update,true);
        $criteria->compare('owner_id',$this->owner_id);
        $criteria->compare('extends_templates_name',$this->extends_templates_name,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Templates the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
