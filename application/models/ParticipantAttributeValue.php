<?php

/**
 * This is the model class for table "{{participant_attribute_values}}".
 *
 * The table naming is misleading, it only contains the available options for
 * attributes of type dropdown in CPDB.
 *
 * The following are the available columns in table '{{participant_attribute_values}}':
 * @property integer $value_id unique id of the value
 * @property integer $attribute_id of the attribute
 * @property string $value
 *
 * @property ParticipantAttributeName $participant_attribute_name
 */
class ParticipantAttributeValue extends LSActiveRecord
{
    /** @inheritdoc */
    public function tableName()
    {
        return '{{participant_attribute_values}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'value_id';
    }

    /**
     * @inheritdoc
     * @return ParticipantAttributeValue
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that will receive user inputs.
        return array(
            ['attribute_id', 'required'],
            ['value', 'LSYii_FilterValidator', 'filter' => 'strip_tags', 'skipOnEmpty' => true],
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            ['value_id, attribute_id, value', 'safe', 'on' => 'search'],
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'participant_attribute_name' => array(self::BELONGS_TO, 'ParticipantAttributeName', 'attribute_id')
        );
    }
}
