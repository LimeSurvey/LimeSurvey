<?php


class QuestionGroupsController extends LSBaseController
{


    /**
     * It's import to have the accessRules set (security issue).
     * Only logged in users should have access to actions. All other permissions
     * should be checked in the action itself.
     *
     * @return array
     */
    public function accessRules()
    {
        return array(
            array(
                'allow',
                'actions' => array(),
                'users' => array('*'), //everybody
            ),
            array(
                'allow',
                'actions' => array('view'),
                'users' => array('@'), //only login users
            ),
            array('deny'), //always deny all actions not mentioned above
        );
    }

    /**
     * This part comes from _renderWrappedTemplate
     *
     * @param string $view
     * @return bool
     */
    protected function beforeRender($view)
    {
        $this->aData['topBar']['type'] = 'group';
        $this->aData['topBar']['showSaveButton'] = true;

        if (isset($this->aData['surveyid'])) {
            $this->aData['oSurvey'] = Survey::model()->findByPk($this->aData['surveyid']);

            // Needed to evaluate EM expressions in question summary
            // See bug #11845
            LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
            LimeExpressionManager::StartProcessingPage(false, true);

            $this->layout = 'layout_questioneditor';
        }

        return parent::beforeRender($view);
    }

    /**
     * @param int $surveyid
     * @param int $gid
     * @param string $landOnSideMenuTab
     *
     * * @return void
     */
    public function actionView($surveyid, $gid, $landOnSideMenuTab = 'structure')
    {
        $aData = array();
        $aData['surveyid'] = $iSurveyID = $surveyid;
        $survey = Survey::model()->findByPk($iSurveyID);
        $aData['gid'] = $gid;
        $baselang = $survey->language;
        if ($gid!==null) {
            $condarray = getGroupDepsForConditions($surveyid, "all", $gid, "by-targgid");
        }
        $aData['condarray'] = $condarray ?? [];

        App()->getClientScript()->registerPackage('ace');
        App()->getClientScript()->registerPackage('questiongroupeditor');

        $oQuestionGroup = $this->getQuestionGroupObject($iSurveyID, $gid);
        $grow           = $oQuestionGroup->attributes;

        $grow = array_map('flattenText', $grow);

        $aData['oQuestionGroup'] = $oQuestionGroup;
        $aData['surveyid'] = $surveyid;
        $aData['gid'] = $gid;
        $aData['grow'] = $grow;

        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title
            . " (".gT("ID").":".$iSurveyID.")";
        $aData['questiongroupbar']['buttons']['view'] = true;

        $aData['questiongroupbar']['buttonspreview'] = true;
        $aData['questiongroupbar']['savebutton']['form'] = true;
        $aData['questiongroupbar']['saveandclosebutton']['form'] = true;
        if (sanitize_paranoid_string(App()->request->getParam('sa') == 'add')) {
            $aData['questiongroupbar']['importbutton'] = true;
        }

        ///////////
        // sidemenu
        // TODO: Code duplication (Line 611 - 614) side menu state
        $aData['sidemenu']['state'] = true;
        $aData['sidemenu']['questiongroups'] = true;
        $aData['sidemenu']['group_name'] = $oQuestionGroup->questiongroupl10ns[$baselang]->group_name ?? '';
        $aData['sidemenu']['explorer']['state'] = true;
        $aData['sidemenu']['explorer']['gid'] = (isset($gid)) ? $gid : false;
        $aData['sidemenu']['explorer']['qid'] = false;
        $aData['sidemenu']['landOnSideMenuTab'] = $landOnSideMenuTab;

        $aData['jsData'] = [
            'surveyid' => $iSurveyID,
            'gid' => $gid,
            'startInEditView' => SettingsUser::getUserSettingValue('noViewMode', App()->user->id) == '1',
            'connectorBaseUrl' => $this->createUrl(
                'admin/questiongroups',
                ['sid' => $iSurveyID, 'sa' => '']
            ),
            'openQuestionUrl' => $this->createUrl(
                'questionEditor/view/',
                ['surveyid'=>$iSurveyID, 'gid'=>$gid, 'qid' => '']
            ),
            'createQuestionUrl' => $this->createUrl(
                "questionEditor/view/",
                ["surveyid" =>  $surveyid, 'gid' => $gid]
            ),
            'i10N' => [
                'Question group' => gT('Question group'),
                'Group overview' => gT('Group overview'),
                'Question list' => gT('Question list'),
                'Create new question group' => gT('Create new question group'),
                'Question group overview' => gT('Question group overview'),
                'Question group editor' => gT('Question group editor'),
                'General Settings' => gT("General Settings"),
                'Group summary' => gT('Group summary'),
                'Random Group' => gT('Random Group'),
                'Title' => gT('Title'),
                'Condition' => gT('Condition'),
                'Description' => gT('Description'),
                'Quick actions' => gT('Quick actions'),
                'Subquestions' => gT('Subquestions'),
                'Answeroptions' => gT('Answer options'),
                'Question type' => gT('Question type'),
                'Default answer' => gT('Default answer'),
                'Create question' => gT('Create question'),
                'Order' => gT('Order'),
                'Question code' => gT('Code'),
                'Question' => gT('Question'),
                'QuestionType' => gT('Question type'),
                'Mandatory' => gT('Mandatory'),
                'Encrypted' => gT('Encrypted'),
                'Actions' => gT('Actions'),
            ]
        ];

        $this->aData = $aData;

        $this->render('group_view', [
            'jsData' => $this->aData['jsData'],
            'gid' => $this->aData['gid']
        ]);
    }

    /**
     * Render view to add new question group.
     * Redirects to the action view
     *
     * @param int $surveyid
     */
    public function add($surveyid)
    {
        $this->actionView($surveyid, null, 'structure');
    }


    /** ++++++++++++  the following functions should be moved to model or a service class ++++++++++++++++++++++++++ */

    /**
     * Returns the QuestionGroup (existing one or new created one)
     *
     * @param int $iSurveyId
     * @param int | null $iQuestionGroupId ID of group
     *
     * @return QuestionGroup
     */
    private function getQuestionGroupObject($iSurveyId, $iQuestionGroupId = null)
    {
        $oQuestionGroup =  QuestionGroup::model()->findByPk($iQuestionGroupId);
        if ($oQuestionGroup == null) {
            $oQuestionGroup = new QuestionGroup();
            $oQuestionGroup->sid = $iSurveyId;
        }

        return $oQuestionGroup;
    }

}