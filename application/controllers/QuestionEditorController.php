<?php


class QuestionEditorController extends LSBaseController
{


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
     *
     * @param string $view
     * @return bool
     */
    protected function beforeRender($view)
    {
        if (isset($this->aData['surveyid'])) {
            $this->aData['oSurvey'] = Survey::model()->findByPk($this->aData['surveyid']);

            // Needed to evaluate EM expressions in question summary
            // See bug #11845
            LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
            LimeExpressionManager::StartProcessingPage(false, true);

            //$basePath = (string) Yii::getPathOfAlias('application.views.layouts');
            //$this->layout = $basePath.'/layout_questioneditor.php';

            $this->layout = 'layout_questioneditor';
        }

        return parent::beforeRender($view);
    }

    /**
     * Main view function prepares the necessary global js parts and renders the HTML
     *
     * @param integer $surveyid
     * @param integer $gid
     * @param integer $qid
     * @param string  $landOnSideMenuTab Name of the side menu tab. Default behavior is to land on structure tab.
     * @throws CException
     * @throws CHttpException
     */
    public function actionView($surveyid, $gid = null, $qid = null, $landOnSideMenuTab = 'structure'){
        $aData = array();
        $iSurveyID = (int) $surveyid;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $gid = $gid ?? $oSurvey->groups[0]->gid;
        $oQuestion = $this->getQuestionObject($qid, null, $gid);
        App()->getClientScript()->registerPackage('questioneditor');
        App()->getClientScript()->registerPackage('ace');
        $qrrow = $oQuestion->attributes;
        $baselang = $oSurvey->language;

        if (App()->session['questionselectormode'] !== 'default') {
            $questionSelectorType = App()->session['questionselectormode'];
        } else {
            $questionSelectorType = App()->getConfig('defaultquestionselectormode');
        }

        $aData['display']['menu_bars']['gid_action'] = 'viewquestion';
        $aData['questionbar']['buttons']['view'] = true;

        // Last question visited : By user (only one by user)
        $setting_entry = 'last_question_' . App()->user->getId();
        SettingGlobal::setSetting($setting_entry, $oQuestion->qid);

        // we need to set the sid for this question
        $setting_entry = 'last_question_sid_' . App()->user->getId();
        SettingGlobal::setSetting($setting_entry, $iSurveyID);

        // we need to set the gid for this question
        $setting_entry = 'last_question_gid_' . App()->user->getId();
        SettingGlobal::setSetting($setting_entry, $gid);

        // Last question for this survey (only one by survey, many by user)
        $setting_entry = 'last_question_' . App()->user->getId() . '_' . $iSurveyID;
        SettingGlobal::setSetting($setting_entry, $oQuestion->qid);

        // we need to set the gid for this question
        $setting_entry = 'last_question_' . App()->user->getId() . '_' . $iSurveyID . '_gid';
        SettingGlobal::setSetting($setting_entry, $gid);

        ///////////
        // combine aData
        $aData['surveyid'] = $iSurveyID;
        $aData['sid'] = $iSurveyID; //todo duplicated here, because it's needed in some functions of
        $aData['oSurvey'] = $oSurvey;
        $aData['aQuestionTypeList'] = QuestionTheme::findAllQuestionMetaDataForSelector();
        $aData['aQuestionTypeStateList'] = QuestionType::modelsAttributes();
        $aData['selectedQuestion'] = QuestionTheme::findQuestionMetaData($oQuestion->type);
        $aData['gid'] = $gid;
        $aData['qid'] = $oQuestion->qid;
        $aData['activated'] = $oSurvey->active;
        $aData['oQuestion'] = $oQuestion;
        $aData['languagelist'] = $oSurvey->allLanguages;
        $aData['qshowstyle'] = '';
        $aData['qrrow'] = $qrrow;
        $aData['baselang'] = $baselang;
        $aData['sImageURL'] = App()->getConfig('adminimageurl');
        $aData['iIconSize'] = App()->getConfig('adminthemeiconsize');
        $aData['display']['menu_bars']['qid_action'] = 'editquestion';
        $aData['display']['menu_bars']['gid_action'] = 'viewquestion';
        $aData['action'] = 'editquestion';
        $aData['editing'] = true;

        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title
            . " (" . gT("ID") . ":" . $iSurveyID . ")";
        $aData['surveyIsActive'] = $oSurvey->active !== 'N';
        $aData['activated'] = $oSurvey->active;
        $aData['jsData'] = [
            'surveyid' => $iSurveyID,
            'surveyObject' => $oSurvey->attributes,
            'gid' => $gid,
            'qid' => $oQuestion->qid,
            'startType' => $oQuestion->type,
            'baseSQACode' => [
                'answeroptions' => SettingsUser::getUserSettingValue('answeroptionprefix', App()->user->id) ?? 'AO' ,
                'subquestions' => SettingsUser::getUserSettingValue('subquestionprefix', App()->user->id) ?? 'SQ',
            ],
            'startInEditView' => SettingsUser::getUserSettingValue('noViewMode', App()->user->id) == '1',
            'connectorBaseUrl' => 'admin/questioneditor',
            'questionSelectorType' => $questionSelectorType,
            'i10N' => [
                'Create question' => gT('Create question'),
                'General settings' => gT("General settings"),
                'Code' => gT('Code'),
                'Text elements' => gT('Text elements'),
                'Question type' => gT('Question type'),
                'Question' => gT('Question'),
                'Help' => gT('Help'),
                'subquestions' => gT('Subquestions'),
                'answeroptions' => gT('Answer options'),
                'Quick add' => gT('Quick add'),
                'Copy subquestions' => gT('Copy subquestions'),
                'Copy answer options' => gT('Copy answer options'),
                'Copy default answers' => gT('Copy default answers'),
                'Copy advanced options' => gT('Copy advanced options'),
                'Predefined label sets' => gT('Predefined label sets'),
                'Save as label set' => gT('Save as label set'),
                'More languages' => gT('More languages'),
                'Add subquestion' => gT('Add subquestion'),
                'Reset' => gT('Reset'),
                'Save' => gT('Save'),
                'Some example subquestion' => gT('Some example subquestion'),
                'Delete' => gT('Delete'),
                'Open editor' => gT('Open editor'),
                'Duplicate' => gT('Duplicate'),
                'No preview available' => gT('No preview available'),
                'Editor' => gT('Editor'),
                'Quick edit' => gT('Quick edit'),
                'Cancel' => gT('Cancel'),
                'Replace' => gT('Replace'),
                'Add' => gT('Add'),
                'Select delimiter' => gT('Select delimiter'),
                'Semicolon' => gT('Semicolon'),
                'Comma' => gT('Comma'),
                'Tab' => gT('Tab'),
                'New rows' => gT('New rows'),
                'Scale' => gT('Scale'),
                'Save and Close' => gT('Save and close'),
                'Script' => gT('Script'),
                'X-Scale (columns)' => gT('X-Scale (columns)'),
                'Y-Scale (lines)' => gT('Y-Scale (lines)'),
                '__SCRIPTHELP' => gT("This optional script field will be wrapped,"
                    . " so that the script is correctly executed after the question is on the screen."
                    . " If you do not have the correct permissions, this will be ignored"),
                "noCodeWarning" =>
                    gT("Please put in a valid code. Only letters and numbers are allowed and it has to start with a letter. For example [Question1]"),
                "alreadyTaken" =>
                    gT("This code is already used - duplicate codes are not allowed."),
                "codeTooLong" =>
                    gT("A question code cannot be longer than 20 characters."),
                "Question cannot be stored. Please check the subquestion codes for duplicates or empty codes." =>
                    gT("Question cannot be stored. Please check the subquestion codes for duplicates or empty codes."),
                "Question cannot be stored. Please check the answer options for duplicates or empty codes." =>
                    gT("Question cannot be stored. Please check the answer options for duplicates or empty codes."),
            ],
        ];

        $aData['topBar']['type'] = 'question';

        $aData['topBar']['importquestion'] = true;
        $aData['topBar']['showSaveButton'] = true;
        $aData['topBar']['savebuttonform'] = 'frmeditgroup';
        $aData['topBar']['closebuttonurl'] = '/admin/survey/sa/listquestions/surveyid/' . $iSurveyID; // Close button

        if ($landOnSideMenuTab !== '') {
            $aData['sidemenu']['landOnSideMenuTab'] = $landOnSideMenuTab;
        }

        $this->aData = $aData;
        $this->render('view', [
            'aQuestionTypeList' => $aData['aQuestionTypeList'],
            'jsData' => $aData['jsData'],
            'aQuestionTypeStateList' => $aData['aQuestionTypeStateList']
        ]);
    }

    /**
     * Creates a question object
     * This is either an instance of the placeholder model QuestionCreate for new questions,
     * or of Question for already existing ones
     *
     * todo: this should be moved to model ...
     *
     * @param int $iQuestionId
     * @param string $sQuestionType
     * @param int $gid
     * @return Question
     * @throws CException
     */
    private function getQuestionObject($iQuestionId = null, $sQuestionType = null, $gid = null)
    {
        $iSurveyId = App()->request->getParam('sid') ?? App()->request->getParam('surveyid'); //todo: this should be done in the action directly
        $oQuestion = Question::model()->findByPk($iQuestionId);

        if ($oQuestion == null) {
            $oQuestion = QuestionCreate::getInstance($iSurveyId, $sQuestionType);
        }

        if ($sQuestionType != null) {
            $oQuestion->type = $sQuestionType;
        }

        if ($gid != null) {
            $oQuestion->gid = $gid;
        }

        return $oQuestion;
    }

    /****
     * *** A lot of getter function regarding functionalities and views.
     * *** All called via ajax
     ****/

    /**
     * Returns all languages in a specific survey as a JSON document
     *
     * @param int $iSurveyId
     *
     * @return void
     */
    public function actionGetPossibleLanguages($iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        $aLanguages = Survey::model()->findByPk($iSurveyId)->allLanguages;
        $this->renderJSON($aLanguages);
    }

    /**
     * Action called by the FE editor when a save is triggered.
     *
     * @param int $sid Survey id
     *
     * @return void
     * @throws CException
     */
    public function actionSaveQuestionData($sid)
    {
        $iSurveyId = (int) $sid;
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'update')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        $questionData = App()->request->getPost('questionData', []);
        // TODO: Unused variable
        $isNewQuestion = false;
        $questionCopy = (boolean) App()->request->getPost('questionCopy');
        $questionCopySettings = App()->request->getPost('copySettings', []);
        $questionCopySettings = array_map( function($value) {return !!$value;}, $questionCopySettings);

        // Store changes to the actual question data, by either storing it, or updating an old one
        $oQuestion = Question::model()->findByPk($questionData['question']['qid']);
        if ($oQuestion == null || $questionCopy == true) {
            $oQuestion = $this->storeNewQuestionData($questionData['question']);
            // TODO: Unused variable
            $isNewQuestion = true;
        } else {
            $oQuestion = $this->updateQuestionData($oQuestion, $questionData['question']);
        }

        /*
         * Setting up a try/catch scenario to delete a copied/created question,
         * in case the storing of the peripherals breaks
         */
        try {
            // Apply the changes to general settings, advanced settings and translations
            $setApplied = [];

            $setApplied['questionI10N'] = $this->applyI10N($oQuestion, $questionData['questionI10N']);

            $setApplied['generalSettings'] = $this->unparseAndSetGeneralOptions(
                $oQuestion,
                $questionData['generalSettings']
            );

            if (!($questionCopy === true && $questionCopySettings['copyAdvancedOptions'] == false)) {
                $setApplied['advancedSettings'] = $this->unparseAndSetAdvancedOptions(
                    $oQuestion,
                    $questionData['advancedSettings']
                );
            }

            if (!($questionCopy === true && $questionCopySettings['copyDefaultAnswers'] == false)) {
                $setApplied['defaultAnswers'] = $this->copyDefaultAnswers($oQuestion, $questionData['question']['qid']);
            }


            // save advanced attributes default values for given question type
            if (array_key_exists('save_as_default', $questionData['generalSettings'])
                && $questionData['generalSettings']['save_as_default']['formElementValue'] == 'Y') {
                SettingsUser::setUserSetting(
                    'question_default_values_' . $questionData['question']['type'],
                    ls_json_encode($questionData['advancedSettings'])
                );
            } elseif (array_key_exists('clear_default', $questionData['generalSettings'])
                && $questionData['generalSettings']['clear_default']['formElementValue'] == 'Y') {
                SettingsUser::deleteUserSetting('question_default_values_' . $questionData['question']['type'], '');
            }

            // If set, store subquestions
            if (isset($questionData['scaledSubquestions'])) {
                if (!($questionCopy === true && $questionCopySettings['copySubquestions'] == false)) {
                    $setApplied['scaledSubquestions'] = $this->storeSubquestions(
                        $oQuestion,
                        $questionData['scaledSubquestions'],
                        $questionCopy
                    );
                }
            }

            // If set, store answer options
            if (isset($questionData['scaledAnswerOptions'])) {
                if (!($questionCopy === true && $questionCopySettings['copyAnswerOptions'] == false)) {
                    $setApplied['scaledAnswerOptions'] = $this->storeAnswerOptions(
                        $oQuestion,
                        $questionData['scaledAnswerOptions'],
                        $questionCopy
                    );
                }
            }
        } catch (CException $ex) {
            throw new LSJsonException(
                500,
                gT('Question has been stored, but an error happened: ')."\n".$ex->getMessage(),
                0,
                App()->createUrl(
                    'admin/questioneditor/sa/view/',
                    ["surveyid"=> $oQuestion->sid, 'gid' => $oQuestion->gid, 'qid'=> $oQuestion->qid]
                )
            );
        }

        // Compile the newly stored data to update the FE
        $oNewQuestion = Question::model()->findByPk($oQuestion->qid);
        $aCompiledQuestionData = $this->getCompiledQuestionData($oNewQuestion);
        $aQuestionAttributeData = $this->getQuestionAttributeData($oQuestion->qid, true);
        $aQuestionGeneralOptions = $this->getGeneralOptions(
            $oQuestion->qid,
            null,
            $oQuestion->gid,
            true,
            $aQuestionAttributeData['question_template']
        );
        $aAdvancedOptions = $this->getAdvancedOptions($oQuestion->qid, null, true);

        // Return a JSON document with the newly stored question data
        $this->renderJSON(
            [
                'success' => array_reduce(
                    $setApplied,
                    function ($coll, $it) {
                        return $coll && $it;
                    },
                    true
                ),
                'message' => ($questionCopy === true
                    ? gT('Question successfully copied')
                    : gT('Question successfully stored')
                ),
                'successDetail' => $setApplied,
                'questionId' => $oQuestion->qid,
                'redirect' => $this->getController()->createUrl(
                    'admin/questioneditor/sa/view/',
                    [
                        'surveyid' => $iSurveyId,
                        'gid' => $oQuestion->gid,
                        'qid' => $oQuestion->qid,
                    ]
                ),
                'newQuestionDetails' => [
                    "question" => $aCompiledQuestionData['question'],
                    "scaledSubquestions" => $aCompiledQuestionData['subquestions'],
                    "scaledAnswerOptions" => $aCompiledQuestionData['answerOptions'],
                    "questionI10N" => $aCompiledQuestionData['i10n'],
                    "questionAttributes" => $aQuestionAttributeData,
                    "generalSettings" => $aQuestionGeneralOptions,
                    "advancedSettings" => $aAdvancedOptions,
                ],
                'transfer' => $questionData,
            ]
        );
        App()->close();
    }

    /**
     * Update the data set in the FE
     *
     * @param int $iQuestionId
     * @param string $type
     * @param int $gid Group id
     * @param string $question_template
     *
     * @return void
     * @throws CException
     */
    public function actionReloadQuestionData($iQuestionId = null, $type = null, $gid = null, $question_template = 'core')
    {
        $iQuestionId = (int) $iQuestionId;
        $oQuestion = $this->getQuestionObject($iQuestionId, $type, $gid);

        $aCompiledQuestionData = $this->getCompiledQuestionData($oQuestion);
        $aQuestionGeneralOptions = $this->getGeneralOptions($oQuestion->qid, $type, $oQuestion->gid, $question_template);
        $aAdvancedOptions = $this->getAdvancedOptions($oQuestion->qid, $type, true, $question_template);

        $aLanguages = [];
        $aAllLanguages = getLanguageData(false, App()->session['adminlang']);
        $aSurveyLanguages = $oQuestion->survey->getAllLanguages();

        array_walk(
            $aSurveyLanguages,
            function ($lngString) use (&$aLanguages, $aAllLanguages) {
                $aLanguages[$lngString] = $aAllLanguages[$lngString]['description'];
            }
        );

        $this->renderJSON(
            array_merge(
                $aCompiledQuestionData,
                [
                    'languages' => $aLanguages,
                    'mainLanguage' => $oQuestion->survey->language,
                    'generalSettings' => $aQuestionGeneralOptions,
                    'advancedSettings' => $aAdvancedOptions,
                    'questiongroup' => $oQuestion->group->attributes,
                ]
            )
        );
    }

    /**
     * @todo document me
     *
     * @param int $iQuestionId
     * @param string $sQuestionType
     * @param int $gid
     * @param boolean $returnArray
     * @param string $question_template
     *
     * @return void|array
     * @throws CException
     */
    public function actionGetGeneralOptions(
        $iQuestionId = null,
        $sQuestionType = null,
        $gid = null,
        $returnArray = false,
        $question_template = 'core'
    ) {
        $aGeneralOptionsArray = $this->getGeneralOptions($iQuestionId,$sQuestionType,$gid,$question_template);

        $this->renderJSON($aGeneralOptionsArray);
    }

    /**
     * @todo document me
     *
     * @param int $iQuestionId
     * @param string $sQuestionType
     * @param int $gid
     * @param string $question_template
     *
     * @return void|array
     * @throws CException
     */
    public function getGeneralOptions($iQuestionId = null, $sQuestionType = null, $gid = null, $question_template = 'core')
    {
        $oQuestion = $this->getQuestionObject($iQuestionId, $sQuestionType, $gid);
        $aGeneralOptionsArray = $oQuestion
            ->getDataSetObject()
            ->getGeneralSettingsArray($oQuestion->qid, $sQuestionType, null, $question_template);

        return $aGeneralOptionsArray;
    }

    /**
     * @todo document me.
     * @todo move this function somewhere else, this should not be part of controller ... (e.g. model)
     *
     * @param Question $oQuestion
     * @return array
     */
    private function getCompiledQuestionData(&$oQuestion)
    {
        LimeExpressionManager::StartProcessingPage(false, true);
        $aQuestionDefinition = array_merge($oQuestion->attributes, ['typeInformation' => $oQuestion->questionType]);
        $oQuestionGroup = QuestionGroup::model()->findByPk($oQuestion->gid);
        $aQuestionGroupDefinition = array_merge($oQuestionGroup->attributes, $oQuestionGroup->questiongroupl10ns);

        $aScaledSubquestions = $oQuestion->getOrderedSubQuestions();
        foreach ($aScaledSubquestions as $scaleId => $aSubquestions) {
            $aScaledSubquestions[$scaleId] = array_map(function ($oSubQuestion) {
                return array_merge($oSubQuestion->attributes, $oSubQuestion->questionl10ns);
            }, $aSubquestions);
        }

        $aScaledAnswerOptions = $oQuestion->getOrderedAnswers();
        foreach ($aScaledAnswerOptions as $scaleId => $aAnswerOptions) {
            $aScaledAnswerOptions[$scaleId] = array_map(function ($oAnswerOption) {
                return array_merge($oAnswerOption->attributes, $oAnswerOption->answerl10ns);
            }, $aAnswerOptions);
        }
        $aReplacementData = [];
        $questioni10N = [];
        foreach ($oQuestion->questionl10ns as $lng => $oQuestionI10N) {
            $questioni10N[$lng] = $oQuestionI10N->attributes;

            templatereplace(
                $oQuestionI10N->question,
                array(),
                $aReplacementData,
                'Unspecified',
                false,
                $oQuestion->qid
            );

            $questioni10N[$lng]['question_expression'] = viewHelper::stripTagsEM(
                LimeExpressionManager::GetLastPrettyPrintExpression()
            );

            templatereplace($oQuestionI10N->help, array(), $aReplacementData, 'Unspecified', false, $oQuestion->qid);
            $questioni10N[$lng]['help_expression'] = viewHelper::stripTagsEM(
                LimeExpressionManager::GetLastPrettyPrintExpression()
            );
        }
        LimeExpressionManager::FinishProcessingPage();
        return [
            'question' => $aQuestionDefinition,
            'questiongroup' => $aQuestionGroupDefinition,
            'i10n' => $questioni10N,
            'subquestions' => $aScaledSubquestions,
            'answerOptions' => $aScaledAnswerOptions,
        ];
    }

}