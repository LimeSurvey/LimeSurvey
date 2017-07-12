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
 * @property string $packages_ltr
 * @property string $packages_rtl
 *
 *
 * @package       LimeSurvey
 * @subpackage    Backend
 */
class TemplateConfiguration extends CActiveRecord
{
    /** @var string $sTemplateName The template name */
    public $sTemplateName='';

    /** @var string $sPackageName Name of the asset package of this template*/
    public $sPackageName;

    /** @var  string $path Path of this template */
    public $path;

    /** @var string[] $sTemplateurl Url to reach the framework */
    public $sTemplateurl;

    /** @var  string $viewPath Path of the views files (twig template) */
    public $viewPath;

    /** @var  string $filesPath Path of the tmeplate's files */
    public $filesPath;

    /** @var string[] $cssFramework What framework css is used */
    public $cssFramework;

    /** @var boolean $isStandard Is this template a core one? */
    public $isStandard;

    /** @var SimpleXMLElement $config Will contain the config.xml */
    public $config;

    /** @var TemplateConfiguration $oMotherTemplate The template name */
    public $oMotherTemplate;

    /** @var SimpleXMLElement $oOptions The template options */
    public $oOptions;


    /** @var string $iSurveyId The current Survey Id. It can be void. It's use only to retreive the current template of a given survey */
    private $iSurveyId='';

    /** @var string $hasConfigFile Does it has a config.xml file? */
    private $hasConfigFile='';//

    /** @var stdClass[] $packages Array of package dependencies defined in config.xml*/
    private $packages;

    /** @var string[] $depends List of all dependencies (could be more that just the config.xml packages) */
    private $depends = array();

    /** @var string $xmlFile What xml config file does it use? (config/minimal) */
    private $xmlFile;

    /**  @var integer $apiVersion: Version of the LS API when created. Must be private : disallow update */
    private $apiVersion;
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
            array('id, templates_name', 'required'),
            array('id, sid, gsid', 'numerical', 'integerOnly'=>true),
            array('templates_name', 'length', 'max'=>150),
            array('cssframework_name', 'length', 'max'=>45),
            array('files_css, files_js, files_print_css, options, cssframework_css, cssframework_js, packages_to_load, packages_ltr, packages_rtl', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, templates_name, sid, gsid, files_css, files_js, files_print_css, options, cssframework_name, cssframework_css, cssframework_js, packages_to_load, packages_ltr, packages_rtl', 'safe', 'on'=>'search'),
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
            'packages_ltr' => 'Packages Ltr',
            'packages_rtl' => 'Packages Rtl',
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
        $criteria->compare('packages_ltr',$this->packages_ltr,true);
        $criteria->compare('packages_rtl',$this->packages_rtl,true);

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
        $this->setThisTemplate();                                               // Set the main config values of this template
        $this->createTemplatePackage($this);                                    // Create an asset package ready to be loaded
        return $this;
    }

    /**
     * get the template API version
     * @return integer
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
    * Returns the complete URL path to a given template name
    *
    * @param string $sTemplateName
    * @return string template url
    */
    public function getTemplateURL()
    {
        if(!isset($this->sTemplateurl)){
            $this->sTemplateurl = Template::getTemplateURL($this->sTemplateName);
        }
        return $this->sTemplateurl;
    }

    public function extendsFile($sFile)
    {
        if( !file_exists($this->path.'/'.$sFile) && !file_exists($this->viewPath.$sFile) ){

            // Copy file from mother template to local directory
            $sRfilePath = $this->getFilePath($sFile, $this);
            $sLfilePath = (pathinfo($sFile, PATHINFO_EXTENSION) == 'twig')?$this->viewPath.$sFile:$this->path.'/'.$sFile;
            copy ( $sRfilePath,  $sLfilePath );
        }

        return $this->getFilePath($sFile, $this);
    }

    public function getTemplateForFile($sFile, $oRTemplate)
    {
        while (!file_exists($oRTemplate->path.'/'.$sFile) && !file_exists($oRTemplate->viewPath.$sFile)){
            $oMotherTemplate = $oRTemplate->oMotherTemplate;
            if(!($oMotherTemplate instanceof TemplateConfiguration)){
                return false;
                break;
            }
            $oRTemplate = $oMotherTemplate;
        }

        return $oRTemplate;
    }


    /**
     * Update the config file of a given template so that it extends another one
     *
     * It will:
     * 1. Delete files and engine nodes
     * 2. Update the name of the template
     * 3. Change the creation/modification date to the current date
     * 4. Change the autor name to the current logged in user
     * 5. Change the author email to the admin email
     *
     * TODO: MOVE TO TEMPLATE MODEL, AND CREATE NEEDED DATABASE ROWS
     *
     * Used in template editor
     * Both templates and configuration files must exist before using this function
     *
     * It's used when extending a template from template editor
     * @param   string  $sToExtends     the name of the template to extend
     * @param   string  $sNewName       the name of the new template
     */
    static public function extendsConfig($sToExtends, $sNewName)
    {
        $sConfigPath = Yii::app()->getConfig('usertemplaterootdir') . "/" . $sNewName;

        // First we get the XML file
        libxml_disable_entity_loader(false);
        $oNewManifest = new DOMDocument();
        $oNewManifest->load($sConfigPath."/config.xml");
        $oConfig            = $oNewManifest->getElementsByTagName('config')->item(0);

        // Then we delete the nodes that should be inherit
        $aNodesToDelete     = array();
        $aNodesToDelete[]   = $oConfig->getElementsByTagName('files')->item(0);
        $aNodesToDelete[]   = $oConfig->getElementsByTagName('engine')->item(0);

        foreach($aNodesToDelete as $node){
            $oConfig->removeChild($node);
        }

        // We replace the name by the new name
        $oMetadatas     = $oConfig->getElementsByTagName('metadatas')->item(0);

        $oOldNameNode   = $oMetadatas->getElementsByTagName('name')->item(0);
        $oNvNameNode    = $oNewManifest->createElement('name', $sNewName);
        $oMetadatas->replaceChild($oNvNameNode, $oOldNameNode);

        // We change the date
        $today          = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
        $oOldDateNode   = $oMetadatas->getElementsByTagName('creationDate')->item(0);
        $oNvDateNode    = $oNewManifest->createElement('creationDate', $today);
        $oMetadatas->replaceChild($oNvDateNode, $oOldDateNode);

        $oOldUpdateNode = $oMetadatas->getElementsByTagName('last_update')->item(0);
        $oNvDateNode    = $oNewManifest->createElement('last_update', $today);
        $oMetadatas->replaceChild($oNvDateNode, $oOldUpdateNode);

        // We change the author name
        $oOldAuthorNode   = $oMetadatas->getElementsByTagName('author')->item(0);
        $oNvAuthorNode    = $oNewManifest->createElement('author', Yii::app()->user->name);
        $oMetadatas->replaceChild($oNvAuthorNode, $oOldAuthorNode);

        // We change the author email
        $oOldMailNode   = $oMetadatas->getElementsByTagName('authorEmail')->item(0);
        $oNvMailNode    = $oNewManifest->createElement('authorEmail', htmlspecialchars(getGlobalSetting('siteadminemail')));
        $oMetadatas->replaceChild($oNvMailNode, $oOldMailNode);

        // TODO: provide more datas in the post variable such as description, url, copyright, etc

        // We add the extend parameter
        $oExtendsNode    = $oNewManifest->createElement('extends', $sToExtends);

        // We test if mother template already extends another template
        if(!empty($oMetadatas->getElementsByTagName('extends')->item(0))){
            $oMetadatas->replaceChild($oExtendsNode, $oMetadatas->getElementsByTagName('extends')->item(0));
        }else{
            $oMetadatas->appendChild($oExtendsNode);
        }

        $oNewManifest->save($sConfigPath."/config.xml");

        libxml_disable_entity_loader(true);

        // TODO: Add to db
    }


    /**
     * Create a package for the asset manager.
     * The asset manager will push to tmp/assets/xyxyxy/ the whole template directory (with css, js, files, etc.)
     * And it will publish the CSS and the JS defined in config.xml. So CSS can use relative path for pictures.
     * The publication of the package itself is in LSETwigViewRenderer::renderTemplateFromString()
     *
     */
    private function createTemplatePackage($oTemplate)
    {
        // Each template in the inheritance tree needs a specific alias
        $sPathName  = 'survey.template-'.$oTemplate->sTemplateName.'.path';
        $sViewName  = 'survey.template-'.$oTemplate->sTemplateName.'.viewpath';

        Yii::setPathOfAlias($sPathName, $oTemplate->path);
        Yii::setPathOfAlias($sViewName, $oTemplate->viewPath);

        $aCssFiles = array();
        if(!empty($this->files_css)){
            $oCssFiles = json_decode($this->files_css);
            foreach($oCssFiles as $action => $aFiles){
                $aCssFiles[$action] = $aFiles;
            }
        }

        $aJsFiles = array();
        if(!empty($this->files_js)){
            $oJsFiles = json_decode($this->files_js);
            foreach($oJsFiles as $action => $aFiles){
                $aJsFiles[$action] = $aFiles;
            }
        }

        $dir         = getLanguageRTL(App()->language) ? 'rtl' : 'ltr';

        // Remove/Replace mother files
        $aCssFiles = $this->changeMotherConfiguration('css', $aCssFiles);
        $aJsFiles  = $this->changeMotherConfiguration('js',  $aJsFiles);

        $this->sPackageName = 'survey-template-'.$this->sTemplateName;
        $sTemplateurl       = $oTemplate->getTemplateURL();

        // The package "survey-template-{sTemplateName}" will be available from anywhere in the app now.
        // To publish it : Yii::app()->clientScript->registerPackage( 'survey-template-{sTemplateName}' );
        // Depending on settings, it will create the asset directory, and publish the css and js files
        Yii::app()->clientScript->addPackage( $this->sPackageName, array(
            'devBaseUrl'  => $sTemplateurl,                                     // Used when asset manager is off
            'basePath'    => $sPathName,                                        // Used when asset manager is on
            'css'         => $aCssFiles,
            'js'          => $aJsFiles,
            'depends'     => $oTemplate->depends,
        ) );
    }

    /**
     * Change the mother template configuration depending on template settings
     * @var $sType     string   the type of settings to change (css or js)
     * @var $aSettings array    array of local setting
     * @return array
     */
    private function changeMotherConfiguration( $sType, $aSettings )
    {
        $aSettingsToAdd = array();

        if (is_a($this->oMotherTemplate, 'TemplateConfiguration')){
            foreach( $aSettings as $key => $aFiles){

                if ($key == "replace" || $key = "remove"){
                    foreach($aFiles as $sFile)
                        Yii::app()->clientScript->removeFileFromPackage($this->oMotherTemplate->sPackageName, $sType, $sFile );
                }
            }
        }

        foreach( $aSettings as $key => $aFiles){
            if ($key == "add" || $key == "replace"){
                foreach($aFiles as $sFile)
                    $aSettingsToAdd[] = $sFile;
            }

        }

        return $aSettingsToAdd;
    }

    /**
     * Configure the mother template (and its mother templates)
     * This is an object recursive call to TemplateConfiguration::setTemplateConfiguration()
     */
    private function setMotherTemplates()
    {
        if(!empty($this->template->extends_templates_name)){
            $sMotherTemplateName   = $this->template->extends_templates_name;
            $this->oMotherTemplate = new TemplateConfiguration;

            if ($this->oMotherTemplate->checkTemplate()){
                $this->oMotherTemplate->setTemplateConfiguration($sMotherTemplateName); // Object Recursion
            }else{
                // Throw exception? Set to default template?
            }
        }
    }

    public function checkTemplate()
    {
        if (!is_dir(Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->template->folder) && !is_dir(Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$this->template->folder)){
            return false;
        }
        return true;
    }

    /**
     * Set the default configuration values for the template, and use the motherTemplate value if needed
     */
    private function setThisTemplate()
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


    /**
     * @return bool
     */
    private function setIsStandard()
    {
        $this->isStandard = Template::isStandardTemplate($this->sTemplateName);
    }


    /**
     * Get the depends package
     * @uses self::@package
     * @return string[]
     */
    private function getDependsPackages($oTemplate)
    {

        /* Start by adding cssFramework package */
        $packages = $this->getFrameworkPackages($oTemplate);

        if (!getLanguageRTL(App()->getLanguage())) {
            $packages = array_merge ($packages, $this->getFrameworkPackages($oTemplate, 'ltr'));
        } else {
            $packages = array_merge ($packages, $this->getFrameworkPackages($oTemplate, 'rtl'));
        }

        /* Core package */
        $packages[]='limesurvey-public';

        /* template packages */
        if (!empty($this->packages->package)) {
            $packages = array_merge ($packages, (array)$this->packages->package);
        }

        /* Adding rtl/tl specific package (see https://bugs.limesurvey.org/view.php?id=11970#c42317 ) */
        $dir = getLanguageRTL(App()->language) ? 'rtl' : 'ltr';

        if (!empty($this->packages->$dir->package)) {
            $packages = array_merge ($packages, (array)$this->packages->$dir->package);
        }

        if (!empty($this->template->extends_templates_name)){
            $sMotherTemplateName = (string) $this->template->extends_templates_name;
            $packages[]          = 'survey-template-'.$sMotherTemplateName;
        }

        return $packages;
    }

    /**
     * Set the framework package
     * @param string $dir (rtl|ltr|)
     * @use self::@cssFramework
     * @return string[] depends for framework
     */
    private function getFrameworkPackages($oTemplate, $dir="")
    {
        // If current template doesn't have a name for the framework package, we use the mother's one
        $framework = isset($oTemplate->cssFramework->name) ? (string) $oTemplate->cssFramework->name : (string) $oTemplate->oMotherTemplate->cssFramework;
        $framework = $dir ? $framework."-".$dir : $framework;

        if  ( isset(Yii::app()->clientScript->packages[$framework]) ) {

            $frameworkPackages = array();

            /* Theming */
            if ($dir) {
                $cssFrameworkCsss = isset ( $oTemplate->cssFramework->$dir->css ) ? $oTemplate->cssFramework->$dir->css : array();
                $cssFrameworkJss  = isset ( $oTemplate->cssFramework->$dir->js  ) ? $oTemplate->cssFramework->$dir->js  : array();
            } else {

                $cssFrameworkCsss =  ! empty ( $oTemplate->cssframework_css       ) ? json_decode($oTemplate->cssframework_css)       : array();
                $cssFrameworkJss  =  ! empty ( $oTemplate->cssframework_js        ) ? json_decode($oTemplate->cssframework_js)        : array();
            }

            if (empty($cssFrameworkCsss) && empty($cssFrameworkJss)) {
                $frameworkPackages[] = $framework;
            } else {

                $cssFrameworkPackage = Yii::app()->clientScript->packages[$framework];     // Need to create an adapted core framework
                $packageCss          = array();                                            // Need to create an adapted template/theme framework */
                $packageJs           = array();                                            // css file to replace from default package */
                $cssDelete           = array();

                // foreach($cssFrameworkCsss as $cssFrameworkCss) {
                foreach($cssFrameworkCsss as $action => $aFiles) {
                    // if(isset($cssFrameworkCss['replace'])) {
                    if($action == 'replace') {
                        foreach($aFiles as $aFile)
                            $cssDelete[] = $aFile;
                    }
                }

                if(isset($cssFrameworkPackage['css'])) {
                    $cssFrameworkPackage['css']=array_diff($cssFrameworkPackage['css'],$cssDelete);
                }

                $jsDelete=array();
                foreach($cssFrameworkJss as $cssFrameworkJs) {
                    if(isset($cssFrameworkJs['replace'])) {
                        $jsDelete[] = $cssFrameworkJs['replace'];
                    }
                    if((string)$cssFrameworkJs) {
                        $packageJs[] = (string)$cssFrameworkJs;
                    }
                }
                if(isset($cssFrameworkPackage['js'])) {
                    $cssFrameworkPackage['js'] = array_diff($cssFrameworkPackage['js'],$cssDelete);
                }

                /* And now : we add : core package fixed + template/theme package */
                Yii::app()->clientScript->packages[$framework] = $cssFrameworkPackage; /* @todo : test if empty css and js : just add depends if yes */
                $aDepends=array(
                    $framework,
                );

                // Remove CSS
                foreach($cssFrameworkCsss as $action => $aFiles) {
                    if($action == 'replace' || $key = "remove") {
                        foreach($aFiles as $sFile)
                            unset($packageCss[$sFile]);
                    }
                }

                // Add CSS
                foreach($cssFrameworkCsss as $action => $aFiles) {
                    if($action == 'add' || $key = "replace") {
                        foreach($aFiles as $sFile)
                            $packageCss[] = $sFile;
                    }
                }

                // TODO: refactorize remove/add CSS (see changeMotherConfiguration) + add JS

                $sTemplateurl = $oTemplate->getTemplateURL();
                $sPathName    = 'survey.template-'.$oTemplate->sTemplateName.'.path';

                Yii::app()->clientScript->addPackage(
                    $framework.'-template', array(
                        'devBaseUrl'  => $sTemplateurl,                        // Don't use asset manager
                        'basePath'    => $sPathName,                            // basePath: the asset manager will be used
                        'css'         => $packageCss,
                        'js'          => $packageJs,
                        'depends'     => $aDepends,
                    )
                );
                $frameworkPackages[]=$framework.'-template';

            }
            return $frameworkPackages;
        }/*elseif($framework){
            throw error ? Only for admin template editor ? disable and reset to default ?
        }*/
        return array();
    }

    /**
     * Get the file path for a given template.
     * It will check if css/js (relative to path), or view (view path)
     * It will search for current template and mother templates
     *
     * @param   string  $sFile          relative path to the file
     * @param   string  $oTemplate      the template where to look for (and its mother templates)
     */
    private function getFilePath($sFile, $oTemplate)
    {
        // Remove relative path
        $sFile = trim($sFile, '.');
        $sFile = trim($sFile, '/');

        // Retreive the correct template for this file (can be a mother template)
        $oTemplate = $this->getTemplateForFile($sFile, $oTemplate);

        if($oTemplate instanceof TemplateConfiguration){
            if(file_exists($oTemplate->path.'/'.$sFile)){
                return $oTemplate->path.'/'.$sFile;
            }elseif(file_exists($oTemplate->viewPath.$sFile)){
                return $oTemplate->viewPath.$sFile;
            }
        }
        return false;
    }

}
