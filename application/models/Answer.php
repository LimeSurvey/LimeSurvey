<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

class Answer extends LSActiveRecord
{
	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
     * @param string $class
	 * @return Answer
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
		return '{{answers}}';
	}

    /**
    * Returns the primary key of this table
    *
    * @access public
    * @return array
    */
    public function primaryKey()
    {
        return array('qid', 'code','language','scale_id');
    }

    /**
     * Defines the relations for this model
     *
     * @access public
     * @return array
     */
    public function relations()
    {
		$alias = $this->getTableAlias();
        return array(
            'questions' => array(self::HAS_ONE, 'Question', '',
                'on' => "$alias.qid = questions.qid",
            ),
            'groups' => array(self::HAS_ONE, 'QuestionGroup', '', 'through' => 'questions',
                'on' => 'questions.gid = groups.gid'
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
            array('qid','numerical', 'integerOnly'=>true),
            array('code','length', 'min' => 1, 'max'=>5),
            array('answer','LSYii_Validators'),
            array('sortorder','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
            array('assessment_value','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
            array('language','length', 'min' => 2, 'max'=>20),// in array languages ?
            array('scale_id','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
        );
    }

    function getAnswers($qid)
    {
        return Yii::app()->db->createCommand()
            ->select()
            ->from(self::tableName())
            ->where(array('and', 'qid='.$qid))
            ->order('code asc')
            ->query();
    }

    /**
     * Return the key=>value answer for a given $qid
     * 
     * @staticvar array $answerCache
     * @param type $qid
     * @param type $code
     * @param type $lang
     * @param type $iScaleID
     * @return array
     */
    function getAnswerFromCode($qid, $code, $lang, $iScaleID=0)
    {
        static $answerCache = array();

        if (array_key_exists($qid, $answerCache)
                && array_key_exists($code, $answerCache[$qid])
                && array_key_exists($lang, $answerCache[$qid][$code])
                && array_key_exists($iScaleID, $answerCache[$qid][$code][$lang])) {
            // We have a hit :)
            return $answerCache[$qid][$code][$lang][$iScaleID];
        } else {
            $answerCache[$qid][$code][$lang][$iScaleID] = Yii::app()->db->cache(6)->createCommand()
			->select('answer')
			->from(self::tableName())
			->where(array('and', 'qid=:qid', 'code=:code', 'scale_id=:scale_id', 'language=:lang'))
			->bindParam(":qid", $qid, PDO::PARAM_INT)
			->bindParam(":code", $code, PDO::PARAM_STR)
			->bindParam(":lang", $lang, PDO::PARAM_STR)
                        ->bindParam(":scale_id", $iScaleID, PDO::PARAM_INT)
			->query()->readAll();
            
            return $answerCache[$qid][$code][$lang][$iScaleID];
        }
    }

    public function oldNewInsertansTags($newsid,$oldsid)
    {
            $criteria = new CDbCriteria;
            $criteria->compare('questions.sid',$newsid);
            $criteria->compare('answer','{INSERTANS::'.$oldsid.'X');
            return $this->with('questions')->findAll($criteria);
    }

    public function updateRecord($data, $condition=FALSE)
    {
        return Yii::app()->db->createCommand()->update(self::tableName(), $data, $condition ? $condition : '');
    }

	function insertRecords($data)
    {
        $ans = new self;
		foreach ($data as $k => $v)
			$ans->$k = $v;
		try
		{
			return $ans->save();
		}
		catch(Exception $e)
		{
			return false;
		}
    }

    /**
     * Updates sort order of answers inside a question
     *
     * @static
     * @access public
     * @param int $qid
     * @param string $lang
     * @return void
     */
    public static function updateSortOrder($qid, $lang)
    {
        $data = self::model()->findAllByAttributes(array('qid' => $qid, 'language' => $lang), array('order' => 'sortorder asc'));
        $position = 0;

        foreach ($data as $row)
        {
            $row->sortorder = $position++;
            $row->save();
        }
    }

    public function getAnswerQuery($surveyid, $lang, $return_query = TRUE)
    {
		$query = Yii::app()->db->createCommand();
		$query->select("{{answers}}.*, {{questions}}.gid");
		$query->from("{{answers}}, {{questions}}");
		$query->where("{{questions}}.sid = :surveyid AND {{questions}}.qid = {{answers}}.qid AND {{questions}}.language = {{answers}}.language AND {{questions}}.language = :lang");
		$query->order('qid, code, sortorder');
		$query->bindParams(":surveyid", $surveyid, PDO::PARAM_INT);
		$query->bindParams(":lang", $lang, PDO::PARAM_STR);
		return ( $return_query ) ? $query->queryAll() : $query;
    }

    function getAllRecords($condition, $order=FALSE)
    {
        $command=Yii::app()->db->createCommand()->select('*')->from($this->tableName())->where($condition);
        if ($order != FALSE)
        {
            $command->order($order);
        }
        return $command->query();
    }

    public function getQuestionsForStatistics($fields, $condition, $orderby)
    {
        return Yii::app()->db->createCommand()
        ->select($fields)
        ->from(self::tableName())
        ->where($condition)
        ->order($orderby)
        ->queryAll();
    }
}
?>
