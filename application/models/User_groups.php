<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class User_groups extends CActiveRecord {
	
	protected $connection;
	
	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
	 * @return CActiveRecord
	 */
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
		return '{{user_groups}}';
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'ugid';
	}
	
	/**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'Users' => array(self::HAS_MANY, 'User','uid')
        );
    }
	
	function getAllRecords($condition=FALSE)
	{
		$this->connection = Yii::app()->db;
		if ($condition != FALSE)
		{
			$where_clause = array("WHERE");
			
			foreach($condition as $key=>$val)
			{
				$where_clause[] = $key.'=\''.$val.'\'';
			}
			
			$where_string = implode(' AND ', $where_clause);
		}
		
		$query = 'SELECT * FROM '.$this->tableName().' '.$where_string;
	
		$data = createCommand($query)->query()->resultAll();
		
		return $data;
	}

	function getSomeRecords($fields,$condition=FALSE, $params=NULL)
	{
		$filter = new CDbCriteria;
		
		foreach ($fields as $field)
		{
			$filter->select[] = $field;
		}
		
		if ($condition != FALSE)
		{
			$filter->condition = $condition;
			$filter->params = $params;
		}
		
		$data = $this->findAll($filter);
		
		return $data;
	}

    /*function insertRecords($data)
    {
        
        return $this->db->insert('user_groups',$data);
    }
	
	function join($fields, $from, $condition=FALSE, $join=FALSE, $order=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		
		$this->db->from($from);
		
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}

		if ($order != FALSE)
		{
			$this->db->order_by($order);	
		}
		
		if (isset($join['where'], $join['type'], $join['on']))
		{
			$this->db->join($condition);	
		}
		
		$data = $this->db->get();
		return $data;
	}
	
	function multi_select($fields, $from, $condition=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		
		foreach ($from AS $f)
		{
			$this->db->from($f);
		}
		
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}

		if ($order != FALSE)
		{
			$this->db->order_by($order);	
		}
		
		if (isset($join['where'], $join['type'], $join['on']))
		{
			$this->db->join($condition);	
		}
		
		$data = $this->db->get();
		return $data;
	}
	
	function update($what, $where=FALSE)
	{
		if ($where != FALSE) $this->db->where($where);
		return (bool) $this->db->update('user_groups', $what);
	}
	
	function delete($condition)
	{
		return (bool) $this->db->delete('user_groups', $condition);
	}*/

}
