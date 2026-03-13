<?php

/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

class AdminController extends LSYii_Controller
{
    public $sTemplate = null; // this is needed for the preview rendering inside the questioneditor
    public $layout = false;
    public $aAdminModulesClasses = array();
    protected $user_id = 0;
    protected $aOverridenCoreActions = array(); // Contains the list of controller's actions overridden by custom modules
    protected $currentModuleAction = '';        // Name of the current action overridden by a custom module

    /**
     * Initialises this controller, does some basic checks and setups
     *
     * REFACTORED ( in LSBaseController )
     *
     * @access protected
     * @return void
     */
    protected function customInit()
    {
        parent::customInit();
        App()->getComponent('bootstrap');
        $this->sessioncontrol();

        $this->user_id = Yii::app()->user->getId();
        // Check if the user really exists
        // This scenario happens if the user was deleted while still being logged in
        if (!empty($this->user_id) && User::model()->findByPk($this->user_id) == null) {
            $this->user_id = null;
            Yii::app()->session->destroy();
        }

        if (!Yii::app()->getConfig("surveyid")) {
            Yii::app()->setConfig("surveyid", returnGlobal('sid'));
        }         //SurveyID
        if (!Yii::app()->getConfig("surveyID")) {
            Yii::app()->setConfig("surveyID", returnGlobal('sid'));
        }         //SurveyID
        if (!Yii::app()->getConfig("ugid")) {
            Yii::app()->setConfig("ugid", returnGlobal('ugid'));
        }                //Usergroup-ID
        if (!Yii::app()->getConfig("gid")) {
            Yii::app()->setConfig("gid", returnGlobal('gid'));
        }                   //GroupID
        if (!Yii::app()->getConfig("qid")) {
            Yii::app()->setConfig("qid", returnGlobal('qid'));
        }                   //QuestionID
        if (!Yii::app()->getConfig("lid")) {
            Yii::app()->setConfig("lid", returnGlobal('lid'));
        }                   //LabelID
        if (!Yii::app()->getConfig("code")) {
            Yii::app()->setConfig("code", returnGlobal('code'));
        }                // ??
        if (!Yii::app()->getConfig("action")) {
            Yii::app()->setConfig("action", returnGlobal('action'));
        }          //Desired action
        if (!Yii::app()->getConfig("subaction")) {
            Yii::app()->setConfig("subaction", returnGlobal('subaction'));
        } //Desired subaction
        if (!Yii::app()->getConfig("editedaction")) {
            Yii::app()->setConfig("editedaction", returnGlobal('editedaction'));
        } // for html editor integration

        // This line is needed for template editor to work
        $oAdminTheme = AdminTheme::getInstance();

        Yii::setPathOfAlias('lsadminmodules', Yii::app()->getConfig('lsadminmodulesrootdir'));
    }

    /**
     * Shows a nice error message to the world
     *
     * todo REFACTORING is this still in use? can't find any call in an action or a view ...
     * todo its used multiple times getController->error, all calls should be replaceable by setFlashMessage
     *
     * @access public
     * @param string $message The error message
     * @return void
     */
    public function error($message, $sURL = array())
    {
        $this->getAdminHeader();
        $sOutput = "<div class='messagebox ui-corner-all'>\n";
        $sOutput .= '<div class="warningheader">' . gT('Error') . '</div><br />' . "\n";
        $sOutput .= $message . '<br /><br />' . "\n";
        if (!empty($sURL) && !is_array($sURL)) {
            $sTitle = gT('Back');
        } elseif (!empty($sURL['url'])) {
            if (!empty($sURL['title'])) {
                $sTitle = $sURL['title'];
            } else {
                $sTitle = gT('Back');
            }
            $sURL = $sURL['url'];
        } else {
            $sTitle = gT('Main Admin Screen');
            $sURL = $this->createUrl('/admin');
        }
        $sOutput .= '<input type="submit" value="' . $sTitle . '" onclick=\'window.open("' . $sURL . '", "_top")\' /><br /><br />' . "\n";
        $sOutput .= '</div>' . "\n";
        $sOutput .= '</div>' . "\n";
        echo $sOutput;

        $this->getAdminFooter('http://manual.limesurvey.org', gT('LimeSurvey online manual'));

        Yii::app()->end();
    }

    /**
     * Load and set session vars
     *
     * REFACTORED (in LSBaseController)
     *
     * @access protected
     * @return void
     */
    protected function sessioncontrol()
    {
        // From personal settings
        if (Yii::app()->request->getPost('action') == 'savepersonalsettings') {
            if (Yii::app()->request->getPost('lang') == 'auto') {
                $sLanguage = getBrowserLanguage();
            } else {
                $sLanguage = \LSYii_Validators::languageCodeFilter(Yii::app()->request->getPost('lang'));
            }
            Yii::app()->session['adminlang'] = $sLanguage;
        }
        if (empty(Yii::app()->session['adminlang'])) {
            Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");
        }
        Yii::app()->setLanguage(Yii::app()->session["adminlang"]);
    }

    /**
     * Checks for action specific authorization and then executes an action
     *
     * REFACTORED ( in LSBaseController)
     *
     * @access public
     * @param string $action
     * @return boolean|null
     */
    public function run($action)
    {
        // Check if the DB is up to date
        if (Yii::app()->db->schema->getTable('{{surveys}}')) {
            $sDBVersion = getGlobalSetting('DBVersion');
        }
        if ((int) $sDBVersion < Yii::app()->getConfig('dbversionnumber') && $action != 'databaseupdate') {
            // Try a silent update first
            Yii::app()->loadHelper('update.updatedb');
            if (!db_upgrade_all(intval($sDBVersion), true)) {
                $this->redirect(array('/admin/databaseupdate/sa/db'));
            }
        }


        if ($action != "databaseupdate" && $action != "db") {
            if (empty($this->user_id) && $action != "authentication" && $action != "remotecontrol") {
                if (!empty($action) && $action != 'index') {
                                    Yii::app()->session['redirect_after_login'] = $this->createUrl('/');
                }

                App()->user->setReturnUrl(App()->request->requestUri);

                // If this is an ajax call, don't redirect, but echo login modal instead
                $isAjax = Yii::app()->request->isAjaxRequest;
                if ($isAjax && Yii::app()->user->getIsGuest()) {
                    http_response_code(401);
                    Yii::import('application.helpers.admin.ajax_helper', true);
                    ls\ajax\AjaxHelper::outputNotLoggedIn();
                    return;
                }

                $this->redirect(array('/admin/authentication/sa/login'));
            } elseif (!empty($this->user_id) && $action != "remotecontrol") {
                if (Yii::app()->session['session_hash'] != hash('sha256', getGlobalSetting('SessionName') . Yii::app()->user->getName() . Yii::app()->user->getId())) {
                    Yii::app()->session->clear();
                    Yii::app()->session->close();
                    $this->redirect(array('/admin/authentication/sa/login'));
                }
            }
        }

        $this->runModuleController($action);
        // this will redirect the default action to the new controller previously "admin/index" or "admin" to "dashboard/view"
        if (empty($action) || $action === 'index') {
            $this->redirect($this->createUrl('dashboard/view'));
        }
        return parent::run($action);
    }

    /**
     * Starting with LS4, 3rd party developer can extends any of the LimeSurve controllers.
     *
     *  REFACTORED ( in LSBaseController)
     *
     */
    protected function runModuleController($action)
    {
        $aOverridenCoreActions = $this->getOverridenCoreAction();
        if (!empty($aOverridenCoreActions)) {
            if (!empty($aOverridenCoreActions[$action])) {
                $this->currentModuleAction = $action; // For subviews rendering, see: AdminController::renderPartial()

                // Since module's class has the same name has core class, we need to load the core and module classes with namespace
                Yii::import('application\\controllers\\admin\\' . $action, true);
                $sActionModuleClass = 'lsadminmodules\\' . $action . '\controller\\' . $action;
                Yii::import($sActionModuleClass, true);
            }
        }
    }


    /**
     * If a module override the views of a controller, renderPartial needs to check module view directories.
     * This work recusively with infinite depth of subdirectories.
     *
     * @param string $view name of the view to be rendered. See {@link getViewFile} for details
     * about how the view script is resolved.
     * @param array $data data to be extracted into PHP variables and made available to the view script
     * @param boolean $return whether the rendering result should be returned instead of being displayed to end users
     * @param boolean $processOutput whether the rendering result should be postprocessed using {@link processOutput}.
     * @return string the rendering result. Null if the rendering result is not required.
     * @throws CException if the view does not exist
     * @see getViewFile
     * @see processOutput
     * @see render
     */
    public function renderPartial($view, $data = null, $return = false, $processOutput = false)
    {
        if (!empty($this->currentModuleAction)) {
          // Standard: the views are stored in a folder that has the same name as the controler file.
          // TODO: check if it is the case for all controllers, if not normalize it, so 3rd party coder can easily extend any LS Core controller/action/view.
            $sParsedView = explode(DIRECTORY_SEPARATOR, $view);
            $sAction = (empty($sParsedView[1])) ? '' : $sParsedView[1];

          // We allow a module to override only the controller views.
            if ($sAction == $this->currentModuleAction) {
              // Convert the view path to module view alias .
                $sModulePath = 'lsadminmodules.' . $sAction . '.views' . substr(ltrim(str_replace(DIRECTORY_SEPARATOR, '.', $view), '.'), strlen($sAction)) ;

                if (file_exists(\Yii::getPathOfAlias($sModulePath) . '.php')) {
                    $view = $sModulePath;
                }
            }
        }

        return parent::renderPartial($view, $data, $return, $processOutput);
    }

    /**
     * Routes all the actions to their respective places
     *
     * todo REFACTORING we don't have to refactore this method ...
     *
     * @access public
     * @return array
     */
    public function actions()
    {
        $aActions = $this->getActionClasses();

        // In the normal LS workflow, action classes are located under the application/controllers/admin/
        foreach ($aActions as $action => $class) {
            $aActions[$action] = "application.controllers.admin.{$class}";
        }

        // But now, they can be in a module added by a third pary developer.
        $aModuleActions = $this->getModulesActions();

        // We keep a trace of the overridden actions and their path. It will be used in the rendering logic (SurveyCommonAction, renderPartial, etc)
        foreach ($aModuleActions as $sAction => $sActionClass) {
          // Module override existing action
            if (!empty($aActions[$sAction])) {
                $this->aOverridenCoreActions[ $sAction ]['core']   =   $aActions[$sAction];
                $this->aOverridenCoreActions[ $sAction ]['module'] =   $aModuleActions[$sAction];
            }
        }

        $aActions = array_merge($aActions, $aModuleActions);
        return $aActions;
    }

    /**
     * This function is very similiar to AdminController::actions()
     * Routes all the modules actions to their respective places
     *
     * todo REFACTORING we don't have to refactore this method ...
     *
     * @access public
     * @return array
     */
    public function getModulesActions()
    {
        $aActions = $this->getAdminModulesActionClasses();
        $aAdminModulesClasses = array();

      // lsadminmodules alias is defined in AdminController::init()
      // Notice that the file and the directory name must be the same.
        foreach ($aActions as $action => $class) {
            $aActions[$action] = 'lsadminmodules\\' . $action . '\controller\\' . $action;
        }

        return $aActions;
    }

    /**
     * Return the list of overridden actions from modules, and generate it if needed
     *
     * REFACTORED ( in LSYiiController)
     *
     * @return array
     */
    protected function getOverridenCoreAction()
    {
        if (empty($this->aOverridenCoreActions)) {
            $this->actions();
        }

        return $this->aOverridenCoreActions;
    }

    public function getActionClasses()
    {
        return [
            'authentication'   => 'Authentication',
            'checkintegrity'   => 'CheckIntegrity',
            'conditions'       => 'ConditionsAction',
            'database'         => 'Database',
            'databaseupdate'   => 'DatabaseUpdate',
            'dataentry'        => 'DataEntry',
            'dumpdb'           => 'dumpdb',
            'emailtemplates'   => 'EmailTemplates',
            'export'           => 'Export',
            'expressions'      => 'Expressions',
            'validate'         => 'ExpressionValidate',
            'globalsettings'   => 'GlobalSettings',
            'htmleditorpop'    => 'HtmlEditorPop',
            'surveysgroups'    => 'SurveysGroupsController',
            'limereplacementfields' => 'limereplacementfields',
            'labels'           => 'Labels',
            'participants'     => 'ParticipantsAction',
            'pluginmanager'    => 'PluginManagerController',
            'printablesurvey'  => 'PrintableSurvey',
            'questionthemes'   => 'QuestionThemes',
            'quotas'           => 'Quotas',
            'remotecontrol'    => 'RemoteControl',
            'saved'            => 'Saved',
            'statistics'       => 'Statistics',
            'surveypermission' => 'SurveyPermission',
            'user'             => 'UserAction',
            'themes'           => 'Themes',
            'tokens'           => 'Tokens',
            'translate'        => 'Translate',
            'update'           => 'Update',
            'pluginhelper'     => 'PluginHelper',
            'notification'     => 'NotificationController',
            'menus'            => 'SurveymenuController',
            'menuentries'      => 'SurveymenuEntryController',
            'tutorials'        => 'TutorialsController',
            'tutorialentries'  => 'TutorialEntryController',
            'extensionupdater' => 'ExtensionUpdaterController',
        ];
    }

    /**
     * This function returns an array similar to getActionClasses()
     * It will generate it by reading the directories names inside of lsadminmodulesrootdir
     * So, by convention, admin module action class must be indentical to directory name
     *
     */
    public function getAdminModulesActionClasses()
    {

      // This function is called at least twice by page load. Once from AdminController, another one by SurveyCommonAction
        if (empty($this->aAdminModulesClasses)) {
            $aAdminModulesClasses = array();
            $slsadminmodules = new DirectoryIterator(Yii::app()->getConfig('lsadminmodulesrootdir'));
            Yii::setPathOfAlias('lsadminmodules', Yii::app()->getConfig('lsadminmodulesrootdir'));

            foreach ($slsadminmodules as $fileinfo) {
                if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                    $sModuleName =  $fileinfo->getFilename();
                    $aAdminModulesClasses[$sModuleName] = $sModuleName;
                }
            }
            $this->aAdminModulesClasses = $aAdminModulesClasses;
        }

        return $this->aAdminModulesClasses;
    }

    /**
     * Prints Admin Header
     *
     * REFACTORED (in LayoutHelper.php)
     *
     * @access protected
     * @param bool $meta
     * @param bool $return
     * @return string|null
     */
    public function getAdminHeader($meta = false, $return = false)
    {
        if (empty(Yii::app()->session['adminlang'])) {
            Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");
        }

        $aData = array();
        $aData['adminlang'] = Yii::app()->language;
        $aData['languageRTL'] = "";
        $aData['styleRTL'] = "";
        Yii::app()->loadHelper("surveytranslator");

        if (getLanguageRTL(Yii::app()->language)) {
            $aData['languageRTL'] = " dir=\"rtl\" ";
            $aData['bIsRTL'] = true;
        } else {
            $aData['languageRTL'] = " dir=\"ltr\" ";
            $aData['bIsRTL'] = false;
        }

        $aData['meta'] = "";
        if ($meta) {
            $aData['meta'] = $meta;
        }

        $aData['baseurl'] = Yii::app()->baseUrl . '/';
        $aData['datepickerlang'] = "";

        $aData['sitename'] = Yii::app()->getConfig("sitename");

        if (!empty(Yii::app()->session['dateformat'])) {
                    $aData['formatdata'] = getDateFormatData(Yii::app()->session['dateformat']);
        }

        // Register admin theme package with asset manager
        $oAdminTheme = AdminTheme::getInstance();

        $aData['sAdmintheme'] = $oAdminTheme->name;
        $aData['aPackageScripts'] = $aData['aPackageStyles'] = array();

            //foreach ($aData['aPackageStyles'] as &$filename)
            //{
                //$filename = str_replace('.css', '-rtl.css', $filename);
            //}

        $sOutput = $this->renderPartial("/admin/super/header", $aData, true);

        if ($return) {
            return $sOutput;
        } else {
            echo $sOutput;
        }
    }

    /**
     * Prints Admin Footer
     *
     * REFACTORED (in LayoutHelper)
     *
     * @access protected
     * @param string $url
     * @param string $explanation
     * @param bool $return
     * @return string|null
     */
    public function getAdminFooter($url, $explanation, $return = false)
    {
        $aData['versionnumber'] = Yii::app()->getConfig("versionnumber");

        $aData['buildtext'] = "";
        if (Yii::app()->getConfig("buildnumber") != "") {
            $aData['buildtext'] = "+" . Yii::app()->getConfig("buildnumber");
        }

        //If user is not logged in, don't print the version number information in the footer.
        if (empty(Yii::app()->session['loginID'])) {
            $aData['versionnumber'] = "";
            $aData['versiontitle'] = "";
            $aData['buildtext'] = "";
        } else {
            $aData['versiontitle'] = gT('Version');
        }

        $aData['imageurl'] = Yii::app()->getConfig("imageurl");
        $aData['url'] = $url;
        return $this->renderPartial("/admin/super/footer", $aData, $return);
    }

    /**
     * Shows a message box
     *
     * REFACTORED ( in LayoutHelper.php )
     *
     * @access public
     * @param string $title
     * @param string $message
     * @param string $class
     * @param boolean $return
     * @return string|null
     */
    public function showMessageBox($title, $message, $class = "message-box-error", $return = false)
    {
        $aData['title'] = $title;
        $aData['message'] = $message;
        $aData['class'] = $class;
        return $this->renderPartial('/admin/super/messagebox', $aData, $return);
    }


    /**
     *
     * REFACTORED (in LayoutHelper.php)
     *
     * @return bool|string
     * @throws CException
     */
    public function loadEndScripts()
    {
        static $bRendered = false;
        if ($bRendered) {
                    return true;
        }
        $bRendered = true;
        if (empty(Yii::app()->session['metaHeader'])) {
                    Yii::app()->session['metaHeader'] = '';
        }

        unset(Yii::app()->session['metaHeader']);

        return $this->renderPartial('/admin/endScripts_view', array());
    }
}
