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
 * NOTE: if you only need to access to the table, you don't need to call prepareTemplateRendering
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
     * @var TemplateConfiguration $oParentTemplate The parent template name
     * A template configuration, in the database, can inherit from another one.
     * This used to manage the different configuration levels for a very same template: global, survey group, survey
     * This is not related to motherTemplate (inheritance between two different templates)
     */
    public $oParentTemplate;

    /**@var boolean Should the magic getters automatically retreives the parent value when field is set to inherit  */
    public $bUseMagicInherit = true;

    // Caches

    /** @var string $sPreviewImgTag the template preview image tag for the template list*/
    public $sPreviewImgTag;

    /** @var boolean $bTemplateCheckResult is the template valid?*/
    private $bTemplateCheckResult;

    /** @var string $sTypeIcon the type of template for icon (core vs user)*/
    private $sTypeIcon;

    /** @var array $aFilesToLoad cache for the method getFilesToLoad()*/
    private $aFilesToLoad;

    /** @var array $aFrameworkAssetsToReplace cache for the method getFrameworkAssetsToReplace()*/
    private $aFrameworkAssetsToReplace;

    /** @var array $aReplacements cache for the method getFrameworkAssetsReplacement */
    private $aReplacements;


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

        //Don't show surveyspecifi settings on the overview
        $criteria->addCondition('sid IS NULL');
        $criteria->addCondition('gsid IS NULL');


        $criteria->compare('id',$this->id);
        $criteria->compare('templates_name',$this->templates_name,true);
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

    // For list, so no "setConfiguration" before
    public function getPreview()
    {
        if (empty($this->sPreviewImgTag)){
            $previewUrl =  Template::getTemplateURL($this->template->name);
            $this->sPreviewImgTag = '<img src="'.$previewUrl.'/preview.png" alt="template preview" height="200"/>';
        }
        return $this->sPreviewImgTag;
    }

    /**
     * Create a new entry in {{templates}} and {{template_configuration}} table using the template manifest
     * @param string $sTemplateName the name of the template to import
     * @return mixed true on success | exception
     */
    public static function importManifest($sTemplateName)
    {
        $oEditedTemplate                      = Template::model()->getTemplateConfiguration($sTemplateName, null,null, false);
        $oEditedTemplate->prepareTemplateRendering($sTemplateName);

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
            $oNewTemplateConfiguration->templates_name    = $oEditedTemplate->sTemplateName;
            $oNewTemplateConfiguration->options           = json_encode($oEditedTemplate->oOptions);


            if ($oNewTemplateConfiguration->save()){
                return true;
            }else{
                throw new Exception($oNewTemplateConfiguration->getErrors());
            }
        }else{
            throw new Exception($oNewTemplate->getErrors());
        }
    }

    public function setToInherit(){
        $this->files_css         = 'inherit';
        $this->files_js          = 'inherit';
        $this->files_print_css   = 'inherit';
        $this->options           = 'inherit';
        $this->cssframework_name = 'inherit';
        $this->cssframework_css  = 'inherit';
        $this->cssframework_js   = 'inherit';
        $this->packages_to_load  = 'inherit';
    }

    public function checkTemplate()
    {
        if (empty($this->bTemplateCheckResult)){
            $this->bTemplateCheckResult = true;
            if (is_object($this->template) && !is_dir(Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->template->folder)&& !is_dir(Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$this->template->folder)){
                $this->bTemplateCheckResult = false;
            }
        }
        return $this->bTemplateCheckResult;
    }

    /**
     * Prepare all the needed datas to render the temple
     * If any problem (like template doesn't exist), it will load the default template configuration
     * NOTE 1: This function will create/update all the packages needed to render the template, which imply to do the same for all mother templates
     * NOTE 2: So if you just want to access the TemplateConfiguration AR Object, you don't need to call it. Call it only before rendering anything related to the template.
     *
     * @param  string $sTemplateName the name of the template to load. The string comes from the template selector in survey settings
     * @param  string $iSurveyId the id of the survey. If
     * @return $this
     */
    public function prepareTemplateRendering($sTemplateName='', $iSurveyId='')
    {
        $this->sTemplateName = $this->template->name;
        $this->setIsStandard();                                                 // Check if  it is a CORE template
        $this->path = ($this->isStandard)
            ? Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->template->folder
            : Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$this->template->folder;
        $this->setMotherTemplates();                                            // Recursive mother templates configuration
        $this->setThisTemplate();                                               // Set the main config values of this template
        $this->createTemplatePackage($this);                                    // Create an asset package ready to be loaded
        return $this;
    }

    /**
     * Add a file replacement in the field `file_{css|js|print_css}` in table {{template_configuration}},
     * eg: {"replace": [ {original files to replace here...}, "css/template.css",]}
     * In general, should be called from TemplateManifest, after adding a file replacement inside the manifest.
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

    public function getTypeIcon()
    {
        if (empty($this->sTypeIcon)){
            $this->sTypeIcon = (Template::isStandardTemplate($this->template->name))?gT("Core Template"):gT("User Template");
        }
        return $this->sTypeIcon;
    }


    public function getButtons()
    {
        $sEditorUrl = Yii::app()->getController()->createUrl('admin/templates/sa/view', array("templatename"=>$this->template->name));
        $sOptionUrl = Yii::app()->getController()->createUrl('admin/templateoptions/sa/update', array("id"=>$this->id));

        $sEditorLink = "<a
            id='template_editor_link'
            href='".$sEditorUrl."'
            class='btn btn-default'>
                <span class='icon-templates'></span>
                ".gT('Template editor')."
            </a>";

            //

        $OptionLink = '';

        if ($this->hasOptionPage){
            $OptionLink .=  "<a
                id='template_options_link'
                href='".$sOptionUrl."'
                class='btn btn-default'>
                    <span class='fa fa-tachometer'></span>
                    ".gT('Template options')."
                </a>";
        }



        return $sEditorLink.'<br><br>'.$OptionLink;
    }

    public function getHasOptionPage()
    {
        $this->prepareTemplateRendering();
        $oRTemplate = $this;
        $sOptionFile = '/options/options.twig';
        while (!file_exists($oRTemplate->path.$sOptionFile)){

            $oMotherTemplate = $oRTemplate->oMotherTemplate;
            if(!($oMotherTemplate instanceof TemplateConfiguration)){
                return false;
                break;
            }
            $oMotherTemplate->prepareTemplateRendering();
            $oRTemplate = $oMotherTemplate;
        }
        return true;
    }

    public function getOptionPage()
    {        
        $this->prepareTemplateRendering();
        return Yii::app()->twigRenderer->renderOptionPage($this, array('templateConfiguration' =>$this->attributes));
    }

    /**
     * From a list of json files in db it will generate a PHP array ready to use by removeFileFromPackage()
     *
     * @var $jFiles string json
     * @return array
     */
    protected function getFilesToLoad($oTemplate, $sType)
    {
        if (empty($this->aFilesToLoad)){
            $sField = 'files_'.$sType;
            $jFiles = $oTemplate->$sField;

            $this->aFilesToLoad = array();

            if(!empty($jFiles)){
                $oFiles = json_decode($jFiles);
                foreach($oFiles as $action => $aFileList){
                    if ($action == "add" || $action == "replace"){

                        // Specific inheritance of one of the value of the json array
                        if ($aFileList[0] == 'inherit'){
                            $aParentjFiles = (array) json_decode($oTemplate->getParentConfiguration->$sField);
                            $aFileList = $aParentjFiles[$action];
                        }

                        $this->aFilesToLoad = array_merge($this->aFilesToLoad, $aFileList);
                    }
                }
            }
        }

        return $this->aFilesToLoad;
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
     * This is an object recursive call to TemplateConfiguration::prepareTemplateRendering()
     */
    protected function setMotherTemplates()
    {
        if(!empty($this->template->extends_templates_name)){
            $sMotherTemplateName   = $this->template->extends_templates_name;
            $this->oMotherTemplate = Template::getTemplateConfiguration($sMotherTemplateName);
            $this->oMotherTemplate->prepareTemplateRendering($sMotherTemplateName);
            if ($this->oMotherTemplate->checkTemplate()){
                $this->oMotherTemplate->prepareTemplateRendering($sMotherTemplateName); // Object Recursion
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
        $this->apiVersion  = (!empty($this->template->api_version))? $this->template->api_version : $this->oMotherTemplate->apiVersion;
        $this->viewPath    = (!empty($this->template->view_folder))  ? $this->path.DIRECTORY_SEPARATOR.$this->template->view_folder.DIRECTORY_SEPARATOR : $this->path.DIRECTORY_SEPARATOR.$this->oMotherTemplate->view_folder.DIRECTORY_SEPARATOR;
        $this->filesPath   = (!empty($this->template->files_folder))  ? $this->path.DIRECTORY_SEPARATOR.$this->template->files_folder.DIRECTORY_SEPARATOR   :  $this->path.DIRECTORY_SEPARATOR.$this->oMotherTemplate->file_folder.DIRECTORY_SEPARATOR;

        // Options are optional
        $this->setOptions();

        // Not mandatory (use package dependances)
        $this->setCssFramework();

        if (!empty($this->packages_to_load)){
            $this->packages = json_decode($this->packages_to_load);
        }

        // Add depend package according to packages
        $this->depends                  = array_merge($this->depends, $this->getDependsPackages($this));
    }

    private function setCssFramework()
    {
        if (!empty($this->cssframework_name)){
            $this->cssFramework = new \stdClass();
            $this->cssFramework->name = $this->cssframework_name;
            $this->cssFramework->css  = json_decode($this->cssframework_css);
            $this->cssFramework->js   = json_decode($this->cssframework_js);

        }else{
            $this->cssFramework = '';
        }
    }

    protected function setOptions()
    {
        $this->oOptions = array();
        if (!empty($this->options)){
            $this->oOptions = json_decode($this->options);
        }

        $this->setOptionInheritance();
    }

    protected function setOptionInheritance()
    {
        $oOptions = $this->oOptions;

        foreach($oOptions as $sKey => $sOption){
            if ($sOption == 'inherit'){
                $aParentOptions = (array) json_decode($this->getParentConfiguration('options'));
                $oOptions->$sKey = $aParentOptions[$sKey];
            }
        }
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
        if (empty($this->aFrameworkAssetsToReplace)){
            $sFieldName  = 'cssframework_'.$sType;
            $aFieldValue = (array) json_decode($this->$sFieldName);

            $this->aFrameworkAssetsToReplace = array();
            if (!empty( $aFieldValue )){
                $this->aFrameworkAssetsToReplace = (array) $aFieldValue['replace'] ;

                // Inner field inheritance
                foreach ($this->aFrameworkAssetsToReplace as $key => $aFiles){
                    foreach($aFiles as $sReplacement){
                        if ( $sReplacement == "inherit"){
                            $aParentReplacement = $this->getParentConfiguration()->getFrameworkAssetsToReplace($sType);
                            $this->aFrameworkAssetsToReplace[$key][1] = $aParentReplacement[$key][1];
                        }
                    }
                }

                if($bInlcudeRemove && isset($aFieldValue['remove'])){
                    $this->aFrameworkAssetsToReplace = array_merge($this->aFrameworkAssetsToReplace, (array) $aFieldValue['remove'] );
                }
            }
        }

        return $this->aFrameworkAssetsToReplace;
    }

    /**
     * Get the list of file replacement from Engine Framework
     * @param string  $sType            css|js the type of file
     * @param boolean $bInlcudeRemove   also get the files to remove
     * @return array
     */
    protected function getFrameworkAssetsReplacement( $sType )
    {
        if (empty($this->aReplacements)){
            $aFrameworkAssetsToReplace = $this->getFrameworkAssetsToReplace($sType);

            $this->aReplacements = array();
            foreach($aFrameworkAssetsToReplace as $key => $aAsset ){
                $aReplace = $aAsset[1];
                $this->aReplacements[] = $aReplace;
            }
        }

        return $this->aReplacements;
    }


    public function getParentConfiguration(){
        if (empty($this->oParentTemplate)){
            //check for surveygroup id
            if($this->sid != null && $this->gsid != null){
                $this->oParentTemplate = Template::getTemplateConfiguration(null,null,$this->gsid);
            }else{
                //check for general global template
                $this->oParentTemplate = Template::getTemplateConfiguration($this->templates_name);
            }
        }
        return $this->oParentTemplate;
    }

    /**
     * Proxy for the AR method to manage the inheritance
     * If one of the field that can be inherited is set to "inherit", then it will return the value of its parent
     * NOTE: this is recursive, if the parent field itself is set to inherit, then it will the value of the parent of the parent, etc
     *
     * @param string $name the name of the attribute
     * @return mixed
     */
    public function __get($name)
    {
        $aAttributesThatCanBeInherited = array('files_css', 'files_js', 'options', 'cssframework_name', 'cssframework_css', 'cssframework_js');

        if (in_array($name, $aAttributesThatCanBeInherited) && $this->bUseMagicInherit){
            // Full inheritance of the whole field
            $sAttribute = parent::__get($name);
            if($sAttribute === 'inherit'){
                // NOTE: this is object recursive (if parent configuration field is set to inherit, then it will lead to this method again.)
                $sAttribute = $this->getParentConfiguration()->$name;
            }
        }else{
            $sAttribute = parent::__get($name);
        }

        return $sAttribute;
    }

}
