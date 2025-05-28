<?php

/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

use LimeSurvey\Menu\Menu;
use LimeSurvey\Menu\MenuItem;

/**
* Survey Common Action
*
* This controller contains common functions for survey related views.
*
* @package        LimeSurvey
* @subpackage    Backend
* @author        LimeSurvey Team
* @method        void index()
*/
class SurveyCommonAction extends CAction
{
    public function __construct($controller = null, $id = null)
    {
        parent::__construct($controller, $id);
        Yii::app()->request->updateNavigationStack();
        // Make sure viewHelper can be autoloaded
        Yii::import('application.helpers.viewHelper');
    }

    /**
     * Override runWithParams() implementation in CAction to help us parse
     * requests with subactions.
     *
     * @param array $params URL Parameters
     * @return bool
     */
    public function runWithParams($params)
    {
        // Default method that would be called if the subaction and run() do not exist
        $sDefault = 'index';
        // Check for a subaction
        if (empty($params['sa'])) {
            $sSubAction = $sDefault; // default
        } else {
            $sSubAction = $params['sa'];
        }
        // Check if the class has the method
        $oClass = new ReflectionClass($this);
        if (!$oClass->hasMethod($sSubAction)) {
            // If it doesn't, revert to default Yii method, that is run() which should reroute us somewhere else
            $sSubAction = 'run';
        }

        // Populate the params. eg. surveyid -> iSurveyId
        $params = $this->addPseudoParams($params);

        if (!empty($params['iSurveyId'])) {
            LimeExpressionManager::SetSurveyId($params['iSurveyId']); // must be called early - it clears internal cache if a new survey is being used
        }
        // Check if the method is public and of the action class, not its parents
        // ReflectionClass gets us the methods of the class and parent class
        // If the above method existence check passed, it might not be neceessary that it is of the action class
        $oMethod  = new ReflectionMethod($this, $sSubAction);

        // Get the action classes from the admin controller as the urls necessarily do not equal the class names. Eg. survey -> surveyaction
        // Merges it with actions from admin modules
        $aActions = array_merge(App()->getController()->getActionClasses(), Yii::app()->getController()->getAdminModulesActionClasses());

        if (empty($aActions[$this->getId()]) || strtolower($oMethod->getDeclaringClass()->name) != strtolower((string) $aActions[$this->getId()]) || !$oMethod->isPublic()) {
            // Either action doesn't exist in our allowlist, or the method class doesn't equal the action class or the method isn't public
            // So let us get the last possible default method, ie. index
            $oMethod = new ReflectionMethod($this, $sDefault);
        }

        // We're all good to go, let's execute it
        // runWithParamsInternal would automatically get the parameters of the method and populate them as required with the params
        return parent::runWithParamsInternal($this, $oMethod, $params);
    }

    /**
     * Some functions have different parameters, which are just an alias of the
     * usual parameters we're getting in the url. This function just populates
     * those variables so that we don't end up in an error.
     *
     * This is also used while rendering wrapped template
     * {@link SurveyCommonAction::renderWrappedTemplate()}
     *
     * @param array $params Parameters to parse and populate
     * @return array Populated parameters
     * @throws CHttpException
     */
    private function addPseudoParams($params)
    {
        // Return if params isn't an array
        if (empty($params) || !is_array($params)) {
            return $params;
        }

        $pseudos = array(
            'id' => 'iId',
            'gid' => 'iGroupId',
            'qid' => 'iQuestionId',
            /* priority is surveyid,surveyId,sid : surveyId=1&sid=2 set iSurveyId to 1 */
            'sid' => array('iSurveyId', 'iSurveyID', 'surveyid'), // Old link use sid
            'surveyId' => array('iSurveyId', 'iSurveyID', 'surveyid'), // PluginHelper->sidebody : if disable surveyId usage : broke API
            'surveyid' => array('iSurveyId', 'iSurveyID', 'surveyid'),
            'srid' => 'iSurveyResponseId',
            'scid' => 'iSavedControlId',
            'uid' => 'iUserId',
            'ugid' => 'iUserGroupId',
            'fieldname' => 'sFieldName',
            'fieldtext' => 'sFieldText',
            'action' => 'sAction',
            'lang' => 'sLanguage',
            'browseLang' => 'sBrowseLang',
            'tokenids' => 'aTokenIds',
            'tokenid' => 'iTokenId',
            'subaction' => 'sSubAction', // /!\ Already filled by sa : can be different (usage of subaction in quota at 2019-09-04)
        );
        // Foreach pseudo, take the key, if it exists,
        // Populate the values (taken as an array) as keys in params
        // with that key's value in the params
        // Chek is 2 params are equal for security issue.
        foreach ($pseudos as $key => $pseudo) {
            // We care only for user parameters, not by code parameters (see issue #15221)
            if ($checkParam = Yii::app()->getRequest()->getParam($key)) {
                $pseudo = (array) $pseudo;
                foreach ($pseudo as $pseud) {
                    if (empty($params[$pseud])) {
                        $params[$pseud] = $checkParam;
                    } elseif ($params[$pseud] != $checkParam) {
                        // Throw error about multiple params (and if they are different) #15204
                        throw new CHttpException(403, sprintf(gT("Invalid parameter %s (%s already set)"), $pseud, $key));
                    }
                }
            }
        }

        /* Control sid,gid and qid params validity see #12434 */
        // Fill param with according existing param, replace existing parameters.
        // iGroupId/gid can be found with qid/iQuestionId
        if (!empty($params['iQuestionId'])) {
            if ((string) (int) $params['iQuestionId'] !== (string) $params['iQuestionId']) {
                // pgsql need filtering before find
                throw new CHttpException(403, gT("Invalid question id"));
            }
            $oQuestion = Question::model()->find("qid=:qid", array(":qid" => $params['iQuestionId'])); //Move this in model to use cache
            if (!$oQuestion) {
                throw new CHttpException(404, gT("Question not found"));
            }
            if (!isset($params['iGroupId'])) {
                $params['iGroupId'] = $params['gid'] = $oQuestion->gid;
            }
        }
        // iSurveyId/iSurveyID/sid can be found with gid/iGroupId
        if (!empty($params['iGroupId'])) {
            if ((string) (int) $params['iGroupId'] !== (string) $params['iGroupId']) {
                // pgsql need filtering before find
                throw new CHttpException(403, gT("Invalid group ID"));
            }
            $oGroup = QuestionGroup::model()->find("gid=:gid", array(":gid" => $params['iGroupId'])); //Move this in model to use cache
            if (!$oGroup) {
                throw new CHttpException(404, gT("Group not found"));
            }
            if (!isset($params['iSurveyId'])) {
                $params['iSurveyId'] = $params['iSurveyID'] = $params['surveyid'] = $params['sid'] = $oGroup->sid;
            }
        }
        // Finally control validity of sid
        if (!empty($params['iSurveyId'])) {
            if ((string) (int) $params['iSurveyId'] !== (string) $params['iSurveyId']) {
                // pgsql need filtering before find
                // 403 mean The request was valid, but the server is refusing action.
                throw new CHttpException(403, gT("Invalid survey ID"));
            }
            $oSurvey = Survey::model()->findByPk($params['iSurveyId']);
            if (!$oSurvey) {
                throw new CHttpException(404, gT("Survey not found"));
            }
            // Minimal permission needed, extra permission must be tested in each controller
            if (!Permission::model()->hasSurveyPermission($params['iSurveyId'], 'survey', 'read')) {
                // 403 mean (too) The user might not have the necessary permissions for a resource.
                // 401 semantically means "unauthenticated"
                throw new CHttpException(403);
            }
            $params['iSurveyId'] = $params['iSurveyID'] = $params['surveyid'] = $params['sid'] = $oSurvey->sid;
        }
        // Finally return the populated array
        return $params;
    }

    /**
     * Action classes require them to have a run method. We reroute it to index
     * if called.
     */
    public function run()
    {
        $this->index();
    }

    /**
     * Routes the action into correct subaction
     *
     * @access protected
     * @param string $sa
     * @param string[] $get_vars
     * @return mixed
     */
    protected function route($sa, array $get_vars)
    {
        $func_args = array();
        foreach ($get_vars as $k => $var) {
                    $func_args[$k] = Yii::app()->request->getQuery($var);
        }

        return call_user_func_array(array($this, $sa), $func_args);
    }

    /**
     * @inheritdoc
     * @param string $_viewFile_
     */
    public function renderInternal($_viewFile_, $_data_ = null, $_return_ = false)
    {
        // we use special variable names here to avoid conflict when extracting data
        if (is_array($_data_)) {
            extract($_data_, EXTR_PREFIX_SAME, 'data');
        } else {
            $data = $_data_;
        }

        if ($_return_) {
            ob_start();
            ob_implicit_flush(0);
            require($_viewFile_);
            return ob_get_clean();
        } else {
            require($_viewFile_);
        }
    }

    /**
     * Rendering the subviews and views of renderWrappedTemplate
     *
     * @param string $sAction
     * @param array|string $aViewUrls
     * @param array $aData
     * @return string
     */
    protected function renderCentralContents($sAction, $aViewUrls, $aData = [])
    {

        //// This will be handle by subviews inclusions
        $aViewUrls = (array) $aViewUrls;
        $sViewPath = '/admin/';
        if (!empty($sAction)) {
                    $sViewPath .= $sAction . '/';
        }
        //TODO : while refactoring, we must replace the use of $aViewUrls by $aData[.. conditions ..],
        //todo and then call to function such as $this->nsurveysummary($aData);
        // Load views
        $content = "";

        foreach ($aViewUrls as $sViewKey => $viewUrl) {
            if (empty($sViewKey) || !in_array($sViewKey, array('message', 'output'))) {
                if (is_numeric($sViewKey)) {
                    $content .= Yii::app()->getController()->renderPartial($sViewPath . $viewUrl, $aData, true);
                } elseif (is_array($viewUrl)) {
                    foreach ($viewUrl as $aSubData) {
                        $aSubData = array_merge($aData, $aSubData);
                        $content .= Yii::app()->getController()->renderPartial($sViewPath . $sViewKey, $aSubData, true);
                    }
                }
            } else {
                switch ($sViewKey) {
                    // We'll use some Bootstrap alerts, and call them inside each correct view.
                    // Message
                    case 'message':
                        if (empty($viewUrl['class'])) {
                            $content .= Yii::app()->getController()->showMessageBox($viewUrl['title'], $viewUrl['message'], null, true);
                        } else {
                            $content .= Yii::app()->getController()->showMessageBox($viewUrl['title'], $viewUrl['message'], $viewUrl['class'], true);
                        }
                        break;

                        // Output
                    case 'output':
                        //// TODO : http://goo.gl/ABl5t5
                        $content .= $viewUrl;

                        if (isset($aViewUrls['afteroutput'])) {
                            $content .= $aViewUrls['afteroutput'];
                        }
                        break;
                }
            }
        }
        return $content;
    }

    /**
     * Load menu bar of user group controller.
     *
     * REFACTORED (it's in UserGroupController and uses function from Layouthelper->renderMenuBar())
     *
     * @param array $aData
     * @return void
     */
    /*
    public function userGroupBar(array $aData)
    {
        $ugid = $aData['ugid'] ?? 0;
        if (!empty($aData['display']['menu_bars']['user_group'])) {
            $data = $aData;
            Yii::app()->loadHelper('database');

            if (!empty($ugid)) {
                $userGroup = UserGroup::model()->findByPk($ugid);
                $uid = Yii::app()->session['loginID'];
                if (($userGroup && ($userGroup->hasUser($uid)) || $userGroup->owner_id == $uid) || Permission::model()->hasGlobalPermission('superadmin')) {
                    $data['userGroup'] = $userGroup;
                } else {
                    $data['userGroup'] = null;
                }
            }

            $data['imageurl'] = Yii::app()->getConfig("adminimageurl");

            if (isset($aData['usergroupbar']['closebutton']['url'])) {
                $sAlternativeUrl = $aData['usergroupbar']['closebutton']['url'];
                $aData['usergroupbar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer(Yii::app()->createUrl($sAlternativeUrl));
            }

            $this->getController()->renderPartial('/admin/usergroup/usergroupbar_view', $data);
        }
    } */

    /**
     * Renders template(s) wrapped in header and footer
     *
     * Addition of parameters should be avoided if they can be added to $aData
     *
     * NOTE FROM LOUIS : We want to remove this function, which doesn't respect MVC pattern.
     * The work it's doing should be handle by layout files, and subviews inside views.
     * Eg : for route "admin/survey/sa/listquestiongroups/surveyid/282267"
     *       the Group controller should use a main layout (with admin menu bar as a widget), then render the list view, in which the question group bar is called as a subview.
     *
     * So for now, we try to evacuate all the renderWrappedTemplate logic (if statements, etc.)
     * to subfunctions, then it will be easier to remove.
     * Comments starting with //// indicate how it should work in the future
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param array|string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     * @param string|boolean $sRenderFile File to be rendered as a layout. Optional.
     * @throws CHttpException
     */
    protected function renderWrappedTemplate($sAction = '', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        // Gather the data

        // This call 2 times addPseudoParams because it's already done in runWithParams : why ?
        $aData = $this->addPseudoParams($aData);

        $basePath = (string) Yii::getPathOfAlias('application.views.admin.super');

        if ($sRenderFile == false) {
            if (!empty($aData['surveyid'])) {
                //todo REFACTORING this should be moved into LSBaseController->beforeRender()
                $aData['oSurvey'] = Survey::model()->findByPk($aData['surveyid']);

                // Needed to evaluate EM expressions in question summary
                // See bug #11845
                LimeExpressionManager::SetSurveyId($aData['surveyid']);
                LimeExpressionManager::StartProcessingPage(false, true);

                // If 'landOnSideMenuTab' is not set already, default to 'settings'.
                if (empty($aData['sidemenu']['landOnSideMenuTab'])) {
                    $aData['sidemenu']['landOnSideMenuTab'] = 'settings';
                }

                $renderFile = $basePath . '/layout_insurvey.php';
            } else {
                $renderFile = $basePath . '/layout_main.php';
            }
        } else {
            $renderFile = $basePath . '/' . $sRenderFile;
        }
        $content = $this->renderCentralContents($sAction, $aViewUrls, $aData);
        $out = $this->renderInternal($renderFile, ['content' => $content, 'aData' => $aData], true);

        App()->getClientScript()->render($out);
        echo $out;
    }

    /**
     * Display the update notification
     *
     *
     * REFACTORED (in LayoutHelper.php)
     * @throws CException
     */
    protected function updatenotification()
    {
        // Never use Notification model for database update.
        // TODO: Real fix: No database queries while doing database update, meaning
        // don't call renderWrappedTemplate.
        if (get_class($this) == 'databaseupdate') {
            return;
        }

        if (!Yii::app()->user->isGuest && Yii::app()->getConfig('updatable')) {
            $updateModel = new UpdateForm();
            $updateNotification = $updateModel->updateNotification;

            if ($updateNotification->result) {
                $scriptToRegister = App()->getConfig('packages') . DIRECTORY_SEPARATOR . 'comfort_update' . DIRECTORY_SEPARATOR. 'comfort_update.js';
                App()->getClientScript()->registerScriptFile($scriptToRegister);
                return $this->getController()->renderPartial("/admin/update/_update_notification", array('security_update_available' => $updateNotification->security_update));
            }
        }
    }

    /**
     * Display notifications
     *
     * * REFACTORED (in LayoutHelper.php)
     */
    protected function notifications()
    {
            $aMessage = App()->session['arrayNotificationMessages'];
        if (!is_array($aMessage)) {
            $aMessage = array();
        }
            unset(App()->session['arrayNotificationMessages']);
            return $this->getController()->renderPartial("notifications/notifications", array('aMessage' => $aMessage));
    }

    /**
     *
     * REFACTORED in LayoutHelper
     *
     * Survey summary
     * @param array $aData
     */
    protected function nsurveysummary($aData)
    {
        if (isset($aData['display']['surveysummary'])) {
            if ((empty($aData['display']['menu_bars']['surveysummary']) || !is_string($aData['display']['menu_bars']['surveysummary'])) && !empty($aData['gid'])) {
                $aData['display']['menu_bars']['surveysummary'] = 'viewgroup';
            }
            $this->_surveysummary($aData);
        }
    }

    /**
     * Header
     *
     * * REFACTORED (in LayoutHelper.php)
     *
     * @param array $aData
     */
    protected function showHeaders($aData, $sendHTTPHeader = true)
    {
        if (!isset($aData['display']['header']) || $aData['display']['header'] !== false) {
            // Send HTTP header
            if ($sendHTTPHeader) {
                header("Content-type: text/html; charset=UTF-8"); // needed for correct UTF-8 encoding
            }
            Yii::app()->getController()->getAdminHeader();
        }
    }

    /**
     * showadminmenu() function returns html text for the administration button bar
     *
     * REFACTORED (in LayoutHelper.php)
     *
     * @access public
     * @param $aData
     * @return string
     * @global string $homedir
     * @global string $scriptname
     * @global string $surveyid
     * @global string $setfont
     * @global string $imageurl
     * @global int $surveyid
     */
    protected function showadminmenu($aData)
    {
        // We don't wont the admin menu to be shown in login page
        if (!Yii::app()->user->isGuest) {
            if (!(App()->getConfig('ssl_disable_alert')) && strtolower(App()->getConfig('force_ssl') != 'on') && \Permission::model()->hasGlobalPermission("superadmin")) {
                $not = new UniqueNotification(array(
                    'user_id' => App()->user->id,
                    'importance' => Notification::HIGH_IMPORTANCE,
                    'title' => gT('SSL not enforced'),
                    'message' => '<span class="ri-error-warning-fill"></span>&nbsp;' .
                        gT("Warning: Please enforce SSL encryption in Global settings/Security after SSL is properly configured for your webserver.")
                ));
                $not->save();
            }

            // Count active survey
            $aData['dataForConfigMenu']['activesurveyscount'] = $aData['activesurveyscount'] = Survey::model()->permission(Yii::app()->user->getId())->active()->count();

            // Count survey
            $aData['dataForConfigMenu']['surveyscount'] = Survey::model()->count();

            // Count user
            $aData['dataForConfigMenu']['userscount'] = User::model()->count();

            //Check if have a comfortUpdate key
            if (getGlobalSetting('emailsmtpdebug') != '') {
                $aData['dataForConfigMenu']['comfortUpdateKey'] = gT('Activated');
            } else {
                $aData['dataForConfigMenu']['comfortUpdateKey'] = gT('None');
            }

            $aData['sitename'] = Yii::app()->getConfig("sitename");

            // Fetch extra menus from plugins, e.g. last visited surveys
            $aData['extraMenus'] = $this->fetchExtraMenus($aData);
            //new create process (including survey, survey group, import survey)
            $aData['extraMenus'][] = $this->getCreateMenu();

            // Get notification menu
            $surveyId = $aData['surveyid'] ?? null;
            Yii::import('application.controllers.admin.NotificationController');
            $aData['adminNotifications'] = NotificationController::getMenuWidget($surveyId, true /* show spinner */);

            $this->getController()->renderPartial("/layouts/adminmenu", $aData);
        }
        return null;
    }

    /**
     *
     *
     * @return Menu
     */
    public function getCreateMenu() {
        $menuItemHeader = [
            'isDivider' => false,
            'isSmallText' => true,
            'label' => 'Create new',
            'href' => '#',
            'iconClass' => 'ri-add-line',
        ];
        $menuItems[] = (new MenuItem($menuItemHeader));

        $menuItemNewSurvey = [
            'isDivider' => false,
            'isSmallText' => false,
            'label' => gT('Survey'),
            'href' => \Yii::app()->createUrl('surveyAdministration/createSurvey'),
            'iconClass' => 'ri-add-line',
        ];
        $menuItems[] = (new MenuItem($menuItemNewSurvey));

        $menuItemNewSurvey = [
            'isDivider' => false,
            'isSmallText' => false,
            'label' => gT('Survey group'),
            'href' => \Yii::app()->createUrl('admin/surveysgroups/sa/create'),
            'iconClass' => 'ri-add-circle-line',
        ];
        $menuItems[] = (new MenuItem($menuItemNewSurvey));

        $menuItemNewSurvey = [
            'isDivider' => false,
            'isSmallText' => false,
            'label' => gT('Import survey'),
            'href' => \Yii::app()->createUrl('surveyAdministration/newSurvey'),
            'iconClass' => 'ri-upload-line',
        ];
        $menuItems[] = (new MenuItem($menuItemNewSurvey));

        $options = [
            'label' => '+',
            'iconClass' => 'ri-add-line',
            'isDropDown' => true,
            'isDropDownButton' => true,
            'menuItems' => $menuItems,
            'isPrepended' => true,
        ];

        $createMenu = new Menu($options);

        return $createMenu;
    }

    /**
     * REFACTORED in LayoutHelper.php
     *
     * @param $aData
     * @throws CException
     */
    protected function titlebar($aData)
    {
        if (isset($aData['title_bar'])) {
            $this->getController()->renderPartial("/layouts/title_bar", $aData);
        }
    }

    /**
     * Render the save/cancel bar for Organize question groups/questions
     *
     * REFACTORED in LayoutHelper
     *
     * @param array $aData
     *
     * @since 2014-09-30
     * @author LimeSurvey GmbH
     */
    protected function organizequestionbar($aData)
    {
        if (isset($aData['organizebar'])) {
            if (isset($aData['questionbar']['closebutton']['url'])) {
                $sAlternativeUrl = $aData['questionbar']['closebutton']['url'];
                $aData['questionbar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer(Yii::app()->createUrl($sAlternativeUrl));
            }

            $aData['questionbar'] = $aData['organizebar'];
            $this->getController()->renderPartial("/admin/survey/Question/questionbar_view", $aData);
        }
    }

    /**
     * REFACTORED in LayoutHelper
     *
     * @param $aData
     * @throws CException
     */
    /*
    public function generaltopbar($aData)
    {
        $aData['topBar'] = $aData['topBar'] ?? [];
        $aData['topBar'] = array_merge(
            [
                'type' => 'survey',
                'sid' => $aData['sid'],
                'gid' => $aData['gid'] ?? 0,
                'qid' => $aData['qid'] ?? 0,
                'showSaveButton' => false
            ],
            $aData['topBar']
        );

        $this->getController()->renderPartial("/admin/survey/topbar/topbar_view", $aData);
    }*/

    /**
     * Shows admin menu for question
     *
     * @deprecated not in use anymore
     *
     * @param array $aData
     */
    public function questionbar($aData)
    {
        if (isset($aData['questionbar'])) {
            if (is_object($aData['oSurvey'])) {
                $iSurveyID = $aData['surveyid'];
                /** @var Survey $oSurvey */
                $oSurvey = $aData['oSurvey'];
                $gid = $aData['gid'];
                $qid = $aData['qid'];

                // action
                $action = (!empty($aData['display']['menu_bars']['qid_action'])) ? $aData['display']['menu_bars']['qid_action'] : null;
                $baselang = $oSurvey->language;

                //Show Question Details
                //Count answer-options for this question
                $aData['qct'] = Answer::model()->countByAttributes(array('qid' => $qid));

                //Count subquestions for this question
                $aData['sqct'] = Question::model()->countByAttributes(array('parent_qid' => $qid));

                $qrrow = Question::model()->findByAttributes(array('qid' => $qid, 'gid' => $gid, 'sid' => $iSurveyID));
                if (is_null($qrrow)) {
                    return;
                }
                $questionsummary = "";

                // Check if other questions in the Survey are dependent upon this question
                $condarray = getQuestDepsForConditions($iSurveyID, "all", "all", $qid, "by-targqid", "outsidegroup");

                // $surveyinfo = $oSurvey->attributes;
                // $surveyinfo = array_map('flattenText', $surveyinfo);
                $aData['activated'] = $oSurvey->active;

                $qrrow = $qrrow->attributes;
                $aData['languagelist'] = $oSurvey->getAllLanguages();
                $aData['qtypes'] = Question::typeList();
                $aData['action'] = $action;
                $aData['surveyid'] = $iSurveyID;
                $aData['qid'] = $qid;
                $aData['gid'] = $gid;
                $aData['qrrow'] = $qrrow;
                $aData['baselang'] = $baselang;

                // TODO: Don't call getAdvancedSettingsWithValues without a question object.
                $aAttributesWithValues = Question::model()->getAdvancedSettingsWithValues($qid, $qrrow['type'], $iSurveyID, $baselang);

                $DisplayArray = array();
                foreach ($aAttributesWithValues as $aAttribute) {
                    if (
                        ($aAttribute['i18n'] == false && isset($aAttribute['value']) && $aAttribute['value'] != $aAttribute['default']) ||
                        ($aAttribute['i18n'] == true && isset($aAttribute['value'][$baselang]) && $aAttribute['value'][$baselang] != $aAttribute['default'])
                    ) {
                        if ($aAttribute['inputtype'] == 'singleselect') {
                            if (isset($aAttribute['options'][$aAttribute['value']])) {
                                                            $aAttribute['value'] = $aAttribute['options'][$aAttribute['value']];
                            }
                        }
                        $DisplayArray[] = $aAttribute;
                    }
                }

                $aData['advancedsettings'] = $DisplayArray;
                $aData['condarray'] = $condarray;
                if (isset($aData['questionbar']['closebutton']['url'])) {
                    $sAlternativeUrl = $aData['questionbar']['closebutton']['url'];
                    $aData['questionbar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer(Yii::app()->createUrl($sAlternativeUrl));
                }
                $questionsummary .= $this->getController()->renderPartial('/admin/survey/Question/questionbar_view', $aData, true);
                $this->getController()->renderPartial('/survey_view', ['display' => $questionsummary]);
            } else {
                Yii::app()->session['flashmessage'] = gT("Invalid survey ID");
                $this->getController()->redirect(array("dashboard/view"));
            }
        }
    }

    /**
     * Shows admin menu for surveys
     *
     * @param array $aData
     * @deprecated
     */
    public function surveybar($aData)
    {
        if ((isset($aData['surveybar']))) {
            $iSurveyID = $aData['surveyid'];
            /** @var Survey $oSurvey */
            $oSurvey = $aData['oSurvey'];
            $gid = $aData['gid'] ?? null;
            $aData['baselang'] = $oSurvey->language;
            App()->getClientScript()->registerPackage('js-cookie');

            //Parse data to send to view

            // ACTIVATE SURVEY BUTTON

            $condition = array('sid' => $iSurveyID, 'parent_qid' => 0);

            $sumcount3 = Question::model()->countByAttributes($condition); //Checked

            $aData['canactivate'] = $sumcount3 > 0 && Permission::model()->hasSurveyPermission($iSurveyID, 'surveyactivation', 'update');
            $aData['candeactivate'] = Permission::model()->hasSurveyPermission($iSurveyID, 'surveyactivation', 'update');
            $aData['expired'] = $oSurvey->expires != '' && ($oSurvey->expires < dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust')));
            $aData['notstarted'] = ($oSurvey->startdate != '') && ($oSurvey->startdate > dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust')));

            // Start of suckerfish menu
            // TEST BUTTON
            if (!$oSurvey->isActive) {
                $aData['icontext'] = gT("Preview survey");
            } else {
                $aData['icontext'] = gT("Run survey");
            }

            $aData['onelanguage'] = (count($oSurvey->allLanguages) == 1);
            $aData['hasadditionallanguages'] = (count($oSurvey->additionalLanguages) > 0);

            // Survey text elements BUTTON
            $aData['surveylocale'] = Permission::model()->hasSurveyPermission($iSurveyID, 'surveylocale', 'read');
            // EDIT SURVEY SETTINGS BUTTON
            $aData['surveysettings'] = Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'read');
            // Survey permission item
            $aData['surveysecurity'] = Permission::model()->hasSurveyPermission($iSurveyID, 'surveysecurity', 'read');
            // CHANGE QUESTION GROUP ORDER BUTTON
            $aData['surveycontentread'] = Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'read');
            $aData['groupsum'] = ($oSurvey->groupsCount > 1);
            // SET SURVEY QUOTAS BUTTON
            $aData['quotas'] = Permission::model()->hasSurveyPermission($iSurveyID, 'quotas', 'read');
            // Assessment menu item
            $aData['assessments'] = Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'read');
            // Survey text elements BUTTON
            // End if survey properties
            // Tools menu item
            // Delete survey item
            $aData['surveydelete'] = Permission::model()->hasSurveyPermission($iSurveyID, 'survey', 'delete');
            // Translate survey item
            $aData['surveytranslate'] = Permission::model()->hasSurveyPermission($iSurveyID, 'translations', 'read');
            // RESET SURVEY LOGIC BUTTON
            //$sumquery6 = "SELECT count(*) FROM ".db_table_name('conditions')." as c, ".db_table_name('questions')."
            // as q WHERE c.qid = q.qid AND q.sid=$iSurveyID"; //Getting a count of conditions for this survey
            // TMSW Condition->Relevance:  How is conditionscount used?  Should Relevance do the same?

            // Only show survey properties menu if at least one item is permitted
            $aData['showSurveyPropertiesMenu'] =
                    $aData['surveylocale']
                || $aData['surveysettings']
                || $aData['surveysecurity']
                || $aData['surveycontentread']
                || $aData['quotas']
                || $aData['assessments'];

            // Put menu items in tools menu
            $event = new PluginEvent('beforeToolsMenuRender', $this);
            $event->set('surveyId', $iSurveyID);
            App()->getPluginManager()->dispatchEvent($event);
            $extraToolsMenuItems = $event->get('menuItems');
            $aData['extraToolsMenuItems'] = $extraToolsMenuItems;

            // Add new menus in survey bar
            $event = new PluginEvent('beforeSurveyBarRender', $this);
            $event->set('surveyId', $iSurveyID);
            App()->getPluginManager()->dispatchEvent($event);
            $beforeSurveyBarRender = $event->get('menus');
            $aData['beforeSurveyBarRender'] = $beforeSurveyBarRender ? $beforeSurveyBarRender : array();

            // Only show tools menu if at least one item is permitted
            $aData['showToolsMenu'] =
                    $aData['surveydelete']
                || $aData['surveytranslate']
                || Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'update')
                || !is_null($extraToolsMenuItems);

            $iConditionCount = Condition::model()->with(array('questions' => array('condition' => 'sid =' . $iSurveyID)))->count();

            $aData['surveycontent'] = Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'update');
            $aData['conditionscount'] = ($iConditionCount > 0);
            // Eport menu item
            $aData['surveyexport'] = Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'export');
            // PRINTABLE VERSION OF SURVEY BUTTON
            // SHOW PRINTABLE AND SCANNABLE VERSION OF SURVEY BUTTON
            //browse responses menu item
            $aData['respstatsread'] = Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'read')
                || Permission::model()->hasSurveyPermission($iSurveyID, 'statistics', 'read')
                || Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'export');
            // Data entry screen menu item
            $aData['responsescreate'] = Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'create');
            $aData['responsesread'] = Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'read');
            // TOKEN MANAGEMENT BUTTON
            if (!$oSurvey->hasTokensTable) {
                $aData['tokenmanagement'] = Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'create');
            } else {
                $aData['tokenmanagement'] = Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'create')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'read')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'export')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'import'); // and export / import ?
            }

            $aData['gid'] = $gid; // = $this->input->post('gid');

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'read')) {
                $aData['permission'] = true;
            } else {
                $aData['gid'] = $gid = null;
                $aData['permission'] = false;
            }

            if (getGroupListLang($gid, $oSurvey->language, $iSurveyID)) {
                $aData['groups'] = getGroupListLang($gid, $oSurvey->language, $iSurveyID);
            } else {
                $aData['groups'] = "<option>" . gT("None") . "</option>";
            }

            $aData['GidPrev'] = getGidPrevious($iSurveyID, $gid);

            $aData['GidNext'] = getGidNext($iSurveyID, $gid);
            $aData['iIconSize'] = Yii::app()->getConfig('adminthemeiconsize');

            if (isset($aData['surveybar']['closebutton']['url'])) {
                $sAlternativeUrl = $aData['surveybar']['closebutton']['url'];
                $aData['surveybar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer(Yii::app()->createUrl($sAlternativeUrl));
            }

            if ($aData['gid'] == null) {
                            $this->getController()->renderPartial("/admin/survey/surveybar_view", $aData);
            }
        }
    }

    /**
     * Show side menu for survey view
     *
     * REFACTORED in LayoutHelper.php
     *
     * @param array $aData all the needed data
     */
    protected function surveysidemenu($aData)
    {
        $iSurveyID = $aData['surveyid'];

        $survey = Survey::model()->findByPk($iSurveyID);
        // TODO : create subfunctions
        $sumresult1 = Survey::model()->with(array(
            'languagesettings' => array('condition' => 'surveyls_language=language')))->find('sid = :surveyid', array(':surveyid' => $aData['surveyid'])); //$sumquery1, 1) ; //Checked

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'read')) {
            $aData['permission'] = true;
        } else {
            $aData['gid'] = null;
            $aData['permission'] = false;
        }

        if (!is_null($sumresult1)) {
            // $surveyinfo = $sumresult1->attributes;
            // $surveyinfo = array_merge($surveyinfo, $sumresult1->defaultlanguage->attributes);
            // $surveyinfo = array_map('flattenText', $surveyinfo);
            $aData['activated'] = $survey->isActive;

            // Tokens
            $bTokenExists = $survey->hasTokensTable;
            if (!$bTokenExists) {
                $aData['tokenmanagement'] = Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'create');
            } else {
                $aData['tokenmanagement'] = Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'create')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'read')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'export')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'import'); // and export / import ?
            }

            // Question explorer
            $aGroups = QuestionGroup::model()->findAllByAttributes(array('sid' => $iSurveyID), array('order' => 'group_order ASC'));
            $aData['quickmenu'] = $this->renderQuickmenu($aData);
            $aData['beforeSideMenuRender'] = $this->beforeSideMenuRender($aData);
            $aData['aGroups'] = $aGroups;
            $aData['surveycontent'] = Permission::model()->hasSurveyPermission($aData['surveyid'], 'surveycontent', 'read');
            $aData['surveycontentupdate'] = Permission::model()->hasSurveyPermission($aData['surveyid'], 'surveycontent', 'update');
            $aData['sideMenuBehaviour'] = getGlobalSetting('sideMenuBehaviour');
            $this->getController()->renderPartial("/admin/super/sidemenu", $aData);
        } else {
            Yii::app()->session['flashmessage'] = gT("Invalid survey ID");
            $this->getController()->redirect(array("dashboard/view"));
        }
    }

    /**
     * Render the quick-menu that is shown
     * when side-menu is hidden.
     *
     * REFACTORED in LayoutHelper
     *
     * Only show home-icon for now.
     *
     * Add support for plugin to attach
     * icon elements using event afterQuickMenuLoad
     *
     * @param array $aData
     * @return string
     * @todo Make quick-menu user configurable
     */
    protected function renderQuickmenu(array $aData)
    {
        $event = new PluginEvent('afterQuickMenuLoad', $this);
        $event->set('aData', $aData);
        $result = App()->getPluginManager()->dispatchEvent($event);

        $quickMenuItems = $result->get('quickMenuItems');
        if (!empty($quickMenuItems)) {
            usort($quickMenuItems, function ($b1, $b2) {
                return (int) $b1['order'] > (int) $b2['order'];
            });
        }

        $aData['quickMenuItems'] = $quickMenuItems;

        if ($aData['quickMenuItems'] === null) {
            $aData['quickMenuItems'] = array();
        }

        $html = $this->getController()->renderPartial('/admin/super/quickmenu', $aData, true);
        return $html;
    }

    /**
     * Returns content from event beforeSideMenuRender
     *
     * REFACTORED in LayoutHelper
     *
     * @param array $aData
     * @return string
     */
    protected function beforeSideMenuRender(array $aData)
    {
        $event = new PluginEvent('beforeSideMenuRender', $this);
        $event->set('aData', $aData);
        $result = App()->getPluginManager()->dispatchEvent($event);
        return $result->get('html');
    }

    /**
     * REFACTORED in LayoutHelper
     *
     * listquestion groups
     * @param array $aData
     */
    protected function listquestiongroups(array $aData)
    {
        if (isset($aData['display']['menu_bars']['listquestiongroups'])) {
            $this->getController()->renderPartial("/questionAdministration/listQuestions", $aData);
        }
    }

    /**
     * REFACTORED in LayoutHelper
     *
     * @param $aData
     * @throws CException
     */
    protected function listquestions($aData)
    {
        if (isset($aData['display']['menu_bars']['listquestions'])) {
            $iSurveyID = $aData['surveyid'];
            $oSurvey = $aData['oSurvey'];

            // The DataProvider will be build from the Question model, search method
            $model = new Question('search');

            // Global filter
            if (isset($_GET['Question'])) {
                $model->setAttributes($_GET['Question'], false);
            }

            // Filter group
            if (isset($_GET['gid'])) {
                $model->gid = $_GET['gid'];
            }

            // Set number of page
            if (isset($_GET['pageSize'])) {
                App()->user->setState('pageSize', (int) $_GET['pageSize']);
            }

            $aData['pageSize'] = App()->user->getState('pageSize', App()->params['defaultPageSize']);

            // We filter the current survey ID
            $model->sid = $iSurveyID;

            $aData['model'] = $model;

            $this->getController()->renderPartial("/admin/survey/Question/listquestions", $aData);
        }
    }

    /**
     *
     * @deprecated use ServiceClass FilterImportedResources instead ... (models/services/)
     *
     * @param string $extractdir
     * @param string $destdir
     * @return array
     */
    protected function filterImportedResources($extractdir, $destdir)
    {
        $aErrorFilesInfo = array();
        $aImportedFilesInfo = array();

        if (!is_dir($extractdir)) {
                    return array(array(), array());
        }

        if (!is_dir($destdir)) {
                    mkdir($destdir);
        }

        $dh = opendir($extractdir);
        if (!$dh) {
            $aErrorFilesInfo[] = array(
                "filename" => '',
                "status" => gT("Extracted files not found - maybe a permission problem?")
            );
            return array($aImportedFilesInfo, $aErrorFilesInfo);
        }
        while ($direntry = readdir($dh)) {
            if ($direntry != "." && $direntry != "..") {
                if (is_file($extractdir . "/" . $direntry)) {
                    // is  a file
                    $extfile = (string) substr(strrchr($direntry, '.'), 1);
                    if (!(stripos(',' . Yii::app()->getConfig('allowedresourcesuploads') . ',', ',' . $extfile . ',') === false)) {
                        // Extension allowed
                        if (!copy($extractdir . "/" . $direntry, $destdir . "/" . $direntry)) {
                            $aErrorFilesInfo[] = array(
                            "filename" => $direntry,
                            "status" => gT("Copy failed")
                            );
                        } else {
                            $aImportedFilesInfo[] = array(
                            "filename" => $direntry,
                            "status" => gT("OK")
                            );
                        }
                    } else {
                        // Extension forbidden
                        $aErrorFilesInfo[] = array(
                        "filename" => $direntry,
                        "status" => gT("Forbidden Extension")
                        );
                    }
                    unlink($extractdir . "/" . $direntry);
                }
            }
        }

        return array($aImportedFilesInfo, $aErrorFilesInfo);
    }

    /**
     * Get extra menus from plugins that are using event beforeAdminMenuRender
     *
     * @param array $aData
     * @return array<ExtraMenu>
     */
    protected function fetchExtraMenus(array $aData)
    {
        $event = new PluginEvent('beforeAdminMenuRender', $this);
        $event->set('data', $aData);
        $result = App()->getPluginManager()->dispatchEvent($event);

        $extraMenus = $result->get('extraMenus');

        if ($extraMenus === null) {
            $extraMenus = array();
        }

        return $extraMenus;
    }

    /**
     * Method to render an array as a json document
     *
     * REFACTORED in LSBaseController (this one called by a lot of actions in different controllers)
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

    /**
     * Validates that the request method is POST.
     *
     * This is intended to be used on subactions. When possible (eg. when refactoring
     * a SurveyCommonAction into an actual controller), use 'postOnly' filter instead.
     *
     * @throws CHttpException with 405 status if the request method is not POST.
     */
    protected function requirePostRequest()
    {
        if (!Yii::app()->getRequest()->isPostRequest) {
            throw new CHttpException(405, gT("Invalid action"));
        }
    }
}
