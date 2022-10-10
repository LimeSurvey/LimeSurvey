<?php

namespace LimeSurvey\Api\Command\V1;

use Survey;
use Yii;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSurveyPermission;
use LimeSurvey\Api\Command\Mixin\CommandResponse;


class SurveyPropertiesGet implements CommandInterface
{
    use AuthSession;
    use AuthSurveyPermission;
    use CommandResponse;

    /**
     * Run survey properties get command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');
        $aSurveySettings = $request->getData('surveySettings', array());

        Yii::app()->loadHelper('surveytranslator');

        if (
            ($response = $this->checkKey($sSessionKey)) !== true
        ) {
            return $response;
        }

        $iSurveyID = (int) $iSurveyID;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if (!isset($oSurvey)) {
            return $this->responseErrorNotFound(
                array('status' => 'Error: Invalid survey ID')
            );
        }

        if (
            ($response = $this->hasSurveyPermission(
                $iSurveyID,
                'surveysettings',
                'read'
            )) !== true
        ) {
            return $response;
        }

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
            return $this->responseErrorBadRequest(
                array('status' => 'No valid Data')
            );
        }

        $aResult = array();
        foreach ($aSurveySettings as $sPropertyName) {
            $aResult[$sPropertyName] = $oSurvey->$sPropertyName;
        }

        return $this->responseSuccess(
            $aResult,
        );
    }
}
