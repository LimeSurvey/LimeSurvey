<?php

namespace ls\models;

use CActiveRecord;
use ls\models\ActiveRecord;

class QuotaLanguageSetting extends ActiveRecord
{
    /**
     * Returns the static model of Settings table
     *
     * @static
     * @access public
     * @param string $class
     * @return CActiveRecord
     */
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{quota_languagesettings}}';
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'quotals_id';
    }

    /**
     * Returns the relations
     *
     * @access public
     * @return array
     */
    public function relations()
    {
        $alias = $this->getTableAlias();

        return array(
            'quota' => array(
                self::BELONGS_TO,
                'ls\models\Quota',
                '',
                'on' => "quota.id = $alias.quotals_quota_id"
            ),
        );
    }

    /**
     * Returns this model's validation rules
     *
     */
    public function rules()
    {
        return array(
            array('quotals_name', 'required'),// No access in quota editor, set to quota.name
            array('quotals_message', 'required'),
            array('quotals_url', 'required', 'isUrl' => true),
            array('quotals_urldescrip', 'required'),
        );
    }

    function insertRecords($data)
    {
        $settings = new self;
        foreach ($data as $k => $v) {
            $settings->$k = $v;
        }

        return $settings->save();
    }
}

