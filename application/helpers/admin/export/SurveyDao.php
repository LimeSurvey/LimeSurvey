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
    public function loadSurveyById($id, $lang = null)
    {
        $survey = new SurveyObj();


        $intId = sanitize_int($id);
        $survey->id = $intId;
        $survey->info = getSurveyInfo($survey->id);
        $availableLanguages = Survey::model()->findByPk($intId)->getAllLanguages();

        if (is_null($lang) || in_array($lang, $availableLanguages) === false) {
            // use base language when requested language is not found or no specific language is requested
            $lang = Survey::model()->findByPk($intId)->language;
        }
        App()->setLanguage($lang);
        $survey->fieldMap = createFieldMap($intId,'full',true,false,$lang);
        // Check to see if timings are present and add to fieldmap if needed
        if ($survey->info['savetimings']=="Y") {
            $survey->fieldMap = $survey->fieldMap + createTimingsFieldMap($intId,'full',true,false,$lang);
        }

        if (empty($intId))
        {
            //The id given to us is not an integer, croak.
            safeDie("An invalid survey ID was encountered: $sid");
        }

        //Load groups
        $sQuery = 'SELECT g.* FROM {{groups}} AS g '.
        'WHERE g.sid = '.$intId.' AND g.language = \'' . $lang . '\' ' .
        'ORDER BY g.group_order;';
        $recordSet = Yii::app()->db->createCommand($sQuery)->query()->readAll();
        $survey->groups = $recordSet;

        //Load questions
        $sQuery = 'SELECT q.* FROM {{questions}} AS q '.
        'JOIN {{groups}} AS g ON (q.gid = g.gid and q.language = g.language) '.
        'WHERE q.sid = '.$intId.' AND q.language = \''.$lang.'\' '.
        'ORDER BY g.group_order, q.question_order;';
        $survey->questions = Yii::app()->db->createCommand($sQuery)->query()->readAll();

        //Load answers
        $sQuery = 'SELECT DISTINCT a.* FROM {{answers}} AS a '.
        'JOIN {{questions}} AS q ON a.qid = q.qid '.
        'WHERE q.sid = '.$intId.' AND a.language = \''.$lang.'\' '.
        'ORDER BY a.qid, a.sortorder;';
        //$survey->answers = Yii::app()->db->createCommand($sQuery)->queryAll();
        $aAnswers= Yii::app()->db->createCommand($sQuery)->queryAll();
        foreach($aAnswers as $aAnswer)
        {
             if(Yii::app()->controller->action->id !='remotecontrol')
				$aAnswer['answer']=stripTagsFull($aAnswer['answer']);
             $survey->answers[$aAnswer['qid']][$aAnswer['scale_id']][$aAnswer['code']]=$aAnswer;
        }
        //Load language settings for requested language
        $sQuery = 'SELECT * FROM {{surveys_languagesettings}} WHERE surveyls_survey_id = '.$intId.' AND surveyls_language = \'' . $lang . '\';';
        $recordSet = Yii::app()->db->createCommand($sQuery)->query();
        $survey->languageSettings = $recordSet->read();
        $recordSet->close();

        if (tableExists('tokens_'.$survey->id) && array_key_exists ('token',SurveyDynamic::model($survey->id)->attributes) && Permission::model()->hasSurveyPermission($survey->id,'tokens','read'))
        {
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
    * @param Survey $survey
    * @param int $iMinimum
    * @param int $iMaximum
    * @param string $sFilter An optional filter for the results, i  string or arry of string
    * @param string $completionState all, complete or incomplete
    */
    public function loadSurveyResults(SurveyObj $survey, $iMinimum, $iMaximum, $sFilter='', $completionState = 'all' )
    {

        // Get info about the survey
        $aSelectFields=Yii::app()->db->schema->getTable('{{survey_' . $survey->id . '}}')->getColumnNames();
        // Allways add Table prefix : see bug #08396 . Don't use array_walk for PHP < 5.3 compatibility
        foreach ($aSelectFields as &$sField)
           $sField ="{{survey_{$survey->id}}}.".$sField;
        $oRecordSet = Yii::app()->db->createCommand()->from('{{survey_' . $survey->id . '}}');
        if (tableExists('tokens_'.$survey->id) && array_key_exists ('token',SurveyDynamic::model($survey->id)->attributes) && Permission::model()->hasSurveyPermission($survey->id,'tokens','read'))
        {
            $oRecordSet->leftJoin('{{tokens_' . $survey->id . '}} tokentable','tokentable.token={{survey_' . $survey->id . '}}.token');
            $aTokenFields=Yii::app()->db->schema->getTable('{{tokens_' . $survey->id . '}}')->getColumnNames();
            foreach ($aTokenFields as &$sField)
               $sField ="tokentable.".$sField;
            $aSelectFields=array_merge($aSelectFields,array_diff($aTokenFields, array('tokentable.token')));
            //$aSelectFields=array_diff($aSelectFields, array('{{survey_{$survey->id}}}.token'));
            //$aSelectFields[]='{{survey_' . $survey->id . '}}.token';
        }
        if ($survey->info['savetimings']=="Y") {
            $oRecordSet->leftJoin("{{survey_" . $survey->id . "_timings}} survey_timings", "{{survey_" . $survey->id . "}}.id = survey_timings.id");
            $aTimingFields=Yii::app()->db->schema->getTable("{{survey_" . $survey->id . "_timings}}")->getColumnNames();
            foreach ($aTimingFields as &$sField)
               $sField ="survey_timings.".$sField;
            $aSelectFields=array_merge($aSelectFields,array_diff($aTimingFields, array('survey_timings.id')));
            //$aSelectFields=array_diff($aSelectFields, array('{{survey_{$survey->id}}}.id'));
            //$aSelectFields[]='{{survey_' . $survey->id . '}}.id';
        }

        $aParams = array(
            'min'=>$iMinimum,
            'max'=>$iMaximum
        );
        $selection = '{{survey_' . $survey->id . '}}.id >= :min AND {{survey_' . $survey->id . '}}.id <= :max';
        $oRecordSet->where($selection, $aParams);

        if(is_string($sFilter) && $sFilter)
            $oRecordSet->andWhere($sFilter);
        elseif(is_array($sFilter) && count($sFilter))
        {
            foreach($sFilter as $filter)
                $oRecordSet->andWhere($filter);
        }

        switch ($completionState)
        {
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
        $oRecordSet->order='{{survey_' . $survey->id . '}}.id ASC';
        $survey->responses=$oRecordSet->select($aSelectFields)->query();
    }
}
