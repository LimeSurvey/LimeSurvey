<?php
namespace ls\models;

use ls\models\ActiveRecord;

class QuotaMember extends ActiveRecord
{
    /**
     * Returns the static model of Settings table
     *
     * @static
     * @access public
     * @param string $class
     * @return QuotaMember
     */
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }

    public function rules()
    {
        return array(
            array('code', 'required', 'on' => array('create'))
        );
    }

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{quota_members}}';
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'id';
    }

    function insertRecords($data)
    {
        $members = new self;
        foreach ($data as $k => $v) {
            $members->$k = $v;
        }

        return $members->save();
    }
}