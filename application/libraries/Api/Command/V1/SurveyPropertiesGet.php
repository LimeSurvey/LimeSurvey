<?php

namespace LimeSurvey\Api\Command\V1;

use Permission;
use Survey;
use Yii;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\ApiSession;

class SurveyPropertiesGet implements CommandInterface
{
    /**
     * Run survey properties get command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\Request\Request $request
     * @return LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');
        $aSurveySettings = $request->getData('surveySettings', array());

        Yii::app()->loadHelper('surveytranslator');
        $apiSession = new ApiSession;
        if ($apiSession->checkKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                return new Response(
                    array('status' => 'Error: Invalid survey ID')
                );
            }
            if (
                Permission::model()->hasSurveyPermission(
                    $iSurveyID,
                    'surveysettings',
                    'read'
                )
            ) {
                $aBasicDestinationFields = Survey::model()
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
                    return new Response(
                        array('status' => 'No valid Data')
                    );
                }
                $aResult = array();
                foreach ($aSurveySettings as $sPropertyName) {
                    $aResult[$sPropertyName] = $oSurvey->$sPropertyName;
                }
                return new Response($aResult);
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
