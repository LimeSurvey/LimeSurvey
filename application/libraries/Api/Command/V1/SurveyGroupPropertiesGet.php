<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\ApiSession;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\CommandRequest;
use LimeSurvey\Api\Command\CommandResponse;

class SurveyGroupPropertiesGet implements CommandInterface
{
    /**
     * Run group properties get command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\CommandRequest $request
     * @return LimeSurvey\Api\Command\CommandResponse
     */
    public function run(CommandRequest $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iGroupID = (int) $request->getData('groupID');
        $aGroupSettings = $request->getData('groupSettings', null);
        $sLanguage = $request->getData('language', null);

        $apiSession = new ApiSession;
        if ($apiSession->checkKey($sSessionKey)) {
            $oGroup = \QuestionGroup::model()
                ->with('questiongroupl10ns')
                ->findByAttributes(array('gid' => $iGroupID));
            if (!isset($oGroup)) {
                return new CommandResponse(
                    array('status' => 'Error: Invalid group ID')
                );
            }

            if (
                \Permission::model()
                ->hasSurveyPermission(
                    $oGroup->sid,
                    'survey',
                    'read'
                )
            ) {
                $iSurveyID = $oGroup->sid;
                if (is_null($sLanguage)) {
                    $sLanguage = \Survey::model()->findByPk($iSurveyID)->language;
                }

                if (!array_key_exists($sLanguage, getLanguageDataRestricted())) {
                    return new CommandResponse(
                        array('status' => 'Error: Invalid language')
                    );
                }

                $aBasicDestinationFields = \QuestionGroup::model()->tableSchema->columnNames;
                array_push($aBasicDestinationFields, 'group_name');
                array_push($aBasicDestinationFields, 'description');
                if (!empty($aGroupSettings)) {
                    $aGroupSettings = array_intersect(
                        $aGroupSettings,
                        $aBasicDestinationFields
                    );
                } else {
                    $aGroupSettings = $aBasicDestinationFields;
                }

                if (empty($aGroupSettings)) {
                    return new CommandResponse(array('status' => 'No valid Data'));
                }

                foreach ($aGroupSettings as $sGroupSetting) {
                    if (isset($oGroup->$sGroupSetting)) {
                        $aResult[$sGroupSetting] = $oGroup->$sGroupSetting;
                    } elseif (
                        isset($oGroup->questiongroupl10ns[$sLanguage])
                        && isset($oGroup->questiongroupl10ns[$sLanguage]->$sGroupSetting)
                    ) {
                        $aResult[$sGroupSetting] = $oGroup->questiongroupl10ns[$sLanguage]->$sGroupSetting;
                    }
                }
                return new CommandResponse($aResult);
            } else {
                return new CommandResponse(
                    array('status' => 'No permission')
                );
            }
        } else {
            return new CommandResponse(array(
                'status' => ApiSession::INVALID_SESSION_KEY
            ));
        }
    }
}
