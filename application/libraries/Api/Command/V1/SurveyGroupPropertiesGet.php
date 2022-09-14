<?php

namespace LimeSurvey\Api\Command\V1;

use Permission;
use QuestionGroup;
use Survey;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\ApiSession;

class SurveyGroupPropertiesGet implements CommandInterface
{
    /**
     * Run group properties get command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\Request\Request $request
     * @return LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iGroupID = (int) $request->getData('groupID');
        $aGroupSettings = $request->getData('groupSettings', null);
        $sLanguage = $request->getData('language', null);

        $apiSession = new ApiSession;
        if ($apiSession->checkKey($sSessionKey)) {
            $oGroup = QuestionGroup::model()
                ->with('questiongroupl10ns')
                ->findByAttributes(array('gid' => $iGroupID));
            if (!isset($oGroup)) {
                return new Response(
                    array('status' => 'Error: Invalid group ID')
                );
            }

            if (
                Permission::model()
                ->hasSurveyPermission(
                    $oGroup->sid,
                    'survey',
                    'read'
                )
            ) {
                $iSurveyID = $oGroup->sid;
                if (is_null($sLanguage)) {
                    $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
                }

                if (!array_key_exists($sLanguage, getLanguageDataRestricted())) {
                    return new Response(
                        array('status' => 'Error: Invalid language')
                    );
                }

                $aBasicDestinationFields = QuestionGroup::model()->tableSchema->columnNames;
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
                    return new Response(array('status' => 'No valid Data'));
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
                return new Response($aResult);
            } else {
                return new Response(
                    array('status' => 'No permission')
                );
            }
        } else {
            return new Response(array(
                'status' => ApiSession::INVALID_SESSION_KEY
            ));
        }
    }
}
