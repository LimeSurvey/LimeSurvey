<?php

namespace LimeSurvey\Api\Command\V1;

use QuestionGroup;
use Survey;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSurveyPermission;
use LimeSurvey\Api\Command\Mixin\CommandResponse;

class QuestionGroupPropertiesGet implements CommandInterface
{
    use AuthSession;
    use AuthSurveyPermission;
    use CommandResponse;

    /**
     * Run group properties get command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iGroupID = (int) $request->getData('groupID');
        $aGroupSettings = $request->getData('groupSettings', null);
        $sLanguage = $request->getData('language', null);

        if (
            ($response = $this->checkKey($sSessionKey)) !== true
        ) {
            return $response;
        }

        $oGroup = QuestionGroup::model()
            ->with('questiongroupl10ns')
            ->findByAttributes(array('gid' => $iGroupID));
        if (!isset($oGroup)) {
            return $this->responseErrorNotFound(
                array('status' => 'Error: Invalid group ID')
            );
        }

        if (
            ($response = $this->hasSurveyPermission(
                $oGroup->sid,
                'survey',
                'read'
            )
            ) !== true
        ) {
            return $response;
        }

        $iSurveyID = $oGroup->sid;
        if (is_null($sLanguage)) {
            $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
        }

        if (!array_key_exists($sLanguage, getLanguageDataRestricted())) {
            return $this->responseErrorBadRequest(
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
            return $this->responseSuccess(
                array('status' => 'No valid Data')
            );
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
        return $this->responseSuccess(
            $aResult
        );
    }
}
