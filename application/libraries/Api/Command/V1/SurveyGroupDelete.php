<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\ApiSession;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\CommandRequest;
use LimeSurvey\Api\Command\CommandResponse;

class SurveyGroupDelete implements CommandInterface
{
    /**
     * Run group delete command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\CommandRequest $request
     * @return LimeSurvey\Api\Command\CommandResponse
     */
    public function run(CommandRequest $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');
        $iGroupID = (int) $request->getData('groupID');

        $apiSession = new ApiSession;
        if ($apiSession->checkKey($sSessionKey)) {
            $oSurvey = \Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                return new CommandResponse(
                    array('status' => 'Error: Invalid survey ID')
                );
            }

            if (
                \Permission::model()
                ->hasSurveyPermission(
                    $iSurveyID,
                    'surveycontent',
                    'delete'
                )
            ) {
                $oGroup = \QuestionGroup::model()
                    ->findByAttributes(array('gid' => $iGroupID));
                if (!isset($oGroup)) {
                    return new CommandResponse(
                        array('status' => 'Error: Invalid group ID')
                    );
                }

                if ($oSurvey->isActive) {
                    return new CommandResponse(
                        array('status' => 'Error:Survey is active and not editable')
                    );
                }

                $dependantOn = getGroupDepsForConditions(
                    $oGroup->sid,
                    "all",
                    $iGroupID,
                    "by-targgid"
                );
                if (isset($dependantOn)) {
                    return new CommandResponse(
                        array('status' => 'Group with dependencies - deletion not allowed')
                    );
                }

                $iGroupsDeleted = \QuestionGroup::deleteWithDependency(
                    $iGroupID,
                    $iSurveyID
                );

                if ($iGroupsDeleted === 1) {
                    \QuestionGroup::model()
                        ->updateGroupOrder($iSurveyID);
                    return new CommandResponse((int) $iGroupID);
                } else {
                    return new CommandResponse(
                        array('status' => 'Group deletion failed')
                    );
                }
            } else {
                return new CommandResponse(
                    array('status' => 'No permission')
                );
            }
        } else {
            return new CommandResponse(
                array('status' => ApiSession::INVALID_SESSION_KEY)
            );
        }
    }
}
