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
     * @param $lang
     * @param FormattingOptions $oOptions
     * @return SurveyObj
     */
    public function loadSurveyById($id, $lang = null, ?FormattingOptions $oOptions = null)
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
        // Ranking items are subquestions: add their texts so ranked codes resolve like answer options
        foreach (Question::getRankingItemTexts($intId, $lang) as $rankingQid => $itemTexts) {
            foreach ($itemTexts as $code => $text) {
                if (!empty($oOptions->stripHtmlCode) && $oOptions->stripHtmlCode == 1) {
                    $text = stripTagsFull($text);
                }
                $survey->answers[$rankingQid][0][$code] = $text;
            }
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
     * @param string $sFilter An optional filter for the results, is a string or an array of strings
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
            // Per-rank-position ranking fields ("Q{qid}_S{sqid}") have no column of
            // their own: their values are decoded from the base "Q{qid}" JSON column
            // (see Writer::write()), so that column must stay selected whenever one
            // of its rank fields is requested.
            $aRankingBaseFields = [];
            foreach ($aFields as $sField) {
                if (isset($survey->fieldMap[$sField]) && $survey->fieldMap[$sField]['type'] === Question::QT_R_RANKING && $survey->fieldMap[$sField]['suffix'] !== '') {
                    $aRankingBaseFields[] = 'Q' . $survey->fieldMap[$sField]['qid'];
                }
            }
            // The id and token are needed to add the participant and timing values (see addJoinedResults())
            $aSelectFields = array_intersect(array_merge($aFields, $aRankingBaseFields, ['id', 'token']), $aSelectFields);
        }
        // Always add Table prefix : see bug #08396 . Don't use array_walk for PHP < 5.3 compatibility
        foreach ($aSelectFields as &$sField) {
            $sField = $oSurvey->responsesTableName . "." . $sField;
        }
        $oRecordSet = Yii::app()->db->createCommand()->from($oSurvey->responsesTableName);
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

    /**
     * Adds the values of the participant and timing columns to a page of results from loadSurveyResults().
     * They are loaded with separate queries instead of joins, otherwise surveys with many questions
     * exceed the maximum number of columns in a query (1664 on PostgreSQL).
     *
     * @param SurveyObj $survey
     * @param array[] $aResponses Response rows, each with the id and token column
     * @param string[]|null $aFields If empty all, otherwise only add the selected participant and timing fields
     * @return array[] The response rows with the participant and timing values
     */
    public function addJoinedResults(SurveyObj $survey, array $aResponses, $aFields = array()): array
    {
        if (empty($aResponses)) {
            return $aResponses;
        }
        $oSurvey = Survey::model()->findByPk($survey->id);
        if (
            tableExists($oSurvey->tokensTableName)
            && array_key_exists('token', SurveyDynamic::model($oSurvey->primaryKey)->attributes)
            && Permission::model()->hasSurveyPermission($oSurvey->primaryKey, 'tokens', 'read')
        ) {
            $aResponses = $this->addJoinedRows($aResponses, $oSurvey->tokensTableName, 'token', 'tid', $aFields);
        }
        if ($survey->info['savetimings'] == "Y") {
            $aResponses = $this->addJoinedRows($aResponses, $oSurvey->timingsTableName, 'id', 'id', $aFields);
        }
        return $aResponses;
    }

    /**
     * Adds the columns of the rows of another table to the response rows, like a left join on the given key column.
     *
     * @param array[] $aResponses Response rows
     * @param string $sTableName Table to load the rows from
     * @param string $sKey Column that links the rows to the responses, it is not added
     * @param string $sOrder Column to sort by, of several rows with the same key the first one is used
     * @param string[]|null $aFields If empty all, otherwise only add the selected columns
     * @return array[]
     */
    private function addJoinedRows(array $aResponses, string $sTableName, string $sKey, string $sOrder, $aFields): array
    {
        $aColumns = array_diff(Yii::app()->db->schema->getTable($sTableName)->getColumnNames(), [$sKey]);
        if (!empty($aFields)) {
            $aColumns = array_intersect($aColumns, $aFields);
        }
        if (empty($aColumns)) {
            return $aResponses;
        }
        $aKeys = array_unique(array_filter(array_column($aResponses, $sKey), function ($value) {
            return $value !== null && $value !== '';
        }));
        $aJoinedRows = array();
        if (!empty($aKeys)) {
            $aRows = Yii::app()->db->createCommand()
                ->select(array_merge([$sKey], $aColumns))
                ->from($sTableName)
                ->where(['in', $sKey, array_values($aKeys)])
                ->order($sOrder)
                ->queryAll();
            foreach ($aRows as $aRow) {
                $sRowKey = $aRow[$sKey];
                unset($aRow[$sKey]);
                if (!isset($aJoinedRows[$sRowKey])) {
                    $aJoinedRows[$sRowKey] = $aRow;
                }
            }
        }
        $aEmptyRow = array_fill_keys($aColumns, null);
        foreach ($aResponses as &$aResponse) {
            $aResponse = array_merge($aResponse, $aJoinedRows[$aResponse[$sKey] ?? ''] ?? $aEmptyRow);
        }
        return $aResponses;
    }
}
