<?php

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
  *     Extensions to the CActiveRecord class
 */

/**
 * @method PluginEvent dispatchPluginModelEvent(string $sEventName, CDbCriteria $criteria = null, array $eventParams = [])
 */
class LSActiveRecord extends CActiveRecord
{
    /** @var string[] Array of attributes that should be XSS filtered on mass updates */
    protected $xssFilterAttributes = [];

    public $bEncryption = false;

    /**
     * Lists the behaviors of this model
     *
     * Below is a list of all behaviors we register:
     * @return array
     * @see PluginEventBehavior
     * @see CTimestampBehavior
     */
    public function behaviors()
    {
        $aBehaviors = [];
        $sCreateFieldName = ($this->hasAttribute('created') ? 'created' : null);
        $sUpdateFieldName = ($this->hasAttribute('modified') ? 'modified' : null);
        $sDriverName = Yii::app()->db->getDriverName();
        if ($sDriverName == 'sqlsrv' || $sDriverName == 'dblib') {
            $sTimestampExpression = new CDbExpression('GETDATE()');
        } else {
            $sTimestampExpression = new CDbExpression('NOW()');
        }
        $aBehaviors['CTimestampBehavior'] = [
            'class'               => 'zii.behaviors.CTimestampBehavior',
            'createAttribute'     => $sCreateFieldName,
            'updateAttribute'     => $sUpdateFieldName,
            'timestampExpression' => $sTimestampExpression
        ];
        // Some tables might not exist/not be up to date during a database upgrade so in that case disconnect plugin events
        if (!Yii::app()->getConfig('Updating')) {
            $aBehaviors['PluginEventBehavior'] = [
                'class' => 'application.models.behaviors.PluginEventBehavior'
            ];
        }
        return $aBehaviors;
    }

    /**
     * Modified version that default to do the same as the original, but allows via a
     * third parameter to retrieve the result as array instead of active records. This
     * solves a joining problem. Usage via findAllAsArray method
     *
     * Performs the actual DB query and populates the AR objects with the query result.
     * This method is mainly internally used by other AR query methods.
     * @param CDbCriteria $criteria the query criteria
     * @param boolean $all whether to return all data
     * @param bool $asAR
     * @return mixed the AR objects populated with the query result
     * @since 1.1.7
     */
    protected function query($criteria, $all = false, $asAR = true)
    {
        if ($asAR === true) {
            return parent::query($criteria, $all);
        } else {
            $this->beforeFind();
            $this->applyScopes($criteria);
            if (!$all) {
                $criteria->limit = 1;
            }

            $command = $this->getCommandBuilder()->createFindCommand($this->getTableSchema(), $criteria);
            //For debug, this command will get you the generated sql:
            //echo $command->getText();

            return $all ? $command->queryAll() : $command->queryRow();
        }
    }

    /**
     * Finds all active records satisfying the specified condition but returns them as array
     *
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return array list of active records satisfying the specified condition. An empty array is returned if none is found.
     */
    public function findAllAsArray($condition = '', $params = [])
    {
        Yii::trace(get_class($this) . '.findAll()', 'system.db.ar.CActiveRecord');
        $criteria = $this->getCommandBuilder()->createCriteria($condition, $params);
        return $this->query($criteria, true, false); //Notice the third parameter 'false'
    }


    /**
     * Return the max value for a field
     *
     * This is a convenience method, that uses the primary key of the model to
     * retrieve the highest value.
     *
     * @param string $field The field that contains the Id, when null primary key is used if it is a single field
     * @param boolean $forceRefresh Don't use value from static cache but always requery the database
     * @return false|int
     * @throws Exception
     */
    public function getMaxId($field = null, $forceRefresh = false)
    {
        static $maxIds = [];

        if (is_null($field)) {
            $primaryKey = $this->getMetaData()->tableSchema->primaryKey;
            if (is_string($primaryKey)) {
                $field = $primaryKey;
            } else {
                // Composite key, throw a warning to the programmer
                throw new Exception(sprintf('Table %s has a composite primary key, please explicitly state what field you need the max value for.', $this->tableName()));
            }
        }

        if ($forceRefresh || !array_key_exists($field, $maxIds)) {
            $maxId = $this->dbConnection->createCommand()
                ->select('MAX(' . $this->dbConnection->quoteColumnName($field) . ')')
                ->from($this->tableName())
                ->queryScalar();

            // Save so we can reuse in the same request
            $maxIds[$field] = $maxId;
        }

        return $maxIds[$field];
    }

    /**
     * Return the min value for a field
     *
     * This is a convenience method, that uses the primary key of the model to
     * retrieve the highest value.
     *
     * @param string $field The field that contains the Id, when null primary key is used if it is a single field
     * @param boolean $forceRefresh Don't use value from static cache but always requery the database
     * @return false|int
     * @throws Exception
     */
    public function getMinId($field = null, $forceRefresh = false)
    {
        static $minIds = [];

        if (is_null($field)) {
            $primaryKey = $this->getMetaData()->tableSchema->primaryKey;
            if (is_string($primaryKey)) {
                $field = $primaryKey;
            } else {
                // Composite key, throw a warning to the programmer
                throw new Exception(sprintf('Table %s has a composite primary key, please explicitly state what field you need the min value for.', $this->tableName()));
            }
        }

        if ($forceRefresh || !array_key_exists($field, $minIds)) {
            $minId = $this->dbConnection->createCommand()
                ->select('MIN(' . $this->dbConnection->quoteColumnName($field) . ')')
                ->from($this->tableName())
                ->queryScalar();

            // Save so we can reuse in the same request
            $minIds[$field] = $minId;
        }

        return $minIds[$field];
    }

    /**
     * @param array $attributes list of attribute values (indexed by attribute names) that the active records should match.
     * An attribute value can be an array which will be used to generate an IN condition.
     * @param string $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return integer number of rows affected by the execution.
     * @todo This should also be moved to the behavior at some point.
     * This method overrides the parent in order to raise PluginEvents for Bulk delete operations.
     *
     * Filter Criteria are wrapped into a CDBCriteria instance so we have a single instance responsible for holding the filter criteria
     * to be passed to the PluginEvent,
     * this also enables us to pass the fully configured CDBCriteria instead of the original Parameters.
     *
     * See {@link find()} for detailed explanation about $condition and $params.
     */
    public function deleteAllByAttributes($attributes, $condition = '', $params = [])
    {
        $builder = $this->getCommandBuilder();
        $table = $this->getTableSchema();
        $criteria = $builder->createColumnCriteria($table, $attributes, $condition, $params);
        $modelEventName = get_class($this);
        $eventParams = [];
        if (is_subclass_of($this, 'Dynamic')) {
            /** @scrutinizer ignore-call since we test if exist by subclass */
            $eventParams['dynamicId'] = $this->getDynamicId();
            $modelEventName = get_parent_class($this);
        }
        $this->dispatchPluginModelEvent('before' . $modelEventName . 'DeleteMany', $criteria, $eventParams);
        $this->dispatchPluginModelEvent('beforeModelDeleteMany', $criteria, $eventParams);
        return parent::deleteAllByAttributes([], $criteria, []);
    }

    /**
     * Updates records with the specified condition.
     * XSS filtering is enforced for attributes listed in model's $xssFilterAttributes property.
     * See {@link find()} for detailed explanation about $condition and $params.
     * Note, the attributes are not checked for safety and no validation is done.
     * @param array $attributes list of attributes (name=>$value) to be updated
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return integer the number of rows being updated
     */
    public function updateAll($attributes, $condition = '', $params = array())
    {
        if (!empty($this->xssFilterAttributes)) {
            $validator = new LSYii_Validators();
            if ($validator->xssfilter) {
                $attributeNames = array_keys($attributes);
                $attributesToFilter = array_intersect($attributeNames, $this->xssFilterAttributes);
                foreach ($attributesToFilter as $attribute) {
                    $attributes[$attribute] = $validator->xssFilter($attributes[$attribute]);
                }
            }
        }

        return parent::updateAll($attributes, $condition, $params);
    }

    /**
     * Overriding of Yii's findByAttributes method to provide encrypted attribute value search
     * @param array $attributes list of attribute values (indexed by attribute names) that the active record should match.
     * An attribute value can be an array which will be used to generate an IN condition.
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return static|null the record found. Null if none is found.
     */
    public function findByAttributes($attributes, $condition = '', $params = [])
    {
        $attributes = $this->encryptAttributeValues($attributes);
        return parent::findByAttributes($attributes, $condition, $params);
    }

    /**
     * Overriding of Yii's findAllByAttributes method to provide encrypted attribute value search
     * @param array $attributes list of attribute values (indexed by attribute names) that the active records should match.
     * An attribute value can be an array which will be used to generate an IN condition.
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return static[] the records found. An empty array is returned if none is found.
     */
    public function findAllByAttributes($attributes, $condition = '', $params = [])
    {
        $attributes = $this->encryptAttributeValues($attributes);
        return parent::findAllByAttributes($attributes, $condition, $params);
    }

    /**
     * @param int $iSurveyId
     * @param string $sClassName
     * @return array
     * TODO: Should be split into seperate functions in the appropiate model or helper class
     * TODO: Make an interface for records that support encryption.
     */
    public function getAllEncryptedAttributes($iSurveyId, $sClassName)
    {
        $aAttributes = [];
        if ($sClassName == 'ParticipantAttribute') {
            // participants attributes
            $aAttributes[] = 'value';
        } elseif ($sClassName == 'Participant') {
            // participants
            $aTokenAttributes = Participant::getParticipantsEncryptionOptions();
            if ($aTokenAttributes['enabled'] = 'Y') {
                foreach ($aTokenAttributes['columns'] as $attribute => $oColumn) {
                    if ($oColumn == 'Y') {
                        $aAttributes[] = $attribute;
                    }
                }
            }
        } elseif ($iSurveyId > 0 && ($sClassName == 'TokenDynamic' || $sClassName == 'Token_' . $iSurveyId || $sClassName == 'Token')) {
            //core token attributes
            $oSurvey = Survey::model()->findByPk($iSurveyId);
            $aTokenAttributes = $oSurvey->getTokenEncryptionOptions();
            if ($aTokenAttributes['enabled'] = 'Y') {
                foreach ($aTokenAttributes['columns'] as $attribute => $oColumn) {
                    if ($oColumn == 'Y') {
                        $aAttributes[] = $attribute;
                    }
                }
            }
            // custom token attributes
            $aCustomAttributes = $oSurvey->tokenAttributes;
            foreach ($aCustomAttributes as $attribute => $value) {
                if ($value['encrypted'] == 'Y') {
                    $aAttributes[] = $attribute;
                }
            }
        } elseif ($sClassName == 'SurveyDynamic' || $sClassName == 'Response_' . $iSurveyId) {
            // response attributes
            $aAttributes = Response::getEncryptedAttributes($iSurveyId);
        }

        return $aAttributes;
    }

    /**
     * Attribute values are encrypted ( if needed )to be used for searching purposes
     * @param array $attributes list of attribute values (indexed by attribute names) that the active records should match.
     * An attribute value can be an array which will be used to generate an IN condition.
     * @return array attributes array with encrypted atrribute values is returned
     */
    public function encryptAttributeValues($attributes = null, $bEncryptedOnly = false, $bReplaceValues = true)
    {
        // load sodium library
        $sodium = Yii::app()->sodium;

        if (method_exists($this, 'getSurveyId')) {
            $iSurveyId = $this->getSurveyId();
        } else {
            $iSurveyId = 0;
        }
        $class = get_class($this);
        $encryptedAttributes = $this->getAllEncryptedAttributes($iSurveyId, $class);
        foreach ($attributes as $key => $attribute) {
            if (in_array($key, $encryptedAttributes)) {
                if ($bReplaceValues) {
                    $attributes[$key] = $sodium->encrypt($attributes[$key]);
                }
            } else {
                if ($bEncryptedOnly) {
                    unset($attributes[$key]);
                }
            }
        }
        return $attributes;
    }

    /**
     * Decrypt values from database
     * @param string $sValueSingle String value which needs to be decrypted
     */
    public function decrypt($value = '')
    {
        // if $sValueSingle is provided, it would decrypt
        if (!empty($value)) {
            // load sodium library
            $sodium = Yii::app()->sodium;

            return $sodium->decrypt($value);
        } else {
            // decrypt attributes
            $this->decryptEncryptAttributes('decrypt');

            return $this;
        }
    }


    /**
     * Decrypt single value
     * @param string $value String value which needs to be decrypted
     * @return string the decrypted string
     */
    public static function decryptSingle($value = ''): string
    {
        // if $value is provided, it would decrypt
        if (!empty($value)) {
            // load sodium library
            $sodium = Yii::app()->sodium;
            return $sodium->decrypt($value);
        }
        return '';
    }

    /**
     * Decrypt single value
     * @param string $value String value which needs to be decrypted
     * @return string the decrypted string
     */
    public static function decryptSingleOld($value = ''): string
    {
        static $sodium = null;
        if (!isset($sodium)) {
            // load sodium library
            $sodium = Yii::app()->sodiumOld;
        }
        // if $value is provided, it would decrypt
        if (isset($value) && $value !== '') {
            try {
                return $sodium->decrypt($value);
            } catch (throwable $e) {
                // if decryption with oldDecrypt fails try it with new decryption
                try {
                    return LSActiveRecord::decryptSingle($value);
                } catch (throwable $e) {
                    // if decryption with new decryption fails just return the current value
                    // this should not happen
                    return $value;
                }
            }
        }
        return '';
    }


    /**
     * Enrypt single value
     * @param string $value String value which needs to be encrypted
     */
    public static function encryptSingle($value = '')
    {
        // if $value is provided, it would decrypt
        if (isset($value) && $value !== "") {
            // load sodium library
            $sodium = Yii::app()->sodium;
            return $sodium->encrypt($value);
        }
    }


    /**
     * Encrypt values
     */
    public function encrypt()
    {
        // encrypt attributes
        $this->decryptEncryptAttributes('encrypt');

        return $this;
    }


    /**
     * Encrypt values before saving to the database
     */
    public function encryptSave($runValidation = false)
    {
        // run validation on attribute values before encryption take place, it is impossible to validate encrypted values
        if ($runValidation) {
            if (!$this->validate()) {
                return false;
            }
        }

        // encrypt attributes
        $this->decryptEncryptAttributes('encrypt');
        // call save() method  without validation, validation is already done ( if needed )
        return $this->save(false);
    }

    /**
     * Encrypt/decrypt values
     */
    public function decryptEncryptAttributes($action = 'decrypt')
    {
        // load sodium library
        $sodium = Yii::app()->sodium;

        $class = get_class($this);
        // TODO: Use OOP polymorphism instead of switching on class names.
        if ($class === 'ParticipantAttribute') {
            $aParticipantAttributes = CHtml::listData(ParticipantAttributeName::model()->findAll(["select" => "attribute_id", "condition" => "encrypted = 'Y' and core_attribute <> 'Y'"]), 'attribute_id', '');
            if (array_key_exists($this->attribute_id, $aParticipantAttributes)) {
                $this->value = $sodium->$action($this->value);
            }
        } else {
            $attributes = $this->encryptAttributeValues($this->attributes, true, false);
            $LEM = LimeExpressionManager::singleton();
            $updatedValues = $LEM->getUpdatedValues();
            foreach ($attributes as $key => $attribute) {
                if ($action === 'decrypt' && array_key_exists($key, $updatedValues)) {
                    continue;
                }
                $this->$key = $sodium->$action($attribute);
            }
        }
    }

    /**
     * Function to show encryption symbol in gridview attribute header if value ois encrypted
     * @param int $surveyId
     * @param string $className
     * @param string $attributeName
     * @return string
     * @throws CException
     */
    public function setEncryptedAttributeLabel(int $surveyId, string $className, string $attributeName)
    {
        $encryptedAttributes = $this->getAllEncryptedAttributes($surveyId, $className);
        $encryptionNotice = gT("This field is encrypted and can only be searched by exact match. Please enter the exact value you are looking for.");
        if (isset($encryptedAttributes)) {
            if (in_array($attributeName, $encryptedAttributes)) {
                return ' <span  data-bs-toggle="tooltip" title="' . $encryptionNotice . '" class="ri-key-2-fill text-success"></span>';
            }
        }
    }

    public function updateSurveyLastModifiedDate($event)
    {
        $sid = null;

        // Try to get survey ID from the event sender if it exists.
        if (isset($event->sender->sid)) {
            $sid = $event->sender->sid;
        } elseif (isset($event->sender->surveyls_survey_id)) {
            $sid = $event->sender->surveyls_survey_id;
        } elseif (isset($event->sender->entity_id) && $event->sender->entity == 'survey') {
            $sid = $event->sender->entity_id;
        }

        if ($sid) {
            Survey::model()->updateLastModifiedDate($sid);
        }
    }

    public function onAfterSave($event)
    {
        $this->updateSurveyLastModifiedDate($event);
        parent::onAfterSave($event);
    }
}
