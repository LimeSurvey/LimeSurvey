<?php

/**
 * This is the model class for table "{{settings_user}}".
 *
 * The following are the available columns in table '{{settings_user}}':
 * @property integer $uid User id
 * @property string $entity Entity name
 * @property string $entity_id Entity ID
 * @property string $stg_name Setting name
 * @property string $stg_value Setting Value
 */
class SettingsUser extends LSActiveRecord
{
    const ENTITY_SURVEY = 100;
    const ENTITY_SURVEYGROUP = 90;
    const ENTITY_THEME = 80;
    const ENTITY_EDITOR = 70;
    const ENTITY_PLUGIN = 50;
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{settings_user}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('uid, stg_name', 'required'),
            array('uid', 'numerical', 'integerOnly' => true),
            array('entity', 'length', 'max' => 15),
            array('entity_id', 'length', 'max' => 31),
            array('stg_name', 'length', 'max' => 63),
            array('stg_value', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('uid, entity, entity_id, stg_name, stg_value', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user' =>  array(self::HAS_ONE, 'User', 'uid')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'uid' => 'Uid',
            'entity' => 'Entity',
            'entity_id' => 'Entity',
            'stg_name' => 'Setting Name',
            'stg_value' => 'Setting Value',
        );
    }

    /**
     * Changes or creates a user setting
     *
     * @param string $stg_name
     * @param integer|string $stg_value
     * @param integer $uid | Can be omitted to just take the currently logged in users id
     * @param string $entity | optional defaults to 'null'
     * @param integer $entity_id | optional defaults to 'null'
     * @return boolean Saving success/failure
     */

    public static function setUserSetting($stg_name, $stg_value, $uid = null, $entity = null, $entity_id = null)
    {
        if ($uid === null) {
            $uid = Yii::app()->user->getId();
        }

        $setting = self::getUserSetting($stg_name, $uid, $entity, $entity_id);

        if ($setting == null) {
            $setting = new SettingsUser();
            $setting->setAttributes([
                'stg_name' => $stg_name,
                'stg_value' => '',
                'uid' => $uid,
                'entity' => $entity,
                'entity_id' => $entity_id
            ]);
        }
        $setting->setAttribute('stg_value', $stg_value);

        return $setting->save();
    }

    /**
     * Deletes user setting
     *
     * @param string $stg_name
     * @param integer $uid | Can be omitted to just take the currently logged in users id
     * @param string $entity | optional defaults to 'null'
     * @param integer $entity_id | optional defaults to 'null'
     * @return boolean Deleting success/failure
     */

    public static function deleteUserSetting($stg_name, $uid = null, $entity = null, $entity_id = null)
    {
        if ($uid === null) {
            $uid = Yii::app()->user->getId();
        }

        $setting = self::getUserSetting($stg_name, $uid, $entity, $entity_id);

        if ($setting !== null) {
            return $setting->delete();
        }

        return false;
    }

    /**
     * Gets a user setting depending on the given parameters
     *
     * @param string $stg_name
     * @param integer $uid | Can be omitted to just take the currently logged in users id
     * @param string $entity | optional defaults to 'null'
     * @param integer $entity_id | optional defaults to 'null'
     * @return SettingsUser The current settings Object
     */
    public static function getUserSetting($stg_name, $uid = null, $entity = null, $entity_id = null)
    {
        if ($uid === null) {
            $uid = Yii::app()->user->getId();
        }
        $searchCriteria = new CDbCriteria();
        $searchParams = [];

        $searchCriteria->addCondition('uid=:uid');
        $searchParams[':uid'] = $uid;
        $searchCriteria->addCondition('stg_name=:stg_name');
        $searchParams[':stg_name'] = $stg_name;
        if ($entity != null) {
            $searchCriteria->addCondition('entity=:entity');
            $searchParams[':entity'] = $entity;
        } else {
            $searchCriteria->addCondition('entity IS NULL');
        }
        if ($entity_id != null) {
            $searchCriteria->addCondition('entity_id=:entity_id');
            $searchParams[':entity_id'] = $entity_id;
        } else {
            $searchCriteria->addCondition('entity_id IS NULL');
        }

        $searchCriteria->params = $searchParams;

        $setting = self::model()->find($searchCriteria);

        return $setting ?? null;
    }

    /**
     * Gets a user settings value depending on the given parameters
     * Shorthand function
     *
     * @param string $stg_name
     * @param integer|null $uid | Can be omitted to just take the currently logged in users id
     * @param integer|null $entity | optional defaults to 'null'
     * @param integer|null $entity_id | optional defaults to 'null'
     * @param mixed $default | optional defaults to 'null'
     * @return mixed|null  The current settings value or null id there is no setting
     */
    public static function getUserSettingValue($stg_name, $uid = null, $entity = null, $entity_id = null, $default = null)
    {
        $setting = self::getUserSetting($stg_name, $uid, $entity, $entity_id);
        return $setting != null ? $setting->getAttribute('stg_value') : $default;
    }

    public static function applyBaseSettings($iUid)
    {
        $defaults = LsDefaultDataSets::getDefaultUserSettings();
        foreach ($defaults as $default) {
            self::setUserSetting($default['stg_name'], $default['stg_value'], $iUid);
        }
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria();

        $criteria->compare('uid', $this->uid);
        $criteria->compare('entity', $this->entity, true);
        $criteria->compare('entity_id', $this->entity_id, true);
        $criteria->compare('stg_name', $this->stg_name, true);
        $criteria->compare('stg_value', $this->stg_value, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return SettingsUser the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }
}
