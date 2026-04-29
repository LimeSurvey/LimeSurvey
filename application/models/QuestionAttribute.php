<?php

/**
 * LimeSurvey
 * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
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
    protected static $questionAttributesSettings = array();

    protected $xssFilterAttributes = ['value'];

    /**
     * @return static
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{question_attributes}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'qaid';
    }

    /**
     * @inheritdoc
     * @todo Remove?
     */
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
    public function defaultScope()
    {
        return array('index' => 'attribute');
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('qid,attribute', 'required'),
            array('value', 'filterXss'),
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
        if (EmCacheHelper::useCache()) {
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
                $aAttributeValues[$aAttribute['name']] = $aAttribute['value'];
            } else {
                foreach ($aLanguages as $language) {
                    if (isset($aAttribute[$language]['value'])) {
                        $aAttributeValues[$aAttribute['name']][$language] = $aAttribute[$language]['value'];
                    }
                }
            }
        }

        if (EmCacheHelper::useCache()) {
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
     * Returns the value for attribute 'question_template'.
     * Fetches the question_template from a question model.
     *
     * Be carefull this attribute is not present in all questions.
     * Even more, standard question types where question theme are not used (or custom question theme are not used),
     * the attribute is missing. In those cases, the deault "core" is used.
     *
     * @return string question_template or 'core' if it not exists
     *
     * @deprecated use $question->question_theme_name instead (Question model)
     */
    public static function getQuestionTemplateValue($questionID)
    {
        /**
         * TODO: This method was modified to get the theme name from the proper place, but it should be deprecated,
         *       as it no longer makes sense (question theme is not a QuestionAttribute anymore).
         */
        $question = Question::model()->findByPk($questionID);
        $value = !empty($question) && !empty($question->question_theme_name) ? $question->question_theme_name : 'core';
        return $value;
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
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(false);
            }
            $xml_config = simplexml_load_file($sXmlFilePath);
            $aXmlAttributes = json_decode(json_encode((array)$xml_config->attributes), true);
            // if only one attribute, then it doesn't return numeric index
            if (!empty($aXmlAttributes) && !array_key_exists('0', $aXmlAttributes['attribute'])) {
                $aTemp = $aXmlAttributes['attribute'];
                unset($aXmlAttributes);
                $aXmlAttributes['attribute'][0] = $aTemp;
            }
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(true);
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
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(false);
            }
            $xml_config = simplexml_load_file($sXmlFilePath);
            $aXmlAttributes = json_decode(json_encode((array)$xml_config->generalattributes), true);
            // if only one attribute, then it doesn't return numeric index
            if (!empty($aXmlAttributes) && !array_key_exists('0', $aXmlAttributes['attribute'])) {
                $aTemp = $aXmlAttributes['attribute'];
                unset($aXmlAttributes);
                $aXmlAttributes['attribute'][0] = $aTemp;
            }
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(true);
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
     *      'expression' : 2 to force Expression Manager when see the survey logic file (add { } and validate, 1 : allow it : validate in survey logic file
     *      'options' : optional options if input type need it
     *      'default' : the default value
     *      'caption' : the label
     *      'help' : an help
     *  ]
     *
     * @return array the event attributes as array or an empty array
     */
    public static function getOwnQuestionAttributesViaPlugin()
    {
        $event = new \LimeSurvey\PluginManager\PluginEvent('newQuestionAttributes');
        $result = App()->getPluginManager()->dispatchEvent($event);

        return (array) $result->get('questionAttributes');
    }

    /**
     * Apply XSS filter to question attribute value unless 'xssfilter' property is false.
     * @param string $attribute the name of the attribute to be validated.
     * @param array<mixed> $params additional parameters passed with rule when being executed.
     * @return void
     */
    public function filterXss($attribute, $params)
    {
        $question = Question::model()->find("qid=:qid", ['qid' => $this->qid]);
        if (empty($question)) {
            return;
        }
        $questionAttributeFetcher = new \LimeSurvey\Models\Services\QuestionAttributeFetcher();
        $questionAttributeFetcher->setQuestion($question);
        $questionAttributeDefinitions = $questionAttributeFetcher->fetch();

        // The value will be filtered unless the attribute definition has the "xssfilter" property set to false
        $shouldFilter = true;
        if (isset($questionAttributeDefinitions[$this->attribute])) {
            $questionAttributeDefinition = $questionAttributeDefinitions[$this->attribute];
            if (array_key_exists("xssfilter", $questionAttributeDefinition) && $questionAttributeDefinition['xssfilter'] == false) {
                $shouldFilter = false;
            }
        }

        if (!$shouldFilter) {
            return;
        }

        // By default, LSYii_Validators only applies an XSS filter. It has other filters but they are not enabled by default.
        $validator = new LSYii_Validators();
        $validator->attributes = [$attribute];
        $validator->validate($this, [$attribute]);
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
