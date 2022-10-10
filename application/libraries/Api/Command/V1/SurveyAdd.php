<?php

namespace LimeSurvey\Api\Command\V1;

use Exception;
use Permission;
use Survey;
use SurveyLanguageSetting;
use Yii;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermission;
use LimeSurvey\Api\Command\Mixin\CommandResponse;

class SurveyAdd implements CommandInterface
{
    use AuthSession;
    use AuthPermission;
    use CommandResponse;

    /**
     * Run survey add command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = $request->getData('surveyID');
        $sSurveyTitle = (string) $request->getData('surveyTitle');
        $sSurveyLanguage = (string) $request->getData('surveyLanguage');
        $sformat = (string) $request->getData('format', 'G');

        if (
            ($response = $this->checkKey($sSessionKey)) !== true
        ) {
            return $response;
        }

        if (
            ($response = $this->hasSurveyPermission(
                $iSurveyID,
                'survey',
                'create'
            )
            ) !== true
        ) {
            return $response;
        }

        Yii::app()->loadHelper("surveytranslator");

        if (
            $sSurveyTitle == ''
            || $sSurveyLanguage == ''
            || !array_key_exists($sSurveyLanguage, getLanguageDataRestricted())
            || !in_array($sformat, array('A', 'G', 'S'))
        ) {
            return $this->responseErrorBadRequest(
                array('status' => 'Faulty parameters')
            );
        }

        $aInsertData = array(
            'template' => App()->getConfig('defaulttheme'),
            'owner_id' => Yii::app()->session['loginID'],
            'active' => 'N',
            'language' => $sSurveyLanguage,
            'format' => $sformat
        );

        if (!is_null($iSurveyID)) {
            $aInsertData['wishSID'] = $iSurveyID;
        }

        try {
            $newSurvey = Survey::model()->insertNewSurvey($aInsertData);
            if (!$newSurvey->sid) {
                // status are a string, another way to send errors ?
                return new Response(
                    array('status' => 'Creation Failed')
                );
            }
            $iNewSurveyid = $newSurvey->sid;

            $sTitle = html_entity_decode($sSurveyTitle, ENT_QUOTES, "UTF-8");

            $aInsertData = array(
                'surveyls_survey_id' => $iNewSurveyid,
                'surveyls_title' => $sTitle,
                'surveyls_language' => $sSurveyLanguage,
            );

            $langsettings = new SurveyLanguageSetting();
            $langsettings->insertNewSurvey($aInsertData);
            Permission::model()->giveAllSurveyPermissions(
                Yii::app()->session['loginID'],
                $iNewSurveyid
            );

            return $this->responseSuccess(
                (int) $iNewSurveyid,
                new StatusSuccess()
            );
        } catch (Exception $e) {
            return $this->responseException($e);
        }
    }
}
