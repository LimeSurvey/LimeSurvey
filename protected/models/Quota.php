<?php
namespace ls\models;

use ls\models\ActiveRecord;

class Quota extends ActiveRecord
{
    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{quota}}';
    }

    /**
     * Returns the relations
     *
     * @access public
     * @return array
     */
    public function relations()
    {
        return [
            'languagesettings' => [self::HAS_MANY, QuotaLanguageSetting::class, 'quotals_quota_id']
        ];
    }

    /**
     * Returns this model's validation rules
     *
     */
    public function rules()
    {
        return array(
            array('name', 'required'),
            // Maybe more restrictive
            array('qlimit', 'numerical', 'integerOnly' => true, 'min' => '0', 'allowEmpty' => true),
            array('action', 'numerical', 'integerOnly' => true, 'min' => '1', 'max' => '2', 'allowEmpty' => true),
            // Default is null ?
            array('active', 'numerical', 'integerOnly' => true, 'min' => '0', 'max' => '1', 'allowEmpty' => true),
            array('autoload_url', 'numerical', 'integerOnly' => true, 'min' => '0', 'max' => '1', 'allowEmpty' => true),
        );
    }



    /**
     * Returns the relations that map to dependent records.
     * Dependent records should be deleted when this object gets deleted.
     * @return string[]
     */
    public function dependentRelations()
    {
        return [
            'languagesettings',
        ];
    }
}

