<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\ApiSession;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\CommandRequest;
use LimeSurvey\Api\Command\CommandResponse;

class SurveyPropertiesGet implements CommandInterface
{
    /**
     * Run survey properties get command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\CommandRequest $request
     * @return LimeSurvey\Api\Command\CommandResponse
     */
    public function run(CommandRequest $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');
        $aSurveySettings = $request->getData('surveySettings', array());

        \Yii::app()->loadHelper('surveytranslator');
        $apiSession = new ApiSession;
        if ($apiSession->checkKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = \Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                return new CommandResponse(
                    array('status' => 'Error: Invalid survey ID')
                );
            }
            if (
                \Permission::model()->hasSurveyPermission(
                    $iSurveyID,
                    'surveysettings',
                    'read'
                )
            ) {
                $aBasicDestinationFields = \Survey::model()
                    ->tableSchema
                    ->columnNames;
                if (!empty($aSurveySettings)) {
                    $aSurveySettings = array_intersect(
                        $aSurveySettings,
                        $aBasicDestinationFields
                    );
                } else {
                    $aSurveySettings = $aBasicDestinationFields;
                }
                if (empty($aSurveySettings)) {
                    return new CommandResponse(
                        array('status' => 'No valid Data')
                    );
                }
                $aResult = array();
                foreach ($aSurveySettings as $sPropertyName) {
                    $aResult[$sPropertyName] = $oSurvey->$sPropertyName;
                }
                return new CommandResponse($aResult);
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
