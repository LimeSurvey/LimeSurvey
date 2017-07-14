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
 * This is the model class for table "{{template_configuration}}".
 *
 * The followings are the available columns in table '{{template_configuration}}':
 * @property string $id
 * @property string $templates_name
 * @property string $gsid
 * @property string $sid
 * @property string $files_css
 * @property string $files_js
 * @property string $files_print_css
 * @property string $options
 * @property string $cssframework_name
 * @property string $cssframework_css
 * @property string $cssframework_js
 * @property string $viewdirectory
 * @property string $filesdirectory
 * @property string $packages_to_load
 * @property string $packages_rtl
 *
 *
 * @package       LimeSurvey
 * @subpackage    Backend
 */
class TemplateConfiguration extends TemplateConfig
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{template_configuration}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('templates_name', 'required'),
            array('id, sid, gsid', 'numerical', 'integerOnly'=>true),
            array('templates_name', 'length', 'max'=>150),
            array('cssframework_name', 'length', 'max'=>45),
            array('files_css, files_js, files_print_css, options, cssframework_css, cssframework_js, packages_to_load', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, templates_name, sid, gsid, files_css, files_js, files_print_css, options, cssframework_name, cssframework_css, cssframework_js, packages_to_load', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'template' => array(self::HAS_ONE, 'Template', array('name' => 'templates_name')),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'templates_name' => 'Templates Name',
            'sid' => 'Sid',
            'gsid' => 'Gsid',
            'files_css' => 'Files Css',
            'files_js' => 'Files Js',
            'files_print_css' => 'Files Print Css',
            'options' => 'Options',
            'cssframework_name' => 'Cssframework Name',
            'cssframework_css' => 'Cssframework Css',
            'cssframework_js' => 'Cssframework Js',
            'packages_to_load' => 'Packages To Load',
        );
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

        $criteria->compare('id',$this->id);
        $criteria->compare('templates_name',$this->templates_name,true);
        $criteria->compare('sid',$this->sid);
        $criteria->compare('gsid',$this->gsid);
        $criteria->compare('files_css',$this->files_css,true);
        $criteria->compare('files_js',$this->files_js,true);
        $criteria->compare('files_print_css',$this->files_print_css,true);
        $criteria->compare('options',$this->options,true);
        $criteria->compare('cssframework_name',$this->cssframework_name,true);
        $criteria->compare('cssframework_css',$this->cssframework_css,true);
        $criteria->compare('cssframework_js',$this->cssframework_js,true);
        $criteria->compare('packages_to_load',$this->packages_to_load,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return TemplateConfigurationDB the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Create a new entry in {{templates}} table using the template manifest
     * @param string $sTemplateName the name of the template to import
     * @return mixed true on success | exception
     */
    public static function importManifest($sTemplateName)
    {
        $oEditedTemplate                      = Template::model()->getTemplateConfiguration($sTemplateName, '', false);
        $oEditTemplateDb                      = Template::model()->findByPk($oEditedTemplate->oMotherTemplate->sTemplateName);
        $oNewTemplate                         = new Template;
        $oNewTemplate->name                   = $oEditedTemplate->sTemplateName;
        $oNewTemplate->folder                 = $oEditedTemplate->sTemplateName;
        $oNewTemplate->title                  = $oEditedTemplate->sTemplateName;  // For now, when created via template editor => name == folder == title
        $oNewTemplate->creation_date          = date("Y-m-d H:i:s");
        $oNewTemplate->author                 = Yii::app()->user->name;
        $oNewTemplate->author_email           = ''; // privacy
        $oNewTemplate->author_url             = ''; // privacy
        $oNewTemplate->api_version            = $oEditTemplateDb->api_version;
        $oNewTemplate->view_folder            = $oEditTemplateDb->view_folder;
        $oNewTemplate->files_folder           = $oEditTemplateDb->files_folder;
        //$oNewTemplate->description           TODO: a more complex modal whith email, author, url, licence, desc, etc
        $oNewTemplate->owner_id               = Yii::app()->user->id;
        $oNewTemplate->extends_templates_name = $oEditedTemplate->oMotherTemplate->sTemplateName;

        if ($oNewTemplate->save()){
            $oNewTemplateConfiguration                    = new TemplateConfiguration;
            $oNewTemplateConfiguration->templates_name    = $oEditedTemplate->sTemplateName;
            if ($oNewTemplateConfiguration->save()){
                return true;
            }else{
                throw new Exception($oNewTemplateConfiguration->getErrors());
            }
        }else{
            throw new Exception($oNewTemplate->getErrors());
        }
    }

    public function checkTemplate()
    {
        if (is_object($this->template) && !is_dir(Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->template->folder)&& !is_dir(Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$this->template->folder)){
            return false;
        }
        return true;
    }

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
        $this->sTemplateName = $this->template->name;
        $this->setIsStandard();                                                 // Check if  it is a CORE template
        $this->path = ($this->isStandard)?Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->template->folder:Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$this->template->folder;
        $this->setMotherTemplates();                                            // Recursive mother templates configuration
        @$this->setThisTemplate();                                               // Set the main config values of this template
        $this->createTemplatePackage($this);                                    // Create an asset package ready to be loaded
        return $this;
    }

    /**
     * Add a file replacement in the field `file_{css|js|print_css}` in table {{template_configuration}},
     * eg: {"replace": [ {original files to replace here...}, "css/template.css",]}
     *
     * @param string $sFile the file to replace
     * @param string $sType css|js
     */
    public function addFileReplacement($sFile, $sType)
    {
        $sField = 'files_'.$sType;
        $oFiles = (array) json_decode($this->$sField);

        $oFiles['replace'][] = $sFile;

        $this->$sField = json_encode($oFiles);

        if ($this->save()){
            return true;
        }else{
            throw new Exception("could not add $sFile to  $sField replacements! ".$this->getErrors());
        }
    }


    /**
     * From a list of json files in db it will generate a PHP array ready to use by removeFileFromPackage()
     *
     * @var $jFiles string json
     * @return array
     */
    protected function getFilesToLoad($oTemplate, $sType)
    {
        $sField = 'files_'.$sType;
        $jFiles = $oTemplate->$sField;

        $aFiles = array();
        if(!empty($jFiles)){
            $oFiles = json_decode($jFiles);
            foreach($oFiles as $action => $aFileList){
                $aFiles = array_merge($aFiles, $aFileList);
            }
        }
        return $aFiles;
    }

    /**
     * Change the mother template configuration depending on template settings
     * @var $sType     string   the type of settings to change (css or js)
     * @var $aSettings array    array of local setting
     * @return array
     */
    protected function changeMotherConfiguration( $sType, $aSettings )
    {
        if (is_a($this->oMotherTemplate, 'TemplateConfiguration')){
            $this->removeFileFromPackage($this->oMotherTemplate->sPackageName, $sType, $aSettings);
        }

        return $aSettings;
    }

    /**
     * Proxy for Yii::app()->clientScript->removeFileFromPackage()
     *
     * @param $sPackageName     string   name of the package to edit
     * @param $sType            string   the type of settings to change (css or js)
     * @param $aSettings        array    array of local setting
     * @return array
     */
    protected function removeFileFromPackage( $sPackageName, $sType, $aSettings )
    {
        foreach( $aSettings as $sFile){
            Yii::app()->clientScript->removeFileFromPackage($sPackageName, $sType, $sFile );
        }
    }

    /**
     * Configure the mother template (and its mother templates)
     * This is an object recursive call to TemplateConfiguration::setTemplateConfiguration()
     */
    protected function setMotherTemplates()
    {
        if(!empty($this->template->extends_templates_name)){
            $sMotherTemplateName   = $this->template->extends_templates_name;
            $this->oMotherTemplate = Template::getTemplateConfiguration($sMotherTemplateName);

            if ($this->oMotherTemplate->checkTemplate()){
                $this->oMotherTemplate->setTemplateConfiguration($sMotherTemplateName); // Object Recursion
            }else{
                // Throw exception? Set to default template?
            }
        }
    }

    /**
     * Set the default configuration values for the template, and use the motherTemplate value if needed
     */
    protected function setThisTemplate()
    {
        // Mandtory setting in config XML (can be not set in inheritance tree, but must be set in mother template (void value is still a setting))
        $this->apiVersion               = (!empty($this->template->api_version))? $this->template->api_version : $this->oMotherTemplate->apiVersion;
        $this->viewPath                 = (!empty($this->template->view_folder))  ? $this->path.DIRECTORY_SEPARATOR.$this->template->view_folder.DIRECTORY_SEPARATOR : $this->path.DIRECTORY_SEPARATOR.$this->oMotherTemplate->view_folder.DIRECTORY_SEPARATOR;
        $this->filesPath                = (!empty($this->template->files_folder))  ? $this->path.DIRECTORY_SEPARATOR.$this->template->files_folder.DIRECTORY_SEPARATOR   :  $this->path.DIRECTORY_SEPARATOR.$this->oMotherTemplate->file_folder.DIRECTORY_SEPARATOR;


        // Options are optional
        // TODO: twig getOption should return mother template option when option = inherit
        $this->oOptions = array();
        if (!empty($this->options)){
            $this->oOptions[] = (array) json_decode($this->options);
        }elseif(!empty($this->oMotherTemplate->oOptions)){
            $this->oOptions[] = $this->oMotherTemplate->oOptions;
        }

        // Not mandatory (use package dependances)
        if (!empty($this->cssframework_name)){
            $this->cssFramework = new \stdClass();
            $this->cssFramework->name = $this->cssframework_name;
            $this->cssFramework->css  = json_decode($this->cssframework_css);
            $this->cssFramework->js   = json_decode($this->cssframework_js);

        }else{
            $this->cssFramework = '';
        }

        if (!empty($this->packages_to_load)){
            $this->packages = json_decode($this->packages_to_load);
        }

        // Add depend package according to packages
        $this->depends                  = array_merge($this->depends, $this->getDependsPackages($this));
    }

    protected function addMotherTemplatePackage($packages)
    {
        if (!empty($this->template->extends_templates_name)){
            $sMotherTemplateName = (string) $this->template->extends_templates_name;
            $packages[]          = 'survey-template-'.$sMotherTemplateName;
        }
        return $packages;
    }

    /**
     * Get the list of file replacement from Engine Framework
     * @param string  $sType            css|js the type of file
     * @param boolean $bInlcudeRemove   also get the files to remove
     * @return array
     */
    protected function getFrameworkAssetsToReplace( $sType, $bInlcudeRemove = false)
    {
        $sFieldName  = 'cssframework_'.$sType;
        $aFieldValue = (array) json_decode($this->$sFieldName);

        $aAssetsToRemove = array();
        if (!empty( $aFieldValue )){
            $aAssetsToRemove = (array) $aFieldValue['replace'] ;
            if($bInlcudeRemove){
                $aAssetsToRemove = array_merge($aAssetsToRemove, (array) $aFieldValue['remove'] );
            }
        }
        return $aAssetsToRemove;
    }
}
