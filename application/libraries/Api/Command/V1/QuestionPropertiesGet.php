<?php

namespace LimeSurvey\Api\Command\V1;

use Answer;
use DefaultValue;
use Question;
use Survey;
use Yii;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Models\Services\QuestionAttributeHelper;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermission;
use LimeSurvey\Api\Command\Mixin\Accessor\QuestionModel;
use LimeSurvey\Api\Command\Mixin\Accessor\QuestionModelWithL10nsByIdAndLanguage;
use LimeSurvey\Api\Command\Mixin\CommandResponse;


class QuestionPropertiesGet implements CommandInterface
{
    use AuthSession;
    use AuthPermission;
    use CommandResponse;
    use QuestionModel;
    use QuestionModelWithL10nsByIdAndLanguage;


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

        if (
            ($response = $this->checkKey($sSessionKey)) !== true
        ) {
            return $response;
        }

        Yii::app()->loadHelper("surveytranslator");

        $oQuestion = $this->getQuestionModel($iQuestionID);
        if (!isset($oQuestion)) {
            return $this->responseErrorNotFound(
                ['status' => 'Error: Invalid questionid']
            );
        }

        $iSurveyID = $oQuestion->sid;

        if (
            ($response = $this->hasSurveyPermission(
                $iSurveyID,
                'survey',
                'read'
            )
            ) !== true
        ) {
            return $response;
        }

        if (is_null($sLanguage)) {
            $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
        }

        if (!array_key_exists($sLanguage, getLanguageDataRestricted())) {
            return $this->responseErrorBadRequest(
                ['status' => 'Error: Invalid language']
            );
        }

        $oQuestion = $this->getQuestionModelCollectionWithL10nsByIdAndLanguage(
            $iQuestionID,
            $sLanguage
        );
        if (!isset($oQuestion)) {
            return $this->responseErrorBadRequest(
                ['status' => 'Error: Invalid questionid']
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
            return $this->responseErrorBadRequest(
                ['status' => 'No valid Data']
            );
        }

        $aResult = [];
        foreach ($aQuestionSettings as $sPropertyName) {
            if ($sPropertyName == 'available_answers' || $sPropertyName == 'subquestions') {
                $oSubQuestions = Question::model()->with('questionl10ns')
                ->findAll(
                    't.parent_qid = :parent_qid and questionl10ns.language = :language',
                    [':parent_qid' => $iQuestionID, ':language' => $sLanguage],
                    ['order' => 'title']
                );

                if (count($oSubQuestions) > 0) {
                    $aData = [];
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
                    [':qid' => $iQuestionID, ':language' => $sLanguage],
                    ['order' => 'sortorder']
                );
                if (count($oAttributes) > 0) {
                    $aData = [];
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
                    ['qid' => $iQuestionID, 'language' => $sLanguage],
                    ['order' => 'sortorder']
                );
                if (count($oAttributes) > 0) {
                    $aData = [];
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
                $model = DefaultValue::model()->with('defaultvaluel10ns')
                ->find(
                    'qid = :qid AND defaultvaluel10ns.language = :language',
                    [':qid' => $iQuestionID, ':language' => $sLanguage]
                );
                $aResult['defaultvalue'] = $model ? $model->defaultvalue : '';
            } else {
                $aResult[$sPropertyName] = $oQuestion->$sPropertyName;
            }
        }
        return $this->responseSuccess($aResult);
    }
}
