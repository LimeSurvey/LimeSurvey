<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Templates_rights extends CActiveRecord {
public static function model()
	{
		return parent::model(__CLASS__);
	}

	/**
	 * Returns the setting's table name to be used by the model
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{templates_rights}}';
	}
	public function primaryKey()
	{
		return 'uid';
	}
	
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('templates_rights');
		
		return $data;
	}

	function getSomeRecords($fields,$condition=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('templates_rights');
		
		return $data;
	}
	
	function insert($values)
	{
		return (bool) $this->db->insert('templates_rights', $values);
	}
	
	function update($what, $where)
	{
		$this->db->where($where);
		return (bool) $this->db->insert('templates_rights', $what);
	}

	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
	 * @return CActiveRecord
	 */
	
	
    function insertRecords($data)
    {
    	$tablename = $this->tableName(); 
        //$ans = new self;
	//	//foreach ($data as $k => $v)
	//		$ans->$k = $v;
		//return $ans->save();
		return Yii::app()->db->createCommand()->insert('{{templates_rights}}', $data);
    }

}
