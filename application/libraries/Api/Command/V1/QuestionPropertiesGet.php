<?php

namespace LimeSurvey\Api\Command\V1;

use Answer;
use DefaultValue;
use Permission;
use Question;
use Survey;
use Yii;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\Response\Status\StatusError;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;
use LimeSurvey\Models\Services\QuestionAttributeHelper;

class QuestionPropertiesGet implements CommandInterface
{
    /**
     * Run survey question properties get command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iQuestionID = (int) $request->getData('questionID');
        $aQuestionSettings = $request->getData('questionSettings');
        $sLanguage = (string) $request->getData('language');

        $apiSession = new ApiSession();
        if ($apiSession->checkKey($sSessionKey)) {
            Yii::app()->loadHelper("surveytranslator");
            $oQuestion = Question::model()->findByAttributes(array('qid' => $iQuestionID));
            if (!isset($oQuestion)) {
                return new Response(
                    array('status' => 'Error: Invalid questionid'),
                    new StatusErrorNotFound()
                );
            }

            $iSurveyID = $oQuestion->sid;

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'survey', 'read')) {
                if (is_null($sLanguage)) {
                    $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
                }

                if (!array_key_exists($sLanguage, getLanguageDataRestricted())) {
                    return new Response(
                        array('status' => 'Error: Invalid language'),
                        new StatusErrorBadRequest()
                    );
                }

                $oQuestion = Question::model()->with('questionl10ns')
                    ->find(
                        't.qid = :qid and questionl10ns.language = :language',
                        array(':qid' => $iQuestionID, ':language' => $sLanguage)
                    );
                if (!isset($oQuestion)) {
                    return new Response(
                        array('status' => 'Error: Invalid questionid'),
                        new StatusErrorBadRequest()
                    );
                }

                $aBasicDestinationFields = Question::model()->tableSchema->columnNames;
                array_push($aBasicDestinationFields, 'available_answers');
                array_push($aBasicDestinationFields, 'subquestions');
                array_push($aBasicDestinationFields, 'attributes');
                array_push($aBasicDestinationFields, 'attributes_lang');
                array_push($aBasicDestinationFields, 'answeroptions');
                array_push($aBasicDestinationFields, 'defaultvalue');
                if (!empty($aQuestionSettings)) {
                    $aQuestionSettings = array_intersect(
                        $aQuestionSettings,
                        $aBasicDestinationFields
                    );
                } else {
                    $aQuestionSettings = $aBasicDestinationFields;
                }

                if (empty($aQuestionSettings)) {
                    return new Response(
                        array('status' => 'No valid Data'),
                        new StatusSuccess()
                    );
                }

                $aResult = array();
                foreach ($aQuestionSettings as $sPropertyName) {
                    if ($sPropertyName == 'available_answers' || $sPropertyName == 'subquestions') {
                        $oSubQuestions = Question::model()->with('questionl10ns')
                            ->findAll(
                                't.parent_qid = :parent_qid and questionl10ns.language = :language',
                                array(':parent_qid' => $iQuestionID, ':language' => $sLanguage),
                                array('order' => 'title')
                            );

                        if (count($oSubQuestions) > 0) {
                            $aData = array();
                            foreach ($oSubQuestions as $oSubQuestion) {
                                if ($sPropertyName == 'available_answers') {
                                    $aData[$oSubQuestion['title']] = array_key_exists(
                                        $sLanguage,
                                        $oSubQuestion->questionl10ns
                                    ) ? $oSubQuestion->questionl10ns[$sLanguage]->question : '';
                                } else {
                                    $aData[$oSubQuestion['qid']]['title'] = $oSubQuestion['title'];
                                    $aData[$oSubQuestion['qid']]['question'] = array_key_exists(
                                        $sLanguage,
                                        $oSubQuestion->questionl10ns
                                    ) ? $oSubQuestion->questionl10ns[$sLanguage]->question : '';
                                    $aData[$oSubQuestion['qid']]['scale_id'] = $oSubQuestion['scale_id'];
                                }
                            }

                            $aResult[$sPropertyName] = $aData;
                        } else {
                            $aResult[$sPropertyName] = 'No available answers';
                        }
                    } elseif ($sPropertyName == 'attributes') {
                        $questionAttributeHelper = new QuestionAttributeHelper();
                        $questionAttributes = $questionAttributeHelper->getQuestionAttributesWithValues($oQuestion, null, null, true);
                        $data = [];
                        foreach ($questionAttributes as $attributeName => $attributeData) {
                            if (empty($attributeData['i18n'])) {
                                $data[$attributeName] = $attributeData['value'];
                            }
                        }
                        if (count($data) > 0) {
                            ksort($data, SORT_NATURAL | SORT_FLAG_CASE);
                            $aResult['attributes'] = $data;
                        } else {
                            $aResult['attributes'] = 'No available attributes';
                        }
                    } elseif ($sPropertyName == 'attributes_lang') {
                        $questionAttributeHelper = new QuestionAttributeHelper();
                        $questionAttributes = $questionAttributeHelper->getQuestionAttributesWithValues($oQuestion, $sLanguage, null, true);
                        $data = [];
                        foreach ($questionAttributes as $attributeName => $attributeData) {
                            if (!empty($attributeData['i18n'])) {
                                $data[$attributeName] = $attributeData[$sLanguage]['value'];
                            }
                        }
                        if (count($data) > 0) {
                            ksort($data, SORT_NATURAL | SORT_FLAG_CASE);
                            $aResult['attributes_lang'] = $data;
                        } else {
                            $aResult['attributes_lang'] = 'No available attributes';
                        }
                    } elseif ($sPropertyName == 'answeroptions') {
                        $oAttributes = Answer::model()->with('answerl10ns')
                            ->findAll(
                                't.qid = :qid and answerl10ns.language = :language',
                                array(':qid' => $iQuestionID, ':language' => $sLanguage),
                                array('order' => 'sortorder')
                            );
                        if (count($oAttributes) > 0) {
                            $aData = array();
                            foreach ($oAttributes as $oAttribute) {
                                $aData[$oAttribute['code']]['answer'] = array_key_exists(
                                    $sLanguage,
                                    $oAttribute->answerl10ns
                                ) ? $oAttribute->answerl10ns[$sLanguage]->answer : '';
                                $aData[$oAttribute['code']]['assessment_value'] = $oAttribute['assessment_value'];
                                $aData[$oAttribute['code']]['scale_id'] = $oAttribute['scale_id'];
                                $aData[$oAttribute['code']]['order'] = $oAttribute['sortorder'];
                            }
                            $aResult['answeroptions'] = $aData;
                        } else {
                            $aResult['answeroptions'] = 'No available answer options';
                        }
                    } elseif ($sPropertyName == 'answeroptions_multiscale') {
                        $oAttributes = Answer::model()->findAllByAttributes(
                            array('qid' => $iQuestionID, 'language' => $sLanguage),
                            array('order' => 'sortorder')
                        );
                        if (count($oAttributes) > 0) {
                            $aData = array();
                            foreach ($oAttributes as $oAttribute) {
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['code'] = $oAttribute['code'];
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['answer'] = $oAttribute['answer'];
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['assessment_value'] = $oAttribute['assessment_value'];
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['scale_id'] = $oAttribute['scale_id'];
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['order'] = $oAttribute['sortorder'];
                            }
                            $aResult['answeroptions'] = $aData;
                        } else {
                            $aResult['answeroptions'] = 'No available answer options';
                        }
                    } elseif ($sPropertyName == 'defaultvalue') {
                        $aResult['defaultvalue'] = DefaultValue::model()->with('defaultvaluel10ns')
                            ->find(
                                'qid = :qid AND defaultvaluel10ns.language = :language',
                                array(':qid' => $iQuestionID, ':language' => $sLanguage)
                            )
                            ->defaultvalue;
                    } else {
                        $aResult[$sPropertyName] = $oQuestion->$sPropertyName;
                    }
                }
                return new Response(
                    $aResult,
                    new StatusSuccess()
                );
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
