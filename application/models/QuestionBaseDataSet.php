<?php

use LimeSurvey\Datavalueobjects\GeneralOption;
use LimeSurvey\Datavalueobjects\SwitchOption;
use LimeSurvey\Datavalueobjects\FormElement;

/**
 * This is a base class to enable all question tpyes to extend the general settings.
 * @TODO: Create an xml based solution to use external question type definitions as well
 */
abstract class QuestionBaseDataSet extends StaticModel
{
    private $iQuestionId;
    private $sQuestionType;
    private $sLanguage;
    private $oQuestion;

    public function __construct($iQuestionId)
    {
        $this->iQuestionId = $iQuestionId;
        $this->oQuestion = Question::model()->findByPk($iQuestionId);
    }

    /**
     * Returns a preformatted block of the general settings for the question editor
     *
     * @param int $iQuestionID
     * @param int $sQuestionType
     * @param string $sLanguage
     * @param null   $questionThemeName
     *
     * @return array
     * @throws CException
     */
    public function getGeneralSettingsArray($iQuestionID = null, $sQuestionType = null, $sLanguage = null, $questionThemeName = null)
    {
        Yii::import('ext.GeneralOptionWidget.settings.*');
        if ($iQuestionID != null) {
            $this->oQuestion = Question::model()->findByPk($iQuestionID);
        } else {
            $iSurveyId = Yii::app()->request->getParam('sid') ??
                Yii::app()->request->getParam('surveyid') ??
                Yii::app()->request->getParam('surveyId');
            $this->oQuestion = $oQuestion = QuestionCreate::getInstance($iSurveyId, $sQuestionType);
        }

        $this->sQuestionType = $sQuestionType == null ? $this->oQuestion->type : $sQuestionType;
        $this->sLanguage = $sLanguage == null ? $this->oQuestion->survey->language : $sLanguage;

        /*
        @todo Discussion:
        General options currently are
        - Question theme => this should have a seperate advanced tab in my opinion
        - Question group
        - Mandatory switch
        - Save as default switch
        - Clear default switch (if default value record exists)
        - Relevance equation
        - Validation => this is clearly a logic function
        Better add to general options:
        - Hide Tip => VERY OFTEN asked for
        - Always hide question => if available
        */
        $generalOptions = [
            /*
            'question_template' => QuestionThemeGeneralOption::make(
                $this->oQuestion,
                $this->sQuestionType,
                $question_template
            ),*/
            'gid'             => GroupSelectorGeneralOption::make($this->oQuestion, $this->sLanguage),
            'other'           => new OtherGeneralOption($this->oQuestion),
            'mandatory'       => new MandatoryGeneralOption($this->oQuestion),
            'relevance'       => new RelevanceEquationGeneralOption($this->oQuestion),
            'encrypted'       => new EncryptionGeneralOption($this->oQuestion),
            'preg'            => new ValidationGeneralOption($this->oQuestion),
            'save_as_default' => new SaveAsDefaultGeneralOption($this->oQuestion)
        ];

        $userSetting = SettingsUser::getUserSettingValue('question_default_values_' . $this->sQuestionType);
        if ($userSetting !== null) {
            $generalOptions['clear_default'] = new ClearDefaultGeneralOption();
        }

        // load visible general settings from config.xml
        $sFolderName = QuestionTemplate::getFolderName($this->sQuestionType);
        $sXmlFilePath = App()->getConfig('rootdir') . '/application/views/survey/questions/answer/' . $sFolderName . '/config.xml';
        if (file_exists($sXmlFilePath)) {
            // load xml file
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(false);
            }
            $xml_config = simplexml_load_file($sXmlFilePath);
            $aXmlAttributes = json_decode(json_encode((array)$xml_config->generalattributes), true);
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(true);
            }
        }

        foreach ($generalOptions as $key => $generalOption) {
            if (
                (isset($aXmlAttributes['attribute']) && in_array($key, $aXmlAttributes['attribute']))
                || !isset($aXmlAttributes['attribute'])
            ) {
                $generalOptionsFiltered[$key] = $generalOption;
            };
        }

        return $generalOptionsFiltered;
    }
}
