<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\ApiSession;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\CommandRequest;
use LimeSurvey\Api\Command\CommandResponse;

class SurveyGroupAdd implements CommandInterface
{
    /**
     * Run group add command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\CommandRequest $request
     * @return LimeSurvey\Api\Command\CommandResponse
     */
    public function run(CommandRequest $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');
        $sGroupTitle = (string) $request->getData('groupTitle');
        $sGroupDescription = (string) $request->getData('groupDescription');

        $apiSession = new ApiSession;
        if ($apiSession->checkKey($sSessionKey)) {
            if (
                \Permission::model()
                ->hasSurveyPermission(
                    $iSurveyID,
                    'survey',
                    'update'
                )
            ) {
                $iSurveyID = (int) $iSurveyID;
                $oSurvey = \Survey::model()->findByPk($iSurveyID);
                if (!isset($oSurvey)) {
                    return new CommandResponse(
                        array('status' => 'Error: Invalid survey ID')
                    );
                }

                if ($oSurvey->isActive) {
                    return new CommandResponse(
                        array('status' => 'Error:Survey is active and not editable')
                    );
                }

                $oGroup = new \QuestionGroup();
                $oGroup->sid = $iSurveyID;
                $oGroup->group_order = getMaxGroupOrder($iSurveyID);
                if (!$oGroup->save()) {
                    return new CommandResponse(
                        array('status' => 'Creation Failed')
                    );
                }

                $oQuestionGroupL10n = new \QuestionGroupL10n();
                $oQuestionGroupL10n->group_name = $sGroupTitle;
                $oQuestionGroupL10n->description = $sGroupDescription;
                $oQuestionGroupL10n->language = \Survey::model()->findByPk($iSurveyID)->language;
                $oQuestionGroupL10n->gid = $oGroup->gid;

                if ($oQuestionGroupL10n->save()) {
                    return new CommandResponse((int) $oGroup->gid);
                } else {
                    return new CommandResponse(
                        array('status' => 'Creation Failed')
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
