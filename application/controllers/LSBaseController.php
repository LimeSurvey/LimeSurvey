<?php

/**
 * Class LSBaseController
 *
 * this controller will have all the necessary methods from the old AdminController
 *
 *
 */
class LSBaseController extends LSYii_Controller
{

    public $sTemplate = null; // this is needed for the preview rendering inside the questioneditor
    public $layout = false;
    //public $aAdminModulesClasses = array();
    protected $user_id = 0;
    //protected $aOverridenCoreActions = array(); // Contains the list of controller's actions overriden by custom modules
   // protected $currentModuleAction = '';        // Name of the current action overriden by a custom module

    // import for all new controllers/actions (REFACTORING) to pass data before rendering the content
    public $aData = [];

    /**
     * Initialises this controller, does some basic checks and setups
     *
     * @access protected
     * @return void
     * @throws CException
     */
    protected function _init()
    {
        parent::_init();

        //REFACTORING we have to set the main layout here (it's in /view/layouts/main)
        $this->layout = 'main';

        App()->getComponent('bootstrap');
        $this->_sessioncontrol();

        $this->user_id = Yii::app()->user->getId();

        if (!Yii::app()->getConfig("surveyid")) {Yii::app()->setConfig("surveyid", returnGlobal('sid')); }         //SurveyID
        if (!Yii::app()->getConfig("surveyID")) {Yii::app()->setConfig("surveyID", returnGlobal('sid')); }         //SurveyID
        if (!Yii::app()->getConfig("ugid")) {Yii::app()->setConfig("ugid", returnGlobal('ugid')); }                //Usergroup-ID
        if (!Yii::app()->getConfig("gid")) {Yii::app()->setConfig("gid", returnGlobal('gid')); }                   //GroupID
        if (!Yii::app()->getConfig("qid")) {Yii::app()->setConfig("qid", returnGlobal('qid')); }                   //QuestionID
        if (!Yii::app()->getConfig("lid")) {Yii::app()->setConfig("lid", returnGlobal('lid')); }                   //LabelID
        if (!Yii::app()->getConfig("code")) {Yii::app()->setConfig("code", returnGlobal('code')); }                // ??
        if (!Yii::app()->getConfig("action")) {Yii::app()->setConfig("action", returnGlobal('action')); }          //Desired action
        if (!Yii::app()->getConfig("subaction")) {Yii::app()->setConfig("subaction", returnGlobal('subaction')); } //Desired subaction
        if (!Yii::app()->getConfig("editedaction")) {Yii::app()->setConfig("editedaction", returnGlobal('editedaction')); } // for html editor integration

        // This line is needed for template editor to work
        $oAdminTheme = AdminTheme::getInstance();

        Yii::setPathOfAlias('lsadminmodules', Yii::app()->getConfig('lsadminmodulesrootdir') );
    }

    /**
     * This part comes from _renderWrappedTemplate (not the best way to refactoring, but a temporary solution)
     *
     * todo REFACTORING find all actions that set $aData['surveyid'] and change the layout directly in the action
     *
     * @param string $view
     * @return bool
     */
    protected function beforeRender($view)
    {
        //this lines come from _renderWarppedTemplate
        //todo: this should be moved to the new questioneditor controller when it is being refactored
        if (!empty($aData['surveyid'])) {
            $aData['oSurvey'] = Survey::model()->findByPk($aData['surveyid']);

            // Needed to evaluate EM expressions in question summary
            // See bug #11845
            LimeExpressionManager::SetSurveyId($aData['surveyid']);
            LimeExpressionManager::StartProcessingPage(false, true);

            $basePath = (string) Yii::getPathOfAlias('application.views.admin.super');
            $this->layout = $basePath.'/layout_insurvey.php';
        }

        return parent::beforeRender($view);
    }

    /**
     * Checks for action specific authorization and then executes an action
     *
     * @access public
     * @param string $action
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function run($action)
    {
        // Check if the DB is up to date
        if (Yii::app()->db->schema->getTable('{{surveys}}')) {
            $sDBVersion = getGlobalSetting('DBVersion');
        }
        if ((int) $sDBVersion < Yii::app()->getConfig('dbversionnumber') && $action != 'databaseupdate') {
            // Try a silent update first
            Yii::app()->loadHelper('update/updatedb');
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
                $isAjax = isset($_GET['ajax']) && $_GET['ajax'];
                if ($isAjax && Yii::app()->user->getIsGuest()) {
                    Yii::import('application.helpers.admin.ajax_helper', true);
                    ls\ajax\AjaxHelper::outputNotLoggedIn();
                    return;
                }

                $this->redirect(array('/admin/authentication/sa/login'));
            } elseif (!empty($this->user_id) && $action != "remotecontrol") {
                if (Yii::app()->session['session_hash'] != hash('sha256',
                        getGlobalSetting('SessionName').Yii::app()->user->getName().Yii::app()->user->getId())) {
                    Yii::app()->session->clear();
                    Yii::app()->session->close();
                    $this->redirect(array('/admin/authentication/sa/login'));
                }
            }
        }

        //todo REFACTORING why do we need this here ...it's for modules created by external developers to write modules
        // ...and modules should be written also the normal yii-way after refactoring all controllers
     //   $this->runModuleController($action);

        parent::run($action);
    }

    /**
     * Load and set session vars
     *
     * todo REFACTORING see comments in mehtod
     *
     * @access protected
     * @return void
     */
    protected function _sessioncontrol()
    {
        // From personal settings

        //todo this should go into specific controller action (atm /admin/user/sa/personalsettings)
        if (Yii::app()->request->getPost('action') == 'savepersonalsettings') {
            if (Yii::app()->request->getPost('lang') == 'auto') {
                $sLanguage = getBrowserLanguage();
            } else {
                $sLanguage = sanitize_languagecode(Yii::app()->request->getPost('lang'));
            }
            Yii::app()->session['adminlang'] = $sLanguage;
        }
        //todo end

        //todo this should be done only once per session and not everytime calling an action ...
        if (empty(Yii::app()->session['adminlang'])) {
            Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");
        }
        Yii::app()->setLanguage(Yii::app()->session["adminlang"]);
        //todo end
    }

    /**
     * Starting with LS4, 3rd party developper can extends any of the LimeSurvey controllers.
     *
     */
    /*
    protected function runModuleController($action)
    {
        $aOverridenCoreActions = $this->getOverridenCoreAction();
        if (!empty($aOverridenCoreActions)){
            if (!empty($aOverridenCoreActions[$action])){
                $this->currentModuleAction = $action; // For subviews rendering, see: AdminController::renderPartial()

                // Since module's class has the same name has core class, we need to load the core and module classes with namespace
                Yii::import('application\\controllers\\admin\\'.$action, true);
                $sActionModuleClass = 'lsadminmodules\\'.$action.'\controller\\'.$action;
                Yii::import($sActionModuleClass, true);
            }
        }
    }*/

    /**
     * Return the list of overriden actions from modules, and generate it if needed
     * @return array
     */
    /*
    protected function getOverridenCoreAction()
    {
        if (empty($this->aOverridenCoreActions)){
            $this->actions();
        }

        return $this->aOverridenCoreActions;
    }*/

}
