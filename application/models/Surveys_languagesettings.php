<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Surveys_languagesettings extends CActiveRecord
{
	/**
	 * Returns the table's name
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{surveys_languagesettings}}';
	}

	/**
	 * Returns the table's primary key
	 *
	 * @access public
	 * @return array
	 */
	public function primaryKey()
	{
		return array('surveyls_survey_id', 'surveyls_language');
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

	/**
	 * Returns the relations of this model
	 *
	 * @access public
	 * @return array
	 */
	public function relations()
	{
		return array(
			'survey' => array(self::BELONGS_TO, 'Survey', '',
				'on' => 't.surveyls_survey_id = survey.sid',
			),
		);
	}

	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}
		
		$data = $this->db->get('surveys_languagesettings');

        return $data;
	}
	
    function getDateFormat($surveyid,$languagecode)
    {
        $this->db->select('surveyls_dateformat');
        $this->db->from('surveys_languagesettings');
        $this->db->join('surveys','surveys.sid = surveys_languagesettings.surveyls_survey_id AND surveyls_survey_id = '.$surveyid);
        $this->db->where('surveyls_language = \''.$languagecode.'\'');
        return $this->db->get();
    }

    function getAllSurveys($hasPermission = FALSE)
    {
        $this->db->select('a.*, surveyls_title, surveyls_description, surveyls_welcometext, surveyls_url');
        $this->db->from('surveys AS a');
        $this->db->join('surveys_languagesettings','surveyls_survey_id=a.sid AND surveyls_language=a.language');

        if ($hasPermission)
        {
            $this->db->where('a.sid IN (SELECT sid FROM '.$this->db->dbprefix("survey_permissions").' WHERE uid='.$this->session->userdata("loginID").' AND permission=\'survey\' and read_p=1) ');
        }
        $this->db->order_by('active DESC, surveyls_title');
        return $this->db->get();
    }

	function update($data, $condition=FALSE)
	{
		$criteria = new CDbCriteria;

        if ($condition != FALSE)
        {	
		    foreach ($condition as $item => $value)
			{
				$criteria->addCondition($item.'="'.$value.'"');
			}
        }
		
		$data = $this->updateAll($data, $criteria);
	}

    function getAllData($sid,$lcode)
    {
    	$query = 'SELECT * FROM '. $this->db->dbprefix('surveys') .', '. $this->db->dbprefix('surveys_languagesettings') .' WHERE sid=? AND surveyls_survey_id=? AND surveyls_language=?';
        return $this->db->query($query, array($sid, $sid, $lcode));
    }

    function insertNewSurvey($data)
    {
        if (isset($data['surveyls_url']) && $data['surveyls_url']== 'http://') {$data['surveyls_url']="";}
        return $this->insertSomeRecords($data);
    }
    function getSurveyNames($surveyid)
    {
        return Yii::app()->db->createCommand()->select('surveyls_title')->from('{{surveys_languagesettings}}')->where('surveyls_language = "'.Yii::app()->session['adminlang'].'" AND surveyls_survey_id = '.$surveyid)->queryAll();
    }

    function updateRecords($data,$condition=FALSE)
    {
        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }

        $this->db->update('surveys_languagesettings',$data);

        if ($this->db->affected_rows() <= 0)
        {
            return false;
        }
        
        return true;
    }

	function insertSomeRecords($data)
    {
        $lang = new self;
		foreach ($data as $k => $v)
			$lang->$k = $v;
		return $lang->save();
    }
}
