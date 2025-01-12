<?php

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
 */

/**
 * Class TemplateConfig
 * Common methods for TemplateConfiguration and TemplateManifest
 *
 */
class TemplateConfig extends CActiveRecord
{
    /** @var string $sTemplateName The template name */
    public $sTemplateName = '';

    /** @var string $sPackageName Name of the asset package of this template*/
    public $sPackageName;

    /** @var  string $path Path of this template */
    public $path;

    /** @var string $sTemplateurl Url to reach the framework */
    public $sTemplateurl;

    /** @var  string $viewPath Path of the views files (twig template) */
    public $viewPath;

    /** @var  string $filesPath Path of the tmeplate's files */
    public $filesPath;

    /** @var object $cssFramework What framework css is used */
    public $cssFramework;

    /** @var boolean $isStandard Is this template a core one? */
    public $isStandard;

    /** @var SimpleXMLElement $config Will contain the config.xml */
    public $config;

    /**
     * @var TemplateConfiguration $oMotherTemplate The mother template object
     * This is used when a template inherit another one.
     */
    public $oMotherTemplate;

    /** @var object $oOptions The template options */
    public $oOptions;
    public $oOptionAttributes;


    /** @var string[] $depends List of all dependencies (could be more that just the config.xml packages) */
    protected $depends = array();

    /**  @var integer $apiVersion: Version of the LS API when created. Must be private : disallow update */
    protected $apiVersion;

    /**
     * @var int? $iSurveyId The current Survey Id. It can be void. It's use only to retrieve
     * the current template of a given survey
     */
    protected $iSurveyId = '';

    /** @var string $hasConfigFile Does it has a config.xml file? */
    protected $hasConfigFile = ''; //

    /** @var string[] $packages Array of package dependencies defined in config.xml*/
    protected $packages;

    /** @var string $xmlFile What xml config file does it use? (config/minimal) */
    protected $xmlFile;

    /** @var array $aCssFrameworkReplacement Css Framework Replacement */
    protected $aCssFrameworkReplacement;

    public $options_page = 'core';

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
     * @return string template url
     */
    public function getTemplateURL()
    {
        if (!isset($this->sTemplateurl)) {
            $this->sTemplateurl = Template::getTemplateURL($this->sTemplateName);
        }
        return $this->sTemplateurl;
    }


    /**
     * Remove the css/js files defined in theme config, from any package (even the core ones)
     * The file should have the exact same name as in the package
     * (see: application/config/packages.php and application/config/vendor.php)
     * eg: to remove awesome-bootstrap-checkbox.css, in the theme config
     * file add <remove>awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css</remove>
     */
    public function removeFiles()
    {
        $aCssFilesToRemove = $this->getFilesTo($this, "css", 'remove');
        $aJsFilesToRemove  = $this->getFilesTo($this, "js", 'remove');

        if (!(empty($aCssFilesToRemove) && empty($aJsFilesToRemove))) {
            $aPackages = App()->clientScript->packages;

            foreach ($aPackages as $sPackageName => $aPackage) {
                $this->removeFilesFromPackage($sPackageName, $aPackage, 'css', $aCssFilesToRemove);
                $this->removeFilesFromPackage($sPackageName, $aPackage, 'js', $aJsFilesToRemove);
            }
        }
    }

    /**
     * Checks if some files are inside a package, and remove them.
     * @param string $sPackageName   name of the package
     * @param array  $aPackage       the package to check (as provided by Yii::app()->clientScript)
     * @param string $sType          the type of file (css or js)
     * @param array $aFilesToRemove  an array containing the files to chech and remove
     */
    protected function removeFilesFromPackage($sPackageName, $aPackage, $sType, $aFilesToRemove)
    {
        if (!empty($aPackage[$sType])) {
            if (!empty($aFilesToRemove)) {
                foreach ($aFilesToRemove as $sFileToRemove) {
                    if (array_search($sFileToRemove, $aPackage[$sType]) !== false) {
                        App()->clientScript->removeFileFromPackage($sPackageName, $sType, $sFileToRemove);
                    }
                }
            }
        }
    }

    /**
     * Get the template for a given file. It checks if a file exist in the current
     * template or in one of its mother templates
     * Can return a 302 redirect (this is not really a throw …
     *
     * @param  string $sFile the  file to look for (must contain relative path, unless it's a view file)
     * @param TemplateConfig $oRTemplate template from which the recurrence should start
     * @param boolean $force file to be in template or mother template
     * @return TemplateConfig
     */
    public function getTemplateForFile($sFile, $oRTemplate, $force = false)
    {
        while (
            !file_exists($oRTemplate->path . $sFile) &&
            !file_exists($oRTemplate->viewPath . $sFile) &&
            !file_exists($oRTemplate->filesPath . $sFile)
        ) {
            $oMotherTemplate = $oRTemplate->oMotherTemplate;
            if (!($oMotherTemplate instanceof TemplateConfiguration)) {
                if (!$force && App()->twigRenderer->getPathOfFile($sFile)) {
                    // return dummy template , new self broke (No DB : TODO : must fix init of self)
                    $templateConfig = new TemplateConfig();
                    $templateConfig->sTemplateName = null;
                    return $templateConfig;
                }
                App()->setFlashMessage(
                    sprintf(
                        gT("Theme '%s' was not found, can't find file: $sFile "),
                        $this->sTemplateName
                    ),
                    'error'
                );
                App()->getController()->redirect(array("themeOptions/index"));
                break;
            }
            $oRTemplate = $oMotherTemplate;
        }

        return $oRTemplate;
    }

    /**
     * Get the file path for a given template.
     * It will check if css/js (relative to path), or view (view path)
     * It will search for current template and mother templates
     *
     * @param   string $sFile relative path to the file
     * @param   TemplateConfig $oTemplate the template where to look for (and its mother templates)
     * @return string|false
     */
    protected function getFilePath($sFile, $oTemplate)
    {
        // Remove relative path
        $sFile = trim($sFile, '.');
        $sFile = trim($sFile, '/');

        // Retrieve the correct template for this file (can be a mother template)
        $oTemplate = $this->getTemplateForFile($sFile, $oTemplate, false);

        if ($oTemplate instanceof TemplateConfiguration) {
            if (file_exists($oTemplate->path . $sFile)) {
                return $oTemplate->path . $sFile;
            } elseif (file_exists($oTemplate->viewPath . $sFile)) {
                return $oTemplate->viewPath . $sFile;
            }
        }
        $sExtension = substr(strrchr($sFile, '.'), 1);
        if ($sExtension === 'twig') {
            return App()->twigRenderer->getPathOfFile($sFile);
        }
        return false;
    }

    /**
     * Get the depends package
     * @uses self::@package
     * TODO: unused variable
     * @param TemplateConfiguration $oTemplate
     * @return string[]
     */
    protected function getDependsPackages($oTemplate)
    {
        $dir = (getLanguageRTL(App()->getLanguage())) ? 'rtl' : 'ltr';

        /* Core package */
        $packages[] = 'limesurvey-public';
        $packages[] = 'template-core';
        $packages[] = ($dir === "ltr") ? 'template-core-ltr' : 'template-core-rtl'; // Awesome Bootstrap Checkboxes

        /* bootstrap */
        if (!empty($this->cssFramework)) {
            // Basic bootstrap package
            if ((string) $this->cssFramework->name === "bootstrap") {
                $packages[] = 'bootstrap';
            }

            // Rtl version of bootstrap
            if ($dir === "rtl" && (string)$this->cssFramework->name === "bootstrap") {
                $packages[] = 'bootstrap-rtl';
            }

            // Remove unwanted bootstrap stuff
            foreach ($this->getFrameworkAssetsToReplace('css', true) as $toReplace) {
                App()->clientScript->removeFileFromPackage('bootstrap', 'css', $toReplace);
            }

            foreach ($this->getFrameworkAssetsToReplace('js', true) as $toReplace) {
                App()->clientScript->removeFileFromPackage('bootstrap', 'js', $toReplace);
            }
        }

        // Moter Template Package
        $packages = $this->addMotherTemplatePackage($packages);

        return $packages;
    }

    // For list, so no "setConfiguration" before

    /**
     * @param string|null $sCustomMessage
     * @throws CException
     * @todo document me
     */
    public function throwConsoleError($sCustomMessage = null)
    {
        $sMessage = "\\n";
        $sMessage .= "\\n";
        $sMessage .= " (¯`·._.·(¯`·._.· Theme Configuration Error  ·._.·´¯)·._.·´¯) \\n";
        $sMessage .= "\\n";

        if ($sCustomMessage == null) {
            $sMessage .= "\\n unknown error";
        } else {
            $sMessage .= $sCustomMessage;
        }

        App()->clientScript->registerScript('error_' . $this->sTemplateName, "throw Error(\"$sMessage\");");
    }


    /**
     * Check if this template is a standard template and save it in current model $this->isStandard
     * @return void
     * @throws CException
     */
    protected function setIsStandard()
    {
        Yii::import('application.helpers.SurveyThemeHelper');
        $this->isStandard = SurveyThemeHelper::isStandardTemplate($this->sTemplateName);
    }


    /**
     * Core config and attributes
     *
     * Most classes and id and attributes from template views are defined here.
     * So even if users extends/modify the core template, we can still apply some debugs
     *
     * NB 1: Some of the classes should be bring back to templates
     *
     * NB 2: This is a temporary function. Before releasing to master, it will be replaced by a XML file inside the
     * template itself
     * So third party providers will also be able to use this mechanics to provides bug fixes/enhancement to their
     * templates
     */
    public function getClassAndAttributes()
    {
        $aClassAndAttributes = array();

        // Welcome
        $aClassAndAttributes['id']['welcomecontainer']     = 'welcome-container';
        $aClassAndAttributes['class']['welcomecontainer']  = '';
        $aClassAndAttributes['class']['surveyname']        = " survey-name ";
        $aClassAndAttributes['class']['description']       = " survey-description ";
        $aClassAndAttributes['class']['welcome']           = " survey-welcome ";
        $aClassAndAttributes['class']['questioncount']     = " number-of-questions  ";
        $aClassAndAttributes['class']['questioncounttext'] = " question-count-text ";

        $aClassAndAttributes['attr']['questioncounttext'] = '';

        // Global
        $aClassAndAttributes['id']['outerframe']    = 'outerframeContainer';
        $aClassAndAttributes['id']['mainrow']       = 'main-row';
        $aClassAndAttributes['id']['maincol']       = 'main-col';
        $aClassAndAttributes['id']['dynamicreload'] = 'dynamicReloadContainer';

        $aClassAndAttributes['class']['html']  = ' no-js ';

        $aClassAndAttributes['class']['body']  = $this->getTemplateAndMotherNames();

        if (!empty($this->aCssFrameworkReplacement)) {
            $aVariationFile = explode('/', (string) $this->aCssFrameworkReplacement[0]);
            $aVariationFile = explode('.', end($aVariationFile));
            $sVariationName = $aVariationFile[0];
            $aClassAndAttributes['class']['body']  .= ' ' . $sVariationName;
        }

        $aClassAndAttributes['class']['outerframe'] = ' outerframe ';
        $aClassAndAttributes['class']['maincol'] = ' ';
        $aClassAndAttributes['attr']['html'] = $thissurvey['attr']['body'] = $aClassAndAttributes['attr']['outerframe'] = $thissurvey['attr']['mainrow'] = $thissurvey['attr']['maincol'] = '';

        // User forms
        $aClassAndAttributes['class']['maincoldivdiva']               = '  ';
        $aClassAndAttributes['class']['maincoldivdivb']               = '  ';
        $aClassAndAttributes['class']['maincoldivdivbp']              = '  ';
        $aClassAndAttributes['class']['maincoldivdivbul']             = '  ';
        $aClassAndAttributes['class']['maincoldivdivbdiv']            = ' ';
        $aClassAndAttributes['class']['maincolform']                  = '  ';
        $aClassAndAttributes['class']['maincolformlabel']             = '  ';
        $aClassAndAttributes['class']['maincolformlabelsmall']        = ' superset ';
        $aClassAndAttributes['class']['maincolformlabelspan']         = ' ';
        $aClassAndAttributes['class']['maincolformdiva']              = '  load-survey-input input-cell ';
        $aClassAndAttributes['class']['maincolformdivainput']         = '  ';
        $aClassAndAttributes['class']['maincolformdivb']              = ' captcha-item ';
        $aClassAndAttributes['class']['maincolformdivblabel']         = '  ';
        $aClassAndAttributes['class']['maincolformdivblabelsmall']    = '  ';
        $aClassAndAttributes['class']['maincolformdivblabelspan']     = '  ';
        $aClassAndAttributes['class']['maincolformdivbdiv']           = '  ';
        $aClassAndAttributes['class']['maincolformdivbdivdiv']        = ' ls-input-group ';
        $aClassAndAttributes['class']['maincolformdivbdivdivdiv']     = ' ls-input-group-extra captcha-widget ';
        $aClassAndAttributes['class']['maincolformdivbdivdivinput']   = '  ';
        $aClassAndAttributes['class']['maincolformdivc']              = ' load-survey-row load-survey-submit ';
        $aClassAndAttributes['class']['maincolformdivcdiv']           = ' load-survey-input input-cell ';
        $aClassAndAttributes['class']['maincolformdivcdivbutton']     = '  ';
        $aClassAndAttributes['class']['maincolformdivd']              = '  ';
        $aClassAndAttributes['class']['maincolformdivddiv']           = '  captcha-item ';
        $aClassAndAttributes['class']['maincolformdivddivlabel']      = '  ';
        $aClassAndAttributes['class']['maincolformdivddivcol']        = '  ';
        $aClassAndAttributes['class']['maincolformdivddivcoldiv']     = ' ls-input-group ';
        $aClassAndAttributes['class']['maincolformdivddivcoldivdiv']  = ' ls-input-group-extra captcha-widget ';
        $aClassAndAttributes['class']['maincolformdivddivcolinput']   = '  ';
        $aClassAndAttributes['class']['maincolformdivddivb']          = ' load-survey-row load-survey-submit ';
        $aClassAndAttributes['class']['maincolformdivddivbdiv']       = ' load-survey-input input-cell ';
        $aClassAndAttributes['class']['maincolformdivddivbdivbutton'] = '  ';


        $aClassAndAttributes['attr']['maincolformdivainput']          = ' type="password" id="token" name="token" value="" required ';
        $aClassAndAttributes['attr']['maincoldivdivbul']              = ' role="alert" ';
        $aClassAndAttributes['attr']['maincolformlabel']              = ' for="token"';
        $aClassAndAttributes['attr']['maincolformlabelsmall']         = ' aria-hidden="true" ';
        $aClassAndAttributes['attr']['maincolformdivblabel']          = ' for="loadsecurity" ';
        $aClassAndAttributes['attr']['maincolformdivblabelsmall']     = ' aria-hidden="true" ';
        $aClassAndAttributes['attr']['maincolformdivbdivdivinput']    = ' type="text" size="15" maxlength="15" id="loadsecurity" name="loadsecurity" value="" alt="" required ';
        $aClassAndAttributes['attr']['maincolformdivcdivbutton']      = ' type="submit" id="default" name="continue"  value="continue" ';
        $aClassAndAttributes['attr']['maincolformdivddivlabel']       = ' for="loadsecurity" ';
        $aClassAndAttributes['attr']['maincolformdivddivcolinput']    = ' type="text" size="15" maxlength="15" id="loadsecurity" name="loadsecurity" value="" alt="" required ';
        $aClassAndAttributes['attr']['maincolformdivddivbdivbutton'] = ' type="submit" id="default" name="continue" value="continue" ';

        $aClassAndAttributes['attr']['maincol'] = $aClassAndAttributes['attr']['maincoldiva'] = $aClassAndAttributes['attr']['maincoldivdivb'] = $aClassAndAttributes['attr']['maincoldivdivbp'] = $aClassAndAttributes['attr']['maincoldivdivbdiv'] = $aClassAndAttributes['attr']['maincolform'] = '';
        $aClassAndAttributes['attr']['maincolformlabelspan'] = $aClassAndAttributes['attr']['maincolformdiva'] = $aClassAndAttributes['attr']['maincolformdivb'] = $aClassAndAttributes['attr']['maincolformdivbdiv'] = $aClassAndAttributes['class']['maincolformdivbdivdivdiv'] = $aClassAndAttributes['attr']['maincolformdivcdiv'] = ' ';
        $aClassAndAttributes['attr']['maincolformdivd'] = $aClassAndAttributes['attr']['maincolformdivddiv'] = $aClassAndAttributes['attr']['maincolformdivddivcol'] = $aClassAndAttributes['attr']['maincolformdivddivcoldiv'] = $aClassAndAttributes['attr']['maincolformdivddivb'] = '';

        // Clear all
        $aClassAndAttributes['class']['clearall']    = 'return-to-survey';
        $aClassAndAttributes['class']['clearalldiv'] = ' url-wrapper url-wrapper-survey-return ';
        $aClassAndAttributes['class']['clearalla']   = ' ls-return ';
        $aClassAndAttributes['attr']['clearall'] = $thissurvey['attr']['clearalldiv'] = $thissurvey['attr']['clearalla'] = '';

        // Load
        $aClassAndAttributes['id']['saveformrowcolinput']    = 'loadname';
        $aClassAndAttributes['id']['captcharowcoldivinput']  = 'loadsecurity';

        $aClassAndAttributes['class']['savemessage']           = ' save-message ';
        $aClassAndAttributes['class']['savemessagetitle']      = '  ';
        $aClassAndAttributes['class']['savemessagetext']       = '  ';
        $aClassAndAttributes['class']['loadform']              = ' load-form ';
        $aClassAndAttributes['class']['loadformul']            = ' ';
        $aClassAndAttributes['class']['loadformform']          = ' ls-form form form-horizontal ';
        $aClassAndAttributes['class']['saveform']              = ' save-survey-form ';
        $aClassAndAttributes['class']['saveformrow']           = ' save-survey-row save-survey-name ';
        $aClassAndAttributes['class']['saveformrowlabel']      = ' load-survey-label ';
        $aClassAndAttributes['class']['saveformrowlabelsmall'] = '  ';
        $aClassAndAttributes['class']['saveformrowlabelspan']  = '  ';
        $aClassAndAttributes['class']['saveformrowcol']        = '  save-survey-input  ';
        $aClassAndAttributes['class']['saveformrowcolinput']   = '  ';
        $aClassAndAttributes['class']['passwordrow']           = ' load-survey-row load-survey-password ';
        $aClassAndAttributes['class']['passwordrowcol']        = ' load-survey-label label-cell ';
        $aClassAndAttributes['class']['passwordrowcolsmall']   = '  ';
        $aClassAndAttributes['class']['passwordrowcolspan']    = '  ';
        $aClassAndAttributes['class']['passwordrowinput']      = ' save-survey-input input-cell ';

        $aClassAndAttributes['class']['captcharow']            = ' save-survey-row save-survey-captcha ';
        $aClassAndAttributes['class']['captcharowlabel']       = ' save-survey-label label-cell ';
        $aClassAndAttributes['class']['captcharowcol']         = ' save-survey-input input-cell ';
        $aClassAndAttributes['class']['captcharowcoldiv']      = ' input-group ';
        $aClassAndAttributes['class']['captcharowcoldivdiv']   = ' input-group-text captcha-image ';
        $aClassAndAttributes['class']['captcharowcoldivinput'] = '  ';
        $aClassAndAttributes['class']['loadrow']               = ' save-survey-row save-survey-submit ';
        $aClassAndAttributes['class']['loadrowcol']            = ' save-survey-input input-cell ';
        $aClassAndAttributes['class']['loadrowcolbutton']      = '  ';
        $aClassAndAttributes['class']['returntosurvey']        = ' return-to-survey ';
        $aClassAndAttributes['class']['returntosurveydiv']     = ' url-wrapper url-wrapper-survey-return ';
        $aClassAndAttributes['class']['returntosurveydiva']    = ' ls-return ';

        $aClassAndAttributes['attr']['loadformul']             = ' role="alert"';
        $aClassAndAttributes['attr']['saveformrowlabel']       = ' for="savename" ';
        $aClassAndAttributes['attr']['saveformrowlabelsmall']  = ' aria-hidden="true" ';
        $aClassAndAttributes['attr']['saveformrowcolinput']    = ' type="text"  name="loadname" value="" required ';
        $aClassAndAttributes['attr']['passwordrowinputi']      = ' type="password" id="loadpass" name="loadpass" value="" required ';

        $aClassAndAttributes['attr']['passwordrowcol']         = ' for="loadpass" ';
        $aClassAndAttributes['attr']['passwordrowcolsmall']    = ' aria-hidden="true"';
        $aClassAndAttributes['attr']['captcharowcoldivdivimg'] = ' alt="captcha" ';
        $aClassAndAttributes['attr']['captcharowcoldivinput']  = '  type="text" size="5" maxlength="3" id="loadsecurity" name="loadsecurity" value="" alt="" ';
        $aClassAndAttributes['attr']['loadrowcolbutton']       = '  type="submit" id="loadbutton" name="loadall"  value="reload" ';

        $aClassAndAttributes['attr']['savemessage'] = $aClassAndAttributes['attr']['savemessagetext'] = $aClassAndAttributes['attr']['savemessagetitle'] = $aClassAndAttributes['attr']['loadform'] = $aClassAndAttributes['attr']['savemessagetextp'] = $aClassAndAttributes['attr']['savemessagetextpb'] = '';
        $aClassAndAttributes['attr']['loadformulli'] = $aClassAndAttributes['attr']['saveform'] = $aClassAndAttributes['attr']['saveformrow'] = $aClassAndAttributes['attr']['saveformrowlabelspan'] = $aClassAndAttributes['attr']['saveformrowcol'] = $aClassAndAttributes['attr']['passwordrow'] = '';
        $aClassAndAttributes['attr']['passwordrowcolspan'] = $aClassAndAttributes['attr']['captcharow'] = $aClassAndAttributes['attr']['captcharowlabel'] = $aClassAndAttributes['attr']['captcharowcol'] = $aClassAndAttributes['attr']['captcharowcoldiv'] = $aClassAndAttributes['attr']['loadrow'] = '';
        $aClassAndAttributes['attr']['loadrowcol'] = $aClassAndAttributes['class']['returntosurvey'] = $aClassAndAttributes['attr']['returntosurveydiv'] = $aClassAndAttributes['class']['returntosurveydiva'] = '';

        //Ã‚Â Save
        $aClassAndAttributes['class']['savecontainer']                 = ' save-message ';
        $aClassAndAttributes['class']['savecontainertitle']            = '  ';
        $aClassAndAttributes['class']['savecontainertext']             = '  ';
        $aClassAndAttributes['class']['savecontainertextpc']           = ' info-email-optional ls-info ';
        $aClassAndAttributes['class']['savecontainerwarning']          = '  ';
        $aClassAndAttributes['class']['saveformcontainer']             = ' save-form ';
        $aClassAndAttributes['class']['saveformcontainerul']           = ' ';
        $aClassAndAttributes['class']['saveformsurvey']                = ' save-survey-form  ';
        $aClassAndAttributes['class']['saveformsurveydiva']            = ' save-survey-row save-survey-name ';
        $aClassAndAttributes['class']['saveformsurveydivalabel']       = '  save-survey-label ';
        $aClassAndAttributes['class']['saveformsurveydivalabelsmall']  = ' ';
        $aClassAndAttributes['class']['saveformsurveydivalabelspan']   = '  ';
        $aClassAndAttributes['class']['saveformsurveydivb']            = ' save-survey-input input-cell ';
        $aClassAndAttributes['class']['saveformsurveydivc']            = '  save-survey-row save-survey-password ';
        $aClassAndAttributes['class']['saveformsurveydivclabel']       = ' save-survey-label label-cell ';
        $aClassAndAttributes['class']['saveformsurveydivcsmall']       = '  ';
        $aClassAndAttributes['class']['saveformsurveydivcspan']        = '  ';
        $aClassAndAttributes['class']['saveformsurveydivcdiv']         = ' save-survey-input input-cell ';
        $aClassAndAttributes['class']['saveformsurveydivd']            = ' save-survey-row save-survey-password ';
        $aClassAndAttributes['class']['saveformsurveydivdlabel']       = ' save-survey-label label-cell ';
        $aClassAndAttributes['class']['saveformsurveydivdlabelsmall']  = ' ';
        $aClassAndAttributes['class']['saveformsurveydivdlabelspan']   = '  ';
        $aClassAndAttributes['class']['saveformsurveydivddiv']         = ' save-survey-input input-cell ';
        $aClassAndAttributes['class']['saveformsurveydive']            = ' save-survey-row save-survey-password ';
        $aClassAndAttributes['class']['saveformsurveydivelabel']       = ' save-survey-label label-cell ';
        $aClassAndAttributes['class']['saveformsurveydivediv']         = ' save-survey-input input-cell ';
        $aClassAndAttributes['class']['saveformsurveydivf']            = ' save-survey-row save-survey-captcha ';
        $aClassAndAttributes['class']['saveformsurveydivflabel']       = ' save-survey-label label-cell ';
        $aClassAndAttributes['class']['saveformsurveydivfdiv']         = ' save-survey-input input-cell ';
        $aClassAndAttributes['class']['saveformsurveydivfdivdiv']      = '  ';
        $aClassAndAttributes['class']['saveformsurveydivfdivdivdiv']   = ' input-group-text captcha-image ';
        $aClassAndAttributes['class']['saveformsurveydivfdivdivinput'] = ' ';
        $aClassAndAttributes['class']['saveformsurveydivg']            = '  save-survey-row save-survey-submit ';
        $aClassAndAttributes['class']['saveformsurveydivgdiv']         = ' save-survey-input input-cell ';
        $aClassAndAttributes['class']['saveformsurveydivgdivbutton']   = '  ';
        $aClassAndAttributes['class']['saveformsurveydivh']            = ' return-to-survey ';
        $aClassAndAttributes['class']['saveformsurveydivhdiv']         = ' url-wrapper url-wrapper-survey-return ';
        $aClassAndAttributes['class']['saveformsurveydivhdiva']        = ' ls-return ';

        $aClassAndAttributes['attr']['saveformcontainerul']            = ' role="alert" ';
        $aClassAndAttributes['attr']['saveformsurveydivalabel']        = ' for="savename" ';
        $aClassAndAttributes['attr']['saveformsurveydivalabelsmall']   = ' aria-hidden="true" ';
        $aClassAndAttributes['attr']['saveformsurveydivclabel']        = ' for="savepass" ';
        $aClassAndAttributes['attr']['saveformsurveydivcsmall']        = ' aria-hidden="true" ';
        $aClassAndAttributes['attr']['saveformsurveydivdlabel']        = ' for="savepass2" ';
        $aClassAndAttributes['attr']['saveformsurveydivdlabelsmall']   = ' aria-hidden="true" ';
        $aClassAndAttributes['attr']['saveformsurveydivelabel']        = ' for="saveemail" ';
        $aClassAndAttributes['attr']['saveformsurveydivflabel']        = ' for="loadsecurity" ';
        $aClassAndAttributes['attr']['saveformsurveydivfdivdivdivimg'] = ' alt="captcha" ';
        $aClassAndAttributes['attr']['saveformsurveydivfdivdivinput']  = ' type="text" size="5" maxlength="3" id="loadsecurity" name="loadsecurity" value="" alt="" ';
        $aClassAndAttributes['attr']['saveformsurveydivgdivbutton']    = ' type="submit" id="savebutton" name="savesubmit" value="save"';

        $aClassAndAttributes['attr']['savecontainer'] = $aClassAndAttributes['attr']['savecontainertitle'] = $aClassAndAttributes['attr']['savecontainertext'] = $aClassAndAttributes['attr']['savecontainerwarning'] = $aClassAndAttributes['attr']['saveformcontainer'] = $aClassAndAttributes['attr']['saveformcontainerli'] = '';
        $aClassAndAttributes['attr']['savecontainertextpa'] = $aClassAndAttributes['attr']['savecontainertextpb'] = $aClassAndAttributes['attr']['savecontainertextpc'] = $aClassAndAttributes['attr']['savecontainertextpd'] = $aClassAndAttributes['attr']['saveformsurveydiva'] = $aClassAndAttributes['attr']['saveformsurveydivalabelspan'] = '';
        $aClassAndAttributes['attr']['saveformsurveydivc'] = $aClassAndAttributes['attr']['saveformsurveydivcspan'] = $aClassAndAttributes['attr']['saveformsurveydivcdiv'] = $aClassAndAttributes['attr']['saveformsurveydivd'] = $aClassAndAttributes['attr']['saveformsurveydivdlabelspan'] = $aClassAndAttributes['attr']['saveformsurveydivddiv'] = '';
        $aClassAndAttributes['attr']['saveformsurveydive'] = $aClassAndAttributes['attr']['saveformsurveydivediv'] = $aClassAndAttributes['attr']['saveformsurveydivf'] = $aClassAndAttributes['attr']['saveformsurveydivfdiv'] = $aClassAndAttributes['attr']['saveformsurveydivfdivdiv'] = $aClassAndAttributes['attr']['saveformsurveydivfdivdivdiv'] = '';
        $aClassAndAttributes['attr']['saveformsurveydivgdiv'] = $aClassAndAttributes['attr']['saveformsurveydivh'] = $aClassAndAttributes['attr']['saveformsurveydivhdiv'] = '';

        // Completed
        $aClassAndAttributes['id']['navigator'] = 'navigator-container';

        $aClassAndAttributes['class']['completedwrapper']     = ' completed-wrapper ';
        $aClassAndAttributes['class']['completedtext']        = ' completed-text ';
        $aClassAndAttributes['class']['quotamessage']         = ' quotamessage limesurveycore text-center ';
        $aClassAndAttributes['class']['navigator']            = ' navigator ';
        $aClassAndAttributes['class']['navigatorcoll']        = '  ';
        $aClassAndAttributes['class']['navigatorcollbutton']  = ' ls-move-btn ls-move-previous-btn action--ls-button-previous';
        $aClassAndAttributes['class']['navigatorcolr']        = '  ';
        $aClassAndAttributes['class']['navigatorcolrbutton']  = ' ls-move-btn ls-move-submit-btn action--ls-button-submit';
        $aClassAndAttributes['class']['completedquotaurl']    = ' url-wrapper url-wrapper-survey-quotaurl text-center ';
        $aClassAndAttributes['class']['completedquotaurla']   = ' ls-endurl ls-quotaurl ';

        $aClassAndAttributes['attr']['navigatorcollbutton'] = '  type="submit" name="move" ';
        $aClassAndAttributes['attr']['navigatorcolrbutton'] = '  type="submit" name="move" value="confirmquota" ';
        $aClassAndAttributes['attr']['completedwrapper'] = $aClassAndAttributes['attr']['completedtext'] = $aClassAndAttributes['attr']['quotamessage'] = $aClassAndAttributes['attr']['navigator'] = $aClassAndAttributes['attr']['navigatorcoll'] = $aClassAndAttributes['attr']['navigatorcolr'] = $aClassAndAttributes['attr']['completedquotaurl'] = '';

        // Register
        $aClassAndAttributes['class']['register']                 = ' register-container';
        $aClassAndAttributes['class']['registerrow']              = ' register-row';
        $aClassAndAttributes['class']['registerrowjumbotron']     = ' register-jumbotron card bg-light p-6 mb-3';
        $aClassAndAttributes['class']['registerrowjumbotrondiv']  = 'card-body';

        $aClassAndAttributes['class']['registerform']             = ' register-form  ';
        $aClassAndAttributes['class']['registerul']               = '  ';
        $aClassAndAttributes['class']['registerformcolrowlabel']  = ' ';
        $aClassAndAttributes['class']['registerformcol']          = ' register-form-column ';
        $aClassAndAttributes['class']['registerformcolrow']       = ' ';
        $aClassAndAttributes['class']['registerformcolrowb']      = '  ';
        $aClassAndAttributes['class']['registerformcolrowc']      = '  ';
        $aClassAndAttributes['class']['registerformcoladdidtions'] = ' register-form-column-additions ';
        $aClassAndAttributes['class']['registerformextras']       = '  ';
        $aClassAndAttributes['class']['registerformcaptcha']      = ' captcha-item ';
        $aClassAndAttributes['class']['registerformcolrowblabel'] = ' ';
        $aClassAndAttributes['class']['registerformcolrowclabel'] = ' ';
        $aClassAndAttributes['class']['registerformextraslabel']  = '  ';
        $aClassAndAttributes['class']['registerformcaptchalabel'] = '  ';
        $aClassAndAttributes['class']['registerformcaptchadivb']  = '  ';
        $aClassAndAttributes['class']['registerformcaptchadivc']  = '  captcha-widget ';
        $aClassAndAttributes['class']['registerformcaptchainput'] = '  ';
        $aClassAndAttributes['class']['registersuccessblock'] = ' col-md-12 p-0 ';
        $aClassAndAttributes['attr']['registersuccessblock'] = ' ';
        $aClassAndAttributes['class']['registersuccesslistlabel'] = ' col-md-4 text-end  ';
        $aClassAndAttributes['attr']['registersuccesslistlabel'] = ' ';
        $aClassAndAttributes['class']['registersuccesslistcontent'] = ' col-md-8 text-start ';
        $aClassAndAttributes['attr']['registersuccesslistcontent'] = ' ';
        $aClassAndAttributes['attr']['registersuccesslist'] = ' ';
        $aClassAndAttributes['class']['registersuccesslist'] = ' list-group ';
        $aClassAndAttributes['attr']['registersuccesslistitem'] = ' ';
        $aClassAndAttributes['class']['registersuccesslistitem'] = ' list-group-item ';

        $aClassAndAttributes['class']['registermandatoryinfo']    = '  ';
        $aClassAndAttributes['class']['registersave']             = ' ';
        $aClassAndAttributes['class']['registersavediv']          = '  ';
        $aClassAndAttributes['class']['registersavedivbutton']    = ' action--ls-button-submit ';
        $aClassAndAttributes['class']['registerhead']             = '  ';

        $aClassAndAttributes['attr']['registerul']                = ' role="alert" ';
        $aClassAndAttributes['attr']['registerformcolrowblabel']  = ' for="register_lastname"  ';
        $aClassAndAttributes['attr']['registerformcolrowclabel']  = ' for="register_email"  ';
        $aClassAndAttributes['attr']['registerformcaptchalabel']  = ' for="loadsecurity"  ';
        $aClassAndAttributes['attr']['registerformcaptchainput']  = ' type="text" size="15" maxlength="15" id="loadsecurity" name="loadsecurity" value="" alt="" required ';
        $aClassAndAttributes['attr']['registermandatoryinfo']     = ' aria-hidden="true" ';
        $aClassAndAttributes['attr']['registersavedivbutton']    = ' type="submit" id="register_button" name="register" value="register"';

        $aClassAndAttributes['attr']['register']                  = $aClassAndAttributes['attr']['registerrow'] = $aClassAndAttributes['attr']['jumbotron'] = $aClassAndAttributes['attr']['registerrowjumbotrondiv'] = $aClassAndAttributes['attr']['registerulli'] = $aClassAndAttributes['class']['registerformcol'] = '';
        $aClassAndAttributes['attr']['registerformcolrow']        = $aClassAndAttributes['attr']['registerformcolrowb'] = $aClassAndAttributes['attr']['registerformcolrowbdiv'] = $aClassAndAttributes['class']['registerformcolrowc'] = $aClassAndAttributes['class']['registerformcolrowcdiv'] = $aClassAndAttributes['attr']['registerformextras'] = '';
        $aClassAndAttributes['attr']['registerformcolrowcdiv']    = $aClassAndAttributes['attr']['registerformcaptcha'] = $aClassAndAttributes['attr']['registerformcaptchadiv'] = $aClassAndAttributes['attr']['registerformcaptchadivb'] = $aClassAndAttributes['attr']['registerformcaptchadivc'] = $aClassAndAttributes['attr']['registersave'] = '';
        $aClassAndAttributes['attr']['registersavediv'] = $aClassAndAttributes['attr']['registerhead'] = $aClassAndAttributes['attr']['registermessagea'] = $aClassAndAttributes['attr']['registermessageb'] = $aClassAndAttributes['attr']['registermessagec'] = '';

        // Warnings
        $aClassAndAttributes['class']['activealert']       = '  ';
        $aClassAndAttributes['class']['errorHtml']         = ' ls-questions-have-errors ';
        $aClassAndAttributes['class']['activealertbutton'] = '  ';
        $aClassAndAttributes['class']['errorHtmlbutton']   = ' ';
        $aClassAndAttributes['attr']['activealertbutton']  = ' type="button"  data-bs-dismiss="alert" aria-label="' . gT("Close") . '" ';
        $aClassAndAttributes['attr']['errorHtmlbutton']    = ' type="button"  data-bs-dismiss="alert" aria-label="' . gT("Close") . '" ';

        $aClassAndAttributes['attr']['activealert'] = 'role="alert"';

        // Required
        $aClassAndAttributes['class']['required']     = '  ';
        $aClassAndAttributes['class']['requiredspan'] = '  ';
        $aClassAndAttributes['attr']['required']      = ' aria-hidden="true" ';
        $aClassAndAttributes['attr']['requiredspan']     = '';

        // Progress bar
        $aClassAndAttributes['class']['topcontainer'] = ' top-container ';
        $aClassAndAttributes['class']['topcontent']   = ' top-content ';
        $aClassAndAttributes['class']['progress']     = ' progress ';
        $aClassAndAttributes['class']['progressbar']  = ' progress-bar ';
        $aClassAndAttributes['attr']['topcontainer'] = '';
        $aClassAndAttributes['attr']['topcontent'] = '';
        $aClassAndAttributes['attr']['progress'] = '';
        $aClassAndAttributes['attr']['progressbar']   = '';

        // No JS alert
        $aClassAndAttributes['class']['nojs'] = ' ls-js-hidden warningjs ';
        $aClassAndAttributes['attr']['nojs']  = ' ';

        // NavBar
//        $aClassAndAttributes['id']['navbar']            = 'navbar';
//        $aClassAndAttributes['class']['navbar']         = ' navbar navbar-default';
//        $aClassAndAttributes['class']['navbarheader']   = ' navbar-header ';
//        $aClassAndAttributes['class']['navbartoggle']   = ' navbar-toggle collapsed ';
//        $aClassAndAttributes['class']['navbarbrand']    = ' navbar-brand ';
//        $aClassAndAttributes['class']['navbarcollapse'] = ' collapse navbar-collapse ';
//        $aClassAndAttributes['class']['navbarlink']     = ' nav navbar-nav  navbar-action-link ';

//        $aClassAndAttributes['attr']['navbartoggle']    = ' data-bs-toggle="collapse" data-bs-target="#navbar" aria-expanded="false" aria-controls="navbar" ';
//        $aClassAndAttributes['attr']['navbar'] = $aClassAndAttributes['attr']['navbarbrand'] = '';

        // Language changer
        $aClassAndAttributes['class']['languagechanger'] = '  form-change-lang  ';
        $aClassAndAttributes['class']['formgroup']       = ' ';
        $aClassAndAttributes['class']['controllabel']    = ' ';
        $aClassAndAttributes['class']['aLCDWithForm']    = '  btn btn-outline-secondary ls-js-hidden ';

        $aClassAndAttributes['attr']['languagechanger']  = $aClassAndAttributes['attr']['formgroup'] = $aClassAndAttributes['attr']['controllabel'] = '';

        // Bootstrap Modal Alert
        $aClassAndAttributes['id']['alertmodal']           = 'bootstrap-alert-box-modal';
        $aClassAndAttributes['id']['mandatorySoftModal']   = 'mandatory-soft-alert-box-modal';
        $aClassAndAttributes['class']['alertmodal']        = ' modal fade ';
        $aClassAndAttributes['class']['modaldialog']       = ' modal-dialog ';
        $aClassAndAttributes['class']['modalcontent']      = ' modal-content ';
        $aClassAndAttributes['class']['modalheader']       = ' modal-header ';
        $aClassAndAttributes['class']['modalclosebutton']  = ' btn-close ';
        $aClassAndAttributes['class']['modaltitle']        = ' modal-title';
        $aClassAndAttributes['class']['modalbody']         = ' modal-body ';
        $aClassAndAttributes['class']['modalfooter']       = ' modal-footer ';
        $aClassAndAttributes['class']['modalfooterlink']   = ' btn btn-outline-secondary ';

        $aClassAndAttributes['attr']['modalheader']       = ' style="min-height:40px;" '; // Todo: move to CSS
        $aClassAndAttributes['attr']['modalclosebutton']  = ' type="button" data-bs-dismiss="modal" aria-hidden="true" ';
        $aClassAndAttributes['attr']['modalfooterlink']   = ' href="#" data-bs-dismiss="modal" ';

        $aClassAndAttributes['attr']['alertmodal'] = $aClassAndAttributes['attr']['modaldialog'] = $aClassAndAttributes['attr']['modalcontent'] = $aClassAndAttributes['attr']['modaltitle'] = $aClassAndAttributes['attr']['modalbody'] = $aClassAndAttributes['attr']['modalfooter'] = '';

        // Soft Mandatory Checkbox
        $aClassAndAttributes['class']['mandsoftcheckbox'] = '';
        $aClassAndAttributes['class']['mandsoftcheckboxlabel'] = '';

        // Assessments
        $aClassAndAttributes['class']['assessmenttable']      = ' assessment-table ';
        $aClassAndAttributes['class']['assessmentstable']     = ' assessments ';
        $aClassAndAttributes['class']['assessmentstablet']    = ' assessments ';
        $aClassAndAttributes['class']['assessmentheading']    = ' assessment-heading ';
        $aClassAndAttributes['class']['assessmentscontainer'] = ' assessments-container ';

        $aClassAndAttributes['attr']['assessmentstablet'] = 'align="center"';

        $aClassAndAttributes['attr']['assessmenttable'] = $aClassAndAttributes['attr']['assessmentstabletr'] = $aClassAndAttributes['attr']['assessmentstablettr'] = $aClassAndAttributes['attr']['assessmentstabletth'] = $aClassAndAttributes['attr']['assessmentstablettd'] = $aClassAndAttributes['attr']['assessmentstableth'] = $aClassAndAttributes['attr']['assessmentstabletd'] = $aClassAndAttributes['attr']['assessmentstabletd'] = $aClassAndAttributes['attr']['assessmentheading'] = $aClassAndAttributes['attr']['assessmentscontainer'] = $aClassAndAttributes['attr']['assessmentstable'] = '';

        // Questions
        $aClassAndAttributes['class']['questioncontainer']       = ' question-container  ';
        $aClassAndAttributes['class']['questiontitlecontainer']  = ' question-title-container ';
        $aClassAndAttributes['class']['questionasterix']         = ' asterisk   ';
        $aClassAndAttributes['class']['questionasterixsmall']    = '  ';
        $aClassAndAttributes['class']['questionasterixspan']     = '  ';
        $aClassAndAttributes['class']['questionnumber']          = ' text-muted question-number ';
        $aClassAndAttributes['class']['questioncode']            = ' text-muted question-code ';
        $aClassAndAttributes['class']['questiontext']            = ' question-text ';
        $aClassAndAttributes['class']['lsquestiontext']          = ' ls-label-question ';
        $aClassAndAttributes['class']['questionvalidcontainer']  = ' question-valid-container  ';
        $aClassAndAttributes['class']['answercontainer']         = ' answer-container   ';
        $aClassAndAttributes['class']['helpcontainer']           = ' question-help-container ';
        $aClassAndAttributes['class']['lsquestionhelp']          = ' ls-questionhelp ';

        $aClassAndAttributes['attr']['questionasterixsmall'] = ' aria-hidden="true" ';

        $aClassAndAttributes['attr']['questioncontainer'] = $aClassAndAttributes['attr']['questiontitlecontainer'] = $aClassAndAttributes['attr']['questionasterix'] = $aClassAndAttributes['attr']['questionasterixspan'] = $aClassAndAttributes['attr']['questionnumber'] = $aClassAndAttributes['attr']['questioncode'] = '';
        $aClassAndAttributes['attr']['questiontext'] = $aClassAndAttributes['attr']['lsquestiontext'] = $aClassAndAttributes['attr']['questionvalidcontainer'] = $aClassAndAttributes['attr']['answercontainer'] = $aClassAndAttributes['attr']['helpcontainer'] = '';

        // Question group
        $aClassAndAttributes['class']['groupcontainer'] = ' group-container ';
        $aClassAndAttributes['class']['groupoutercontainer'] = ' group-outer-container ';
        $aClassAndAttributes['class']['grouptitle']     = ' group-title  ';
        $aClassAndAttributes['class']['groupdesc']      = ' group-description ';

        $aClassAndAttributes['attr']['questiongroup'] = $aClassAndAttributes['attr']['groupcontainer'] = $aClassAndAttributes['attr']['groupcontainer'] = $aClassAndAttributes['attr']['groupdesc'] = '';

        // Privacy
        $aClassAndAttributes['class']['privacycontainer'] = ' privacy ';
        $aClassAndAttributes['class']['privacycol']       = ' ';
        $aClassAndAttributes['class']['privacyhead']      = 'ls-privacy-head';
        $aClassAndAttributes['class']['privacybody']      = ' ls-privacy-body ';

        $aClassAndAttributes['class']['privacydatasecmodalbody'] = '';
        $aClassAndAttributes['class']['privacydatasectextbody'] = '';

        $aClassAndAttributes['class']['privacydataseccheckbox'] = '';
        $aClassAndAttributes['class']['privacydataseccheckboxlabel'] = '';

        $aClassAndAttributes['attr']['privacycontainer'] = $aClassAndAttributes['attr']['privacycol'] = $aClassAndAttributes['attr']['privacyhead'] = $aClassAndAttributes['attr']['privacybody'] = '';

        // Clearall Links
//        $aClassAndAttributes['class']['clearalllinks'] = ' ls-no-js-hidden ';
//        $aClassAndAttributes['class']['clearalllink']  = ' ls-link-action ls-link-clearall ';
//        $aClassAndAttributes['attr']['clearalllinks']  = $aClassAndAttributes['attr']['clearalllink'] = ' ';

        // Clearall Buttons
        $aClassAndAttributes['class']['clearallwrapper'] = $aClassAndAttributes['class']['clearallconfirm'] = ""; // No need, adding it if need something after
        $aClassAndAttributes['class']['clearalllabel'] = "ls-js-hidden";
        $aClassAndAttributes['attr']['clearallconfirm']  = 'value="confirm" name="confirm-clearall" type="checkbox"';
        $aClassAndAttributes['attr']['clearallbutton'] = 'type="submit" name="clearall" value="clearall" data-confirmedby="confirm-clearall"';
        $aClassAndAttributes['class']['clearallbutton'] = "ls-clearaction ls-clearall"; // Not needed, keep it (and adding to twig to be most compatible in future)

        // Language changer
//        $aClassAndAttributes['id']['lctdropdown'] = 'langs-container';

//        $aClassAndAttributes['class']['lctli']          = ' ls-no-js-hidden form-change-lang ';
//        $aClassAndAttributes['class']['lctla']          = ' ';
//        $aClassAndAttributes['class']['lctspan']        = ' ';
//        $aClassAndAttributes['class']['lctdropdown']    = ' language_change_container ';
//        $aClassAndAttributes['class']['lctdropdownli']  = 'index-item ';
//        $aClassAndAttributes['class']['lctdropdownlia'] = 'ls-language-link ';

//        $aClassAndAttributes['attr']['lctla']       = ' data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" ';
//        $aClassAndAttributes['attr']['lctdropdown'] = ' style="overflow: scroll" ';

//        $aClassAndAttributes['attr']['lctli'] = $aClassAndAttributes['attr']['lctspan'] = $aClassAndAttributes['attr']['lctdropdownli'] = $aClassAndAttributes['attr']['lctdropdownlia'] = ' ';
//        $aClassAndAttributes['attr']['navigatorcontainer'] = $aClassAndAttributes['attr']['navigatorbuttonl'] = $aClassAndAttributes['attr']['loadsavecontainer'] = $aClassAndAttributes['attr']['loadsavecol'] = '';

        // Navigator
        $aClassAndAttributes['id']['navigatorcontainer'] = 'navigator-container';

        $aClassAndAttributes['class']['navigatorcontainer']    = '   ';
        $aClassAndAttributes['class']['navigatorbuttonl']      = '  ';
        $aClassAndAttributes['class']['navigatorbuttonprev']   = ' ls-move-btn ls-move-previous-btn action--ls-button-previous';
        $aClassAndAttributes['class']['navigatorbuttonr']      = '  ';
        $aClassAndAttributes['class']['navigatorbuttonsubmit'] = ' ls-move-btn ls-move-submit-btn action--ls-button-submit ';
        $aClassAndAttributes['class']['navigatorbuttonnext']   = ' ls-move-btn ls-move-next-btn ls-move-submit-btn action--ls-button-submit ';
        // Save/Load buttons
        $aClassAndAttributes['class']['loadsavecontainer']     = '  ';
        $aClassAndAttributes['class']['loadsaveccol']          = ' save-clearall-wrapper '; /* ???? save or clearall ???? */
        $aClassAndAttributes['class']['loadbutton']           = 'ls-saveaction ls-loadall ';
        $aClassAndAttributes['class']['savebutton']           = 'ls-saveaction ls-saveall ';
        $aClassAndAttributes['attr']['loadbutton']            = ' type="submit" value="loadall" name="loadall" ';
        $aClassAndAttributes['attr']['savebutton']            = ' type="submit" value="saveall" name="saveall" ';

        $aClassAndAttributes['attr']['navigatorbuttonprev']   = ' id="ls-button-previous" type="submit" value="moveprev" name="move" ';
        $aClassAndAttributes['attr']['navigatorbuttonsubmit'] = ' id="ls-button-submit" type="submit" value="movesubmit" name="move" ';
        $aClassAndAttributes['attr']['navigatorbuttonnext']   = ' id="ls-button-submit" type="submit" value="movenext" name="move" ';

        // Index Menu
//        $aClassAndAttributes['class']['indexmenugli']     = ' ls-index-menu ls-no-js-hidden  ';
//        $aClassAndAttributes['class']['indexmenuglia']    = '   ';
//        $aClassAndAttributes['class']['indexmenugspan']   = '  ';
//        $aClassAndAttributes['class']['indexmenusgul']    = '  ';
//        $aClassAndAttributes['class']['indexmenusli']     = ' ls-index-menu ls-no-js-hidden  ';
//        $aClassAndAttributes['class']['indexmenuslia']    = '   ';
//        $aClassAndAttributes['class']['indexmenusspan']   = '  ';
//        $aClassAndAttributes['class']['indexmenussul']    = '  ';
//        $aClassAndAttributes['class']['indexmenusddh']    = '  ';
//        $aClassAndAttributes['class']['indexmenusddspan'] = '  ';
//        $aClassAndAttributes['class']['indexmenusddul']   = '  dropdown-sub-menu ';

//        $aClassAndAttributes['attr']['indexmenuglia']          = ' data-bs-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"';
//        $aClassAndAttributes['attr']['indexmenuslia']          = ' data-bs-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"';

//        $aClassAndAttributes['attr']['indexmenussul'] = '';
//        $aClassAndAttributes['attr']['indexmenusgli'] = '';

        // Preview submit
        $aClassAndAttributes['class']['previewsubmit']      = ' completed-wrapper  ';
        $aClassAndAttributes['class']['previewsubmittext']  = ' completed-text  ';
        $aClassAndAttributes['class']['submitwrapper']      = ' completed-wrapper  ';
        $aClassAndAttributes['class']['submitwrappertext']  = ' completed-text  ';
        // class name for last message elements
        $aClassAndAttributes['class']['submitwrappertextHeading']  = ' completed-heading ';
        $aClassAndAttributes['class']['submitwrappertextContent']  = ' completed-Content ';
        // ===
        $aClassAndAttributes['class']['submitwrapperdiva']  = ' url-wrapper url-wrapper-survey-print ';
        $aClassAndAttributes['class']['submitwrapperdivaa'] = ' ls-print ';
        $aClassAndAttributes['class']['submitwrapperdivb']  = ' url-wrapper url-wrapper-survey-print ';
        $aClassAndAttributes['class']['submitwrapperdivba'] = ' ls-print ';

        $aClassAndAttributes['attr']['previewsubmit']     = $aClassAndAttributes['attr']['previewsubmittext'] = $aClassAndAttributes['attr']['previewsubmitstrong'] = $aClassAndAttributes['attr']['submitwrapper'] = $aClassAndAttributes['attr']['submitwrappertext'] = $aClassAndAttributes['attr']['submitwrapperdiv'] = '';
        $aClassAndAttributes['attr']['submitwrapperdiva'] = $aClassAndAttributes['attr']['submitwrapperdivaa'] = $aClassAndAttributes['attr']['submitwrapperdivb'] = $aClassAndAttributes['attr']['submitwrapperdivba'] = '';

        // Survey list
        $aClassAndAttributes['id']['surveylistrow']          = 'surveys-list-container';
        $aClassAndAttributes['id']['surveylistrowjumbotron'] = 'surveys-list-jumbotron';
        $aClassAndAttributes['id']['surveylistfooter']       = 'surveyListFooter';

        $aClassAndAttributes['class']['surveylistrow']             = '  ';
        $aClassAndAttributes['class']['surveylistrowdiva']         = '  survey-list-heading ';
        $aClassAndAttributes['class']['surveylistrowdivadiv']      = '  ';
        $aClassAndAttributes['class']['surveylistrowdivb']         = '  survey-list ';
        $aClassAndAttributes['class']['surveylistrowdivbdiv']      = ' surveys-list-container ';
        $aClassAndAttributes['class']['surveylistrowdivbdivul']    = ' surveys-list ';
        $aClassAndAttributes['class']['surveylistrowdivbdivulli']  = '  ';
        $aClassAndAttributes['class']['surveylistrowdivbdivullia'] = ' surveytitle  ';
        $aClassAndAttributes['class']['surveylistrowdivc']         = ' survey-contact ';
        $aClassAndAttributes['class']['surveylistfooter']          = ' footer ';
        $aClassAndAttributes['class']['surveylistfootercont']      = '  ';

        $aClassAndAttributes['attr']['surveylistfootercontpaa']    = ' href="https://www.gitit-tech.com"  target="_blank" ';
        $aClassAndAttributes['attr']['surveylistfootercontpab']    = ' href="https://www.gitit-tech.com"  target="_blank" ';

        $aClassAndAttributes['attr']['surveylistrow'] = $aClassAndAttributes['attr']['surveylistrowjumbotron'] = $aClassAndAttributes['attr']['surveylistrowdiva'] = $aClassAndAttributes['attr']['surveylistrowdivadiv'] = $aClassAndAttributes['attr']['surveylistrowdivb'] = $aClassAndAttributes['attr']['surveylistrowdivbdivul'] = '';
        $aClassAndAttributes['attr']['surveylistrowdivbdivulli'] = $aClassAndAttributes['attr']['surveylistrowdivc'] = $aClassAndAttributes['attr']['surveylistfooter'] = $aClassAndAttributes['attr']['surveylistfootercont'] = $aClassAndAttributes['class']['surveylistfootercontp'] = '';

        // Save/Load links
//        $aClassAndAttributes['class']['loadlinksli']  = ' ls-no-js-hidden ';
//        $aClassAndAttributes['class']['loadlinkslia'] = ' ls-link-action ls-link-loadall ';
//        $aClassAndAttributes['class']['savelinksli']  = ' ls-no-js-hidden ';
//        $aClassAndAttributes['class']['savelinkslia'] = '';
//        $aClassAndAttributes['attr']['loadlinksli'] = '';

        // Here you can add metas from core
        $aClassAndAttributes['metas'] = '    ';

        // Maybe add a plugin event here?

        return $aClassAndAttributes;
    }

    /**
     * @todo document me
     * @return string
     */
    public function __toString()
    {
        $s = '';
        foreach ($this as $k => $v) {
            $s .= " <strong> $k : </strong>  $v  <br/>";
        }

        $aProp = get_object_vars($this);
        foreach ($aProp as $k => $v) {
            $s .= " <strong> $k : </strong>  $v  <br/>";
        }
        return $s;
    }

    /**
     * Uninstalls the selected surveytheme and deletes database entry and configuration
     * @param string $templatename Name of Template
     * @return bool|int
     * @throws CDbException
     */
    public static function uninstall($templatename)
    {
        if (Permission::model()->hasGlobalPermission('templates', 'delete')) {
            $oTemplate = Template::model()->findByAttributes(['name' => $templatename]);
            if ($oTemplate) {
                if ($oTemplate->delete()) {
                    return TemplateConfiguration::model()->deleteAll(
                        'template_name=:templateName',
                        [':templateName' => $templatename]
                    );
                }
            }
        }
        return false;
    }

    /**
     * Uninstalls all surveythemes that are being extended from the supplied surveytheme name
     * @param $templateName
     * @return void
     * @throws CDbException
     */
    public static function uninstallThemesRecursive($templateName): void
    {
        $extendedTemplates = Template::model()->findAll('extends=:templateName', [':templateName' => $templateName]);
        if (!empty($extendedTemplates)) {
            foreach ($extendedTemplates as $extendedTemplate) {
                self::uninstallThemesRecursive($extendedTemplate->name);
            }
        }
        self::uninstall($templateName);
    }

    /**
     * Checks if a theme is valid
     * Can be extended with more checks in the future if needed
     * @param $themeName
     * @param $themePath
     * @param bool $redirect
     * @return bool
     * @throws CDbException
     */
    public static function validateTheme($themeName, $themePath, bool $redirect = true): bool
    {
        // check compatability with current limesurvey version
        $isCompatible = TemplateConfig::isCompatible($themePath);
        if ($isCompatible === false) {
            self::uninstallThemesRecursive($themeName);
            if ($redirect) {
                App()->setFlashMessage(
                    sprintf(
                        gT("Theme '%s' has been uninstalled because it's not compatible with this LimeSurvey version."),
                        $themeName
                    ),
                    'error'
                );
                App()->getController()->redirect(["themeOptions/index", "#" => "surveythemes"]);
                App()->end();
            }
        } elseif ((!$isCompatible) && $redirect) {
            App()->setFlashMessage(
                sprintf(
                    gT("Theme '%s' was not found."),
                    $themeName
                ),
                'error'
            );
        }
        // add more tests here

        // all checks succeeded, continue loading the theme
        return true;
    }

    /**
     * Checks if theme is compatible with the current limesurvey version
     * @param $themePath
     * @param bool $redirect
     * @return bool|null
     */
    public static function isCompatible($themePath)
    {
        $extensionConfig = ExtensionConfig::loadFromFile($themePath);
        if ($extensionConfig === null) {
            return null;
        }
        if (!$extensionConfig->isCompatible()) {
            return false;
        }
        return true;
    }

    /**
     * Create a new entry in {{templates}} and {{template_configuration}} table using the template manifest
     * @param string $sTemplateName the name of the template to import
     * @param array $aDatas
     * @return boolean true on success | exception
     * @throws Exception|InvalidArgumentException
     */
    public static function importManifest($sTemplateName, $aDatas)
    {
        if (empty($aDatas)) {
            throw new InvalidArgumentException('$aDatas cannot be empty');
        }

        $oNewTemplate                   = new Template();
        $oNewTemplate->name             = $sTemplateName;
        $oNewTemplate->folder           = $sTemplateName;
        $oNewTemplate->title            = $aDatas['title']; // For now, when created via template editor => name == folder == title. If you change it, please, also update TemplateManifest::getTemplateURL
        $oNewTemplate->creation_date    = date("Y-m-d H:i:s");
        $oNewTemplate->author           = App()->user->name;
        $oNewTemplate->author_email     = ''; // privacy
        $oNewTemplate->author_url       = ''; // privacy
        $oNewTemplate->api_version      = $aDatas['api_version'];
        $oNewTemplate->view_folder      = $aDatas['view_folder'];
        $oNewTemplate->files_folder     = $aDatas['files_folder'];
        $oNewTemplate->description      = $aDatas['description'];
        $oNewTemplate->owner_id         = App()->user->id;
        $oNewTemplate->extends          = $aDatas['extends'];

        if ($oNewTemplate->save()) {
            $oNewTemplateConfiguration                  = new TemplateConfiguration();
            $oNewTemplateConfiguration->template_name   = $sTemplateName;

            // Those ones are only filled when importing manifest from upload directory

            $oNewTemplateConfiguration->files_css         = self::formatToJsonArray($aDatas['files_css']);
            $oNewTemplateConfiguration->files_js          = self::formatToJsonArray($aDatas['files_js']);
            $oNewTemplateConfiguration->files_print_css   = self::formatToJsonArray($aDatas['files_print_css']);
            $oNewTemplateConfiguration->cssframework_name = $aDatas['cssframework_name'];
            $oNewTemplateConfiguration->cssframework_css  = self::formatToJsonArray($aDatas['cssframework_css']);
            $oNewTemplateConfiguration->cssframework_js   = self::formatToJsonArray($aDatas['cssframework_js']);
            $oNewTemplateConfiguration->options           = self::convertOptionsToJson($aDatas['aOptions']);
            $oNewTemplateConfiguration->packages_to_load  = self::formatToJsonArray($aDatas['packages_to_load']);


            if ($oNewTemplateConfiguration->save()) {
                // Find all surveys using this theme (if reinstalling) and create an entry on db for them
                $aSurveysUsingThisTeme  =  Survey::model()->findAll(
                    'template=:template',
                    array(':template' => $sTemplateName)
                );
                foreach ($aSurveysUsingThisTeme as $oSurvey) {
                     TemplateConfiguration::checkAndcreateSurveyConfig($oSurvey->sid);
                }

                return true;
            } else {
                throw new Exception(json_encode($oNewTemplateConfiguration->getErrors()));
            }
        } else {
            throw new Exception(json_encode($oNewTemplate->getErrors()));
        }
    }

    /**
     * Convert the values to a json.
     * It checks that the correct values is inserted.
     * @param array|object $oFiled the filed to convert
     * @param boolean $bConvertEmptyToString formats empty values as empty strings instead of objects.
     * @return string  json
     */
    public static function formatToJsonArray($oFiled, $bConvertEmptyToString = false)
    {
        if ($bConvertEmptyToString) {
            foreach ($oFiled as $option => $optionValue) {
                // clean every value from newlines, tabs and blank spaces for options
                $oFiled->$option = trim(preg_replace('/[ \t]+/', ' ', preg_replace('/\s*$^\s*/m', "", $optionValue)));
            }
        }
        // encode then decode will convert the SimpleXML to a normal object
        $jFiled = json_encode($oFiled);
        $oFiled = json_decode($jFiled);

        // If in template manifest, a single file is provided, a string is produced instead of an array.
        // We force it to array here

        foreach (array('add', 'replace', 'remove') as $sAction) {
            if (is_object($oFiled) && !empty($oFiled->$sAction) && is_string($oFiled->$sAction)) {
                $sValue      = $oFiled->$sAction;
                $oFiled->$sAction = array($sValue);
                $jFiled      = json_encode($oFiled);
            }
        }
        // Converts empty objects to empty strings
        if ($bConvertEmptyToString) {
            $jFiled = str_replace('{}', '""', $jFiled);
        }
        return $jFiled;
    }

    /**
     * Extracts option values from theme options node (XML) into a json key-value map.
     * Inner nodes (which maybe inside each option element) are ignored.
     * Option values are trimmed as they may contain undesired new lines in the XML document.
     * @param array|object $options the filed to convert
     * @return string  json
     */
    public static function convertOptionsToJson($options)
    {
        $optionsArray = [];
        foreach ($options as $option => $optionValue) {
            // Trim values, as they may be in a new line in the XML. For example:
            // <sample_option>
            //      default value
            // </sample_option>
            // Also, by casting, inner nodes are eliminated
            // and only the text value inside the node is obtained
            $optionsArray[$option] = trim((string) $optionValue);
        }
        if (empty($optionsArray)) {
            return '""';
        }
        return json_encode($optionsArray);
    }

    /**
     * Returns an array of all unique template folders that are registered in the database
     * @return array|null
     */
    public static function getAllDbTemplateFolders()
    {
        static $aAllDbTemplateFolders = [];
        if (empty($aAllDbTemplateFolders)) {
            $oCriteria = new CDbCriteria();
            $oCriteria->select = 'folder';
            $oAllDbTemplateFolders = Template::model()->findAll($oCriteria);

            $aAllDbTemplateFolders = array();
            foreach ($oAllDbTemplateFolders as $oAllDbTemplateFolder) {
                $aAllDbTemplateFolders[] = $oAllDbTemplateFolder->folder;
            }

            $aAllDbTemplateFolders = array_unique($aAllDbTemplateFolders);
        }

        return $aAllDbTemplateFolders;
    }

    /**
     * Returns an array with uninstalled and/or incompatible survey themes
     * @return TemplateConfiguration[]
     */
    public static function getTemplatesWithNoDb(): array
    {
        static $aTemplatesWithoutDB = [];
        if (empty($aTemplatesWithoutDB)) {
            $aTemplatesWithoutDB['valid'] = [];
            $aTemplatesWithoutDB['invalid'] = [];
            $aTemplatesDirectories = Template::getAllTemplatesDirectories();
            $aTemplatesInDb = self::getAllDbTemplateFolders();

            foreach ($aTemplatesDirectories as $sName => $sPath) {
                if (!in_array($sName, $aTemplatesInDb)) {
                    // Get the theme manifest by forcing xml load
                    try {
                        $aTemplatesWithoutDB['valid'][$sName] = Template::getTemplateConfiguration($sName, null, null, true);
                        if (
                            empty($aTemplatesWithoutDB['valid'][$sName]->config)
                            || empty($aTemplatesWithoutDB['valid'][$sName]->config->metadata)
                        ) {
                            unset($aTemplatesWithoutDB['valid'][$sName]);
                            $aTemplatesWithoutDB['invalid'][$sName]['error'] = gT('Invalid theme configuration file');
                        }
                    } catch (Exception $e) {
                        unset($aTemplatesWithoutDB['valid'][$sName]);
                        $aTemplatesWithoutDB['invalid'][$sName]['error'] = $e->getMessage();
                    }
                }
            }
        }
        return $aTemplatesWithoutDB;
    }

    /**
     * From a list of json files in db it will generate a PHP array ready to use by removeFileFromPackage()
     *
     * @var $sType string js or css ?
     * @return array
     */
    protected function getFilesToLoad($oTemplate, $sType)
    {
        $aFiles        = $this->getFilesTo($oTemplate, $sType, 'add');
        $aReplaceFiles = $this->getFilesTo($oTemplate, $sType, 'replace');
        $aFiles        = array_merge($aFiles, $aReplaceFiles);
        return $aFiles;
    }


    /**
     * Change the mother template configuration depending on template settings
     * @var $sType     string   the type of settings to change (css or js)
     * @var $aSettings array    array of local setting
     * @return array
     */
    protected function changeMotherConfiguration($sType, $aSettings)
    {
        if (is_a($this->oMotherTemplate, 'TemplateConfiguration')) {
            // Check if each file exist in this template path
            // If the file exists in local template, we can remove it from mother template package.
            // Else, we must remove it from current package, and if it doesn't exist in mother template definition,
            // we must add it.
            // (and leave it in moter template definition if it already exists.)
            foreach ($aSettings as $key => $sFileName) {
                if (!is_string($sFileName)) {
                    continue;
                }
                if (file_exists($this->path . $sFileName)) {
                    App()->clientScript->removeFileFromPackage(
                        $this->oMotherTemplate->sPackageName,
                        $sType,
                        $sFileName
                    );
                } else {
                    // File doesn't exist locally, so it should be removed
                    $key = array_search($sFileName, $aSettings);
                    unset($aSettings[$key]);

                    $oRTemplate = self::getTemplateForAsset($sFileName, $this);

                    if ($oRTemplate) {
                        App()->clientScript->addFileToPackage($oRTemplate->sPackageName, $sType, $sFileName);
                    } else {
                        $sMessage  = "Can't find file '$sFileName' defined in theme '$this->sTemplateName' \\n";
                        $sMessage .= "\\n";
                        $sMessage .= "Note: Make sure this file exist in the current theme, or in one of its parent themes.  \\n ";
                        $sMessage .= "Note: Remember you can set in config.php 'force_xmlsettings_for_survey_rendering' so configuration is read from XML instead of DB (no reset needed)  \\n ";
                        $sMessage .= "\\n";
                        $sMessage .= "\\n";
                        self::throwConsoleError($sMessage);
                    }
                }
            }
        }
        return $aSettings;
    }

    /**
     * Find which template should be used to render a given view
     * @param  string    $sFile           the file to check
     * @param  TemplateConfiguration  $oRTemplate    the template where the custom option page should be looked for
     * @return TemplateConfiguration|boolean
     */
    public function getTemplateForAsset($sFile, $oRTemplate)
    {
        do {
            if (
                !($oRTemplate instanceof TemplateConfiguration) ||
                !($oRTemplate->oMotherTemplate instanceof TemplateConfiguration)
            ) {
                return false;
                break;
            }

            $oMotherTemplate = $oRTemplate->oMotherTemplate;
            $oRTemplate = $oMotherTemplate;
            $sFilePath = Yii::getPathOfAlias(
                App()->clientScript->packages[$oRTemplate->sPackageName]["basePath"]
            ) . DIRECTORY_SEPARATOR . $sFile;
        } while (!file_exists($sFilePath));

        return $oRTemplate;
    }
}
