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
class TemplateManifest extends TemplateConfiguration
{
    public $templateEditor;


    // Public interface specific to TemplateManifest

    /**
     * Update the configuration file "last update" node.
     * For now, it is called only from template editor
     */
    public function actualizeLastUpdate()
    {
        libxml_disable_entity_loader(false);
        $config = simplexml_load_file(realpath ($this->xmlFile));
        $config->metadatas->last_update = date("Y-m-d H:i:s");
        $config->asXML( realpath ($this->xmlFile) );                // Belt
        touch ( $this->path );                                      // & Suspenders ;-)
        libxml_disable_entity_loader(true);
    }

    /**
     * Used from the template editor.
     * It returns an array of editable files by screen for a given file type
     *
     * @param   string  $sType      the type of files (view/css/js)
     * @param   string  $sScreen    the screen you want to retreive the files from. If null: all screens
     * @return  array   array       ( [screen name] => array([files]) )
     */
    public function getValidScreenFiles($sType = "view", $sScreen=null)
    {
        $aScreenFiles = array();

        $filesFromXML = (is_null($sScreen)) ? (array) $this->templateEditor->screens->xpath('//file') : $this->templateEditor->screens->xpath('//'.$sScreen.'/file');

        foreach( $filesFromXML as $file){

            if ( $file->attributes()->type == $sType ){
                $aScreenFiles[] = (string) $file;
            }
        }

        $aScreenFiles = array_unique($aScreenFiles);
        return $aScreenFiles;
    }

    /**
     * Returns the layout file name for a given screen
     *
     * @param   string  $sScreen    the screen you want to retreive the files from. If null: all screens
     * @return  string  the file name
     */
    public function getLayoutForScreen($sScreen)
    {
        $filesFromXML = $this->templateEditor->screens->xpath('//'.$sScreen.'/file');

        foreach( $filesFromXML as $file){

            if ( $file->attributes()->role == "layout" ){
                return (string) $file;
            }
        }

        return false;
    }

    /**
     * Retreives the absolute path for a file to edit (current template, mother template, etc)
     * Also perform few checks (permission to edit? etc)
     *
     * @param string $sfile relative path to the file to edit
     */
    public function getFilePathForEdition($sFile, $aAllowedFiles=null)
    {

        // Check if the file is allowed for edition ($aAllowedFiles is produced via getValidScreenFiles() )
        if (is_array($aAllowedFiles)){
            if (!in_array($sFile, $aAllowedFiles)){
                return false;
            }
        }

        return $this->getFilePath($sFile, $this);
    }

    /**
    * Copy a file from mother template to local directory and edit manifest if needed
    *
    * @param string $sTemplateName
    * @return string template url
    */
    public function extendsFile($sFile)
    {
        if( !file_exists($this->path.'/'.$sFile) && !file_exists($this->viewPath.$sFile) ){

            // Copy file from mother template to local directory
            $sRfilePath = $this->getFilePath($sFile, $this);
            $sLfilePath = (pathinfo($sFile, PATHINFO_EXTENSION) == 'twig')?$this->viewPath.$sFile:$this->path.'/'.$sFile;
            copy ( $sRfilePath,  $sLfilePath );

            // If it's a css or js file from config... must update DB and XML too....
            $sExt = pathinfo($sLfilePath, PATHINFO_EXTENSION);
            if ($sExt == "css" || $sExt == "js"){

                // Check if that CSS/JS file is in DB/XML
                $aFiles = $this->getFilesForPackages($sExt, $this);
                $sFile  = str_replace('./', '', $sFile);

                // The CSS/JS file is a configuration one....
                if(in_array($sFile, $aFiles)){
                    $this->addFileReplacement($sFile, $sExt);
                    $this->addFileReplacementInDB($sFile, $sExt);
                }
            }
        }
        return $this->getFilePath($sFile, $this);
    }

    /**
    * Get the files (css or js) defined in the manifest of a template and its mother templates
    *
    * @param  string $type       css|js
    * @param string $oRTemplate template from which the recurrence should start
    * @return array
    */
    public function getFilesForPackages($type, $oRTemplate)
    {
        $aFiles = array();
        while(is_a($oRTemplate, 'TemplateManifest')){
            $aTFiles = isset($oRTemplate->config->files->$type->filename)?(array) $oRTemplate->config->files->$type->filename:array();
            $aFiles  = array_merge($aTFiles, $aFiles);
            $oRTemplate = $oRTemplate->oMotherTemplate;
        }
        return $aFiles;
    }

    /**
     * Add a file replacement entry in DB
     * In the first place it tries to get the all the configuration entries for this template
     * (it can be void if edited from template editor, or they can be numerous if the template has local config at survey/survey group/user level)
     * Then, it call $oTemplateConfiguration->addFileReplacement($sFile, $sType) for each one of them.
     *
     * @param string $sFile the file to replace
     * @param string $sType css|js
     */
    public function addFileReplacementInDB($sFile, $sType)
    {
        $oTemplateConfigurationModels = TemplateConfiguration::model()->findAllByAttributes(array('templates_name'=>$this->sTemplateName));
        foreach($oTemplateConfigurationModels as $oTemplateConfigurationModel){
            $oTemplateConfigurationModel->addFileReplacement($sFile, $sType);
        }
    }

    /**
     * Get the list of all the files inside the file folder for a template and its mother templates
     * @return array
     */
    public function getOtherFiles()
    {
        $otherfiles = array();

        if (!empty($this->oMotherTemplate)){
            $otherfiles = $this->oMotherTemplate->getOtherFiles();
        }

        if ( file_exists($this->filesPath) && $handle = opendir($this->filesPath)){

            while (false !== ($file = readdir($handle))){
                if (!array_search($file, array("DUMMYENTRY", ".", "..", "preview.png"))) {
                    if (!is_dir($this->viewPath . DIRECTORY_SEPARATOR . $file)) {
                        $otherfiles[] = $this->sFilesDirectory . DIRECTORY_SEPARATOR . $file;
                    }
                }
            }

            closedir($handle);
        }
        return $otherfiles;
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
        $this->setTemplateName($sTemplateName, $iSurveyId);                     // Check and set template name
        $this->setIsStandard();                                                 // Check if  it is a CORE template
        $this->setPath();                                                       // Check and set path
        $this->readManifest();                                                  // Check and read the manifest to set local params
        $this->setMotherTemplates();                                            // Recursive mother templates configuration
        $this->setThisTemplate();                                               // Set the main config values of this template
        $this->createTemplatePackage($this);                                    // Create an asset package ready to be loaded
        return $this;
    }

    /**
     * Add a file replacement entry
     * eg: <filename replace="css/template.css">css/template.css</filename>
     *
     * @param string $sFile the file to replace
     * @param string $sType css|js
     */
    public function addFileReplacement($sFile, $sType)
    {
        // First we get the XML file
        libxml_disable_entity_loader(false);
        $oNewManifest = new DOMDocument();
        $oNewManifest->load($this->path."/config.xml");

        $oConfig   = $oNewManifest->getElementsByTagName('config')->item(0);
        $oFiles    = $oNewManifest->getElementsByTagName('files')->item(0);
        $oOptions  = $oNewManifest->getElementsByTagName('options')->item(0);   // Only for the insert before statement

        if (is_null($oFiles)){
            $oFiles    = $oNewManifest->createElement('files');
        }

        $oAssetType = $oFiles->getElementsByTagName($sType)->item(0);
        if (is_null($oAssetType)){
            $oAssetType   = $oNewManifest->createElement($sType);
            $oFiles->appendChild($oAssetType);
        }

        $oNewManifest->createElement('filename');

        $oAssetElem       = $oNewManifest->createElement('filename', $sFile);
        $replaceAttribute = $oNewManifest->createAttribute('replace');
        $replaceAttribute->value = $sFile;
        $oAssetElem->appendChild($replaceAttribute);
        $oAssetType->appendChild($oAssetElem);
        $oConfig->insertBefore($oFiles,$oOptions);
        $oNewManifest->save($this->path."/config.xml");
        libxml_disable_entity_loader(true);
    }

    /**
     * Read the config.xml file of the template and push its contents to $this->config
     */
    private function readManifest()
    {
        $this->xmlFile         = $this->path.DIRECTORY_SEPARATOR.'config.xml';
        $bOldEntityLoaderState = libxml_disable_entity_loader(true);            // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection
        $sXMLConfigFile        = file_get_contents( realpath ($this->xmlFile)); // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string
        $this->config          = simplexml_load_string($sXMLConfigFile);        // Using PHP >= 5.4 then no need to decode encode + need attributes : then other function if needed :https://secure.php.net/manual/en/book.simplexml.php#108688 for example

        libxml_disable_entity_loader($bOldEntityLoaderState);                   // Put back entity loader to its original state, to avoid contagion to other applications on the server
    }

    /**
     * Set the path of the current template
     * It checks if it's a core or a user template, if it exists, and if it has a config file
     */
    private function setPath()
    {
        // If the template is standard, its root is based on standardtemplaterootdir, else, it is a user template, its root is based on usertemplaterootdir
        $this->path = ($this->isStandard)?Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->sTemplateName:Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$this->sTemplateName;

        // If the template directory doesn't exist, we just set Default as the template to use
        // TODO: create a method "setToDefault"
        if (!is_dir($this->path)) {
            $this->sTemplateName = 'default';
            $this->isStandard    = true;
            $this->path = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->sTemplateName;
            if(!$this->iSurveyId){
                setGlobalSetting('defaulttemplate', 'default');
            }
        }

        // If the template doesn't have a config file (maybe it has been deleted, or whatever),
        // then, we load the default template
        $this->hasConfigFile = (string) is_file($this->path.DIRECTORY_SEPARATOR.'config.xml');
        if (!$this->hasConfigFile) {
            $this->path = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$this->sTemplateName;

        }
    }

    /**
     * Set the template name.
     * If no templateName provided, then a survey id should be given (it will then load the template related to the survey)
     *
     * @var     $sTemplateName  string the name of the template
     * @var     $iSurveyId      int    the id of the survey
      */
    private function setTemplateName($sTemplateName='', $iSurveyId='')
    {
        // If it is called from the template editor, a template name will be provided.
        // If it is called for survey taking, a survey id will be provided
        if ($sTemplateName == '' && $iSurveyId == '') {
            /* Some controller didn't test completely survey id (PrintAnswersController for example), then set to default here */
            $sTemplateName = Template::templateNameFilter(Yii::app()->getConfig('defaulttemplate','default'));
        }

        $this->sTemplateName = $sTemplateName;
        $this->iSurveyId     = (int) $iSurveyId;

        if ($sTemplateName == '') {
            $oSurvey       = Survey::model()->findByPk($iSurveyId);

            if($oSurvey) {
                $this->sTemplateName = $oSurvey->template;
            } else {
                $this->sTemplateName = Template::templateNameFilter(App()->getConfig('defaulttemplate','default'));
            }
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
        $aFiles = array();
        //if(!empty($jFiles)){
        if(isset($oTemplate->config->files->$sType->filename)){
            $aFiles = (array) $oTemplate->config->files->$sType->filename;
        }
        return $aFiles;
    }

    /**
     * Change the mother template configuration depending on template settings
     * @param $sType     string   the type of settings to change (css or js)
     * @param $aSettings array    array of local setting
     * @return array
     */
    protected function changeMotherConfiguration( $sType, $aSettings )
    {
        foreach( $aSettings as $key => $aSetting){
            if (!empty($aSetting['replace']) || !empty($aSetting['remove'])){
                $this->removeFileFromPackage($this->oMotherTemplate->sPackageName, $sType, $aSetting['replace']);
                unset($aSettings[$key]);
            }
        }

        return $aSettings;
    }

    /**
     * Proxy for Yii::app()->clientScript->removeFileFromPackage()
     * It's not realy needed here, but it is needed for TemplateConfiguration model.
     * So, we use it here to have the same interface for TemplateManifest and TemplateConfiguration,
     * So, in the future, we'll can both inherit them from a same object (best would be to extend CModel to create a LSYii_Template)
     *
     * @param $sPackageName     string   name of the package to edit
     * @param $sType            string   the type of settings to change (css or js)
     * @param $aSettings        array    array of local setting
     * @return array
     */
    protected function removeFileFromPackage( $sPackageName, $sType, $aSetting )
    {
        Yii::app()->clientScript->removeFileFromPackage($sPackageName, $sType, $aSetting );
    }

    /**
     * Configure the mother template (and its mother templates)
     * This is an object recursive call to TemplateManifest::setTemplateConfiguration()
     */
    protected function setMotherTemplates()
    {
        if (isset($this->config->metadatas->extends)){
            $sMotherTemplateName   = (string) $this->config->metadatas->extends;
            $this->oMotherTemplate = new TemplateManifest;
            $this->oMotherTemplate->setTemplateConfiguration($sMotherTemplateName); // Object Recursion
        }
    }

    /**
     * Set the default configuration values for the template, and use the motherTemplate value if needed
     */
    private function setThisTemplate()
    {
        // Mandtory setting in config XML (can be not set in inheritance tree, but must be set in mother template (void value is still a setting))
        $this->apiVersion               = (isset($this->config->metadatas->apiVersion))            ? $this->config->metadatas->apiVersion                                                       : $this->oMotherTemplate->apiVersion;
        $this->viewPath                 = (!empty($this->config->xpath("//viewdirectory")))   ? $this->path.DIRECTORY_SEPARATOR.$this->config->engine->viewdirectory.DIRECTORY_SEPARATOR    : $this->path.DIRECTORY_SEPARATOR.$this->oMotherTemplate->config->engine->viewdirectory.DIRECTORY_SEPARATOR;
        $this->filesPath                = (!empty($this->config->xpath("//filesdirectory")))  ? $this->path.DIRECTORY_SEPARATOR.$this->config->engine->filesdirectory.DIRECTORY_SEPARATOR   :  $this->path.DIRECTORY_SEPARATOR.$this->oMotherTemplate->config->engine->filesdirectory.DIRECTORY_SEPARATOR;
        $this->sFilesDirectory          = (!empty($this->config->xpath("//filesdirectory")))  ? $this->config->engine->filesdirectory   :  $this->oMotherTemplate->sFilesDirectory;
        $this->templateEditor           = (!empty($this->config->xpath("//template_editor"))) ? $this->config->engine->template_editor : $this->oMotherTemplate->templateEditor;

        // Options are optional
        if (!empty($this->config->xpath("//options"))){
            $this->oOptions = $this->config->xpath("//options");
        }elseif(!empty($this->oMotherTemplate->oOptions)){
            $this->oOptions = $this->oMotherTemplate->oOptions;
        }else{
            $this->oOptions = "";
        }

        // Not mandatory (use package dependances)
        $this->cssFramework             = (!empty($this->config->xpath("//cssframework")))    ? $this->config->engine->cssframework                                                                                  : '';
        $this->packages                 = (!empty($this->config->xpath("//packages")))        ? $this->config->engine->packages                                                                                      : array();

        // Add depend package according to packages
        $this->depends                  = array_merge($this->depends, $this->getDependsPackages($this));
        //var_dump($this->depends); die();
    }



    /**
     * Common privates methods
     */




    /**
     * Get the depends package
     * @uses self::@package
     * @return string[]
     */
    protected function getDependsPackages($oTemplate)
    {
        $dir = (getLanguageRTL(App()->getLanguage()))?'rtl':'ltr';

        /* Core package */
        $packages[] = 'limesurvey-public';
        $packages[] = 'template-core';
        $packages[] = ( $dir == "ltr")? 'template-core-ltr' : 'template-core-rtl'; // Awesome Bootstrap Checkboxes

        /* bootstrap */
        if(!empty($this->cssFramework)){

            // Basic bootstrap package
            if((string)$this->cssFramework->name == "bootstrap"){
                $packages[] = 'bootstrap';
            }

            // Rtl version of bootstrap
            if ($dir == "rtl"){
                $packages[] = 'bootstrap-rtl';
            }

            // Remove unwanted bootstrap stuff
            foreach( $this->getFrameworkAssetsToReplace('css', true) as $toReplace){
                Yii::app()->clientScript->removeFileFromPackage('bootstrap', 'css', $toReplace );
            }

            foreach( $this->getFrameworkAssetsToReplace('js', true) as $toReplace){
                Yii::app()->clientScript->removeFileFromPackage('bootstrap', 'js', $toReplace );
            }
        }

        /* Moter Template */
        if (isset($this->config->metadatas->extends)){
            $sMotherTemplateName = (string) $this->config->metadatas->extends;
            $packages[]          = 'survey-template-'.$sMotherTemplateName;
        }

        return $packages;
    }

    /**
     * Different implemation of  privates methods
     */

    /**
     * Get the list of file replacement from Engine Framework
     * @param string  $sType            css|js the type of file
     * @param boolean $bInlcudeRemove   also get the files to remove
     * @return array
     */
    protected function getFrameworkAssetsToReplace( $sType, $bInlcudeRemove = false)
    {
        $aAssetsToRemove = array();
        if (!empty($this->cssFramework->$sType)){
            $aAssetsToRemove =  (array) $this->cssFramework->$sType->attributes()->replace ;
            if($bInlcudeRemove){
                $aAssetsToRemove = array_merge($aAssetsToRemove, (array) $this->cssFramework->$sType->attributes()->remove );
            }
        }
        return $aAssetsToRemove;
    }

}
