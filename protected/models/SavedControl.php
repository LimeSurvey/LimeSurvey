<?php
namespace ls\models;

use CActiveRecord;
use CDbCriteria;
use ls\models\ActiveRecord;

class SavedControl extends ActiveRecord
{
    /**
     * Returns the table's name
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{saved_control}}';
    }

    /**
     * Returns the table's primary key
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'scid';
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

    function getAllRecords($condition = false)
    {
        if ($condition != false) {
            $this->db->where($condition);
        }

        $data = $this->db->get('saved_control');

        return $data;
    }


    /**
     * Deletes some records meeting speicifed condition
     *
     * @access public
     * @param array $condition
     * @return int (rows deleted)
     */
    public function deleteSomeRecords($condition)
    {
        $record = new self;
        $criteria = new CDbCriteria;

        if ($condition != false) {
            foreach ($condition as $column => $value) {
                $criteria->addCondition("$column='$value'");
            }
        }

        return $record->deleteAll($criteria);
    }

    function insertRecords($data)
    {
        return $this->db->insert('saved_control', $data);
    }

}
