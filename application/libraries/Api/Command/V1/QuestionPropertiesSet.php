<?php

namespace LimeSurvey\Api\Command\V1;

use Exception;
use Question;
use Survey;
use Yii;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermission;
use LimeSurvey\Api\Command\Mixin\Accessor\QuestionModel;
use LimeSurvey\Api\Command\Mixin\CommandResponse;

class QuestionPropertiesSet implements CommandInterface
{
    use AuthSession;
    use AuthPermission;
    use CommandResponse;
    use QuestionModel;

    /**
     * Run survey question properties set command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iQuestionID = (int) $request->getData('questionID');
        $aQuestionData = $request->getData('questionData');
        $sLanguage = (string) $request->getData('language');

        if (
            ($response = $this->checkKey($sSessionKey)) !== true
        ) {
            return $response;
        }

        Yii::app()->loadHelper("surveytranslator");

        $oQuestion = $this->getQuestionModel($iQuestionID);
        if (is_null($oQuestion)) {
            return $this->responseErrorNotFound(
                ['status' => 'Error: Invalid group ID']
            );
        }

        $iSurveyID = $oQuestion->sid;

        if (
            ($response = $this->hasSurveyPermission(
                $iSurveyID,
                'survey',
                'update'
            )
            ) !== true
        ) {
            return $response;
        }

        if (is_null($sLanguage)) {
            $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
        }

        if (!array_key_exists($sLanguage, getLanguageDataRestricted())) {
            return $this->responseErrorBadRequest(
                ['status' => 'Error: Invalid language']
            );
        }

        // Remove fields that may not be modified
        unset($aQuestionData['qid']);
        unset($aQuestionData['gid']);
        unset($aQuestionData['sid']);
        unset($aQuestionData['parent_qid']);
        unset($aQuestionData['language']);
        unset($aQuestionData['type']);
        // Remove invalid fields
        $aDestinationFields = array_flip(Question::model()->tableSchema->columnNames);
        $aQuestionData = array_intersect_key($aQuestionData, $aDestinationFields);
        $aQuestionAttributes = $oQuestion->getAttributes();

        if (empty($aQuestionData)) {
            return $this->responseErrorBadRequest(
                ['status' => 'No valid Data']
            );
        }

        foreach ($aQuestionData as $sFieldName => $sValue) {
            //all the dependencies that this question has to other questions
            $dependencies = getQuestDepsForConditions($oQuestion->sid, $oQuestion->gid, $iQuestionID);
            //all dependencies by other questions to this question
            $is_criteria_question = getQuestDepsForConditions($oQuestion->sid, $oQuestion->gid, "all", $iQuestionID, "by-targqid");
            //We do not allow questions with dependencies in the same group to change order - that would lead to broken dependencies

            if ((isset($dependencies) || isset($is_criteria_question)) && $sFieldName == 'question_order'
            ) {
                $aResult[$sFieldName] = 'Questions with dependencies - Order cannot be changed';
                continue;
            }
            $oQuestion->setAttribute(
                $sFieldName,
                $sValue
            );

            try {
                $bSaveResult = $oQuestion->save(); // save the change to database
                Question::model()->updateQuestionOrder($oQuestion->gid);
                $aResult[$sFieldName] = $bSaveResult;
                //unset fields that failed
                if (!$bSaveResult) {
                    $oQuestion->$sFieldName = $aQuestionAttributes[$sFieldName];
                }
            } catch (Exception $e) {
                //unset fields that caused exception
                $oQuestion->$sFieldName = $aQuestionAttributes[$sFieldName];
            }
        }

        return $this->responseSuccess($aResult);
    }
}
