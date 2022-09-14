<?php

namespace LimeSurvey\Api\Command\V1;

use Permission;
use QuestionGroup;
use Survey;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\ApiSession;

class SurveyGroupDelete implements CommandInterface
{
    /**
     * Run group delete command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\Request\Request $request
     * @return LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');
        $iGroupID = (int) $request->getData('groupID');

        $apiSession = new ApiSession;
        if ($apiSession->checkKey($sSessionKey)) {
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                return new Response(
                    array('status' => 'Error: Invalid survey ID')
                );
            }

            if (
                Permission::model()
                ->hasSurveyPermission(
                    $iSurveyID,
                    'surveycontent',
                    'delete'
                )
            ) {
                $oGroup = QuestionGroup::model()
                    ->findByAttributes(array('gid' => $iGroupID));
                if (!isset($oGroup)) {
                    return new Response(
                        array('status' => 'Error: Invalid group ID')
                    );
                }

                if ($oSurvey->isActive) {
                    return new Response(
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
                    return new Response(
                        array('status' => 'Group with dependencies - deletion not allowed')
                    );
                }

                $iGroupsDeleted = QuestionGroup::deleteWithDependency(
                    $iGroupID,
                    $iSurveyID
                );

                if ($iGroupsDeleted === 1) {
                    QuestionGroup::model()
                        ->updateGroupOrder($iSurveyID);
                    return new Response((int) $iGroupID);
                } else {
                    return new Response(
                        array('status' => 'Group deletion failed')
                    );
                }
            } else {
                return new Response(
                    array('status' => 'No permission')
                );
            }
        } else {
            return new Response(
                array('status' => ApiSession::INVALID_SESSION_KEY)
            );
        }
    }
}
