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
        $aActions = array_merge(Yii::app()->getController()->getActionClasses(), Yii::app()->getController()->getAdminModulesActionClasses());

        if (empty($aActions[$this->getId()]) || strtolower($oMethod->getDeclaringClass()->name) != strtolower($aActions[$this->getId()]) || !$oMethod->isPublic()) {
            // Either action doesn't exist in our whitelist, or the method class doesn't equal the action class or the method isn't public
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
     * {@link Survey_Common_Action::renderWrappedTemplate()}
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
                throw new CHttpException(403, gT("Invalid group id"));
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
                throw new CHttpException(403, gT("Invalid survey id"));
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
        //todo and then call to function such as $this->_nsurveysummary($aData);
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
     * Renders template(s) wrapped in header and footer
     *
     * Addition of parameters should be avoided if they can be added to $aData
     *
     * NOTE FROM LOUIS : We want to remove this function, wich doesn't respect MVC pattern.
     * The work it's doing should be handle by layout files, and subviews inside views.
     * Eg : for route "admin/survey/sa/listquestiongroups/surveyid/282267"
     *       the Group controller should use a main layout (with admin menu bar as a widget), then render the list view, in wich the question group bar is called as a subview.
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
     * Show admin menu for question group view
     *
     * @param array $aData ?
     * @todo Not used?
     */
    public function nquestiongroupbar($aData)
    {
        if (isset($aData['questiongroupbar'])) {
            if (!isset($aData['gid'])) {
                if (isset($_GET['gid'])) {
                    $aData['gid'] = $_GET['gid'];
                }
            }

            $aData['surveyIsActive'] = $aData['oSurvey']->active !== 'N';

            $surveyid = $aData['surveyid'];
            $gid = $aData['gid'];
            $oSurvey = $aData['oSurvey'];

            $aData['sumcount4'] = Question::model()->countByAttributes(array('sid' => $surveyid, 'gid' => $gid));

            $sumresult1 = Survey::model()->with(array(
                'languagesettings' => array('condition' => 'surveyls_language=language')))->findByPk($surveyid);
            $aData['activated'] = $activated = $sumresult1->active;
            if ($gid !== null) {
                $condarray = getGroupDepsForConditions($surveyid, "all", $gid, "by-targgid");
            }
            $aData['condarray'] = $condarray ?? [];

            $aData['languagelist'] = $oSurvey->getAllLanguages();

            if (isset($aData['questiongroupbar']['closebutton']['url'])) {
                $sAlternativeUrl = $aData['questiongroupbar']['closebutton']['url'];
                $aData['questiongroupbar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer(Yii::app()->createUrl($sAlternativeUrl));
            }

            $this->getController()->renderPartial("/questionGroupsAdministration/questiongroupbar_view", $aData);
        }
    }

    /**
     * Renders the fullpager bar
     * That's the white bar with action buttons example: 'Back' Button
     * @param array $aData
     * @throws CException
     */
    public function fullpagebar(array $aData)
    {
        if ((isset($aData['fullpagebar']))) {
            if (isset($aData['fullpagebar']['closebutton']['url']) && !isset($aData['fullpagebar']['closebutton']['url_keep'])) {
                $sAlternativeUrl = '/admin/index';
                $aData['fullpagebar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer(Yii::app()->createUrl($sAlternativeUrl));
            }
            App()->getClientScript()->registerScriptFile(
                App()->getConfig('adminscripts') . 'topbar.js',
                CClientScript::POS_END
            );
            $this->getController()->renderPartial("/admin/super/fullpagebar_view", $aData);
        }
    }

    /**
     * Renders the green bar with page title
     * Also called SurveyManagerBar
     * @todo Needs to be removed later. Duplication in LayoutHelper.
     * @todo Not used?
     * @param array $aData
     */
    public function surveyManagerBar(array $aData)
    {
        if (isset($aData['pageTitle'])) {
            Yii::app()->getController()->renderPartial("/layouts/surveymanagerbar", $aData);
        }
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
     * a Survey_Common_Action into an actual controller), use 'postOnly' filter instead.
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
