<?php

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

    //Question theme
    public function getQuestionThemeOption()
    {
        $aQuestionTemplateList = QuestionTemplate::getQuestionTemplateList($this->sQuestionType);
        $aQuestionTemplateAttributes = Question::model()->getAdvancedSettingsWithValues($this->oQuestion->qid, $this->sQuestionType, $this->oQuestion->survey->sid)['question_template'];

        $sQuestionTemplateSettingsItemHtml = '<p class="help-block collapse" id="help_question_template">'.gT("Use a customized question theme for this question").'</p>'
            .'<select id="question_template" name="question_template" class="form-control">';
        $aOptionsArray = [];
        
        foreach ($aQuestionTemplateList as $code => $value) {
            $bSelected = false;
            $sSelected = '';
        
            if (!empty($aQuestionTemplateAttributes) && isset($aQuestionTemplateAttributes['value'])) {
                $bSelected = $aQuestionTemplateAttributes['value'] == $code;

                $question_template_preview = $bSelected ? $value['preview'] : $question_template_preview;
                $sSelected = $bSelected ? 'selected' : '';
            }
            if (YII_DEBUG) {
                $sQuestionTemplateSettingsItemHtml .= sprintf("<option value='%s' %s>%s (code: %s)</option>", $code, $sSelected, $value['title'], $code);
            } else {
                $sQuestionTemplateSettingsItemHtml .= sprintf("<option value='%s' %s>%s</option>", $code, $sSelected, $value['title']);
            }
            $aOptionsArray[] = ['name' => $value['title'], 'value' => $code, 'selected' => $bSelected];
        }
        $sQuestionTemplateSettingsItemHtml .= '</select>'
            .'<div class="help-block" id="QuestionTemplatePreview">'
                .'<strong>'.gT("Preview:").'</strong>'
                    .'<div class="">'
                        .'<img src="'.$question_template_preview.'" class="img-thumbnail img-responsive center-block">'
                    .'</div>'
            .'</div>';

        return [
                'name' => 'QuestionTheme',
                'title' => gT('Question theme'),
                'formElementId' => 'question_template',
                'formElementName' => false, //false means identical to id
                'formElementHelp' => gT("Use a customized question theme for this question"),
                'formElement' => 'select',
                'formElementOptions' => [
                    'classes' => ['form-control'],
                    'options' => $aOptionsArray,
                ],
                'formElementHtml' => $sQuestionTemplateSettingsItemHtml //just for proof of concept
            ];
    }

    //Question group
    public function getQuestionGroupSelector()
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
        $QuestionGroupSettingsItemHtml = '';
        $QuestionGroupSettingsItemHtml = '<div class="">'
            .'<select name="gid" id="gid" class="form-control" '.($this->oQuestion->survey->isActive ? " disabled ":'').'>'
                .getGroupList3($this->oQuestion->gid, $this->oQuestion->survey->sid)
            .'</select>';
        if ($this->oQuestion->survey->isActive) {
            $QuestionGroupSettingsItemHtml .= '<input type="hidden" name="gid" value="'.$this->oQuestion->gid.'" />';
        }
        $QuestionGroupSettingsItemHtml .= '</div>';
        return [
            'name' => 'QuestionGroup',
            'title' => gT('Question group'),
            'formElementId' => 'gid',
            'formElementName' => false,
            'formElementHelp' => gT("If you want to change the question group this question is in."),
            'formElement' => 'select',
            'formElementOptions' => [
                'classes' => ['form-control'],
                'options' => $aGroupOptions,
            ],
            'formElementHtml' => $QuestionGroupSettingsItemHtml
        ];
    }

    public function getOtherSwitch()
    {
        return  [
                'name' => 'other',
                'title' => gT('Other'),
                'formElementId' => 'other',
                'formElementName' => false,
                'formElementHelp' => gT('Activate the "other" option for your question'),
                'formElement' => 'switcher',
                'formElementOptions' => [
                    'classes' => [],
                    'switchData' => [
                        'on-text' => "On",
                        'off-text' => "Off",
                        'on-color' => "primary",
                        'off-color' => "warning",
                        'size' => "small",
                    ],
                ],
                'formElementHtml' => '<input data-on-text="On" data-off-text="Off" data-on-color="primary" data-off-color="warning" data-size="small" data-is-bootstrap-switch="1" id="other" name="other" type="checkbox" value="1">'
            ];
    }

    public function getMandatorySwitch()
    {
        return [
                'name' => 'mandatory',
                'title' => gT('Mandatory'),
                'formElementId' => 'mandatory',
                'formElementName' => false,
                'formElementHelp' => gT('Makes this question mandatory in your survey'),
                'formElement' => 'switcher',
                'formElementOptions' => [
                    'classes' => [],
                    'switchData' => [
                        'on-text' => "On",
                        'off-text' => "Off",
                        'on-color' => "primary",
                        'off-color' => "warning",
                        'size' => "small",
                    ],
                ],
                'formElementHtml' =>'<input data-on-text="On" data-off-text="Off" data-on-color="primary" data-off-color="warning" data-size="small" data-is-bootstrap-switch="1" id="mandatory" name="mandatory" type="checkbox" value="1">'
            ];
    }
        
    public function getRelevanceEquationInput()
    {
        $relevanceIntputHtml = ''
            .'<div class="input-group">
                    <div class="input-group-addon">{</div>
                    <textarea class="form-control" rows="1" id="relevance" name="relevance" '
                        .(count($this->oQuestion->conditions)>0 ? "readonly='readonly'" : "").'>'
                            .$this->oQuestion->relevance
                        .'</textarea>'
                    .'<div class="input-group-addon">}</div>'
                .'</div>';
        if (count($this->oQuestion->conditions) > 0) {
            $relevanceIntputHtml .= '<div class="help-block text-warning">'
                . gT("Note: You can't edit the relevance equation because there are currently conditions set for this question.")
                .'</div>';
        }

        return [
                'name' => 'RelevanceEquation',
                'title' => gT('Relevance equation'),
                'formElementId' => 'relevance',
                'formElementName' => false,
                'formElementHelp' => (count($this->oQuestion->conditions)>0
                    ? gT("Note: You can't edit the relevance equation because there are currently conditions set for this question.")
                    : gT("The relevance equation can be used to add branching logic. This is a rather advanced topic. If you are unsure, just leave it be.")),
                'formElement' => 'textarea',
                'formElementOptions' => [
                    'classes' => 'form-control',
                    'attributes' => [
                        'rows' => 1,
                        'readonly' => count($this->oQuestion->conditions)>0
                    ],
                    'inputGroup' => [
                        'prefix' => '{',
                        'suffix' => '}',
                    ]
                ],
                'formElementHtml' => $relevanceIntputHtml
            ];
    }
            
    public function getValidationInput()
    {
        return  [
                'name' => 'RelevanceEquation',
                'title' => gT('Relevance equation'),
                'formElementId' => 'preg',
                'formElementName' => false,
                'formElementHelp' => gT('You can add any regular expression based validation in here'),
                'formElement' => 'input',
                'formElementOptions' => [
                    'classes' => 'form-control',
                    'inputGroup' => [
                        'prefix' => 'RegExp',
                    ]
                ],
                'formElementHtml' => '<input class="form-control" type="text" id="preg" name="preg" size="50" value="'.$this->oQuestion->preg.'" />'
            ];
    }
}
