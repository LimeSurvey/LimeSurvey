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
    //todo: this variable should go to the questioneditor controller when refactoring it ...no need to declare it here
    /** @var null  this is needed for the preview rendering inside the questioneditor */
    public $sTemplate = null;

    /** @var array  import for all new controllers/actions (REFACTORING) to pass data before rendering the content*/
    public $aData = [];

    /** @var int userId of the logged in user */
    protected $userId = 0; //todo: do we really need this here ?? why?

    /**
     * Initialises this controller, does some basic checks and setups
     *
     * @access protected
     * @return void
     * @throws CException
     */
    protected function customInit()
    {
        parent::customInit();

        //REFACTORING we have to set the main layout here (it's in /view/layouts/main)
        $this->layout = 'main';

        App()->getComponent('bootstrap');
        $this->sessionControl();

        $this->userId = Yii::app()->user->getId();

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
        AdminTheme::getInstance();

        Yii::setPathOfAlias('lsadminmodules', Yii::app()->getConfig('lsadminmodulesrootdir'));
    }

    /**
     * This part comes from renderWrappedTemplate (not the best way to refactoring, but a temporary solution)
     *
     * todo REFACTORING find all actions that set $aData['surveyid'] and change the layout directly in the action
     *
     * @param string $view
     * @return bool
     */
    protected function beforeRender($view)
    {
        return parent::beforeRender($view);
    }

    /**
     * Checks for action specific authorization and then executes an action
     *
     * TODO: check the dbupdate mechanism, do we really want to check db update before every action??
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
        $sDBVersion = getGlobalSetting('DBVersion');

        if ((int) $sDBVersion < Yii::app()->getConfig('dbversionnumber') && $action != 'databaseupdate') {
            // Try a silent update first
            Yii::app()->loadHelper('update/updatedb');
            if (!db_upgrade_all(intval($sDBVersion), true)) {
                $this->redirect(array('/admin/databaseupdate/sa/db'));
            }
        }

        if ($action != "databaseupdate" && $action != "db") {
            if (empty($this->userId) && $action != "authentication" && $action != "remotecontrol") {
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
            } elseif (!empty($this->userId) && $action != "remotecontrol") {
                /** @var LSUserIdentity */
                $user = Yii::app()->user;
                /** @var string */
                $hash = hash('sha256', getGlobalSetting('SessionName') . $user->getName() . $user->getId());
                if (Yii::app()->session['session_hash'] != $hash) {
                    Yii::app()->session->clear();
                    Yii::app()->session->close();
                    $this->redirect(array('/admin/authentication/sa/login'));
                }
            }
        }

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
    protected function sessionControl()
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
     * Method to render an array as a json document
     * (this one called by a lot of actions in different controllers)
     *
     * @param array $aData
     * @return void
     */
    protected function renderJSON($aData, $success = true)
    {
        $aData['success'] = $aData['success'] ?? $success;

        if (Yii::app()->getConfig('debug') > 0) {
            $aData['debug'] = [$_POST, $_GET];
        }

        echo Yii::app()->getController()->renderPartial('/admin/super/_renderJson', [
            'data' => $aData
        ], true, false);
        return;
    }
}
