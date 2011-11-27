<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Label extends CActiveRecord
{
	/**
	 * Returns the table's name
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{labels}}';
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
		    foreach ($condition as $item => $value)
			{
				$criteria->addCondition($item.'="'.$value.'"');
			}
        }
		
		$data = $this->findAll($criteria);

        return $data;
	}

	function getSomeRecords($fields,$condition=FALSE)
	{
		if ($condition != FALSE)
        {	
		    foreach ($condition as $item => $value)
			{
				$criteria->addCondition($item.'="'.$value.'"');
			}
        }
		
		$data = $this->findAll($criteria);

        return $data;
	}
    
    function getLabelCodeInfo($lid)
    {
		return Yii::app()->db->createCommand()->select('code, title, sortorder, language, assessment_value')->order('language, sortorder, code')->where('lid='.$lid)->from(tableName())->query()->readAll();
    }

	function insertRecords($data)
    {
        $lbls = new self;
		foreach ($data as $k => $v)
			$lbls->$k = $v;
		$lbls->save();
    }

}
