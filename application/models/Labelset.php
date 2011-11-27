<?php if(! defined('BASEPATH')) die("No direct script access allowed");

class Labelset extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	/**
	   * Return the table's name
	   *
	   * @access public
	   * @return string
	*/
	public function tableName()
	{
		return '{{labelsets}}';
	}
	
	function getAllRecords($condition=NULL, $params=array())
	{
		return Labelset::model()->findAll($condition, $params);
	}

	
    
    function getLID()
    {
        $this->db->select('lid');
        $this->db->order_by('lid','asc');
        return $this->db->get('labelsets');
    }

    function insertRecords($data)
    {
        
        return $this->db->insert('labelsets',$data);
    }
}
