<?php

namespace LimeSurvey\Api\Command\V1;

use Condition;
use Exception;
use LimeExpressionManager;
use Permission;
use Question;
use Survey;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\Response\Status\StatusError;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;

class QuestionDelete implements CommandInterface
{
    /**
     * Run survey question delete command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iQuestionID = (int) $request->getData('questionID');

        $apiSession = new ApiSession();
        if (
            $apiSession->checkKey($sSessionKey)
        ) {
            $iQuestionID = (int) $iQuestionID;
            $oQuestion = Question::model()->findByPk($iQuestionID);
            if (!isset($oQuestion)) {
                return new Response(
                    array('status' => 'Error: Invalid question ID'),
                    new StatusErrorNotFound()
                );
            }

            $iSurveyID = $oQuestion['sid'];

            if (
                Permission::model()
                ->hasSurveyPermission(
                    $iSurveyID,
                    'surveycontent',
                    'delete'
                )
            ) {
                $oSurvey = Survey::model()->findByPk($iSurveyID);

                if ($oSurvey->isActive) {
                    return new Response(
                        array('status' => 'Survey is active and not editable'),
                        new StatusErrorBadRequest()
                    );
                }

                $oCondition = Condition::model()->findAllByAttributes(array('cqid' => $iQuestionID));
                if (count($oCondition) > 0) {
                    return new Response(
                        array('status' => 'Cannot delete Question. Others rely on this question'),
                        new StatusErrorBadRequest()
                    );
                }

                LimeExpressionManager::RevertUpgradeConditionsToRelevance(null, $iQuestionID);

                try {
                    $oQuestion->delete();
                    return new Response(
                        (int) $iQuestionID,
                        new StatusSuccess()
                    );
                } catch (Exception $e) {
                    return new Response(
                        array('status' => $e->getMessage()),
                        new StatusError()
                    );
                }
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
