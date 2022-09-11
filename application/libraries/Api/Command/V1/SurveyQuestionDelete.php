<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\ApiSession;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\CommandRequest;
use LimeSurvey\Api\Command\CommandResponse;

class SurveyQuestionDelete implements CommandInterface
{
    /**
     * Run survey question delete command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\CommandRequest $request
     * @return LimeSurvey\Api\Command\CommandResponse
     */
    public function run(CommandRequest $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iQuestionID = (int) $request->getData('questionID');

        $apiSession = new ApiSession;
        if (
            $apiSession->checkKey($sSessionKey)
        ) {
            $iQuestionID = (int) $iQuestionID;
            $oQuestion = \Question::model()->findByPk($iQuestionID);
            if (!isset($oQuestion)) {
                return new CommandResponse(array('status' => 'Error: Invalid question ID'));
            }

            $iSurveyID = $oQuestion['sid'];

            if (\Permission::model()
                ->hasSurveyPermission(
                    $iSurveyID,
                    'surveycontent',
                    'delete'
                )
            ) {
                $oSurvey = \Survey::model()->findByPk($iSurveyID);

                if ($oSurvey->isActive) {
                    return new CommandResponse(array('status' => 'Survey is active and not editable'));
                }

                $oCondition = \Condition::model()->findAllByAttributes(array('cqid' => $iQuestionID));
                if (count($oCondition) > 0) {
                    return new CommandResponse(
                        array('status' => 'Cannot delete Question. Others rely on this question')
                    );
                }

                \LimeExpressionManager::RevertUpgradeConditionsToRelevance(null, $iQuestionID);

                try {
                    $oQuestion->delete();
                    return new CommandResponse((int) $iQuestionID);
                } catch (\Exception $e) {
                    return new CommandResponse(array('status' => $e->getMessage()));
                }
            } else {
                return new CommandResponse(array('status' => 'No permission'));
            }
        } else {
            return new CommandResponse(array('status' => ApiSession::INVALID_SESSION_KEY));
        }
    }
}
