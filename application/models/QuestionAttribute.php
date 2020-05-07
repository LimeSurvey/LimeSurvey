<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
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

    /**
     * @inheritdoc
     * @return QuestionAttribute
     */
    public static function model($class = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($class);
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

    /** @inheritdoc */
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
        return array('index'=>'attribute');
    }
    
    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('qid,attribute', 'required'),
            array('value', 'LSYii_Validators'),
        );
    }


    /**
     * @param integer $iQuestionID
     * @param string $sAttributeName
     * @param string $sValue
     * @return CDbDataReader
     */
    public function setQuestionAttributeWithLanguage($iQuestionID, $sAttributeName, $sValue, $sLanguage)
    {
        $oModel = new self;
        $aResult = $oModel->findAll('attribute=:attributeName and qid=:questionID and language=:language', array(':attributeName'=>$sAttributeName, ':language'=>$sLanguage, ':questionID'=>$iQuestionID));
        if (!empty($aResult)) {
            $oModel->updateAll(array('value'=>$sValue), 'attribute=:attributeName and qid=:questionID and language=:language', array(':attributeName'=>$sAttributeName, ':language'=>$sLanguage, ':questionID'=>$iQuestionID));
        } else {
            $oModel = new self;
            $oModel->attribute = $sAttributeName;
            $oModel->value = $sValue;
            $oModel->qid = $iQuestionID;
            $oModel->language = $sLanguage;
            $oModel->save();
        }
        return Yii::app()->db->createCommand()
            ->select()
            ->from($this->tableName())
            ->where(array('and', 'qid=:qid'))->bindParam(":qid", $qid)
            ->order('qaid asc')
            ->query();
    }

    /**
     * @param integer $iQuestionID
     * @param string $sAttributeName
     * @param string $sValue
     * @return CDbDataReader|boolean
     */
    public function setQuestionAttribute($iQuestionID, $sAttributeName, $sValue)
    {
        $oModel = new self;
        $aResult = $oModel->findAll('attribute=:attributeName and qid=:questionID', array(':attributeName'=>$sAttributeName, ':questionID'=>$iQuestionID));
        if (!empty($aResult)) {
            $oModel->updateAll(array('value'=>$sValue), 'attribute=:attributeName and qid=:questionID', array(':attributeName'=>$sAttributeName, ':questionID'=>$iQuestionID));
        } else {
            $oModel = new self;
            $oModel->attribute = $sAttributeName;
            $oModel->value = $sValue;
            $oModel->qid = $iQuestionID;
            return $oModel->save();
        }
        return Yii::app()->db->createCommand()
            ->select()
            ->from($this->tableName())
            ->where(array('and', 'qid=:qid'))->bindParam(":qid", $qid)
            ->order('qaid asc')
            ->query();
    }

    /**
     * Set attributes for multiple questions
     *
     * NOTE: We can't use self::setQuestionAttribute() because it doesn't check for question types first.
     * TODO: the question type check should be done via rules, or via a call to a question method
     * TODO: use an array for POST values, like for a form submit So we could parse it from the controller instead of using $_POST directly here
     *
     * @var integer $iSid                   the sid to update  (only to check permission)
     * @var array $aQidsAndLang           an array containing the list of primary keys for questions ( {qid, lang} )
     * @var array $aAttributesToUpdate    array continaing the list of attributes to update
     * @var array $aValidQuestionTypes    the question types we can update for those attributes
     */
    public function setMultiple($iSid, $aQidsAndLang, $aAttributesToUpdate, $aValidQuestionTypes)
    {
        // Permissions check
        if (Permission::model()->hasSurveyPermission($iSid, 'surveycontent', 'update')) {
            // For each question
            foreach ($aQidsAndLang as $sQidAndLang) {
                $aQidAndLang  = explode(',', $sQidAndLang); // Each $aQidAndLang correspond to a question primary key, which is a pair {qid, lang}.
                $iQid         = $aQidAndLang[0]; // Those pairs are generated by CGridView
                $sLanguage    = $aQidAndLang[1];

                // We need to generate a question object to check for the question type
                // So, we can also force the sid: we don't allow to update questions on different surveys at the same time (permission check is by survey)
                $oQuestion = Question::model()->find('qid=:qid and language=:language and sid=:sid', array(":qid"=>$iQid, ":language"=>$sLanguage, ":sid"=>$iSid));

                // For each attribute
                foreach ($aAttributesToUpdate as $sAttribute) {
                    // TODO: use an array like for a form submit, so we can parse it from the controller instead of using $_POST directly here
                    $sValue         = Yii::app()->request->getPost($sAttribute);
                    $iInsertCount   = QuestionAttribute::model()->findAllByAttributes(array('attribute'=>$sAttribute, 'qid'=>$iQid));

                    // We check if we can update this attribute for this question type
                    // TODO: if (in_array($oQuestion->attributes, $sAttribute))
                    if (in_array($oQuestion->type, $aValidQuestionTypes)) {
                        if (count($iInsertCount) > 0) {
                            // Update
                                QuestionAttribute::model()->updateAll(array('value'=>$sValue), 'attribute=:attribute AND qid=:qid', array(':attribute'=>$sAttribute, ':qid'=>$iQid));
                        } else {
                            // Create
                            $oAttribute            = new QuestionAttribute;
                            $oAttribute->qid       = $iQid;
                            $oAttribute->value     = $sValue;
                            $oAttribute->attribute = $sAttribute;
                            $oAttribute->save();
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
     * @param int $iQuestionID
     * @param string $sLanguage restrict to this language (if null $oQuestion->survey->allLanguages will be used)
     * @return array|boolean
     *
     * @throws CException throws exception if questiontype is null
     * @todo this function is doing to much things to prepare just an array. Find a better solution (maybe service class)
     */
    public function getQuestionAttributes($iQuestionID, $sLanguage = null)
    {
        $iQuestionID = (int) $iQuestionID;
        // Limit the size of the attribute cache due to memory usage
        $cacheKey = 'getQuestionAttributes_' . $iQuestionID . '_' . json_encode($sLanguage);
        if (EmCacheHelper::useCache()) {
            $value = EmCacheHelper::get($cacheKey);
            if ($value !== false) {
                return $value;
            }
        }
        $oQuestion = Question::model()->with('survey')->find("qid=:qid", array('qid'=>$iQuestionID));
        if ($oQuestion) {
            if ($sLanguage) {
                $aLanguages = array($sLanguage);
            } else {
                $aLanguages = $oQuestion->survey->allLanguages;
            }
            // For some reason this happened in bug #10684
            if ($oQuestion->type == null) {
                throw new \CException("Question is corrupt: no type defined for question ".$iQuestionID);
            }
            $aAttributeValues = self::getAttributesAsArrayFromDB($iQuestionID);
            $aAttributeFromXmlOrDefault = self::getQuestionAttributesSettings($oQuestion->type); //from xml files
            $aAttributeNames = self::addAdditionalAttributesFromExtendedTheme($aAttributeFromXmlOrDefault, $oQuestion);
            // Fill aQuestionAttributes with default attribute or with aAttributeValues
            $aQuestionAttributes = self::rewriteQuestionAttributeArray($aAttributeNames, $aAttributeValues, $aLanguages);
        } else {
            return false; // return false but don't set $aQuestionAttributesStatic[$iQuestionID]
        }
        if (EmCacheHelper::useCache()) {
            EmCacheHelper::set($cacheKey, $aQuestionAttributes);
        }

        return $aQuestionAttributes;
    }

    /**
     * Returns an array with attributes like
     *   $aQuestionAttributes[$aAttribute['name']]['expression'] this will be overwritten if there are no languages and
     *   will be set to the default value of the attribute if there is any --> e.g. $aQuestionAttributes["question_template"] = "core"
     *   If there are languages the next array element will be appended to the result array
     *   $aQuestionAttributes[$aAttribute['name']][$sLanguage]
     *
     * @param array $aAttributeNames array of attributes (see addAdditionalAttributesFromExtendedTheme())
     * @param array $aAttributeValues array of attribute values (see getAttributesAsArrayFromDB())
     * @param array $aLanguages  like $aLanguages[0] = 'en'
     * @return array
     */
    private static function rewriteQuestionAttributeArray($aAttributeNames, $aAttributeValues, $aLanguages){
        $aQuestionAttributes = array();
        foreach ($aAttributeNames as $aAttribute) {
            $aQuestionAttributes[$aAttribute['name']]['expression'] = isset($aAttribute['expression']) ? $aAttribute['expression'] : 0;

            // convert empty array to empty string
            if (empty($aAttribute['default']) && is_array($aAttribute['default'])){
                $aAttribute['default'] = '';
            }

            if ($aAttribute['i18n'] == false) {
                if (isset($aAttributeValues[$aAttribute['name']][''])) {
                    $aQuestionAttributes[$aAttribute['name']] = $aAttributeValues[$aAttribute['name']][''];
                } elseif (isset($aAttributeValues[$aAttribute['name']])) {
                    /* Some survey have language is set for attribute without language (see #11980). This must fix for public survey and not only for admin. */
                    $aQuestionAttributes[$aAttribute['name']] = reset($aAttributeValues[$aAttribute['name']]);
                } else {
                    $aQuestionAttributes[$aAttribute['name']] = $aAttribute['default'];
                }
            } else {
                foreach ($aLanguages as $sLanguage) {
                    if (isset($aAttributeValues[$aAttribute['name']][$sLanguage])) {
                        $aQuestionAttributes[$aAttribute['name']][$sLanguage] = $aAttributeValues[$aAttribute['name']][$sLanguage];
                    } elseif (isset($aAttributeValues[$aAttribute['name']][''])) {
                        $aQuestionAttributes[$aAttribute['name']][$sLanguage] = $aAttributeValues[$aAttribute['name']][''];
                    } else {
                        $aQuestionAttributes[$aAttribute['name']][$sLanguage] = $aAttribute['default'];
                    }
                }
            }
        }

        return $aQuestionAttributes;
    }


    /**
     * Get whole existing attribute for one question as array
     *
     * @param int $iQuestionID  the question id
     * @return array the returning array structure will be like
     *               $aAttributeValues[$oAttributeValue->attribute][$oAttributeValue->language]
     *               $aAttributeValues[$oAttributeValue->attribute]['']
     */
    public static function getAttributesAsArrayFromDB($iQuestionID){
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
    public static function addAdditionalAttributesFromExtendedTheme($aAttributeNames, $oQuestion){
        $retAttributeNamesExtended = $aAttributeNames;
        /* @var $oAttributeValue QuestionAttribute*/
        $oAttributeValue = self::model()->resetScope()->find("qid=:qid and attribute=:attribute",
            ['qid' => $oQuestion->qid, 'attribute' => 'question_template']);
        if($oAttributeValue !== null){
            $aAttributeValueQuestionTemplate['question_template'] = $oAttributeValue->value;
            $retAttributeNamesExtended = Question::getQuestionTemplateAttributes($retAttributeNamesExtended, $aAttributeValueQuestionTemplate, $oQuestion);
        }

        return $retAttributeNamesExtended;
    }

    /**
     * @param $data
     * @return bool
     * @deprecated at 2018-01-29 use $model->attributes = $data && $model->save()
     */
    public static function insertRecords($data)
    {
        $attrib = new self;
        foreach ($data as $k => $v) {
            $attrib->$k = $v;
        }
        return $attrib->save();
    }

    /**
     * @param string $fields
     * @param mixed $condition
     * @param string $orderby
     * @return array
     */
    public function getQuestionsForStatistics($fields, $condition, $orderby = false)
    {
        $command = Yii::app()->db->createCommand()
            ->select($fields)
            ->from($this->tableName())
            ->where($condition);
        if ($orderby != false) {
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
        $criteria->params = [':qid'=>$this->qid];
        if ($this->language) {
            $criteria->addCondition('language=:language');
            $criteria->params = [':qid'=>$this->qid, ':language'=>$this->language];
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
     * @return (string|bool|null)[]
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
            "i18n"=> false,
            "readonly" => false,
            "readonly_when_active" => false,
            "expression"=> null,
        );
    }


    /**
     * Return the question attribute settings for the passed type (parameter)
     *
     * @param $sType : type of question (this is the attribute 'question_type' in table question_theme)
     *
     * @return array : the attribute settings for this question type
     *                 returns values from getGeneralAttributesFromXml and getAdvancedAttributesFromXml if this fails
     *                 getAttributesDefinition and getDefaultSettings are returned
     *
     * @throws CException
     */
    public static function getQuestionAttributesSettings($sType)
    {
        $sXmlFilePath = QuestionTheme::getQuestionXMLPathForBaseType($sType);
        // get attributes from config.xml
        $generalAttributes = self::getGeneralAttibutesFromXml($sXmlFilePath);
        $advancedAttributes = self::getAdvancedAttributesFromXml($sXmlFilePath);
        self::$questionAttributesSettings[$sType] = array_merge($generalAttributes, $advancedAttributes);

        // if empty, fall back to getting attributes from questionHelper
        if (empty(self::$questionAttributesSettings[$sType])) {
            self::$questionAttributesSettings[$sType] = array();
            $attributes = \LimeSurvey\Helpers\questionHelper::getAttributesDefinitions();
            /* Filter to get this question type setting */
            $aQuestionTypeAttributes = array_filter($attributes, function($attribute) use ($sType) {
                return stripos($attribute['types'], $sType) !== false;
            });
            foreach ($aQuestionTypeAttributes as $attribute=>$settings) {
                  self::$questionAttributesSettings[$sType][$attribute] = array_merge(
                      QuestionAttribute::getDefaultSettings(),
                      array("category"=>gT("Plugins")),
                      $settings,
                      array("name"=>$attribute)
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
     * @return array The advanced attribute settings for this question type
     */
    protected static function getAdvancedAttributesFromXml($sXmlFilePath){
        $aXmlAttributes = array();
        $aAttributes = array();

        if(file_exists($sXmlFilePath)){
            // load xml file
            libxml_disable_entity_loader(false);
            $xml_config = simplexml_load_file($sXmlFilePath);
            $aXmlAttributes = json_decode(json_encode((array)$xml_config->attributes), TRUE);
            // if only one attribute, then it doesn't return numeric index
            if (!empty($aXmlAttributes && !array_key_exists('0', $aXmlAttributes['attribute']))){
                $aTemp = $aXmlAttributes['attribute'];
                unset($aXmlAttributes);
                $aXmlAttributes['attribute'][0] = $aTemp;

            }
            libxml_disable_entity_loader(true);
        } else {
            return null;
        }

        // set $aAttributes array with attribute data
        if (!empty($aXmlAttributes['attribute'])){
            foreach ($aXmlAttributes['attribute'] as $key => $value) {
                if(empty($value['name'])) {
                    /* Allow comments in attributes */
                    continue;
                }
                /* settings the default value */
                $aAttributes[$value['name']] =self::getDefaultSettings();
                /* settings the xml value */
                foreach ($value as $key2 => $value2) {
                    if ($key2 === 'options' && !empty($value2)){
                        foreach ($value2['option'] as $key3 => $value3) {
                            if (isset($value3['value'])){
                                $value4 = is_array($value3['value'])?'':$value3['value'];
                                $aAttributes[$value['name']]['options'][$value4] = $value3['text'];
                            }
                        }
                    } else {
                        $aAttributes[$value['name']][$key2] = $value2;
                    }
                }
            }
        }
        return $aAttributes;
    }

    /**
     * Read question attributes from XML file and convert it to array
     *
     * @param string $sXmlFilePath Path to XML
     *
     * @return array The general attribute settings for this question type
     */
    protected static function getGeneralAttibutesFromXml($sXmlFilePath)
    {
        $aAttributes = array();

        if (file_exists($sXmlFilePath)) {
            // load xml file
            libxml_disable_entity_loader(false);
            $xml_config = simplexml_load_file($sXmlFilePath);
            $aXmlAttributes = json_decode(json_encode((array)$xml_config->generalattributes), true);
            // if only one attribute, then it doesn't return numeric index
            if (!empty($aXmlAttributes && !array_key_exists('0', $aXmlAttributes['attribute']))) {
                $aTemp = $aXmlAttributes['attribute'];
                unset($aXmlAttributes);
                $aXmlAttributes['attribute'][0] = $aTemp;

            }
            libxml_disable_entity_loader(true);
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
    public static function getOwnQuestionAttributesViaPlugin(){
        $event = new \LimeSurvey\PluginManager\PluginEvent('newQuestionAttributes');
        $result = App()->getPluginManager()->dispatchEvent($event);

        return (array) $result->get('questionAttributes');
    }
}
