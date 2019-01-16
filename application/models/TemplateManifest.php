<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
    public $sPreviewImgTag;

    /* There is no option inheritance on Manifest mode: values from XML are always used. So no: $bUseMagicInherit */


    /**
     * Public interface specific to TemplateManifest
     * They are used in TemplateEditor
     */

    /**
     * Update the configuration file "last update" node.
     * For now, it is called only from template editor
     */
    public function actualizeLastUpdate()
    {
        libxml_disable_entity_loader(false);
        $config = simplexml_load_file(realpath($this->xmlFile));
        $config->metadata->last_update = date("Y-m-d H:i:s");
        $config->asXML(realpath($this->xmlFile)); // Belt
        touch($this->path); // & Suspenders ;-)
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
    public function getValidScreenFiles($sType = "view", $sScreen = null)
    {
        $aScreenFiles = array();

        if (empty($this->templateEditor)) {
            return array();
        }

        $filesFromXML = (is_null($sScreen)) ? (array) $this->templateEditor->screens->xpath('//file') : $this->templateEditor->screens->xpath('//'.$sScreen.'/file');

        foreach ($filesFromXML as $file) {
            if ($file->attributes()->type == $sType) {
                $aScreenFiles[] = (string) $file;
            }
        }

        $oEvent = new PluginEvent('getValidScreenFiles');
        $oEvent->set('type', $sType);
        $oEvent->set('screen',$sScreen);
        //$oEvent->set('files',$aScreenFiles); // Not needed since we have remove and add event
        App()->getPluginManager()->dispatchEvent($oEvent);
        $aScreenFiles = array_values(array_diff($aScreenFiles, (array) $oEvent->get('remove')));
        $aScreenFiles = array_merge($aScreenFiles, (array)$oEvent->get('add'));
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
        if (empty($this->templateEditor)) {
            return false;
        }

        $filesFromXML = $this->templateEditor->screens->xpath('//'.$sScreen.'/file');


        foreach ($filesFromXML as $file) {

            if ($file->attributes()->role == "layout") {
                return (string) $file;
            }
        }

        return false;
    }



    /**
     * Returns the content file name for a given screen
     *
     * @param   string  $sScreen    the screen you want to retreive the files from. If null: all screens
     * @return  string  the file name
     */
    public function getContentForScreen($sScreen)
    {
        if (empty($this->templateEditor)) {
            return false;
        }

        $filesFromXML = $this->templateEditor->screens->xpath('//'.$sScreen.'/file');

        foreach ($filesFromXML as $file) {

            if ($file->attributes()->role == "content") {

                // The path of the file is defined inside the theme itself.
                $aExplodedFile = explode(DIRECTORY_SEPARATOR, $file);
                $sFormatedFile = end($aExplodedFile);

                // The file extension (.twig) is defined inside the theme itself.
                $aExplodedFile = explode('.', $sFormatedFile);
                $sFormatedFile = $aExplodedFile[0];
                return (string) $sFormatedFile;
            }
        }

        return false;
    }

    /**
     * Retreives the absolute path for a file to edit (current template, mother template, etc)
     * Also perform few checks (permission to edit? etc)
     *
     * @param string $sFile relative path to the file to edit
     */
    public function getFilePathForEditing($sFile, $aAllowedFiles = null)
    {

        // Check if the file is allowed for edition ($aAllowedFiles is produced via getValidScreenFiles() )
        if (is_array($aAllowedFiles)) {
            if (!in_array($sFile, $aAllowedFiles)) {
                return false;
            }
        }

        return $this->getFilePath($sFile, $this);
    }

    /**
     * Copy a file from mother template to local directory and edit manifest if needed
     *
     * @return string template url
     */
    public function extendsFile($sFile)
    {
        if (!file_exists($this->path.$sFile) && !file_exists($this->viewPath.$sFile)) {

            // Copy file from mother template to local directory
            $sSourceFilePath = $this->getFilePath($sFile, $this);
            $sDestinationFilePath = (pathinfo($sFile, PATHINFO_EXTENSION) == 'twig') ? $this->viewPath.$sFile : $this->path.$sFile;

            //PHP 7 seems not to create the folder on copy automatically.
            @mkdir(dirname($sDestinationFilePath), 0775, true);

            copy($sSourceFilePath, $sDestinationFilePath);

            // If it's a css or js file from config... must update DB and XML too....
            $sExt = pathinfo($sDestinationFilePath, PATHINFO_EXTENSION);
            if ($sExt == "css" || $sExt == "js") {

                // Check if that CSS/JS file is in DB/XML
                $aFiles = $this->getFilesForPackages($sExt, $this);
                $sFile  = str_replace('./', '', $sFile);

                // The CSS/JS file is a configuration one....
                if (in_array($sFile, $aFiles)) {
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
        while (is_a($oRTemplate, 'TemplateManifest')) {
            $aTFiles = isset($oRTemplate->config->files->$type->filename) ? (array) $oRTemplate->config->files->$type->filename : array();
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
        $oTemplateConfigurationModels = TemplateConfiguration::model()->findAllByAttributes(array('template_name'=>$this->sTemplateName));
        foreach ($oTemplateConfigurationModels as $oTemplateConfigurationModel) {
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

        if (!empty($this->oMotherTemplate)) {
            $otherfiles = $this->oMotherTemplate->getOtherFiles();
        }

        if (file_exists($this->filesPath) && $handle = opendir($this->filesPath)) {

            while (false !== ($file = readdir($handle))) {
                if (!array_search($file, array("DUMMYENTRY", ".", "..", "preview.png"))) {
                    if (!is_dir($this->viewPath.DIRECTORY_SEPARATOR.$file)) {
                        $otherfiles[$file] = $this->filesPath.DIRECTORY_SEPARATOR.$file;
                    }
                }
            }

            closedir($handle);
        }
        return $otherfiles;
    }


    /**
     *
     */
    public function getTemplateURL()
    {
        return Template::getTemplateURL($this->sTemplateName);
    }


    public function getButtons()
    {
        $sEditorUrl  = Yii::app()->getController()->createUrl('admin/themes/sa/view', array("templatename"=>$this->sTemplateName));
        $sDeleteUrl   = Yii::app()->getController()->createUrl('admin/themes/sa/deleteAvailableTheme/');


        // TODO: load to DB
        $sEditorLink = "<a
            id='template_editor_link_".$this->sTemplateName."'
            href='".$sEditorUrl."'
            class='btn btn-default btn-block'>
                <span class='icon-templates'></span>
                ".gT('Theme editor')."
            </a>";

            //

        $sLoadLink = CHtml::form( array("/admin/themeoptions/sa/importmanifest/"), 'post',array('id'=>'frmínstalltheme','name'=>'frmínstalltheme')) .
                "<input type='hidden' name='templatename' value='".$this->sTemplateName."'>
                <button id='template_options_link_".$this->sTemplateName."'
                class='btn btn-default btn-block'>
                    <span class='fa fa-download text-warning'></span>
                    ".gT('Install')."
                </button>
                </form>";


        $sDeleteLink = '';
        // We don't want user to be able to delete standard theme. Must be done via ftp (advanced users only)
        if(Permission::model()->hasGlobalPermission('templates','delete') && !Template::isStandardTemplate($this->sTemplateName) ){
          $sDeleteLink = '<a
              id="template_delete_link_'.$this->sTemplateName.'"
              href="'.$sDeleteUrl.'"
              data-post=\'{ "templatename": "'.$this->sTemplateName.'" }\'
              data-text="'.gT('Are you sure you want to delete this theme? ').'"
              title="'.gT('Delete').'"
              class="btn btn-danger btn-block selector--ConfirmModal">
                  <span class="fa fa-trash "></span>
                  '.gT('Delete').'
                  </a>';
      }

      return $sEditorLink.$sLoadLink.$sDeleteLink;

    }

    /**
     * Create a new entry in {{templates}} and {{template_configuration}} table using the template manifest
     * @param string $sTemplateName the name of the template to import
     * @return boolean true on success | exception
     * @throws Exception
     */
    public static function importManifest($sTemplateName, $aDatas = array())
    {
        $oTemplate                  = Template::getTemplateConfiguration($sTemplateName, null, null, true);
        $aDatas['extends']          = $bExtends = (string) $oTemplate->config->metadata->extends;

        if ($bExtends && !Template::model()->findByPk($bExtends)) {
            Yii::app()->setFlashMessage(sprintf(gT("You can't import the theme '%s' because '%s'  is not installed."), $sTemplateName, $bExtends), 'error');
            Yii::app()->getController()->redirect(array("admin/themeoptions"));
        }

        // Metadas is never inherited
        $aDatas['api_version']      = (string) $oTemplate->config->metadata->apiVersion;
        $aDatas['author_email']     = (string) $oTemplate->config->metadata->authorEmail;
        $aDatas['author_url']       = (string) $oTemplate->config->metadata->authorUrl;
        $aDatas['copyright']        = (string) $oTemplate->config->metadata->copyright;
        $aDatas['version']          = (string) $oTemplate->config->metadata->version;
        $aDatas['license']          = (string) $oTemplate->config->metadata->license;
        $aDatas['description']      = (string) $oTemplate->config->metadata->description;

        // Engine, files, and options can be inherited from a moter template
        // It means that the while field should always be inherited, not a subfield (eg: all files, not only css add)
        $oREngineTemplate = (!empty($bExtends)) ? self::getTemplateForXPath($oTemplate, 'engine') : $oTemplate;


        $aDatas['view_folder']       = (string) $oREngineTemplate->config->engine->viewdirectory;
        $aDatas['files_folder']      = (string) $oREngineTemplate->config->engine->filesdirectory;
        $aDatas['cssframework_name'] = (string) $oREngineTemplate->config->engine->cssframework->name;
        $aDatas['cssframework_css']  = self::getAssetsToReplaceFormated($oREngineTemplate->config->engine, 'css'); //self::formatArrayFields($oREngineTemplate, 'engine', 'cssframework_css');
        $aDatas['cssframework_js']   = self::formatArrayFields($oREngineTemplate, 'engine', 'cssframework_js');
        $aDatas['packages_to_load']  = self::formatArrayFields($oREngineTemplate, 'engine', 'packages');


        // If empty in manifest, it should be the field in db, so the Mother Template css/js files will be used...
        if (is_object($oTemplate->config->files)) {
            $aDatas['files_css']         = self::formatArrayFields($oTemplate, 'files', 'css');
            $aDatas['files_js']          = self::formatArrayFields($oTemplate, 'files', 'js');
            $aDatas['files_print_css']   = self::formatArrayFields($oTemplate, 'files', 'print_css');
        } else {
            $aDatas['files_css'] = $aDatas['files_js'] = $aDatas['files_print_css'] = null;
        }

        $aDatas['aOptions'] = (!empty($oTemplate->config->options[0]) && count($oTemplate->config->options[0]) == 0) ? array() : $oTemplate->config->options[0]; // If template provide empty options, it must be cleaned to avoid crashes

        return parent::importManifest($sTemplateName, $aDatas);
    }

    /**
     * Create a new entry in {{template_configuration}} table using the survey theme options from lss export file
     * @param     $iSurveyId      int    the id of the survey
     * @param $xml SimpleXMLElement
     * @return boolean true on success
     */
    public static function importManifestLss($iSurveyId = 0, $xml =null)
    {
        if ((int)$iSurveyId > 0 && !empty($xml)){
            $oTemplateConfiguration = new TemplateConfiguration;
            $oTemplateConfiguration->setToInherit();

            $oTemplateConfiguration->bJustCreated = true;
            $oTemplateConfiguration->isNewRecord = true;
            $oTemplateConfiguration->id = null;
            $oTemplateConfiguration->template_name = $xml->template_name->__toString();
            $oTemplateConfiguration->sid = $iSurveyId;

            if (isAssociativeArray((array)$xml->config->options)){
                $oTemplateConfiguration->options  = TemplateConfig::formatToJsonArray($xml->config->options);
            }

            if ($oTemplateConfiguration->save()){
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $sFieldPath
     */
    public static function getTemplateForXPath($oTemplate, $sFieldPath)
    {
        $oRTemplate = $oTemplate;
        while (!is_object($oRTemplate->config->$sFieldPath) || empty($oRTemplate->config->$sFieldPath)) {
            $sRTemplateName = (string) $oRTemplate->config->metadata->extends;

            if (!empty($sRTemplateName)) {
                $oRTemplate = Template::getTemplateConfiguration($sRTemplateName, null, null, true);
                if (!is_a($oRTemplate, 'TemplateManifest')) {
                    // Think about what to do..
                    throw new Exception("Error: Can't find a template for '$oRTemplate->sTemplateName' in xpath '$sFieldPath'.");
                }
            } else {
                throw new Exception("Error: Can't find a template for '$oRTemplate->sTemplateName' in xpath '$sFieldPath'.");
            }
        }

        return $oRTemplate;
    }

    /**
     * This will prepare an array for the field, so the json_encode will create
     * If a field is empty, its value should not be null, but an empty array for the json encoding in DB
     *
     * @param TemplateManifest $oTemplate
     * @param string $sFieldPath path to the field (under config)
     * @param string $sFieldName name of the field
     * @return array field value | empty array
     */
    public static function formatArrayFields($oTemplate, $sFieldPath, $sFieldName)
    {
        return (empty($oTemplate->config->$sFieldPath->$sFieldName->value) && empty($oTemplate->config->$sFieldPath->$sFieldName)) ? array() : $oTemplate->config->$sFieldPath->$sFieldName;
    }

    /**
     * Get the DOMDocument of the Manifest
     * @param  string      $sConfigPath path where to find the manifest
     * @return DOMDocument
     */
    public static function getManifestDOM($sConfigPath)
    {
        // First we get the XML file
        $oNewManifest = new DOMDocument();
        $oNewManifest->load($sConfigPath."/config.xml");
        return $oNewManifest;
    }


    /**
     * Change the name inside the DOMDocument (will not save it)
     * @param DOMDocument   $oNewManifest  The DOMDOcument of the manifest
     * @param string        $sName         The wanted name
     */
    public static function changeNameInDOM($oNewManifest, $sName)
    {
        $oConfig      = $oNewManifest->getElementsByTagName('config')->item(0);
        $ometadata = $oConfig->getElementsByTagName('metadata')->item(0);
        $oOldNameNode = $ometadata->getElementsByTagName('name')->item(0);
        $oNvNameNode  = $oNewManifest->createElement('name', $sName);
        $ometadata->replaceChild($oNvNameNode, $oOldNameNode);
    }

    /**
     * Change the date inside the DOMDocument
     * @param DOMDocument   $oNewManifest  The DOMDOcument of the manifest
     * @param string        $sDate         The wanted date, if empty the current date with config time adjustment will be used
     */
    public static function changeDateInDOM($oNewManifest, $sDate = '')
    {
        $date           = (empty($date)) ?dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust")) : $date;
        $oConfig        = $oNewManifest->getElementsByTagName('config')->item(0);
        $ometadata = $oConfig->getElementsByTagName('metadata')->item(0);
        $oOldDateNode   = $ometadata->getElementsByTagName('creationDate')->item(0);
        $oNvDateNode    = $oNewManifest->createElement('creationDate', $sDate);
        $ometadata->replaceChild($oNvDateNode, $oOldDateNode);
        $oOldUpdateNode = $ometadata->getElementsByTagName('last_update')->item(0);
        $oNvDateNode    = $oNewManifest->createElement('last_update', $sDate);
        $ometadata->replaceChild($oNvDateNode, $oOldUpdateNode);
    }

    /**
     * Change the template name inside the manifest (called from template editor)
     * NOTE: all tests (like template exist, etc) are done from template controller.
     *
     * @param string $sOldName The old name of the template
     * @param string $sNewName The newname of the template
     */
    public static function rename($sOldName, $sNewName)
    {
        libxml_disable_entity_loader(false);
        $sConfigPath = Yii::app()->getConfig('userthemerootdir')."/".$sNewName;
        $oNewManifest = self::getManifestDOM($sConfigPath);
        self::changeNameInDOM($oNewManifest, $sNewName);
        self::changeDateInDOM($oNewManifest);
        $oNewManifest->save($sConfigPath."/config.xml");
        libxml_disable_entity_loader(true);
    }

    /**
     * Delete files and engine node inside the DOM
     *
     * @param DOMDocument   $oNewManifest  The DOMDOcument of the manifest
     */
    public static function deleteEngineInDom($oNewManifest)
    {
        $oConfig            = $oNewManifest->getElementsByTagName('config')->item(0);

        // Then we delete the nodes that should be inherit
        $aNodesToDelete     = array();
        //$aNodesToDelete[]   = $oConfig->getElementsByTagName('files')->item(0);
        $aNodesToDelete[]   = $oConfig->getElementsByTagName('engine')->item(0);

        foreach ($aNodesToDelete as $node) {
            // If extended template already extend another template, it will not have those nodes
            if (is_a($node, 'DOMNode')) {
                $oConfig->removeChild($node);
            }
        }
    }

    /**
     * Change author inside the DOM
     *
     * @param DOMDocument   $oNewManifest  The DOMDOcument of the manifest
     */
    public static function changeAuthorInDom($oNewManifest)
    {
        $oConfig          = $oNewManifest->getElementsByTagName('config')->item(0);
        $ometadata = $oConfig->getElementsByTagName('metadata')->item(0);
        $oOldAuthorNode   = $ometadata->getElementsByTagName('author')->item(0);
        $oNvAuthorNode    = $oNewManifest->createElement('author', Yii::app()->user->name);
        $ometadata->replaceChild($oNvAuthorNode, $oOldAuthorNode);
    }

    /**
     * Change author email inside the DOM
     *
     * @param DOMDocument   $oNewManifest  The DOMDOcument of the manifest
     */
    public static function changeEmailInDom($oNewManifest)
    {
        $oConfig        = $oNewManifest->getElementsByTagName('config')->item(0);
        $ometadata = $oConfig->getElementsByTagName('metadata')->item(0);
        $oOldMailNode   = $ometadata->getElementsByTagName('authorEmail')->item(0);
        $oNvMailNode    = $oNewManifest->createElement('authorEmail', htmlspecialchars(Yii::app()->getConfig('siteadminemail')));
        $ometadata->replaceChild($oNvMailNode, $oOldMailNode);
    }

    /**
     * Change the extends node inside the DOM
     * If it doesn't exist, it will create it
     * @param DOMDocument   $oNewManifest  The DOMDOcument of the manifest
     * @param string        $sToExtends    Name of the template to extends
     */
    public static function changeExtendsInDom($oNewManifest, $sToExtends)
    {
        $oExtendsNode = $oNewManifest->createElement('extends', $sToExtends);
        $oConfig = $oNewManifest->getElementsByTagName('config')->item(0);
        $ometadata = $oConfig->getElementsByTagName('metadata')->item(0);

        // We test if mother template already extends another template
        if (!empty($ometadata->getElementsByTagName('extends')->item(0))) {
            $ometadata->replaceChild($oExtendsNode, $ometadata->getElementsByTagName('extends')->item(0));
        } else {
            $ometadata->appendChild($oExtendsNode);
        }
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
    public static function extendsConfig($sToExtends, $sNewName)
    {
        $sConfigPath = Yii::app()->getConfig('userthemerootdir')."/".$sNewName;

        // First we get the XML file
        libxml_disable_entity_loader(false);
        $oNewManifest = self::getManifestDOM($sConfigPath);

        self::deleteEngineInDom($oNewManifest);
        self::changeNameInDOM($oNewManifest, $sNewName);
        self::changeDateInDOM($oNewManifest);
        self::changeAuthorInDom($oNewManifest);
        self::changeEmailInDom($oNewManifest);
        self::changeExtendsInDom($oNewManifest, $sToExtends);

        $oNewManifest->save($sConfigPath."/config.xml");

        libxml_disable_entity_loader(true);
    }

    /**
     * Read the config.xml file of the template and push its contents to $this->config
     */
    private function readManifest()
    {
        $this->xmlFile = $this->path.'config.xml';

        if (file_exists(realpath($this->xmlFile))) {
            $bOldEntityLoaderState = libxml_disable_entity_loader(true); // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection
            $sXMLConfigFile        = file_get_contents(realpath($this->xmlFile)); // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string
            $oXMLConfig = simplexml_load_string($sXMLConfigFile);
            foreach ($oXMLConfig->config->xpath("//file") as $oFileName) {
                        $oFileName[0] = get_absolute_path($oFileName[0]);
            }

            $this->config = $oXMLConfig; // Using PHP >= 5.4 then no need to decode encode + need attributes : then other function if needed :https://secure.php.net/manual/en/book.simplexml.php#108688 for example
            libxml_disable_entity_loader($bOldEntityLoaderState); // Put back entity loader to its original state, to avoid contagion to other applications on the server
        } else {
            throw new Exception(" Error: Can't find a manifest for $this->sTemplateName in ' $this->path ' ");
        }
    }

    /**
     * Set the path of the current template
     * It checks if it's a core or a user template, if it exists, and if it has a config file
     */
    private function setPath()
    {
        // If the template is standard, its root is based on standardthemerootdir, else, it is a user template, its root is based on userthemerootdir
        $this->path = ($this->isStandard) ? Yii::app()->getConfig("standardthemerootdir").DIRECTORY_SEPARATOR.$this->sTemplateName.DIRECTORY_SEPARATOR : Yii::app()->getConfig("userthemerootdir").DIRECTORY_SEPARATOR.$this->sTemplateName.DIRECTORY_SEPARATOR;

        // If the template directory doesn't exist, we just set Default as the template to use
        // TODO: create a method "setToDefault"
        if (!is_dir($this->path)) {
            if (!$this->iSurveyId) {
                \SettingGlobal::setSetting('defaulttheme',Yii::app()->getConfig('defaultfixedtheme'));
                /* @todo ? : check if installed, install if not */
            }
            $this->sTemplateName = Yii::app()->getConfig('defaulttheme');
            if(Template::isStandardTemplate(Yii::app()->getConfig('defaulttheme'))) {
                $this->isStandard    = true;
                $this->path = Yii::app()->getConfig("standardthemerootdir").DIRECTORY_SEPARATOR.$this->sTemplateName.DIRECTORY_SEPARATOR;
            } else {
                $this->isStandard    = false;
                $this->path = Yii::app()->getConfig("userthemerootdir").DIRECTORY_SEPARATOR.$this->sTemplateName.DIRECTORY_SEPARATOR;
            }
        }

        // If the template doesn't have a config file (maybe it has been deleted, or whatever),
        // then, we load the default template
        $this->hasConfigFile = (string) is_file($this->path.'config.xml');
        if (!$this->hasConfigFile) {
            $this->path = Yii::app()->getConfig("standardthemerootdir").DIRECTORY_SEPARATOR.$this->sTemplateName.DIRECTORY_SEPARATOR;

        }
    }

    /**
     * Set the template name.
     * If no templateName provided, then a survey id should be given (it will then load the template related to the survey)
     *
     * @var     $sTemplateName  string the name of the template
     * @var     $iSurveyId      int    the id of the survey
     */
    private function setTemplateName($sTemplateName = '', $iSurveyId = '')
    {
        // If it is called from the template editor, a template name will be provided.
        // If it is called for survey taking, a survey id will be provided
        if ($sTemplateName == '' && $iSurveyId == '') {
            /* Some controller didn't test completely survey id (PrintAnswersController for example), then set to default here */
            $sTemplateName = App()->getConfig('defaulttheme');
        }

        $this->sTemplateName = $sTemplateName;
        $this->iSurveyId     = (int) $iSurveyId;

        if ($sTemplateName == '') {
            $oSurvey = Survey::model()->findByPk($iSurveyId);

            if ($oSurvey) {
                $this->sTemplateName = $oSurvey->template;
            } else {
                $this->sTemplateName = App()->getConfig('defaulttheme');
            }
        }
    }


    /**
     * Specific Integration of TemplateConfig.
     */


    public function setBasics($sTemplateName = '', $iSurveyId = '', $bUseMagicInherit = false)
    {
        // In manifest mode, we always use the default value from manifest, so no inheritance, no $bUseMagicInherit set needed
        $this->setTemplateName($sTemplateName, $iSurveyId); // Check and set template name
        $this->setIsStandard(); // Check if  it is a CORE template
        $this->setPath(); // Check and set path
        $this->readManifest(); // Check and read the manifest to set local params
    }

    /**
     * Get showpopups value from config or template configuration
     */
    public function getshowpopups(){
        $config = (int)Yii::app()->getConfig('showpopups');
        if ($config == 2){
            if (isset($this->oOptions->showpopups)){
                $this->showpopups = (int)$this->oOptions->showpopups;
            } else {
               $this->showpopups = 1;
           }
        } else {
            $this->showpopups = $config;
        }
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
        $oNewManifest->load($this->path."config.xml");

        $oConfig   = $oNewManifest->getElementsByTagName('config')->item(0);
        $oFiles    = $oNewManifest->getElementsByTagName('files')->item(0);
        $oOptions  = $oNewManifest->getElementsByTagName('options')->item(0); // Only for the insert before statement

        if (is_null($oFiles)) {
            $oFiles = $oNewManifest->createElement('files');
        }

        $oAssetType = $oFiles->getElementsByTagName($sType)->item(0);
        if (is_null($oAssetType)) {
            $oAssetType = $oNewManifest->createElement($sType);
            $oFiles->appendChild($oAssetType);
        }

        $oNewManifest->createElement('filename');

        $oAssetElem       = $oNewManifest->createElement('filename', $sFile);
        $replaceAttribute = $oNewManifest->createAttribute('replace');
        $replaceAttribute->value = $sFile;
        $oAssetElem->appendChild($replaceAttribute);
        $oAssetType->appendChild($oAssetElem);
        $oConfig->insertBefore($oFiles, $oOptions);
        $oNewManifest->save($this->path."config.xml");
        libxml_disable_entity_loader(true);
    }

    /**
     * From a list of json files in db it will generate a PHP array ready to use by removeFileFromPackage()
     *
     * @var $sType string js or css ?
     * @return array
     */
    protected function getFilesTo($oTemplate, $sType, $sAction)
    {
        $aFiles = array();
        $oRFilesTemplate = (!empty($bExtends)) ? self::getTemplateForXPath($oTemplate, 'files') : $oTemplate;

        if (isset($oRFilesTemplate->config->files->$sType->$sAction)) {
            $aFiles = (array) $oTemplate->config->files->$sType->$sAction;
        }

        return $aFiles;
    }


    /**
     * Proxy for Yii::app()->clientScript->removeFileFromPackage()
     * It's not realy needed here, but it is needed for TemplateConfiguration model.
     * So, we use it here to have the same interface for TemplateManifest and TemplateConfiguration,
     * So, in the future, we'll can both inherit them from a same object (best would be to extend CModel to create a LSYii_Template)
     *
     * @param string $sPackageName     string   name of the package to edit
     * @param $sType            string   the type of settings to change (css or js)
     * @param $aSettings        array    array of local setting
     * @return array
     */
    protected function removeFileFromPackage($sPackageName, $sType, $aSetting)
    {
        Yii::app()->clientScript->removeFileFromPackage($sPackageName, $sType, $aSetting);
    }

    /**
     * Configure the mother template (and its mother templates)
     * This is an object recursive call to TemplateManifest::prepareTemplateRendering()
     */
    protected function setMotherTemplates()
    {
        if (isset($this->config->metadata->extends)) {
            $sMotherTemplateName   = (string) $this->config->metadata->extends;
            if (!empty($sMotherTemplateName)){

                $instance= Template::getTemplateConfiguration($sMotherTemplateName, null, null, true);
                $instance->prepareTemplateRendering($sMotherTemplateName);
                $this->oMotherTemplate = $instance; // $instance->prepareTemplateRendering($sMotherTemplateName, null);
            }

        }
    }

    /**
     * @param TemplateManifest $oRTemplate
     * @param string $sPath
     */
    protected function getTemplateForPath($oRTemplate, $sPath)
    {
        while (empty($oRTemplate->config->xpath($sPath))) {
            $oMotherTemplate = $oRTemplate->oMotherTemplate;
            if (!($oMotherTemplate instanceof TemplateConfiguration)) {
                throw new Exception("Error: Can't find a template for '$oRTemplate->sTemplateName' in xpath '$sPath'.");
            }
            $oRTemplate = $oMotherTemplate;
        }
        return $oRTemplate;
    }

    /**
     * Set the default configuration values for the template, and use the motherTemplate value if needed
     */
    protected function setThisTemplate()
    {
        // Mandtory setting in config XML (can be not set in inheritance tree, but must be set in mother template (void value is still a setting))
        $this->apiVersion         = (isset($this->config->metadata->apiVersion)) ? $this->config->metadata->apiVersion : null;


        $this->viewPath           = $this->path.$this->getTemplateForPath($this, '//viewdirectory')->config->engine->viewdirectory.DIRECTORY_SEPARATOR;
        $this->filesPath          = $this->path.$this->getTemplateForPath($this, '//filesdirectory')->config->engine->filesdirectory.DIRECTORY_SEPARATOR;
        $this->templateEditor     = $this->getTemplateForPath($this, '//template_editor')->config->engine->template_editor;

        // Options are optional
        if (!empty($this->config->xpath("//options"))) {
            $aOptions = $this->config->xpath("//options");
            $this->oOptions = $aOptions[0];
        } elseif (!empty($this->oMotherTemplate->oOptions)) {
            $this->oOptions = $this->oMotherTemplate->oOptions;
        } else {
            $this->oOptions = "";
        }

        // Not mandatory (use package dependances)
        $this->cssFramework             = (!empty($this->config->xpath("//cssframework"))) ? $this->config->engine->cssframework : '';
        // Add depend package according to packages
        $this->depends                  = array_merge($this->depends, $this->getDependsPackages($this));

        //Add extra packages from xml
        $this->packages                 = array();
        $packageActionFromEngineSection = json_decode(json_encode($this->config->engine->packages));
        if (!empty($packageActionFromEngineSection)) {
            if (!empty($packageActionFromEngineSection->add)) {
                $this->packages = array_merge(
                    !is_array($packageActionFromEngineSection->add) ? [$packageActionFromEngineSection->add] : $packageActionFromEngineSection->add,
                    $this->packages
                );
            }
            if (!empty($packageActionFromEngineSection->remove)) {
                $this->packages =  array_diff($this->packages, $packageActionFromEngineSection->remove);
            }
        }
        $this->depends = array_merge($this->depends, $this->packages);
    }


    protected function addMotherTemplatePackage($packages)
    {
        if (isset($this->config->metadata->extends)) {
            $sMotherTemplateName = (string) $this->config->metadata->extends;
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
    protected function getFrameworkAssetsToReplace($sType, $bInlcudeRemove = false)
    {
        $aAssetsToRemove = array();
        if (!empty($this->cssFramework->$sType) && !empty($this->cssFramework->$sType->attributes()->replace)) {
            $aAssetsToRemove = (array) $this->cssFramework->$sType->attributes()->replace;
            if ($bInlcudeRemove) {
                $aAssetsToRemove = array_merge($aAssetsToRemove, (array) $this->cssFramework->$sType->attributes()->remove);
            }
        }
        return $aAssetsToRemove;
    }



    /**
     * Get the list of file replacement from Engine Framework
     * @param string  $sType            css|js the type of file
     * @param boolean $bInlcudeRemove   also get the files to remove
     * @return stdClass
     */
    static public function getAssetsToReplaceFormated($oEngine, $sType, $bInlcudeRemove = false)
    {
        $oAssetsToReplaceFormated = new stdClass();
        if (!empty($oEngine->cssframework->$sType) && !empty($oEngine->cssframework->$sType->attributes()->replace)) {
            //var_dump($oEngine->cssframework->$sType);  die();

            $sAssetsToReplace   = (string) $oEngine->cssframework->$sType->attributes()->replace;
            $sAssetsReplacement = (string) $oEngine->cssframework->$sType;

            // {"replace":[["css/bootstrap.css","css/cerulean.css"]]}
            $oAssetsToReplaceFormated->replace = array(array($sAssetsToReplace, $sAssetsReplacement));

        }
        return $oAssetsToReplaceFormated;
    }

    /**
     * Get the list of file replacement from Engine Framework
     * @param string  $sType            css|js the type of file
     * @return array
     */
    protected function getFrameworkAssetsReplacement($sType)
    {
        $aAssetsToRemove = array();
        if (!empty($this->cssFramework->$sType)) {
            $nodes = (array) $this->config->xpath('//cssframework/'.$sType.'[@replace]');
            if (!empty($nodes)) {
                foreach ($nodes as $key => $node) {
                    $nodes[$key] = (string) $node[0];
                }

                $aAssetsToRemove = $nodes;
            }
        }
        return $aAssetsToRemove;
    }

    /**
     * @return string
     */
    public function getTemplateAndMotherNames()
    {
        $oRTemplate = $this;
        $sTemplateNames = $this->sTemplateName;

        while (!empty($oRTemplate->oMotherTemplate)) {

            $sTemplateNames .= ' ' . $oRTemplate->config->metadata->extends;
            $oRTemplate      = $oRTemplate->oMotherTemplate;
            if (!($oRTemplate instanceof TemplateConfiguration)) {
                // Throw alert: should not happen
                break;
            }
        }

        return $sTemplateNames;
    }
}
