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
     * @param string $sLanguage
     * @param null   $question_template
     *
     * @return array
     * @throws CException
     */
    public function getGeneralSettingsArray($iQuestionID = null, $sQuestionType = null, $sLanguage = null, $question_template=null)
    {
        if ($iQuestionID != null) {
            $this->oQuestion = Question::model()->findByPk($iQuestionID);
        } else {
            $iSurveyId = Yii::app()->request->getParam('sid') ?? Yii::app()->request->getParam('surveyid');
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
            'question_template' => $this->getQuestionThemeOption($question_template),
            'gid' => $this->getQuestionGroupSelector(),
            'other' => $this->getOtherSwitch(),
            'mandatory' => $this->getMandatorySetting(),
            'relevance' => $this->getRelevanceEquationInput(),
            'encrypted' => $this->getEncryptionSwitch(),
            'save_as_default' => $this->getSaveAsDefaultSwitch()
        ];
        
        $userSetting = SettingsUser::getUserSettingValue('question_default_values_' . $this->sQuestionType);
        if ($userSetting !== null){
            $generalOptions['clear_default'] = $this->getClearDefaultSwitch();
        }

        $generalOptions['preg'] = $this->getValidationInput();

        // load visible general settings from config.xml
        $sFolderName = QuestionTemplate::getFolderName($this->sQuestionType);
        $sXmlFilePath = App()->getConfig('rootdir').'/application/views/survey/questions/answer/'.$sFolderName.'/config.xml';
        if(file_exists($sXmlFilePath)){
            // load xml file
            libxml_disable_entity_loader(false);
            $xml_config = simplexml_load_file($sXmlFilePath);
            $aXmlAttributes = json_decode(json_encode((array)$xml_config->generalattributes), TRUE);
            libxml_disable_entity_loader(true);


        }
        foreach ($generalOptions as $key => $generalOption){
            if ((isset($aXmlAttributes['attribute']) && in_array($key, $aXmlAttributes['attribute'])) || !isset($aXmlAttributes['attribute'])){
                $generalOptionsFiltered[$key] = $generalOption;
            };
        }

        return $generalOptionsFiltered;
    }

    /**
     * Returns a preformatted block of the advanced settings for the question editor
     *
     * @param int    $iQuestionID
     * @param int    $sQuestionType
     * @param string $sLanguage
     * @param string   $sQuestionTemplate
     *
     * @deprecated use getPreformattedBlockOfAdvancedSettings() instead of this function
     *
     * @return array
     * @throws CException
     */
    public function getAdvancedOptions($iQuestionID = null, $sQuestionType = null, $sLanguage = null,  $sQuestionTemplate = null)
    {
        if ($iQuestionID != null) {
            $this->oQuestion = Question::model()->findByPk($iQuestionID);
        } else {
            $iSurveyId = App()->request->getParam('sid') ?? App()->request->getParam('surveyid'); //todo this should be done in controller ...
            $this->oQuestion = $oQuestion = QuestionCreate::getInstance($iSurveyId, $sQuestionType);
        }
        
        $this->sQuestionType = $sQuestionType == null ? $this->oQuestion->type : $sQuestionType;
        $this->sLanguage = $sLanguage == null ? $this->oQuestion->survey->language : $sLanguage;

        $aAdvancedOptionsArray = [];
        if ($iQuestionID == null) { //this is only the case if question is new and has not been saved
            $userSetting = SettingsUser::getUserSettingValue('question_default_values_' . $this->oQuestion->type);
            if ($userSetting !== null){
                $aAdvancedOptionsArray = (array) json_decode($userSetting);
            }
        }

        if (empty($aAdvancedOptionsArray)) {
            //this function call must be here, because $this->aQuestionAttributes is used in function below (parseFromAttributeHelper)
            $this->aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($this->oQuestion->qid, $sLanguage);
            if( $sQuestionTemplate === null && $this->aQuestionAttributes['question_template'] !== 'core') {
                $sQuestionTemplate = $this->aQuestionAttributes['question_template'];
            }
            $sQuestionTemplate = $sQuestionTemplate == '' || $sQuestionTemplate == 'core' ? null : $sQuestionTemplate;

            $aQuestionTypeAttributes = QuestionTheme::getQuestionThemeAttributeValues($this->sQuestionType, $sQuestionTemplate);
            uasort($aQuestionTypeAttributes, 'categorySort');

            foreach ($aQuestionTypeAttributes as $sAttributeName => $aQuestionAttributeArray) {
                if ($sAttributeName == 'question_template') {
                    continue; // Avoid double displaying
                }
                $formElementValue = isset($this->aQuestionAttributes[$sAttributeName]) ? $this->aQuestionAttributes[$sAttributeName] : '';
                $aAdvancedOptionsArray[$aQuestionAttributeArray['category']][$sAttributeName] = $this->parseFromAttributeHelper($sAttributeName, $aQuestionAttributeArray, $formElementValue);
            }
        }
        
        return $aAdvancedOptionsArray;
    }

    /**
     * Returns a preformatted block of the advanced settings for the question editor (qe).
     * The advanced settings are the part at the bottom of the qe. They depend on the question type and the
     * question theme.
     * Result should look like:
     * Display  --> category
     *      repeat_headings   --> attributename
     *          name
     *          title
     *          inputtpye
     *          formElementId
     *          formElementName
     *          formElementHelp
     *          formElementValue
     *          aFormElementOptions
     *      answer_width
     *          name
     *          ...
     * Logic
     *      min_answers
     *          name
     *          ...
     * @param Question|QuestionCreate $oQuestion
     * @param string $sQuestionTheme
     *
     * @throws Exception when question type attributes are not available
     * @return array
     */
    public function getPreformattedBlockOfAdvancedSettings($oQuestion,  $sQuestionTheme = null){
        $advancedOptionsArray = array();
        $this->oQuestion = $oQuestion;
        $this->sQuestionType = $this->oQuestion->type;
        $this->sLanguage = $this->oQuestion->survey->language;

        //get all attributes for advanced settings (e.g. Subquestions, Attribute, Display, Display Theme options, Logic, Other, Statistics)
        if ($this->oQuestion->qid == null) { //this is only the case if question is new and has not been saved
            $userSetting = SettingsUser::getUserSettingValue('question_default_values_' . $this->oQuestion->type);
            if ($userSetting !== null){
                $advancedOptionsArray = (array) json_decode($userSetting);
            }
        }
        if (empty($advancedOptionsArray)) {
            $questionThemeFromDB = QuestionAttribute::model()->find("qid=:qid AND attribute=:attribute", array('qid'=>$this->oQuestion->qid, 'attribute' => "question_template"));
            if( $sQuestionTheme === null && $questionThemeFromDB->value !== 'core') {
                $sQuestionTheme = $questionThemeFromDB->value;
            }
            $sQuestionTheme = $sQuestionTheme == '' || $sQuestionTheme == 'core' ? null : $sQuestionTheme;

            $aQuestionTypeAttributes = QuestionTheme::getQuestionThemeAttributeValues($this->sQuestionType, $sQuestionTheme);
            uasort($aQuestionTypeAttributes, 'categorySort');
            $questionAttributesValuesFromDB = QuestionAttribute::model()->findAll("qid=:qid", array('qid'=>$this->oQuestion->qid));

            foreach ($aQuestionTypeAttributes as $sAttributeName => $aQuestionAttributeArray) {
                if ($sAttributeName == 'question_template') {
                    continue; // Avoid double displaying
                }
                $formElementValue = isset($questionAttributesValuesFromDB[$sAttributeName]) ? $questionAttributesValuesFromDB[$sAttributeName]->value : '';
                $advancedOptionsArray[$aQuestionAttributeArray['category']][$sAttributeName] = $this->parseFromAttributeHelper($sAttributeName, $aQuestionAttributeArray, $formElementValue);
            }
        }
        return $advancedOptionsArray;
    }

    //Question theme
    protected function getQuestionThemeOption($currentSetQuestionTheme = null)
    {
        $aQuestionTemplateList = QuestionTemplate::getQuestionTemplateList($this->sQuestionType);
        $aQuestionTemplateAttributes = Question::model()->getAdvancedSettingsWithValues($this->oQuestion->qid, $this->sQuestionType, $this->oQuestion->survey->sid)['question_template'];

        $aOptionsArray = [];
        foreach ($aQuestionTemplateList as $code => $value) {
            $aOptionsArray[] = [
                'value' => $code,
                'text' => $value['title']
            ];
        }

        if ($currentSetQuestionTheme == null) {
            $currentSetQuestionTheme = (isset($aQuestionTemplateAttributes['value']) && $aQuestionTemplateAttributes['value'] !== '')
                ? $aQuestionTemplateAttributes['value']
                : 'core';
        }

        return [
            'name' => 'question_template',
            'title' => gT('Question theme'),
            'formElementId' => 'question_template',
            'formElementName' => false, //false means identical to id
            'formElementHelp' => gT("Use a customized question theme for this question"),
            'inputtype' => 'questiontheme',
            'formElementValue' => $currentSetQuestionTheme,
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
        $aGroupOptions = [];
        array_walk(
            $aGroupsToSelect,
            function ($oQuestionGroup) use (&$aGroupOptions){
                $aGroupOptions[] = [
                    'value' => $oQuestionGroup->gid,
                    'text' => $oQuestionGroup->questiongroupl10ns[$this->sLanguage]->group_name,
                ];
            }
        );

        return [
            'name' => 'gid',
            'title' => gT('Question group'),
            'formElementId' => 'gid',
            'formElementName' => false,
            'formElementHelp' => gT("If you want to change the question group this question is in."),
            'inputtype' => 'questiongroup',
            'formElementValue' => $this->oQuestion->gid,
            'formElementOptions' => [
                'classes' => ['form-control'],
                'options' => $aGroupOptions,
            ],
            'disableInActive' => true
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
                'formElementValue' => $this->oQuestion->other,
                'formElementOptions' => [
                    'classes' => [],
                    'options' => [
                        'option' => [
                            [
                                'text' => gT("Off"),
                                'value' => 'N'
                            ],
                            [
                                'text' => gT("On"),
                                'value' => 'Y'
                            ],
                        ]
                    ],
                ],
                'disableInActive' => true
            ];
    }

    protected function getMandatorySetting()
    {
        return [
                'name' => 'mandatory',
                'title' => gT('Mandatory'),
                'formElementId' => 'mandatory',
                'formElementName' => false,
                'formElementHelp' => gT('Makes this question mandatory in your survey. Option "Soft" gives a possibility to skip a question without giving any answer.'),
                'inputtype' => 'buttongroup',
                'formElementValue' => $this->oQuestion->mandatory,
                'formElementOptions' => [
                    'classes' => [],
                    'options' => [
                        [
                            'text' => gT("On"),
                            'value' => 'Y'
                        ],
                        [
                            'text' => gT("Soft"),
                            'value' => 'S'
                        ],
                        [
                            'text' => gT("Off"),
                            'value' => 'N'
                        ],
                    ],
                ]
            ];
    }

    protected function getEncryptionSwitch()
    {
        return [
                'name' => 'encrypted',
                'title' => gT('Encrypted'),
                'formElementId' => 'encrypted',
                'formElementName' => false,
                'formElementHelp' => gT('Store the answers to this question encrypted'),
                'inputtype' => 'switch',
                'formElementValue' => $this->oQuestion->encrypted,
                'formElementOptions' => [
                    'classes' => [],
                    'options' => [
                        'option' => [
                            [
                                'text' => gT("Off"),
                                'value' => 'N'
                            ],
                            [
                                'text' => gT("On"),
                                'value' => 'Y'
                            ],

                        ]
                    ],
                ],
                'disableInActive' => true
            ];
    }

    protected function getSaveAsDefaultSwitch()
    {
        return [
                'name' => 'save_as_default',
                'title' => gT('Save as default values'),
                'formElementId' => 'save_as_default',
                'formElementName' => false,
                'formElementHelp' => gT('All attribute values for this question type will be saved as default'),
                'inputtype' => 'switch',
                'formElementValue' => ($this->oQuestion->same_default == 1) ? 'Y' : 'N',
                'formElementOptions' => [
                    'classes' => [],
                    'options' => [
                        'option' => [
                            [
                                'text' => gT("Off"),
                                'value' => 'N'
                            ],
                            [
                                'text' => gT("On"),
                                'value' => 'Y'
                            ],
                        ]
                    ],
                ],
            ];
    }

    protected function getClearDefaultSwitch()
    {
        return [
                'name' => 'clear_default',
                'title' => gT('Clear default values'),
                'formElementId' => 'clear_default',
                'formElementName' => false,
                'formElementHelp' => gT('Default attribute values for this question type will be cleared'),
                'inputtype' => 'switch',
                'formElementValue' => '',
                'formElementOptions' => [
                    'classes' => [],
                    'options' => [
                        'option' => [
                            [
                                'text' => gT("Off"),
                                'value' => 'N'
                            ],
                            [
                                'text' => gT("On"),
                                'value' => 'Y'
                            ],
                        ]
                    ],
                ],
            ];
    }
        
    protected function getRelevanceEquationInput()
    {
        $inputtype = 'textarea';
        
        if (count($this->oQuestion->conditions) > 0) {
            $inputtype = 'text';
            $content = gT("Note: You can't edit the condition because there are currently conditions set for this question by the condition designer.");
        }

        return [
                'name' => 'relevance',
                'title' => gT('Condition'),
                'formElementId' => 'relevance',
                'formElementName' => false,
                'formElementHelp' => (count($this->oQuestion->conditions)>0 ? '' :gT("A condition can be used to add branching logic using ExpressionScript. Either edit it directly here or use the Condition designer.")),
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

    /**
     * @param $sAttributeKey
     * @param $aAttributeArray
     * @param $formElementValue
     * @return array
     */
    protected function parseFromAttributeHelper($sAttributeKey, $aAttributeArray, $formElementValue)
    {
        $aAttributeArray = array_merge(QuestionAttribute::getDefaultSettings(),$aAttributeArray);
        $aAdvancedAttributeArray = [
            'name' => empty($aAttributeArray['name']) ? $sAttributeKey : $aAttributeArray['name'],
            'title' => CHtml::decode($aAttributeArray['caption']),
            'inputtype' => $aAttributeArray['inputtype'],
            'formElementId' => $sAttributeKey,
            'formElementName' => false,
            'formElementHelp' => $aAttributeArray['help'],
            'formElementValue' => $formElementValue
        ];
        unset($aAttributeArray['caption']);
        unset($aAttributeArray['help']);
        unset($aAttributeArray['inputtype']);

        $aFormElementOptions = $aAttributeArray;
        $aFormElementOptions['classes'] = isset($aFormElementOptions['classes']) ? array_merge($aFormElementOptions['classes'], ['form-control']) : ['form-control'];

        if(!is_array($aFormElementOptions['expression']) && $aFormElementOptions['expression'] == 2) {
            $aFormElementOptions['inputGroup'] = [
                'prefix' => '{',
                'suffix' => '}',
                ];
        }
        $aAdvancedAttributeArray['aFormElementOptions'] = $aFormElementOptions;
        
        return $aAdvancedAttributeArray;
    }
}
