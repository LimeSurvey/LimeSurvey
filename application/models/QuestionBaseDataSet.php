<?php
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
    private $aQuestionAttributes;

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
     * @param int $iSurveyID
     * @param string $sLanguage
     * @return array
     */
    public function getGeneralSettingsArray($iQuestionID = null, $sQuestionType = null, $sLanguage = null)
    {
        if ($iQuestionID != null) {
            $this->oQuestion = Question::model()->findByPk($iQuestionID);
        }
        
        $this->sQuestionType = $sQuestionType == null ? $this->oQuestion->type : $sQuestionType;
        $this->sLanguage = $sLanguage == null ? $this->oQuestion->survey->language : $sLanguage;
        $this->aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($this->oQuestion->qid, $this->sLanguage);
        /*
        @todo Discussion:
        General options currently are
        - Question theme => this should have a seperate advanced tab in my opinion
        - Question group
        - Mandatory switch
        - Relevance equation
        - Validation => this is clearly a logic function

        Better add to general options:
        - Hide Tip => VERY OFTEN asked for
        - Always hide question => if available
        */
        return [
            $this->getQuestionThemeOption(),
            $this->getQuestionGroupSelector(),
            $this->getOtherSwitch(),
            $this->getMandatorySwitch(),
            $this->getRelevanceEquationInput(),
            $this->getValidationInput(),
        ];
    }

    /**
     * Returns a preformatted block of the advanced settings for the question editor
     *
     * @param int $iQuestionID
     * @param int $sQuestionType
     * @param int $iSurveyID
     * @param string $sLanguage
     * @return array
     */
    public function getAdvancedOptions($iQuestionID = null, $sQuestionType = null, $sLanguage = null)
    {
        if ($iQuestionID != null) {
            $this->oQuestion = Question::model()->findByPk($iQuestionID);
        }
        
        $this->sQuestionType = $sQuestionType == null ? $this->oQuestion->type : $sQuestionType;
        $this->sLanguage = $sLanguage == null ? $this->oQuestion->survey->language : $sLanguage;
        $this->aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($this->oQuestion->qid, $this->sLanguage);
        
        $sQuestionType = $this->sQuestionType;
        
        $aAttributes = \LimeSurvey\Helpers\questionHelper::getAttributesDefinitions();
        /* Filter to get this question type setting */
        $aQuestionTypeAttributes = array_filter($aAttributes, function($attribute) use ($sQuestionType) {
            return stripos($attribute['types'], $sQuestionType) !== false;
        });

        $aAdvancedOptionsArray = [];
        foreach($aQuestionTypeAttributes as $sAttributeName => $aQuestionAttributeArray) {
            $aAdvancedOptionsArray[$aQuestionAttributeArray['category']][] = $this->parseFromAttributeHelper($sAttributeName, $aQuestionAttributeArray);
        }
        
        return $aAdvancedOptionsArray;

    }

    //Question theme
    protected function getQuestionThemeOption()
    {
        $aQuestionTemplateList = QuestionTemplate::getQuestionTemplateList($this->sQuestionType);
        $aQuestionTemplateAttributes = Question::model()->getAdvancedSettingsWithValues($this->oQuestion->qid, $this->sQuestionType, $this->oQuestion->survey->sid)['question_template'];

        $aOptionsArray = [];
        foreach ($aQuestionTemplateList as $code => $value) {
            $bSelected = false;
            $sSelected = '';
        
            if (!empty($aQuestionTemplateAttributes) && isset($aQuestionTemplateAttributes['value'])) {
                $bSelected = $aQuestionTemplateAttributes['value'] == $code;
            }
            $aOptionsArray[] = ['name' => $value['title'], 'value' => $code, 'selected' => $bSelected];
        }

        return [
                'name' => 'QuestionTheme',
                'title' => gT('Question theme'),
                'formElementId' => 'question_template',
                'formElementName' => false, //false means identical to id
                'formElementHelp' => gT("Use a customized question theme for this question"),
                'inputtype' => 'select',
                'formElementValue' => isset($aQuestionTemplateAttributes['value']) ? $aQuestionTemplateAttributes['value'] : '',
                'formElementOptions' => [
                    'classes' => ['form-control'],
                    'options' => $aOptionsArray,
                ],
            ];
    }

    //Question group
    protected function getQuestionGroupSelector()
    {
        $aGroupsToSelect = QuestionGroup::model()->findAllByAttributes(array('sid' => $this->oQuestion->sid), array('order'=>'group_order'));
        $aGroupOptions = array_map(
            function ($oQuestionGroup) {
                return [
                    'name' => $oQuestionGroup->questionGroupL10ns[$this->sLanguage]->group_name,
                    'value' => $oQuestionGroup->gid,
                    'selected' => $oQuestionGroup->gid == $this->oQuestion->gid
                ];
            },
            $aGroupsToSelect
        );

        return [
            'name' => 'QuestionGroup',
            'title' => gT('Question group'),
            'formElementId' => 'gid',
            'formElementName' => false,
            'formElementHelp' => gT("If you want to change the question group this question is in."),
            'inputtype' => 'select',
            'formElementValue' => $this->oQuestion->gid,
            'formElementOptions' => [
                'classes' => ['form-control'],
                'options' => $aGroupOptions,
            ],
        ];
    }

    protected function getOtherSwitch()
    {
        return  [
                'name' => 'other',
                'title' => gT('Other'),
                'formElementId' => 'other',
                'formElementName' => false,
                'formElementHelp' => gT('Activate the "other" option for your question'),
                'inputtype' => 'switch',
                'formElementValue' => $this->oQuestion->other == 'Y',
                'formElementOptions' => [
                    'classes' => [],
                    'switchData' => [
                        'onText' => gT("On"),
                        'offText' => gT("Off"),
                        'onColor' => "primary",
                        'offColor' => "warning",
                        'size' => "small",
                    ],
                ],                
            ];
    }

    protected function getMandatorySwitch()
    {
        return [
                'name' => 'mandatory',
                'title' => gT('Mandatory'),
                'formElementId' => 'mandatory',
                'formElementName' => false,
                'formElementHelp' => gT('Makes this question mandatory in your survey'),
                'inputtype' => 'switch',
                'formElementValue' => $this->oQuestion->mandatory == 'Y',
                'formElementOptions' => [
                    'classes' => [],
                    'switchData' => [
                        'onText' => gT("On"),
                        'offText' => gT("Off"),
                        'onColor' => "primary",
                        'offColor' => "warning",
                        'size' => "small",
                    ],
                ],
            ];
    }
        
    protected function getRelevanceEquationInput()
    {
        $inputtype = 'textarea';
        
        if (count($this->oQuestion->conditions) > 0) {
            $inputtype = 'text';
            $content = gT("Note: You can't edit the relevance equation because there are currently conditions set for this question.");
        }

        return [
                'name' => 'RelevanceEquation',
                'title' => gT('Relevance equation'),
                'formElementId' => 'relevance',
                'formElementName' => false,
                'formElementHelp' => (count($this->oQuestion->conditions)>0 ? '' :gT("The relevance equation can be used to add branching logic. This is a rather advanced topic. If you are unsure, just leave it be.")),
                'inputtype' => 'textarea',
                'formElementValue' => $this->oQuestion->relevance,
                'formElementOptions' => [
                    'classes' => ['form-control'],
                    'attributes' => [
                        'rows' => 1,
                        'readonly' => count($this->oQuestion->conditions)>0
                    ],
                    'inputGroup' => [
                        'prefix' => '{',
                        'suffix' => '}',
                    ]
                ],
            ];
    }
            
    protected function getValidationInput()
    {
        return  [
                'name' => 'validation',
                'title' => gT('Input validation'),
                'formElementId' => 'preg',
                'formElementName' => false,
                'formElementHelp' => gT('You can add any regular expression based validation in here'),
                'inputtype' => 'text',
                'formElementValue' => $this->oQuestion->preg,
                'formElementOptions' => [
                    'classes' => ['form-control'],
                    'inputGroup' => [
                        'prefix' => 'RegExp',
                    ]
                ],
            ];
    }

    protected function parseFromAttributeHelper($sAttributeKey,$aAttributeArray) 
    {
        $aAdvancedAttributeArray = [
            'name' => $sAttributeKey,
            'title' => $aAttributeArray['caption'],
            'inputtype' => $aAttributeArray['inputtype'],
            'formElementId' => $sAttributeKey,
            'formElementName' => false,
            'formElementHelp' => $aAttributeArray['help'],
            'formElementValue' => isset($this->aQuestionAttributes[$sAttributeKey]) ? $this->aQuestionAttributes[$sAttributeKey] : ''
        ];
        unset($aAttributeArray['caption']);
        unset($aAttributeArray['help']);
        unset($aAttributeArray['inputtype']);

        $aAdvancedAttributeArray['aFormElementOptions'] = array_merge(['classes' => ['form-control']], $aAttributeArray);
        
        return $aAdvancedAttributeArray;
    }
}
