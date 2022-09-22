<?php

namespace LimeSurvey\Api\Command\V1;

use Exception;
use Permission;
use Question;
use Survey;
use Yii;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\Response\Status\StatusError;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;

class QuestionPropertiesSet implements CommandInterface
{
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

        $apiSession = new ApiSession();
        if ($apiSession->checkKey($sSessionKey)) {
            Yii::app()->loadHelper("surveytranslator");
            $iQuestionID = (int) $iQuestionID;
            $oQuestion = Question::model()->findByAttributes(array('qid' => $iQuestionID));
            if (is_null($oQuestion)) {
                return new Response(
                    array('status' => 'Error: Invalid group ID'),
                    new StatusErrorNotFound()
                );
            }

            $iSurveyID = $oQuestion->sid;

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'survey', 'update')) {
                if (is_null($sLanguage)) {
                    $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
                }

                if (!array_key_exists($sLanguage, getLanguageDataRestricted())) {
                    return new Response(
                        array('status' => 'Error: Invalid language'),
                        new StatusErrorBadRequest()
                    );
                }

                $oQuestion = Question::model()->findByAttributes(array('qid' => $iQuestionID));
                if (!isset($oQuestion)) {
                    return new Response(
                        array('status' => 'Error: Invalid questionid'),
                        new StatusErrorBadRequest()
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
                    return new Response(
                        array('status' => 'No valid Data'),
                        new StatusSuccess()
                    );
                }

                foreach ($aQuestionData as $sFieldName => $sValue) {
                    //all the dependencies that this question has to other questions
                    $dependencies = getQuestDepsForConditions($oQuestion->sid, $oQuestion->gid, $iQuestionID);
                    //all dependencies by other questions to this question
                    $is_criteria_question = getQuestDepsForConditions($oQuestion->sid, $oQuestion->gid, "all", $iQuestionID, "by-targqid");
                    //We do not allow questions with dependencies in the same group to change order - that would lead to broken dependencies

                    if ((isset($dependencies) || isset($is_criteria_question)) && $sFieldName == 'question_order') {
                        $aResult[$sFieldName] = 'Questions with dependencies - Order cannot be changed';
                        continue;
                    }
                    $oQuestion->setAttribute($sFieldName, $sValue);

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
                return new Response(
                    $aResult,
                    new StatusSuccess()
                );
            } else {
                return new Response(
                    array('status' => 'No permission'),
                    new StatusErrorUnauthorised()
                );
            }
        } else {
            return new Response(
                array('status' => ApiSession::INVALID_SESSION_KEY),
                new StatusErrorUnauthorised()
            );
        }
    }
}
