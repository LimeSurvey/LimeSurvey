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
use LimeSurvey\Api\Command\Response\Status\StatusError;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;

class SurveyAdd implements CommandInterface
{
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

        $apiSession = new ApiSession();

        Yii::app()->loadHelper("surveytranslator");
        if ($apiSession->checkKey($sSessionKey)) {
            if (Permission::model()->hasGlobalPermission('surveys', 'create')) {
                if (
                    $sSurveyTitle == ''
                    || $sSurveyLanguage == ''
                    || !array_key_exists($sSurveyLanguage, getLanguageDataRestricted())
                    || !in_array($sformat, array('A', 'G', 'S'))
                ) {
                    return new Response(
                        array('status' => 'Faulty parameters'),
                        new StatusErrorBadRequest()
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
                        return array('status' => 'Creation Failed'); // status are a string, another way to send errors ?
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

                    return new Response(
                        (int) $iNewSurveyid,
                        new StatusSuccess()
                    );
                } catch (Exception $e) {
                    return new Response(
                        array('status' => $e->getMessage()),
                        new StatusError()
                    );
                }
            } else {
                return new Response(
                    array('status' => 'No permission'),
                    new StatusErrorUnauthorised()
                );
            }
        } else {
            return new Response(
                array('status' => ApiSession::INVALID_SESSION_KEY),
                new StatusErrorUnauthorised()
            );
        }
    }
}
