<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Saved_control extends CActiveRecord {
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
		return 'sid';
	}

	/**
	 * Return the static model for this table
	 *
	 * @static
	 * @access public
	 * @return CActiveRecord
	 */
	public static function model()
	{
		return parent::model(__CLASS__);
	}
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('saved_control');
		
		return $data;
	}

	public static function getSomeRecords($condition=FALSE)
	{
		$record = new self;
		$criteria = new CDbCriteria;
		
		if($condition != FALSE)
		{
			foreach ($condition as $column=>$value)
			{
				$criteria->addCondition("$column='$value'");
			}
		}
		
		return $record->findAll($criteria);
	}
    
    public function getCountOfAll($sid)
    {
        $data = Yii::app()->db->createCommand("SELECT COUNT(*) AS countall FROM {{saved_control}} WHERE sid=$sid")->query();
        $row = $data->read();
        
        return $row['countall'];
    }
    
    function insertRecords($data)
    {
        return $this->db->insert('saved_control', $data); 
    }

}
