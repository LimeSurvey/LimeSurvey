<?php


class AssessmentController extends LSBaseController
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
        return [
            [
                'allow',
                'actions' => [],
                'users'   => ['*'], //everybody
            ],
            [
                'allow',
                'actions' => ['view'],
                'users'   => ['@'], //only login users
            ],
            ['deny'], //always deny all actions not mentioned above
        ];
    }


    /**
     * This part comes from _renderWrappedTemplate
     *
     * @param string $view View
     *
     * @return bool
     */
    protected function beforeRender($view)
    {
        if (isset($this->aData['surveyid'])) {
            $this->aData['oSurvey'] = $this->aData['oSurvey'] ?? Survey::model()->findByPk($this->aData['surveyid']);

            // Needed to evaluate EM expressions in question summary
            // See bug #11845
            LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
            LimeExpressionManager::StartProcessingPage(false, true);

            $this->layout = 'layout_questioneditor';
        }

        return parent::beforeRender($view);
    }

    /**
     * Shows the list (if assessment is activated) or the button to activate assessment mode
     *
     * @param int $surveyid the survey id
     *
     */
    public function actionIndex($surveyid){

        $iSurveyID = sanitize_int($surveyid);
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'read')) {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."), 'error');
            $this->redirect(array("admin/"));
        }

        $action = CHtml::encode(Yii::app()->request->getParam('action'));
        $oSurvey =     Survey::model()->findByPk($iSurveyID);
        $languages = $oSurvey->additionalLanguages;
        $surveyLanguage = $oSurvey->language;

        Yii::app()->session['FileManagerContext'] = "edit:assessments:{$iSurveyID}"; //todo: do we nee this ??

        array_unshift($languages, $surveyLanguage); // makes an array with ALL the languages supported by the survey -> $assessmentlangs

        Yii::app()->setConfig("baselang", $surveyLanguage);
        Yii::app()->setConfig("assessmentlangs", $languages);

        $this->aData['sidemenu']['landOnSideMenuTab']         = 'structure';

        $aData = [];
        $aData['survey'] = $oSurvey;
        $aData['surveyid'] = $iSurveyID;
        $aData['action'] = $action;

        Yii::app()->loadHelper('admin.htmleditor');

        $this->prepareDataArray($aData);

        $aData['asessementNotActivated'] = false;
        if ($oSurvey->assessments != 'Y') {
            $aData['asessementNotActivated'] = array(
                'title' => gT("Assessments mode not activated"),
                'message' => gT("Assessment mode for this survey is not activated.").'<br/>'
                    . gt("If you want to activate it click here:").'<br/>'
                    . '<a type="submit" class="btn btn-primary" href="'
                    . App()->getController()->createUrl('admin/assessments', ['action'=> 'asessementactivate', 'surveyid'=> $iSurveyID])
                    .'">'.gT('Activate assessements').'</a>',
                'class'=> 'warningheader col-sm-12 col-md-6 col-md-offset-3');
        }
        /*
        $urls = [];
        $urls['assessments']['assessments_view'][] = $aData;
        */

        $this->aData = $aData;
        $this->render('assessments_view', $this->aData);
        //$this->_renderWrappedTemplate('', 'assessments/assessments_view', $aData);
    }


    /**     *******************************  FOLLOWING FUNCTIONS ARE NO ACTIONS                *********************  */

    /**
     * @param array $aData
     * @param boolean $collectEdit
     * @return array
     */
    private function prepareDataArray(&$aData, $collectEdit = false)
    {
        $iSurveyID = $aData['surveyid'];
        $oSurvey = $aData['survey'];

        $aHeadings = array(gT("Scope"), gT("Question group"), gT("Minimum"), gT("Maximum"));
        $aData['headings'] = $aHeadings;
        $oAssessments = Assessment::model();
        $oAssessments->sid = $iSurveyID;

        $aData['groups'] = $this->collectGroupData($oSurvey, $aData);
        $this->setSearchParams($oAssessments);
        $aData['model'] = $oAssessments;
        if (isset($_POST['pageSize'])) {
            Yii::app()->user->setState('pageSize', Yii::app()->request->getParam('pageSize'));
        }
        $aData['actiontitle'] = gT("Add");
        $aData['actionvalue'] = "assessmentadd";
        $aData['editId'] = '';

        if ($collectEdit === true) {
            $aData = $this->collectEditData($aData);
        }

        $aData['imageurl'] = Yii::app()->getConfig('adminimageurl');
        $aData['assessments'] = $oAssessments;
        $aData['assessmentlangs'] = Yii::app()->getConfig("assessmentlangs");
        $aData['baselang'] = $oSurvey->language;
        $aData['subaction'] = gT("Assessments");
        $aData['gid'] = App()->request->getPost('gid', '');
        return $aData;
    }

    /**
     * return the groups of the current survey
     *
     *
     * @param Survey $oSurvey
     * @param array $aData
     * @return array $aGroups groupnames in array
     */
    private function collectGroupData($oSurvey, &$aData = array())
    {
        $aGroups = [];
        $db = Yii::app()->db;
        $quotedQGL10ns = $db->quoteTableName('questiongroupl10ns');
        $quotedLanguage = $db->quoteColumnName('language');

        $groups = QuestionGroup::model()->with(
            [
                'questiongroupl10ns' => [
                    'condition' => $quotedQGL10ns . '.' . $quotedLanguage . ' = :language',
                    'params' => array(':language' => $oSurvey->language)
                ]
            ]
        )->findAllByAttributes(array('sid' => $oSurvey->sid));
        foreach ($groups as $group) {
            $groupId = $group->attributes['gid'];
            $groupName = $group->getGroupNameI10N($oSurvey->language);
            $aGroups[$groupId] = $groupName;
        }
        return $aGroups;
    }

    /**
     * Set search params from Yii grid view.
     *
     * @param Assessment $oAssessments
     * @return void
     */
    private function setSearchParams(Assessment $oAssessments)
    {
        /*
        ["Assessment"]=>
            array(5) {
            ["scope"]=>
                string(1) "T"
                ["name"]=>
                string(0) ""
                ["minimum"]=>
                string(0) ""
                ["maximum"]=>
                string(0) ""
                ["message"]=>
                string(0) ""
            }
         */
        if (isset($_POST['Assessment']['scope'])) {
            $oAssessments->scope = $_POST['Assessment']['scope'];
        }

        if (isset($_POST['Assessment']['name'])) {
            $oAssessments->name = $_POST['Assessment']['name'];
        }

        if (isset($_POST['Assessment']['minimum'])) {
            $oAssessments->minimum = $_POST['Assessment']['minimum'];
        }

        if (isset($_POST['Assessment']['maximum'])) {
            $oAssessments->maximum = $_POST['Assessment']['maximum'];
        }

        if (isset($_POST['Assessment']['message'])) {
            $oAssessments->message = $_POST['Assessment']['message'];
        }
    }

    /**
     * @param array $aData
     * @return array
     */
    private function collectEditData(array $aData)
    {
        $oAssessment = Assessment::model()->find("id=:id", array(':id' => App()->request->getParam('id')));
        if (!$oAssessment) {
            throw new CHttpException(500);
        }
        // 404 ?

        $editData = $oAssessment->attributes;
        $aData['actiontitle'] = gT("Edit");
        $aData['actionvalue'] = "assessmentupdate";
        $aData['editId'] = $editData['id'];
        $aData['editdata'] = $editData;

        return $aData;
    }


    /**
     * @param int $iSurveyID
     * @param string $action
     * @return void
     */
    /*  --> is in index now
    private function _showAssessments($iSurveyID, $action)
    {
        $oSurvey = Survey::model()->findByPk($iSurveyID);

        $aData = [];
        $aData['survey'] = $oSurvey;
        $aData['surveyid'] = $iSurveyID;
        $aData['action'] = $action;

        Yii::app()->loadHelper('admin.htmleditor');

        $this->prepareDataArray($aData);

        $aData['asessementNotActivated'] = false;
        if ($oSurvey->assessments != 'Y') {
            $aData['asessementNotActivated'] = array(
                'title' => gT("Assessments mode not activated"),
                'message' => gT("Assessment mode for this survey is not activated.").'<br/>'
                    . gt("If you want to activate it click here:").'<br/>'
                    . '<a type="submit" class="btn btn-primary" href="'
                    . App()->getController()->createUrl('admin/assessments', ['action'=> 'asessementactivate', 'surveyid'=> $iSurveyID])
                    .'">'.gT('Activate assessements').'</a>',
                'class'=> 'warningheader col-sm-12 col-md-6 col-md-offset-3');
        }
        $urls = [];
        $urls['assessments']['assessments_view'][] = $aData;

        $this->_renderWrappedTemplate('', 'assessments/assessments_view', $aData);
    }*/


}
