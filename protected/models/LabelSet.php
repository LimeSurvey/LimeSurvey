<?php
namespace ls\models;

use Yii;

class LabelSet extends ActiveRecord
{
    /**
     * Returns the table's name
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{labelsets}}';
    }

    /**
     * Returns the table's primary key
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'lid';
    }

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
     * Returns this model's validation rules
     *
     */
    public function rules()
    {
        return array(
            array('label_name', 'required'),
            array('label_name', 'length', 'min' => 1, 'max' => 100),
            array('label_name', 'required'),
            array('languages', 'required'),
            array('languages', 'required'),
        );
    }

    function getAllRecords($condition = false)
    {
        if ($condition != false) {
            foreach ($condition as $item => $value) {
                $criteria->addCondition($item . '="' . $value . '"');
            }
        }

        $data = $this->findAll($criteria);

        return $data;
    }

    function getLID()
    {
        return Yii::app()->db->createCommand()->select('lid')->order('lid asc')->from('{{labelsets}}')->query()->readAll();
    }

    function insertRecords($data)
    {
        $lblset = new self;
        foreach ($data as $k => $v) {
            $lblset->$k = $v;
        }
        if ($lblset->save()) {
            return $lblset->lid;
        }

        return false;
    }
}
