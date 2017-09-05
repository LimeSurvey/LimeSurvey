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
 * Common methods for TemplateConfiguration and TemplateManifest
 */

 class TemplateConfig extends CActiveRecord
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

    /** @var  string $sFilesDirectory name of the file directory */
    public $sFilesDirectory;

    /** @var  string $filesPath Path of the tmeplate's files */
    public $filesPath;

    /** @var string[] $cssFramework What framework css is used */
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

    /** @var array $oOptions The template options */
    public $oOptions;


    /** @var string[] $depends List of all dependencies (could be more that just the config.xml packages) */
    protected $depends = array();

    /**  @var integer $apiVersion: Version of the LS API when created. Must be private : disallow update */
    protected $apiVersion;

    /** @var string $iSurveyId The current Survey Id. It can be void. It's use only to retreive the current template of a given survey */
    protected $iSurveyId='';

    /** @var string $hasConfigFile Does it has a config.xml file? */
    protected $hasConfigFile='';//

    /** @var stdClass[] $packages Array of package dependencies defined in config.xml*/
    protected $packages;

    /** @var string $xmlFile What xml config file does it use? (config/minimal) */
    protected $xmlFile;



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


     /**
     * Get the template for a given file. It checks if a file exist in the current template or in one of its mother templates
     *
     * @param  string $sFile      the  file to look for (must contain relative path, unless it's a view file)
     * @param string $oRTemplate template from which the recurrence should start
     * @return TemplateManifest
     */
     public function getTemplateForFile($sFile, $oRTemplate)
     {
         while (!file_exists($oRTemplate->path.'/'.$sFile) && !file_exists($oRTemplate->viewPath.$sFile)){
             $oMotherTemplate = $oRTemplate->oMotherTemplate;
             if(!($oMotherTemplate instanceof TemplateConfiguration)){
                 throw new Exception("no template found for  $sFile!");
                 break;
             }
             $oRTemplate = $oMotherTemplate;
         }

         return $oRTemplate;
     }


     /**
      * Create a package for the asset manager.
      * The asset manager will push to tmp/assets/xyxyxy/ the whole template directory (with css, js, files, etc.)
      * And it will publish the CSS and the JS defined in config.xml. So CSS can use relative path for pictures.
      * The publication of the package itself is in LSETwigViewRenderer::renderTemplateFromString()
      *
      * @param $oTemplate TemplateManifest
      */
     protected function createTemplatePackage($oTemplate)
     {
         // Each template in the inheritance tree needs a specific alias
         $sPathName  = 'survey.template-'.$oTemplate->sTemplateName.'.path';
         $sViewName  = 'survey.template-'.$oTemplate->sTemplateName.'.viewpath';

         Yii::setPathOfAlias($sPathName, $oTemplate->path);
         Yii::setPathOfAlias($sViewName, $oTemplate->viewPath);

         $aCssFiles  = $aJsFiles = array();

         // First we add the framework replacement (bootstrap.css must be loaded before template.css)
         $aCssFiles  = $this->getFrameworkAssetsReplacement('css');
         $aJsFiles   = $this->getFrameworkAssetsReplacement('js');

         // Then we add the template config files
         $aTCssFiles = $this->getFilesToLoad($oTemplate, 'css');
         $aTJsFiles  = $this->getFilesToLoad($oTemplate, 'js');

         $aCssFiles  = array_merge($aCssFiles, $aTCssFiles);
         $aJsFiles   = array_merge($aJsFiles, $aTJsFiles);

         $dir        = getLanguageRTL(App()->language) ? 'rtl' : 'ltr';

         // Remove/Replace mother template files
         $aCssFiles = $this->changeMotherConfiguration('css', $aCssFiles);
         $aJsFiles  = $this->changeMotherConfiguration('js',  $aJsFiles);

         // Then we add the direction files if they exist
         // TODO: attribute system rather than specific fields for RTL

         $this->sPackageName = 'survey-template-'.$this->sTemplateName;
         $sTemplateurl       = $oTemplate->getTemplateURL();

         $aDepends          = empty($oTemplate->depends)?array():$oTemplate->depends;


         // The package "survey-template-{sTemplateName}" will be available from anywhere in the app now.
         // To publish it : Yii::app()->clientScript->registerPackage( 'survey-template-{sTemplateName}' );
         // Depending on settings, it will create the asset directory, and publish the css and js files
         Yii::app()->clientScript->addPackage( $this->sPackageName, array(
             'devBaseUrl'  => $sTemplateurl,                                     // Used when asset manager is off
             'basePath'    => $sPathName,                                        // Used when asset manager is on
             'css'         => $aCssFiles,
             'js'          => $aJsFiles,
             'depends'     => $aDepends,
         ) );
     }

     /**
      * Get the file path for a given template.
      * It will check if css/js (relative to path), or view (view path)
      * It will search for current template and mother templates
      *
      * @param   string  $sFile          relative path to the file
      * @param   string  $oTemplate      the template where to look for (and its mother templates)
      */
     protected function getFilePath($sFile, $oTemplate)
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

         // Moter Template Package
         $packages = $this->addMotherTemplatePackage($packages);

         return $packages;
     }



     /**
     * @return bool
     */
     protected function setIsStandard()
     {
        $this->isStandard = Template::isStandardTemplate($this->sTemplateName);
     }


    /**
     * Core config and attributes
     *
     * Most classes and id and attributes from template views are defined here.
     * So even if users extends/modify the core template, we can still apply some debugs
     *
     * NB 1: Some of the classes should be bring back to templates
     *
     * NB 2: This is a temporary function. Before releasing to master, it will be replaced by a XML file inside the template itself
     * So third party providers will also be able to use this mechanics to provides bug fixes/enhancement to their templates
     */
    public function getClassAndAttributes()
    {

        $aClassAndAttributes = array();

        // Welcome
        $aClassAndAttributes['id']['welcomecontainer']     =  'welcome-container ';
        $aClassAndAttributes['class']['welcomecontainer']  = '';
        $aClassAndAttributes['class']['surveyname']        = " survey-name text-center ";
        $aClassAndAttributes['class']['description']       = " text-info text-center survey-description ";
        $aClassAndAttributes['class']['welcome']           = " survey-welcome h4 text-primary ";
        $aClassAndAttributes['class']['questioncount']     = " number-of-questions text-muted ";
        $aClassAndAttributes['class']['questioncounttext'] = " question-count-text ";

        $aClassAndAttributes['attr']['questioncounttext'] = '';

        // Global
        $aClassAndAttributes['id']['outerframe']    = 'outerframeContainer' ;
        $aClassAndAttributes['id']['mainrow']       = 'main-row' ;
        $aClassAndAttributes['id']['maincol']       = 'main-col' ;
        $aClassAndAttributes['id']['dynamicreload'] = 'dynamicReloadContainer' ;

        $aClassAndAttributes['class']['html']  = 'no-js';
        $aClassAndAttributes['class']['body']  = ''.$this->sTemplateName;
        $aClassAndAttributes['class']['outerframe'] = ' outerframe container ' ;
        $aClassAndAttributes['class']['maincol'] = ' col-centered ' ;
        $aClassAndAttributes['attr']['html']   = $thissurvey['attr']['body'] = $thissurvey['attr']['mainrow'] = $thissurvey['attr']['maincol']  = '';


        // Clear all
        $aClassAndAttributes['class']['clearall']    = 'return-to-survey';
        $aClassAndAttributes['class']['clearalldiv'] = ' url-wrapper url-wrapper-survey-return ';
        $aClassAndAttributes['class']['clearalla']   = ' ls-return ';
        $aClassAndAttributes['attr']['clearall'] = $thissurvey['attr']['clearalldiv'] = $thissurvey['attr']['clearalla'] = '';

        // Load
        $aClassAndAttributes['id']['saveformrowcolinput']    = 'loadname';
        $aClassAndAttributes['id']['captcharowcoldivinput']  = 'loadsecurity';

        $aClassAndAttributes['class']['savemessage']           = '  well clearfix save-message ';
        $aClassAndAttributes['class']['savemessagetitle']      = '  h2 ';
        $aClassAndAttributes['class']['savemessagetext']       = ' text-info ';
        $aClassAndAttributes['class']['loadform']              = ' load-form ';
        $aClassAndAttributes['class']['loadformul']            = ' alert alert-danger list-unstyled ';
        $aClassAndAttributes['class']['loadformform']          = ' ls-form form form-horizontal ';
        $aClassAndAttributes['class']['saveform']              = ' save-survey-form ';
        $aClassAndAttributes['class']['saveformrow']           = ' row form-group save-survey-row save-survey-name ';
        $aClassAndAttributes['class']['saveformrowlabel']      = ' control-label col-sm-3 load-survey-label ';
        $aClassAndAttributes['class']['saveformrowlabelsmall'] = ' text-danger asterisk fa fa-asterisk pull-left small ';
        $aClassAndAttributes['class']['saveformrowlabelspan']  = ' sr-only text-danger asterisk ';
        $aClassAndAttributes['class']['saveformrowcol']        = ' col-sm-7 save-survey-input input-cell ';
        $aClassAndAttributes['class']['saveformrowcolinput']   = ' form-control ';
        $aClassAndAttributes['class']['passwordrow']           = ' row form-group load-survey-row load-survey-password ';
        $aClassAndAttributes['class']['passwordrowcol']        = ' control-label col-sm-3 load-survey-label label-cell ';
        $aClassAndAttributes['class']['passwordrowcolsmall']   = ' text-danger asterisk fa fa-asterisk pull-left small ';
        $aClassAndAttributes['class']['passwordrowcolspan']    = ' sr-only text-danger asterisk ';
        $aClassAndAttributes['class']['captcharow']            = ' row form-group save-survey-row save-survey-captcha ';
        $aClassAndAttributes['class']['captcharowlabel']       = ' control-label col-sm-3 save-survey-label label-cell ';
        $aClassAndAttributes['class']['captcharowcol']         = ' col-sm-7 save-survey-input input-cell ';
        $aClassAndAttributes['class']['captcharowcoldiv']      = '  input-group ';
        $aClassAndAttributes['class']['captcharowcoldivdiv']   = ' input-group-addon captcha-image ';
        $aClassAndAttributes['class']['captcharowcoldivinput'] = ' form-control ';
        $aClassAndAttributes['class']['loadrow']               = ' row form-group save-survey-row save-survey-submit ';
        $aClassAndAttributes['class']['loadrowcol']            = ' col-sm-7 col-md-offset-3 save-survey-input input-cell ';
        $aClassAndAttributes['class']['loadrowcolbutton']      = ' btn btn-default ';
        $aClassAndAttributes['class']['returntosurvey']        = ' return-to-survey ';
        $aClassAndAttributes['class']['returntosurveydiv']     = ' url-wrapper url-wrapper-survey-return ';
        $aClassAndAttributes['class']['returntosurveydiva']    = ' ls-return ';

        $aClassAndAttributes['attr']['loadformul']             = ' role="alert"';
        $aClassAndAttributes['attr']['saveformrowlabel']       = ' for="savename" ';
        $aClassAndAttributes['attr']['saveformrowlabelsmall']  = ' aria-hidden="true" ';
        $aClassAndAttributes['attr']['saveformrowcolinput']    = ' type="text"  name="loadname" value="" required ';
        $aClassAndAttributes['attr']['passwordrowcol']         = ' for="loadpass" ';
        $aClassAndAttributes['attr']['passwordrowcolsmall']    = ' aria-hidden="true"';
        $aClassAndAttributes['attr']['captcharowcoldivdivimg'] = ' alt="captcha" ';
        $aClassAndAttributes['attr']['captcharowcoldivinput']  = '  type="text" size="5" maxlength="3" id="loadsecurity" name="loadsecurity" value="" alt="" ';
        $aClassAndAttributes['attr']['loadrowcolbutton']       = '  type="submit" id="loadbutton" name="loadall"  value="reload" ';



        $aClassAndAttributes['attr']['savemessage'] = $aClassAndAttributes['attr']['savemessagetext'] = $aClassAndAttributes['attr']['savemessagetitle'] = $aClassAndAttributes['attr']['loadform']  = $aClassAndAttributes['attr']['savemessagetextp'] = $aClassAndAttributes['attr']['savemessagetextpb'] = '';
        $aClassAndAttributes['attr']['loadformulli'] = $aClassAndAttributes['attr']['saveform']  = $aClassAndAttributes['attr']['saveformrow'] = $aClassAndAttributes['attr']['saveformrowlabelspan'] = $aClassAndAttributes['attr']['saveformrowcol'] = $aClassAndAttributes['attr']['passwordrow'] = '';
        $aClassAndAttributes['attr']['passwordrowcolspan'] = $aClassAndAttributes['attr']['captcharow']  = $aClassAndAttributes['attr']['captcharowlabel']  = $aClassAndAttributes['attr']['captcharowcol'] = $aClassAndAttributes['attr']['captcharowcoldiv'] = $aClassAndAttributes['attr']['loadrow'] = '';
        $aClassAndAttributes['attr']['loadrowcol'] = $aClassAndAttributes['class']['returntosurvey'] = $aClassAndAttributes['attr']['returntosurveydiv'] = $aClassAndAttributes['class']['returntosurveydiva']  = '';


        // Completed
        $aClassAndAttributes['id']['navigator']           = 'navigator-container';

        $aClassAndAttributes['class']['completedwrapper']     = ' completed-wrapper ';
        $aClassAndAttributes['class']['completedtext']        = ' completed-text ';
        $aClassAndAttributes['class']['quotamessage']         = ' quotamessage limesurveycore ';
        $aClassAndAttributes['class']['navigator']            = ' navigator row ';
        $aClassAndAttributes['class']['navigatorcoll']        = ' col-xs-6 text-left ';
        $aClassAndAttributes['class']['navigatorcollbutton']  = ' ls-move-btn ls-move-previous-btn btn btn-lg btn-default ';
        $aClassAndAttributes['class']['navigatorcolr']        = ' col-xs-6 text-right ';
        $aClassAndAttributes['class']['navigatorcolrbutton']  = ' ls-move-btn ls-move-submit-btn btn btn-lg btn-primary ';
        $aClassAndAttributes['class']['completedquotaurl']    = ' url-wrapper url-wrapper-survey-quotaurl ';
        $aClassAndAttributes['class']['completedquotaurla']   = ' ls-endurl ls-quotaurl ';
        $aClassAndAttributes['class']['completedquotaurla']   = ' ls-endurl ls-quotaurl ';
        $aClassAndAttributes['class']['completedquotaurla']   = ' ls-endurl ls-quotaurl ';


        $aClassAndAttributes['attr']['navigatorcollbutton'] = '  type="submit" name="move" accesskey="p" ';
        $aClassAndAttributes['attr']['navigatorcolrbutton'] = '  type="submit" name="move" value="confirmquota" accesskey="l"   ';
        $aClassAndAttributes['attr']['completedwrapper'] =  $aClassAndAttributes['attr']['completedtext'] = $aClassAndAttributes['attr']['quotamessage']  = $aClassAndAttributes['attr']['navigator'] = $aClassAndAttributes['attr']['navigatorcoll'] = $aClassAndAttributes['attr']['navigatorcolr'] = $aClassAndAttributes['attr']['completedquotaurl'] ='';


        // Register
        $aClassAndAttributes['class']['register']                 = ' container ';
        $aClassAndAttributes['class']['registerrow']              = ' row ';
        $aClassAndAttributes['class']['registerrowjumbotrondiv']  = ' container clearfix ';
        $aClassAndAttributes['class']['registerform']             = ' register-form row ';
        $aClassAndAttributes['class']['registerul']               = ' alert alert-danger list-unstyled ';
        $aClassAndAttributes['class']['registerformcol']          = ' col-md-8 col-md-offset-2 ';
        $aClassAndAttributes['class']['registerformcolrow']       = ' form-group row ';
        $aClassAndAttributes['class']['registerformcolrowb']      = ' form-group row ';
        $aClassAndAttributes['class']['registerformcolrowc']      = ' form-group row ';
        $aClassAndAttributes['class']['registerformextras']       = ' form-group row ';
        $aClassAndAttributes['class']['registerformcaptcha']      = ' form-group row captcha-item ';
        $aClassAndAttributes['class']['registerformcolrowblabel'] = ' control-label ';
        $aClassAndAttributes['class']['registerformcolrowclabel'] = ' control-label ';
        $aClassAndAttributes['class']['registerformextraslabel']  = ' control-label ';
        $aClassAndAttributes['class']['registerformcaptchalabel'] = ' control-label ';
        $aClassAndAttributes['class']['registerformcaptchadivb']  = ' input-group ';
        $aClassAndAttributes['class']['registerformcaptchadivc']  = ' control-label captcha-widget ';
        $aClassAndAttributes['class']['registerformcaptchainput'] = ' form-control ';
        $aClassAndAttributes['class']['registermandatoryinfo']    = ' row ';
        $aClassAndAttributes['class']['registersave']             = ' form-group row';
        $aClassAndAttributes['class']['registersavediv']          = ' col-md-offset-7 ';
        $aClassAndAttributes['class']['registersavedivbutton']    = ' btn btn-default ';
        $aClassAndAttributes['class']['registerhead']             = ' row h2 ';




        $aClassAndAttributes['attr']['registerul']                = ' role="alert" ';
        $aClassAndAttributes['attr']['registerformcolrowblabel']  = ' for="register_lastname"  ';
        $aClassAndAttributes['attr']['registerformcolrowclabel']  = ' for="register_email"  ';
        $aClassAndAttributes['attr']['registerformcaptchalabel']  = ' for="loadsecurity"  ';
        $aClassAndAttributes['attr']['registerformcaptchainput']  = ' type="text" size="15" maxlength="15" id="loadsecurity" name="loadsecurity" value="" alt="" required ';
        $aClassAndAttributes['attr']['registermandatoryinfo']     = ' aria-hidden="true" ';
        $aClassAndAttributes['class']['registersavedivbutton']    = ' type="submit" id="savebutton" name="savesubmit" value="save"';

        $aClassAndAttributes['attr']['register']                  = $aClassAndAttributes['attr']['registerrow'] = $aClassAndAttributes['attr']['jumbotron'] = $aClassAndAttributes['attr']['registerrowjumbotrondiv'] = $aClassAndAttributes['attr']['registerulli'] = $aClassAndAttributes['class']['registerformcol'] = '';
        $aClassAndAttributes['attr']['registerformcolrow']        =  $aClassAndAttributes['attr']['registerformcolrowb'] = $aClassAndAttributes['attr']['registerformcolrowbdiv'] = $aClassAndAttributes['class']['registerformcolrowc'] = $aClassAndAttributes['class']['registerformcolrowcdiv'] = $aClassAndAttributes['attr']['registerformextras'] = '';
        $aClassAndAttributes['attr']['registerformcolrowcdiv']    = $aClassAndAttributes['attr']['registerformcaptcha'] = $aClassAndAttributes['attr']['registerformcaptchadiv'] = $aClassAndAttributes['attr']['registerformcaptchadivb'] = $aClassAndAttributes['attr']['registerformcaptchadivc'] = $aClassAndAttributes['attr']['registersave'] = '';
        $aClassAndAttributes['attr']['registersavediv'] = $aClassAndAttributes['attr']['registerhead'] = $aClassAndAttributes['attr']['registermessagea'] = $aClassAndAttributes['attr']['registermessageb'] = $aClassAndAttributes['attr']['registermessagec'] = '';

        // Warnings
        $aClassAndAttributes['class']['activealert']       = ' alert alert-warning alert-dismissible fade in alert-dismissible ';
        $aClassAndAttributes['class']['errorHtml']         = ' fade in alert-dismissible ls-questions-have-errors alert alert-danger ';
        $aClassAndAttributes['class']['activealertbutton'] = ' close ';
        $aClassAndAttributes['class']['errorHtmlbutton']   = ' close ';
        $aClassAndAttributes['attr']['activealertbutton']  = ' type="button"  data-dismiss="alert" aria-label="Close" ';
        $aClassAndAttributes['attr']['errorHtmlbutton']    = ' type="button"  data-dismiss="alert" aria-label="Close" ';

        $aClassAndAttributes['attr']['activealert']  = 'role="alert"';

        // Required
        $aClassAndAttributes['class']['required']     = ' text-danger asterisk fa fa-asterisk pull-left small ';
        $aClassAndAttributes['class']['requiredspan'] = ' sr-only text-danger asterisk ';
        $aClassAndAttributes['attr']['required']      = ' aria-hidden="true" ';
        $aClassAndAttributes['class']['required']     = '';

        // Progress bar
        $aClassAndAttributes['class']['topcontainer'] = ' top-container ';
        $aClassAndAttributes['class']['topcontent']   = ' container top-content ';
        $aClassAndAttributes['class']['progress']     = ' progress ';
        $aClassAndAttributes['class']['progressbar']  = ' progress-bar ';
        $aClassAndAttributes['attr']['progressbar']   = $aClassAndAttributes['attr']['topcontainer'] = $aClassAndAttributes['class']['topcontent'] = $aClassAndAttributes['attr']['progressbar']  =  $aClassAndAttributes['attr']['progress']  = ' ';

        // No JS alert
        $aClassAndAttributes['class']['nojs'] = ' alert alert-danger ls-js-hidden warningjs ';
        $aClassAndAttributes['attr']['nojs']  = ' alert alert-danger ls-js-hidden warningjs ';

        // NavBar
        $aClassAndAttributes['id']['navbar']            = 'navbar';
        $aClassAndAttributes['class']['navbar']         = ' navbar navbar-default navbar-fixed-top ';
        $aClassAndAttributes['class']['navbarheader']   = ' navbar-header ';
        $aClassAndAttributes['class']['navbartoggle']   = ' navbar-toggle collapsed ';
        $aClassAndAttributes['class']['navbarbrand']    = ' navbar-brand ';
        $aClassAndAttributes['class']['navbarcollapse'] = ' collapse navbar-collapse ';
        $aClassAndAttributes['class']['navbarlink']     = ' nav navbar-nav navbar-right navbar-action-link ';

        $aClassAndAttributes['attr']['navbartoggle']    = ' data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar" ';
        $aClassAndAttributes['attr']['navbar']  =  $aClassAndAttributes['attr']['navbarheader']  = $aClassAndAttributes['attr']['navbarbrand'] = $aClassAndAttributes['attr']['navbarcollapse']  = $aClassAndAttributes['attr']['navbarlink'] = '';

        // Language changer
        $aClassAndAttributes['class']['languagechanger'] = '  form-inline form-change-lang  ';
        $aClassAndAttributes['class']['formgroup']       = '  form-group ';
        $aClassAndAttributes['class']['controllabel']    = '  control-label  ';
        $aClassAndAttributes['class']['formcontrol']     = '  form-control  ';
        $aClassAndAttributes['class']['aLCDWithForm']    = '  btn btn-default ls-js-hidden ';

        $aClassAndAttributes['attr']['languagechanger']  =  $aClassAndAttributes['attr']['formgroup']  = $aClassAndAttributes['attr']['controllabel'] = '';

        // Bootstrap Modal Alert
        $aClassAndAttributes['id']['alertmodal']           = 'bootstrap-alert-box-modal';
        $aClassAndAttributes['class']['alertmodal']        = ' modal fade ';
        $aClassAndAttributes['class']['modaldialog']       = ' modal-dialog ';
        $aClassAndAttributes['class']['modalcontent']      = ' modal-content ';
        $aClassAndAttributes['class']['modalheader']       = ' modal-header ';
        $aClassAndAttributes['class']['modalclosebutton']  = ' close ';
        $aClassAndAttributes['class']['modaltitle']        = ' modal-title h4 ';
        $aClassAndAttributes['class']['modalbody']         = ' modal-body ';
        $aClassAndAttributes['class']['modalfooter']       = ' modal-footer ';
        $aClassAndAttributes['class']['modalfooterlink']   = ' btn btn-default ';

        $aClassAndAttributes['attr']['modalheader']       = ' style="min-height:40px;" ';    // Todo: move to CSS
        $aClassAndAttributes['attr']['modalclosebutton']  = ' type="button" data-dismiss="modal" aria-hidden="true" ';
        $aClassAndAttributes['attr']['modalfooterlink']   = ' href="#" data-dismiss="modal" ';

        $aClassAndAttributes['attr']['alertmodal'] = $aClassAndAttributes['attr']['modaldialog'] = $aClassAndAttributes['attr']['modalcontent'] = $aClassAndAttributes['attr']['modaltitle'] = $aClassAndAttributes['attr']['modalbody'] = $aClassAndAttributes['attr']['modalfooter'] =  '';

        // Assessments
        $aClassAndAttributes['class']['assessmenttable']      = ' assessment-table table ';
        $aClassAndAttributes['class']['assessmentstable']     = ' assessments table ';
        $aClassAndAttributes['class']['assessmentstablet']    = ' assessments table ';
        $aClassAndAttributes['class']['assessmentheading']    = ' assessment-heading ';
        $aClassAndAttributes['class']['assessmentscontainer'] = ' assessments-container ';

        $aClassAndAttributes['attr']['assessmentstablet'] = 'align="center"';

        $aClassAndAttributes['attr']['assessmenttable'] = $aClassAndAttributes['attr']['assessmentstablettr'] = $aClassAndAttributes['attr']['assessmentstabletth'] = $aClassAndAttributes['attr']['assessmentstablettd'] = $aClassAndAttributes['attr']['assessmentstableth'] = $aClassAndAttributes['attr']['assessmentstabletd'] = $aClassAndAttributes['attr']['assessmentstabletd'] = $aClassAndAttributes['attr']['assessmentheading'] = $aClassAndAttributes['attr']['assessmentscontainer'] = $aClassAndAttributes['attr']['assessmentstable'] = '';

        // Questions
        $aClassAndAttributes['class']['questioncontainer']       = ' question-container row ';
        $aClassAndAttributes['class']['questiontitlecontainer']  = ' question-title-container bg-primary col-xs-12 ';
        $aClassAndAttributes['class']['questionasterix']         = ' asterisk pull-left ';
        $aClassAndAttributes['class']['questionasterixsmall']    = ' text-danger fa fa-asterisk small ';
        $aClassAndAttributes['class']['questionasterixspan']     = ' sr-only text-danger ';
        $aClassAndAttributes['class']['questionnumber']          = ' text-muted question-number ';
        $aClassAndAttributes['class']['questioncode']            = ' text-muted question-code ';
        $aClassAndAttributes['class']['questiontext']            = ' question-text ';
        $aClassAndAttributes['class']['lsquestiontext']          = ' ls-label-question ';
        $aClassAndAttributes['class']['questionvalidcontainer']  = ' question-valid-container bg-primary text-info col-xs-12 ';
        $aClassAndAttributes['class']['answercontainer']         = ' answer-container  col-xs-12 ';
        $aClassAndAttributes['class']['helpcontainer']           = ' question-help-container text-info col-xs-12 ';
        $aClassAndAttributes['class']['lsquestionhelp']          = ' ls-questionhelp ';

        $aClassAndAttributes['attr']['questionasterixsmall'] = ' aria-hidden="true" ';

        $aClassAndAttributes['attr']['questioncontainer'] = $aClassAndAttributes['attr']['questiontitlecontainer'] = $aClassAndAttributes['attr']['questionasterix'] = $aClassAndAttributes['attr']['questionasterixspan'] = $aClassAndAttributes['attr']['questionnumber'] = $aClassAndAttributes['attr']['questioncode'] =  '';
        $aClassAndAttributes['attr']['questiontext'] = $aClassAndAttributes['attr']['lsquestiontext'] = $aClassAndAttributes['attr']['questionvalidcontainer'] = $aClassAndAttributes['attr']['answercontainer'] = $aClassAndAttributes['attr']['helpcontainer'] = '';

        // Question group
        $aClassAndAttributes['class']['groupcontainer'] = ' group-container ';
        $aClassAndAttributes['class']['groupcontainer'] = ' group-title text-center h3 ';
        $aClassAndAttributes['class']['groupdesc']      = ' group-description row well ';

        $aClassAndAttributes['attr']['questiongroup']  = $aClassAndAttributes['attr']['groupcontainer'] = $aClassAndAttributes['attr']['groupcontainer'] = $aClassAndAttributes['attr']['groupdesc'] = '';

        // Privacy
        $aClassAndAttributes['class']['privacycontainer'] = ' row privacy ';
        $aClassAndAttributes['class']['privacycol']       = ' col-sm-12 col-centered ';
        $aClassAndAttributes['class']['privacyhead']      = ' h4 text-primary ';
        $aClassAndAttributes['class']['privacybody']      = ' ls-privacy-body ';

        $aClassAndAttributes['attr']['privacycontainer'] = $aClassAndAttributes['attr']['privacycol'] = $aClassAndAttributes['attr']['privacyhead'] = $aClassAndAttributes['attr']['privacybody'] = '';

        // Clearall Links
        $aClassAndAttributes['class']['clearalllinks'] = ' ls-no-js-hidden ';
        $aClassAndAttributes['class']['clearalllink']  = ' ls-link-action ls-link-clearall ';

        $aClassAndAttributes['attr']['clearalllinks']  = $aClassAndAttributes['attr']['clearalllink'] = ' ';

        // Language changer
        $aClassAndAttributes['id']['lctdropdown']    = 'langs-container';

        $aClassAndAttributes['class']['lctli']          = ' dropdown ls-no-js-hidden lctli ';
        $aClassAndAttributes['class']['lctla']          = ' dropdown-toggle ';
        $aClassAndAttributes['class']['lctspan']        = ' caret ';
        $aClassAndAttributes['class']['lctdropdown']    = ' dropdown-menu ';
        $aClassAndAttributes['class']['lctdropdownli']  = ' index-item ';
        $aClassAndAttributes['class']['lctdropdownlia'] = ' ls-language-link ';

        $aClassAndAttributes['attr']['lctla']       = ' data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" ';
        $aClassAndAttributes['attr']['lctdropdown'] = ' style="overflow: scroll" ';

        $aClassAndAttributes['attr']['lctli'] = $aClassAndAttributes['attr']['lctspan'] = $aClassAndAttributes['attr']['lctdropdownli'] = $aClassAndAttributes['attr']['lctdropdownlia'] = ' ';
        $aClassAndAttributes['attr']['navigatorcontainer'] = $aClassAndAttributes['attr']['navigatorbuttonl'] = $aClassAndAttributes['attr']['loadsavecontainer'] = $aClassAndAttributes['attr']['loadsavecol']  = '';

        // Navigator
        $aClassAndAttributes['id']['navigatorcontainer'] = 'navigator-container';

        $aClassAndAttributes['class']['navigatorcontainer']    = ' navigator row ';
        $aClassAndAttributes['class']['navigatorbuttonl']      = ' col-xs-6 text-left ';
        $aClassAndAttributes['class']['navigatorbuttonprev']   = ' ls-move-btn ls-move-previous-btn btn btn-lg btn-default ';
        $aClassAndAttributes['class']['navigatorbuttonr']      = ' col-xs-6 text-right ';
        $aClassAndAttributes['class']['navigatorbuttonsubmit'] = ' ls-move-btn ls-move-submit-btn btn btn-lg btn-primary ';
        $aClassAndAttributes['class']['navigatorbuttonnext']   = ' ls-move-btn ls-move-next-btn ls-move-submit-btn btn btn-lg btn-primary ';
        $aClassAndAttributes['class']['loadsavecontainer']     = ' navigator row ';
        $aClassAndAttributes['class']['loadsavecol']           = ' col-sm-6 save-clearall-wrapper ';
        $aClassAndAttributes['class']['loadbutton']            = ' ls-saveaction ls-loadall btn btn-default ';
        $aClassAndAttributes['class']['savebutton']            = ' ls-saveaction ls-loadall btn btn-default ';

        $aClassAndAttributes['attr']['navigatorbuttonprev']   = ' type="submit" value="moveprev" name="move" accesskey="p" accesskey="n"';
        $aClassAndAttributes['attr']['navigatorbuttonsubmit'] = ' type="submit" value="movesubmit" name="move" accesskey="l" ';
        $aClassAndAttributes['attr']['navigatorbuttonnext']   = ' type="submit" value="movenext" name="move"  ';
        $aClassAndAttributes['attr']['loadbutton']            = ' type="submit" value="loadall" name="loadall" accesskey="L"';
        $aClassAndAttributes['attr']['savebutton']            = ' type="submit" value="saveall" name="saveall" accesskey="s" ';

        // Index Menu
        $aClassAndAttributes['class']['indexmenugli']     = ' dropdown ls-index-menu ls-no-js-hidden  ';
        $aClassAndAttributes['class']['indexmenuglia']    = ' dropdown-toggle  ';
        $aClassAndAttributes['class']['indexmenugspan']   = ' caret ';
        $aClassAndAttributes['class']['indexmenusgul']    = ' dropdown-menu ';
        $aClassAndAttributes['class']['indexmenusli']     = ' dropdown ls-index-menu ls-no-js-hidden  ';
        $aClassAndAttributes['class']['indexmenuslia']    = ' dropdown-toggle  ';
        $aClassAndAttributes['class']['indexmenusspan']   = ' caret ';
        $aClassAndAttributes['class']['indexmenussul']    = ' dropdown-menu ';
        $aClassAndAttributes['class']['indexmenusddh']    = ' dropdown-menu ';
        $aClassAndAttributes['class']['indexmenusddspan'] = ' caret ';
        $aClassAndAttributes['class']['indexmenusddul']   = ' dropdown-menu dropdown-sub-menu ';

        $aClassAndAttributes['attr']['indexmenuglia']          = ' data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"';
        $aClassAndAttributes['attr']['indexmenuslia']          = ' data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"';

        $aClassAndAttributes['attr']['indexmenugli']  = $aClassAndAttributes['attr']['indexmenugspan'] = $aClassAndAttributes['attr']['indexmenusgul'] = $aClassAndAttributes['attr']['indexmenusli'] = $aClassAndAttributes['attr']['indexmenusspan'] = $aClassAndAttributes['attr']['indexmenussul'] = '';
        $aClassAndAttributes['attr']['indexmenusddh'] = $aClassAndAttributes['attr']['indexmenusddspan']  = $aClassAndAttributes['attr']['indexmenusddul'] = $aClassAndAttributes['attr']['indexmenussli'] = $aClassAndAttributes['attr']['indexmenusgli'] = '';


        // Save/Load links
        $aClassAndAttributes['class']['loadlinksli']  = ' ls-no-js-hidden ';
        $aClassAndAttributes['class']['loadlinkslia'] = ' ls-link-action ls-link-loadall ';
        $aClassAndAttributes['class']['savelinksli']  = ' ls-no-js-hidden ';
        $aClassAndAttributes['class']['savelinkslia'] = 'ls-link-action ls-link-saveall';

        $aClassAndAttributes['attr']['loadlinksli']     = $aClassAndAttributes['attr']['savelinksli'] = $aClassAndAttributes['class']['savelinkslia'] = '';

        // Here you can add metas from core
        $aClassAndAttributes['metas']    = '    ';

        // Maybe add a plugin event here?

        return $aClassAndAttributes;

    }


    // TODO: try to refactore most of those methods in TemplateConfiguration and TemplateManifest so we can define their body here.
    // It will consist in adding private methods to get the values of variables... See what has been done for createTemplatePackage
    // Then, the lonely differences between TemplateManifest and TemplateConfiguration should be how to retreive and format the data
    // Note: signature are already the same

    public function prepareTemplateRendering($sTemplateName='', $iSurveyId='', $bUseMagicInherit=true){}
    public function addFileReplacement($sFile, $sType){}

    protected function getFilesToLoad($oTemplate, $sType){}
    protected function changeMotherConfiguration( $sType, $aSettings ){}
    protected function getFrameworkAssetsToReplace( $sType, $bInlcudeRemove = false){}
    protected function getFrameworkAssetsReplacement($sType){}
    protected function removeFileFromPackage( $sPackageName, $sType, $aSettings ){}
    protected function setMotherTemplates(){}
    protected function setThisTemplate(){}
 }
