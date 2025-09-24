<?php

class SurveyDao
{
    /**
     * Loads a survey from the database that has the given ID.  If no matching
     * survey is found then null is returned.  Note that no results are loaded
     * from this function call, only survey structure/definition.
     *
     * In the future it would be nice to load all languages from the db at
     * once and have the infrastructure be able to return responses based
     * on language codes.
     *
     * @param int $id
     * @return SurveyObj
     */
    public function loadSurveyById($id, $lang = null, FormattingOptions $oOptions = null)
    {
        $survey = new SurveyObj();


        $intId = sanitize_int($id);
        $survey->id = $intId;
        $survey->info = getSurveyInfo($survey->id);
        $oSurvey = Survey::model()->findByPk($intId);

        $availableLanguages = $oSurvey->allLanguages;

        if (is_null($lang) || in_array($lang, $availableLanguages) === false) {
            // use base language when requested language is not found or no specific language is requested
            $lang = $oSurvey->language;
        }
        App()->setLanguage($lang);
        $survey->fieldMap = createFieldMap($oSurvey, 'full', true, false, $lang);
        // Check to see if timings are present and add to fieldmap if needed
        if ($survey->info['savetimings'] == "Y") {
            $survey->fieldMap = $survey->fieldMap + createTimingsFieldMap($intId, 'full', true, false, $lang);
        }

        if (empty($intId)) {
            //The id given to us is not an integer, croak.
            safeDie("An invalid survey ID was encountered");
        }

        $survey->groups = QuestionGroup::model()->findAll(array("condition" => 'sid=' . $intId, 'order' => 'group_order'));
        $survey->questions = Question::model()->findAll(array("condition" => 'sid=' . $intId, 'order' => 'question_order'));
        $aAnswers = Answer::model()->with('answerl10ns', 'question')->findAll(array('condition' => 'question.sid=' . $intId . ' AND ' . Yii::app()->db->quoteTableName('answerl10ns') . '.language = \'' . $lang . '\'', 'order' => 'question.question_order, t.scale_id'));
        foreach ($aAnswers as $aAnswer) {
            if (!empty($oOptions->stripHtmlCode) && $oOptions->stripHtmlCode == 1) {
                $answer = stripTagsFull($aAnswer->answerl10ns[$lang]->answer);
            } else {
                $answer = $aAnswer->answerl10ns[$lang]->answer;
            }
            $survey->answers[$aAnswer->question->qid][$aAnswer->scale_id][$aAnswer->code] = $answer;
        }
        //Load language settings for requested language
        $sQuery = 'SELECT * FROM {{surveys_languagesettings}} WHERE surveyls_survey_id = ' . $intId . ' AND surveyls_language = \'' . $lang . '\';';
        $recordSet = Yii::app()->db->createCommand($sQuery)->query();
        $survey->languageSettings = $recordSet->read();
        $recordSet->close();

        if (tableExists('tokens_' . $survey->id) && array_key_exists('token', SurveyDynamic::model($survey->id)->attributes) && Permission::model()->hasSurveyPermission($survey->id, 'tokens', 'read')) {
            // Now add the tokenFields
            $survey->tokenFields = getTokenFieldsAndNames($survey->id);
            unset($survey->tokenFields['token']);
        }

        return $survey;
    }

    /**
     * Loads results for the survey into the $survey->responses array.  The
     * results  begin from $minRecord and end with $maxRecord.  Either none,
     * or both,  the $minRecord and $maxRecord variables must be provided.
     * If none are then all responses are loaded.
     *
     * @param SurveyObj $survey
     * @param int $iMinimum
     * @param int $iMaximum
     * @param string $sFilter An optional filter for the results, i  string or arry of string
     * @param string $completionState all, complete or incomplete
     * @param array $aFields If empty all, otherwise only select the selected fields from the survey response table
     * @param string $sResponsesId
     * @return CDbCommand
     */
    public function loadSurveyResults(SurveyObj $survey, $iMinimum, $iMaximum, $sFilter = '', $completionState = 'all', $aFields = array(), $sResponsesId = null): CDbCommand
    {
        $oSurvey = Survey::model()->findByPk($survey->id);

        $aSelectFields = Yii::app()->db->schema->getTable($oSurvey->responsesTableName)->getColumnNames();
        // Get info about the survey
        if (!empty($aFields)) {
            $aSelectFields = array_intersect($aFields, $aSelectFields);
        }
        // Always add Table prefix : see bug #08396 . Don't use array_walk for PHP < 5.3 compatibility
        foreach ($aSelectFields as &$sField) {
            $sField = $oSurvey->responsesTableName . "." . $sField;
        }
        $oRecordSet = Yii::app()->db->createCommand()->from($oSurvey->responsesTableName);
        if (
            tableExists($oSurvey->tokensTableName)
            && array_key_exists('token', SurveyDynamic::model($oSurvey->primaryKey)->attributes)
            && Permission::model()->hasSurveyPermission($oSurvey->primaryKey, 'tokens', 'read')
        ) {
            $oRecordSet->leftJoin($oSurvey->tokensTableName . ' tokentable', $oSurvey->responsesTableName . '.token=tokentable.token');
            $aTokenFields = Yii::app()->db->schema->getTable($oSurvey->tokensTableName)->getColumnNames();
            foreach ($aTokenFields as &$sField) {
                $sField = "tokentable." . $sField;
            }
            $aSelectFields = array_merge($aSelectFields, array_diff($aTokenFields, ['tokentable.token']));
            //$aSelectFields=array_diff($aSelectFields, array('{{responses_{$survey->id}}}.token'));
            //$aSelectFields[]='{{responses_' . $survey->id . '}}.token';
        }
        if ($survey->info['savetimings'] == "Y") {
            $oRecordSet->leftJoin("{{timings_" . $survey->id . "}} timings", "{{responses_" . $survey->id . "}}.id = timings_timings.id");
            $aTimingFields = Yii::app()->db->schema->getTable("{{timings_" . $survey->id . "}}")->getColumnNames();
            foreach ($aTimingFields as &$sField) {
                $sField = "timings." . $sField;
            }
            $aSelectFields = array_merge($aSelectFields, array_diff($aTimingFields, ['timings.id']));
            //$aSelectFields=array_diff($aSelectFields, array('{{responses_{$survey->id}}}.id'));
            //$aSelectFields[]='{{responses_' . $survey->id . '}}.id';
        }
        if (empty($sResponsesId)) {
            $aParams = [
                'min' => $iMinimum,
                'max' => $iMaximum
            ];
            $selection = $oSurvey->responsesTableName . '.id >= :min AND ' . $oSurvey->responsesTableName . '.id <= :max';
            $oRecordSet->where($selection, $aParams);
        } else {
            $aResponsesId = explode(',', $sResponsesId);

            foreach ($aResponsesId as $i => $iResponseId) {
                $iResponseId = (int)$iResponseId;
                $selection = $oSurvey->responsesTableName . '.id = :id' . $i;


                if ($i === 0) {
                    $oRecordSet->where($selection, ['id' . $i => $iResponseId]);
                } else {
                    $oRecordSet->orWhere($selection, ['id' . $i => $iResponseId]);
                }
            }
        }

        if (is_string($sFilter) && $sFilter) {
            $oRecordSet->andWhere($sFilter);
        } elseif (is_array($sFilter) && count($sFilter)) {
            foreach ($sFilter as $filter) {
                $oRecordSet->andWhere($filter);
            }
        }

        switch ($completionState) {
            case 'incomplete':
                $oRecordSet->andWhere('submitdate IS NULL');
                break;
            case 'complete':
                $oRecordSet->andWhere('submitdate IS NOT NULL');
                break;
            case 'all':
            default:
                // Do nothing, all responses
                break;
        }
        $oRecordSet->order = $oSurvey->responsesTableName . '.id ASC';
        $oRecordSet->select($aSelectFields);
        return $oRecordSet;
    }
}
