<?php

namespace LimeSurvey\Api\Command\V1;

use Condition;
use Exception;
use LimeExpressionManager;
use Permission;
use Survey;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\ApiSession;

class SurveyQuestionDelete implements CommandInterface
{
    /**
     * Run survey question delete command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\Request\Request $request
     * @return LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iQuestionID = (int) $request->getData('questionID');

        $apiSession = new ApiSession;
        if (
            $apiSession->checkKey($sSessionKey)
        ) {
            $iQuestionID = (int) $iQuestionID;
            $oQuestion = Question::model()->findByPk($iQuestionID);
            if (!isset($oQuestion)) {
                return new Response(array('status' => 'Error: Invalid question ID'));
            }

            $iSurveyID = $oQuestion['sid'];

            if (Permission::model()
                ->hasSurveyPermission(
                    $iSurveyID,
                    'surveycontent',
                    'delete'
                )
            ) {
                $oSurvey = Survey::model()->findByPk($iSurveyID);

                if ($oSurvey->isActive) {
                    return new Response(array('status' => 'Survey is active and not editable'));
                }

                $oCondition = Condition::model()->findAllByAttributes(array('cqid' => $iQuestionID));
                if (count($oCondition) > 0) {
                    return new Response(
                        array('status' => 'Cannot delete Question. Others rely on this question')
                    );
                }

                LimeExpressionManager::RevertUpgradeConditionsToRelevance(null, $iQuestionID);

                try {
                    $oQuestion->delete();
                    return new Response((int) $iQuestionID);
                } catch (Exception $e) {
                    return new Response(array('status' => $e->getMessage()));
                }
            } else {
                return new Response(array('status' => 'No permission'));
            }
        } else {
            return new Response(array('status' => ApiSession::INVALID_SESSION_KEY));
        }
    }
}
