<?php

/**
 * LimeSurvey
 * Copyright (C) 2013-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * Files Purpose: lots of common functions
 */

use LimeSurvey\Models\Services\QuestionAttributeHelper;

/**
 * Class QuestionAttribute
 *
 * @property integer $qaid ID Primary key
 * @property integer $qid Question ID
 * @property string $attribute attribute name (max 50 chars)
 * @property string $value Attribute value
 * @property string $language Language code eg:'en'
 *
 * @property Question $question
 * @property Survey $survey
 *
 * @todo Should probably change question_attributes table to question_attribute_values
 * @see participant_attributes and participant_attribute_values
 */
class QuestionAttribute extends LSActiveRecord
{
    /** @var string A date limit (date_min/date_max) given as YYYY-MM-DD date, optionally followed by HH:MM or HH:MM:SS */
    public const DATE_LIMIT_PATTERN = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])([ T]([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?)?$/D';

    protected static $questionAttributesSettings = array();

    protected $xssFilterAttributes = ['value'];

    /**
     * @var array<string,array<string,mixed>> Attribute definitions of the last validated question, by question id, type and theme.
     * Only one question is kept: attribute rows are saved question by question (editor, import).
     */
    private static $attributeDefinitions = [];

    /**
     * @var array{subscriptions: array, attributes: array}|null Result of the newQuestionAttributes event, with the
     * subscriptions it was dispatched to: the event is dispatched again only when plugins are (un)loaded
     */
    private static $pluginAttributes = null;

    /**
     * @var array{key: string, definition: array<string,mixed>|false}|null Definition of this attribute during one
     * validation, so that the validation rules look up the question only once
     */
    private $validatedAttributeDefinition;

    /**
     * @return static
     */
    #[\Override]
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    #[\Override]
    public function tableName()
    {
        return '{{question_attributes}}';
    }

    /** @inheritdoc */
    #[\Override]
    public function primaryKey()
    {
        return 'qaid';
    }

    /**
     * @inheritdoc
     * @todo Remove?
     */
    #[\Override]
    public function relations()
    {
        return array(
            /** NB! do not use this relation use $this->question instead @see getQuestion() */
            'qid' => array(self::BELONGS_TO, 'Question', 'qid', 'together' => true),
        );
    }

    /**
     * This defaultScope indexes the ActiveRecords given back by attribute name
     * Important: This does not work if you want to retrieve records for more than one question at a time.
     * In that case disable the defaultScope by using MyModel::model()->resetScope()->findAll();
     * @return array Scope that indexes the records by their attribute bane
     */
    #[\Override]
    public function defaultScope()
    {
        return array('index' => 'attribute');
    }

    /** @inheritdoc */
    #[\Override]
    public function rules()
    {
        return array(
            array('qid,attribute', 'required'),
            array('value', 'sanitizeByInputType'),
            array('value', 'filterXss'),
            array('value', 'validateDateLimit'),
            array('language', 'LSYii_Validators', 'isLanguage' => true)
        );
    }


    /**
     * @param integer $iQuestionID
     * @param string $sAttributeName
     * @param string $sValue
     * @param string $sLanguage
     * @return CDbDataReader
     * @todo A function should not both set and get something; split into two functions
     */
    public function setQuestionAttributeWithLanguage($iQuestionID, $sAttributeName, $sValue, $sLanguage)
    {
        $oModel = new self();
        $aResult = $oModel->findAll('attribute=:attributeName and qid=:questionID and language=:language', array(':attributeName' => $sAttributeName, ':language' => $sLanguage, ':questionID' => $iQuestionID));
        if (!empty($aResult)) {
            foreach ($aResult as $questionAttribute) {
                $questionAttribute->value = $sValue;
                $questionAttribute->save();
            }
        } else {
            $oModel = new self();
            $oModel->attribute = $sAttributeName;
            $oModel->value = $sValue;
            $oModel->qid = $iQuestionID;
            $oModel->language = $sLanguage;
            $oModel->save();
        }
        return Yii::app()->db->createCommand()
            ->select()
            ->from($this->tableName())
            ->where(array('and', 'qid=:qid'))->bindParam(":qid", $iQuestionID)
            ->order('qaid asc')
            ->query();
    }

    /**
     * @param integer $iQuestionID
     * @param string $sAttributeName
     * @param string $sValue
     * @return CDbDataReader|boolean
     * @todo A function should not both set and get something; split into two functions
     */
    public function setQuestionAttribute($iQuestionID, $sAttributeName, $sValue)
    {
        $oModel = new self();
        $aResult = $oModel->findAll('attribute=:attributeName and qid=:questionID', array(':attributeName' => $sAttributeName, ':questionID' => $iQuestionID));
        if (!empty($aResult)) {
            foreach ($aResult as $questionAttribute) {
                $questionAttribute->value = $sValue;
                $questionAttribute->save();
            }
        } else {
            $oModel = new self();
            $oModel->attribute = $sAttributeName;
            $oModel->value = $sValue;
            $oModel->qid = $iQuestionID;
            return $oModel->save();
        }
        return Yii::app()->db->createCommand()
            ->select()
            ->from($this->tableName())
            ->where(array('and', 'qid=:qid'))->bindParam(":qid", $iQuestionID)
            ->order('qaid asc')
            ->query();
    }

    /**
     * Set attributes for multiple questions simultaneously
     *
     * This function updates specified attributes for multiple questions at once.
     * It first checks if the user has permission to update survey content,
     * then handles special case for random order attributes, and finally
     * applies the requested attribute updates to all specified questions.
     *
     * @param integer $surveyId The survey ID
     * @param array $questionIds Array of question IDs to update
     * @param array $attributesWithValue Array of attribute names and the new value
     * @param array $validQuestionTypes Array of question types that are valid for these attributes
     *
     * @return void No direct return value, updates are applied to the database
     */
    public function setMultipleAttributes(
        $surveyId,
        $questionIds,
        $attributesWithValue,
        $validQuestionTypes
    ) {
        // Permissions check
        if (
            Permission::model()->hasSurveyPermission(
                $surveyId,
                'surveycontent',
                'update'
            )
        ) {
            $attributesWithValue = $this->setRandomOrderAttributes(
                $surveyId,
                $questionIds,
                $attributesWithValue,
                $validQuestionTypes
            );
            if (!empty($attributesWithValue)) {
                foreach ($questionIds as $questionId) {
                    $questionId = (int)$questionId;
                    $question = Question::model()->find(
                        'qid=:qid AND sid=:sid',
                        [":qid" => $questionId, ":sid" => $surveyId]
                    );
                    // Skip questions that don't belong to the survey
                    if (empty($question)) {
                        continue;
                    }
                    // For each attribute
                    foreach ($attributesWithValue as $attribute => $value) {
                        if (in_array($question->type, $validQuestionTypes)) {
                            $this->setQuestionAttribute(
                                $questionId,
                                $attribute,
                                $value
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns Question attribute array name=>value
     * --> returns result from emCache if it is set OR
     * --> build the returned array and set the emCache to it
     *
     * --get attributes from XML-Files
     * --get additional attributes from extended theme
     * --prepare an easier/smaller array to return
     *
     * @access public
     * @param int|Question $q
     * @param string $sLanguage restrict to this language (if null $oQuestion->survey->allLanguages will be used)
     * @return array|false
     *
     * @throws CException throws exception if questiontype is null
     */
    public function getQuestionAttributes($q, $sLanguage = null)
    {
        $receivedIDOnly = (!($q instanceof Question));
        if ($receivedIDOnly) {
            $iQuestionID = intval($q);
        } else {
            $iQuestionID = $q->qid;
        }
        static $survey = '';
        // Limit the size of the attribute cache due to memory usage
        $cacheKey = 'getQuestionAttributes_' . $iQuestionID . '_' . json_encode($sLanguage);
        if (class_exists('EmCacheHelper', false) && EmCacheHelper::useCache()) {
            $value = EmCacheHelper::get($cacheKey);
            if ($value !== false) {
                return $value;
            }
        }

        if ($receivedIDOnly) {
            $oQuestion = Question::model()->find("qid=:qid", ['qid' => $iQuestionID]);
        } else {
            $oQuestion = $q;
        }
        if (empty($oQuestion)) {
            return false; // return false but don't set $aQuestionAttributesStatic[$iQuestionID]
        }

        $questionAttributeHelper = new QuestionAttributeHelper();
        $aQuestionAttributes = $questionAttributeHelper->getQuestionAttributesWithValues($oQuestion, $sLanguage);

        $aLanguages = empty($sLanguage) ? $oQuestion->survey->allLanguages : [$sLanguage];

        $aAttributeValues = [];
        foreach ($aQuestionAttributes as $aAttribute) {
            if ($aAttribute['i18n'] == false) {
                // Values stored before they were sanitized on save, or by a group import, are sanitized here
                $aAttributeValues[$aAttribute['name']] = self::sanitizeValue($aAttribute['value'], $aAttribute);
            } else {
                foreach ($aLanguages as $language) {
                    if (isset($aAttribute[$language]['value'])) {
                        $aAttributeValues[$aAttribute['name']][$language] = self::sanitizeValue($aAttribute[$language]['value'], $aAttribute);
                    }
                }
            }
        }

        if (class_exists('EmCacheHelper', false) && EmCacheHelper::useCache()) {
            EmCacheHelper::set($cacheKey, $aAttributeValues);
        }

        return $aAttributeValues;
    }

    /**
     * Get whole existing attribute for one question as array
     *
     * @param int $iQuestionID  the question id
     * @return array the returning array structure will be like
     *               $aAttributeValues[$oAttributeValue->attribute][$oAttributeValue->language]
     *               $aAttributeValues[$oAttributeValue->attribute]['']
     */
    public static function getAttributesAsArrayFromDB($iQuestionID)
    {
        /* Get whole existing attribute for this question in an array */
        $oAttributeValues = self::model()->resetScope()->findAll("qid=:qid", ['qid' => $iQuestionID]);
        $aAttributeValues = array();
        foreach ($oAttributeValues as $oAttributeValue) {
            if ($oAttributeValue->language) {
                $aAttributeValues[$oAttributeValue->attribute][$oAttributeValue->language] = $oAttributeValue->value;
            } else {
                /* Don't replace existing language, use '' for null key (and for empty string) */
                $aAttributeValues[$oAttributeValue->attribute][''] = $oAttributeValue->value;
            }
        }

        return $aAttributeValues;
    }

    /**
     * Insert additional attributes from an extended question theme
     *
     * @param array $aAttributeNames array of attributes (see getQuestionAttributesSettings())
     * @param Question $oQuestion
     * @return array|mixed returns $aAttributeNames with appended additional attributes
     */
    public static function addAdditionalAttributesFromExtendedTheme($aAttributeNames, $oQuestion)
    {
        $retAttributeNamesExtended = $aAttributeNames;
        /** @var string|null */
        $questionThemeName = $oQuestion->question_theme_name;
        if (!empty($questionThemeName)) {
            $aThemeAttributes = QuestionTheme::getAdditionalAttrFromExtendedTheme($questionThemeName, $oQuestion->type);
            $questionAttributeHelper = new QuestionAttributeHelper();
            $retAttributeNamesExtended = $questionAttributeHelper->mergeQuestionAttributes($retAttributeNamesExtended, $aThemeAttributes);
        }

        return $retAttributeNamesExtended;
    }

    /**
     * @param string $fields
     * @param mixed $condition
     * @param string|false $orderby
     * @return array
     */
    public function getQuestionsForStatistics($fields, $condition, $orderby = false)
    {
        $command = Yii::app()->db->createCommand()
            ->select($fields)
            ->from($this->tableName())
            ->where($condition);
        if ($orderby !== false) {
            $command->order($orderby);
        }
        return $command->queryAll();
    }

    /**
     * @return Question
     */
    public function getQuestion()
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('qid=:qid');
        $criteria->params = [':qid' => $this->qid];
        if ($this->language) {
            $criteria->addCondition('language=:language');
            $criteria->params = [':qid' => $this->qid, ':language' => $this->language];
        }
        /** @var Question $model */
        $model = Question::model()->find($criteria);
        return $model;
    }

    /**
     * @return Survey
     */
    public function getSurvey()
    {
        return $this->question->survey;
    }

    /**
     * Get default settings for an attribute, return an array of string|null
     *
     * @todo Move to static property?
     * @return array<string, mixed>
     */
    public static function getDefaultSettings()
    {
        return array(
            "name" => null,
            "caption" => '',
            "inputtype" => "text",
            "options" => null,
            "category" => gT("Attribute"),
            "default" => '',
            "help" => '',
            "value" => '',
            "sortorder" => 1000,
            "i18n" => false,
            "readonly" => false,
            "readonly_when_active" => false,
            "expression" => null,
            "xssfilter" => true,
            "min" => null, // Used for integer type
            "max" => null, // Used for integer type
        );
    }


    /**
     * Return the question attribute settings for the passed type (parameter)
     *
     * @param string $sType : type of question (this is the attribute 'question_type' in table question_theme)
     * @param boolean $advancedOnly If true, only fetch advanced attributes
     * @return array The attribute settings for this question type
     *                 returns values from getGeneralAttributesFromXml and getAdvancedAttributesFromXml if this fails
     *                 getAttributesDefinition and getDefaultSettings are returned
     *
     * @throws CException
     */
    public static function getQuestionAttributesSettings($sType, $advancedOnly = false)
    {
        $sXmlFilePath = QuestionTheme::getQuestionXMLPathForBaseType($sType);
        if ($advancedOnly) {
            $generalAttributes = [];
        } else {
            // Get attributes from config.xml
            $generalAttributes = self::getGeneralAttibutesFromXml($sXmlFilePath);
        }
        $advancedAttributes = self::getAdvancedAttributesFromXml($sXmlFilePath);
        self::$questionAttributesSettings[$sType] = array_merge($generalAttributes, $advancedAttributes);

        // if empty, fall back to getting attributes from questionHelper
        if (empty(self::$questionAttributesSettings[$sType])) {
            self::$questionAttributesSettings[$sType] = array();
            $attributes = \LimeSurvey\Helpers\questionHelper::getAttributesDefinitions();
            /* Filter to get this question type setting */
            $aQuestionTypeAttributes = array_filter($attributes, function ($attribute) use ($sType) {
                return stripos((string) $attribute['types'], $sType) !== false;
            });
            foreach ($aQuestionTypeAttributes as $attribute => $settings) {
                  self::$questionAttributesSettings[$sType][$attribute] = array_merge(
                      QuestionAttribute::getDefaultSettings(),
                      array("category" => gT("Plugins")),
                      $settings,
                      array("name" => $attribute)
                  );
            }
        }
        return self::$questionAttributesSettings[$sType];
    }

    /**
     * Read question attributes from XML file and convert it to array
     *
     * @param string $sXmlFilePath Path to XML
     *
     * @return ?array The advanced attribute settings for this question type
     */
    protected static function getAdvancedAttributesFromXml($sXmlFilePath)
    {
        $aXmlAttributes = array();
        $aAttributes = array();

        if (file_exists($sXmlFilePath)) {
            // load xml file
            $xml_config = simplexml_load_file($sXmlFilePath);
            $aXmlAttributes = json_decode(json_encode((array)$xml_config->attributes), true);
            // if only one attribute, then it doesn't return numeric index
            if (!empty($aXmlAttributes) && !array_key_exists('0', $aXmlAttributes['attribute'])) {
                $aTemp = $aXmlAttributes['attribute'];
                unset($aXmlAttributes);
                $aXmlAttributes['attribute'][0] = $aTemp;
            }
        } else {
            return null;
        }

        // set $aAttributes array with attribute data
        if (!empty($aXmlAttributes['attribute'])) {
            foreach ($aXmlAttributes['attribute'] as $key => $value) {
                if (empty($value['name'])) {
                    /* Allow comments in attributes */
                    continue;
                }
                /* settings the default value */
                $aAttributes[$value['name']] = self::getDefaultSettings();
                /* settings the xml value */
                foreach ($value as $key2 => $value2) {
                    if ($key2 === 'options' && !empty($value2)) {
                        foreach ($value2['option'] as $key3 => $value3) {
                            if (isset($value3['value'])) {
                                $value4 = is_array($value3['value']) ? '' : $value3['value'];
                                $aAttributes[$value['name']]['options'][$value4] = $value3['text'];
                            }
                        }
                    } else {
                        $aAttributes[$value['name']][$key2] = $value2;
                    }
                }
            }
        }

        // Filter all pesky '[]' values (empty values should be null, e.g. <default></default>).
        $questionAttributeHelper = new QuestionAttributeHelper();
        $aAttributes = $questionAttributeHelper->sanitizeQuestionAttributes($aAttributes);

        return $aAttributes;
    }

    /**
     * Read question attributes from XML file and convert it to array
     *
     * @param string $sXmlFilePath Path to XML
     *
     * @return ?array The general attribute settings for this question type
     * @todo What's the opposite of a "general" attribute? How many types of attributes are there?
     */
    protected static function getGeneralAttibutesFromXml($sXmlFilePath)
    {
        $aAttributes = array();

        if (file_exists($sXmlFilePath)) {
            // load xml file
            $xml_config = simplexml_load_file($sXmlFilePath);
            $aXmlAttributes = json_decode(json_encode((array)$xml_config->generalattributes), true);
            // if only one attribute, then it doesn't return numeric index
            if (!empty($aXmlAttributes) && !array_key_exists('0', $aXmlAttributes['attribute'])) {
                $aTemp = $aXmlAttributes['attribute'];
                unset($aXmlAttributes);
                $aXmlAttributes['attribute'][0] = $aTemp;
            }
        } else {
            return null;
        }

        // set $aAttributes array with attribute data
        if (!empty($aXmlAttributes['attribute'])) {
            foreach ($aXmlAttributes['attribute'] as $key => $xmlAttribute) {
                /* settings the default value */
                $aAttributes[$xmlAttribute] = self::getDefaultSettings();
                /* settings the xml value */
                $aAttributes[$xmlAttribute]['name'] = $xmlAttribute;
            }
        }

        // Filter all pesky '[]' values (empty values should be null, e.g. <default></default>).
        $questionAttributeHelper = new QuestionAttributeHelper();
        $aAttributes = $questionAttributeHelper->sanitizeQuestionAttributes($aAttributes);

        return $aAttributes;
    }

    /**
     * New event to allow plugin to add own question attribute (settings)
     *
     * Using $event->append('questionAttributes', $questionAttributes);
     *
     * $questionAttributes=[
     *  attributeName=>[
     *      'types' : Apply to this question type
     *      'category' : Where to put it
     *      'sortorder' : Qort order in this category
     *      'inputtype' : type of input
     *      'expression' : 2 to force Expression Manager when see the survey logic overview (add { } and validate, 1 : allow it : validate in survey logic overview
     *      'options' : optional options if input type need it
     *      'default' : the default value
     *      'caption' : the label
     *      'help' : an help
     *  ]
     *
     * The event is dispatched once per request: the attributes don't depend on a question.
     *
     * @return array the event attributes as array or an empty array
     */
    public static function getOwnQuestionAttributesViaPlugin()
    {
        $pluginManager = App()->getPluginManager();
        $subscriptions = $pluginManager->getSubscriptions('newQuestionAttributes');
        if (self::$pluginAttributes === null || self::$pluginAttributes['subscriptions'] !== $subscriptions) {
            $event = new \LimeSurvey\PluginManager\PluginEvent('newQuestionAttributes');
            $result = $pluginManager->dispatchEvent($event);
            self::$pluginAttributes = [
                'subscriptions' => $subscriptions,
                'attributes' => (array) $result->get('questionAttributes'),
            ];
        }

        return self::$pluginAttributes['attributes'];
    }

    /**
     * Apply XSS filter to question attribute value unless 'xssfilter' property is false.
     * @param string $attribute the name of the attribute to be validated.
     * @param array<mixed> $params additional parameters passed with rule when being executed.
     * @return void
     */
    public function filterXss($attribute, $params)
    {
        $questionAttributeDefinition = $this->getAttributeDefinition();
        if ($questionAttributeDefinition === false) {
            return;
        }

        // The value will be filtered unless the attribute definition has the "xssfilter" property set to false
        if (
            !empty($questionAttributeDefinition)
            && array_key_exists("xssfilter", $questionAttributeDefinition)
            && $questionAttributeDefinition['xssfilter'] == false
        ) {
            return;
        }

        // By default, LSYii_Validators only applies an XSS filter. It has other filters but they are not enabled by default.
        $validator = new LSYii_Validators();
        $validator->attributes = [$attribute];
        $validator->validate($this, [$attribute]);
    }

    /**
     * Sanitize the value of this attribute according to its definition, see sanitizeValue().
     * @param string $attribute the name of the attribute to be validated.
     * @param array<mixed> $params additional parameters passed with rule when being executed.
     * @return void
     */
    public function sanitizeByInputType($attribute, $params)
    {
        if (!is_scalar($this->$attribute) || trim((string) $this->$attribute) === '') {
            return;
        }
        $questionAttributeDefinition = $this->getAttributeDefinition();
        if (!empty($questionAttributeDefinition)) {
            $this->$attribute = self::sanitizeValue($this->$attribute, $questionAttributeDefinition);
        }
    }

    /**
     * Attributes with a fixed format only keep values matching their definition: integer, float and columns
     * attributes must be numbers (otherwise emptied), switch attributes must be 0 or 1, single select and
     * button group attributes must be one of their options (otherwise both are reset to the default value).
     * Other attributes and attributes allowing expressions are returned unchanged.
     * @param mixed $value the attribute value
     * @param array<string,mixed> $definition the attribute definition (inputtype, options, default, expression)
     * @return mixed the sanitized value
     */
    public static function sanitizeValue($value, array $definition)
    {
        if (!is_scalar($value) || empty($definition['inputtype']) || !empty($definition['expression'])) {
            return $value;
        }
        $trimmedValue = trim((string) $value);
        if ($trimmedValue === '') {
            return $value;
        }
        $default = isset($definition['default']) && is_scalar($definition['default']) ? (string) $definition['default'] : '';
        switch ($definition['inputtype']) {
            case 'integer':
            case 'float':
            case 'columns':
                // Decimals are kept for integer attributes too: older versions may have accepted them
                return is_numeric($trimmedValue) ? $trimmedValue : '';
            case 'switch':
                // The switch always submits 1/0, old survey files may contain Y/N
                $trimmedValue = strtr($trimmedValue, ['Y' => '1', 'N' => '0']);
                return in_array($trimmedValue, ['0', '1'], true) ? $trimmedValue : $default;
            case 'singleselect':
            case 'buttongroup':
                if (empty($definition['options']) || !is_array($definition['options'])) {
                    return $value;
                }
                // Options are value => label: the editors submit the key, also for a plain list of labels
                $validValues = array_map('strval', array_keys($definition['options']));
                return in_array($trimmedValue, $validValues, true) ? $trimmedValue : $default;
        }
        return $value;
    }

    /**
     * Returns the definition of this attribute for the current type and theme of its question.
     * @return array<string,mixed>|false the definition, an empty array if the attribute is unknown, false if the question does not exist
     */
    private function getAttributeDefinition()
    {
        $validationKey = $this->qid . '|' . $this->attribute;
        if (isset($this->validatedAttributeDefinition) && $this->validatedAttributeDefinition['key'] === $validationKey) {
            return $this->validatedAttributeDefinition['definition'];
        }
        $definition = false;
        $question = Question::model()->find("qid=:qid", ['qid' => $this->qid]);
        if (!empty($question)) {
            $cacheKey = $question->qid . '|' . $question->type . '|' . $question->question_theme_name;
            if (!isset(self::$attributeDefinitions[$cacheKey])) {
                $questionAttributeFetcher = new \LimeSurvey\Models\Services\QuestionAttributeFetcher();
                $questionAttributeFetcher->setQuestion($question);
                self::$attributeDefinitions = [$cacheKey => $questionAttributeFetcher->fetch()];
            }
            $definition = self::$attributeDefinitions[$cacheKey][$this->attribute] ?? [];
        }
        $this->validatedAttributeDefinition = ['key' => $validationKey, 'definition' => $definition];
        return $definition;
    }

    /**
     * Forgets the attribute definition of the previous validation: the question type or theme may have changed since
     * @return boolean whether validation should be executed
     */
    #[\Override]
    protected function beforeValidate()
    {
        $this->validatedAttributeDefinition = null;
        return parent::beforeValidate();
    }

    /**
     * Date limits may be a date, an English date description or an expression; a value starting like a
     * YYYY-MM-DD date must parse as a date/time as a whole.
     * @param string $attribute the name of the attribute to be validated.
     * @param array<mixed> $params additional parameters passed with rule when being executed.
     * @return void
     */
    public function validateDateLimit($attribute, $params)
    {
        if (!in_array($this->attribute, ['date_min', 'date_max'], true)) {
            return;
        }
        $value = trim((string) $this->$attribute);
        if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}/', $value) && strtotime($value) === false) {
            $this->addError($attribute, gT('Invalid date.'));
        }
    }

    /**
     * Massive action "Present subquestions/answer options in random order"
     * has to update random_order, answer_order and/or subquestion_order
     * depending on question type.
     * This function checks for random_order attribute and updates the corresponding fields instead if necessary.
     * $attributesToUpdate will be returned with the random_order attr removed
     * @param int $surveyId the survey ID
     * @param array $questionIds the question IDs
     * @param array $attributesWithValue the attributes to update
     * @param array $validQuestionTypes the valid question types
     *
     * @return array $attributesToUpdate with random_order attr removed
     */
    public function setRandomOrderAttributes(
        int $surveyId,
        array $questionIds,
        array $attributesWithValue,
        array $validQuestionTypes
    ) {
        if (array_key_exists('random_order', $attributesWithValue)) {
            $value = $attributesWithValue['random_order'];
            $stringValue = $value === '1' ? 'random' : 'normal';
            foreach ($questionIds as $questionId) {
                $questionModel = Question::model()->find(
                    'qid=:qid AND sid=:sid',
                    [":qid" => $questionId, ":sid" => $surveyId]
                );
                // Skip questions that don't belong to the survey
                if (empty($questionModel)) {
                    continue;
                }
                if (in_array($questionModel->type, $validQuestionTypes)) {
                    if (
                        in_array($questionModel->type, Question::ORDER_TYPES_SUBQUESTION)
                    ) {
                        $updateAttribute = 'subquestion_order';
                        $updateValue = $stringValue;
                    } elseif (
                        in_array($questionModel->type, Question::ORDER_TYPES_ANSWER)
                    ) {
                        $updateAttribute = 'answer_order';
                        $updateValue = $stringValue;
                    } else {
                        $updateAttribute = 'random_order';
                        $updateValue = $value;
                    }
                    $this->setQuestionAttribute($questionId, $updateAttribute, $updateValue);
                }
            }
            unset($attributesWithValue['random_order']);
        }
        return $attributesWithValue;
    }
}
