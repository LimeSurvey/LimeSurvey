<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Survey extends CActiveRecord
{
	/**
	 * Returns the table's name
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{surveys}}';
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

	/**
	 * Returns this model's relations
	 *
	 * @access public
	 * @return array
	 */
	public function relations()
	{
		return array(
			'languagesettings' => array(self::HAS_ONE, 'Surveys_languagesettings', '',
				'on' => 't.sid = languagesettings.surveyls_survey_id AND t.language = languagesettings.surveyls_language'),
			'owner' => array(self::BELONGS_TO, 'User', '', 'on' => 't.owner_id = owner.uid'),
		);
	}

	/**
	 * Returns this model's scopes
	 *
	 * @access public
	 * @return array
	 */
	public function scopes()
	{
		return array(
			'active' => array(
				'condition' => 'active = "Y"',
			),
		);
	}

	/**
	 * permission scope for this model
	 *
	 * @access public
	 * @param int $loginID
	 * @return CActiveRecord
	 */
	public function permission($loginID)
	{
		$loginID = (int) $loginID;
		$criteria = $this->getDBCriteria();
		$criteria->mergeWith(array(
			'condition' => 'sid IN (SELECT sid FROM {{survey_permissions}} WHERE uid = :uid AND permission = :permission AND read_p = 1)',
		));
		$criteria->params[':uid'] = $loginID;
		$criteria->params[':permission'] = 'survey';

		return $this;
	}

    public function getAllRecords($condition=FALSE)
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
    	/**
	 * Returns users meeting given condition
	 *
	 * @access public
	 * @return string
	 */
    public function getSomeRecords($fields,$condition=FALSE)
    {
		$criteria = new CDbCriteria;

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

    public function getDataOnSurvey($surveyid)
    {
        $sql = "SELECT * FROM ".$this->db->dbprefix('surveys')." inner join ".$this->db->dbprefix('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=".$surveyid;
        $this->load->helper('database');
        return db_select_limit_assoc($sql, 1);

    }

    /**
    * Creates a new survey - does some basic checks of the suppplied data
    *
    * @param string $data
    * @return mixed
    */
    public function insertNewSurvey($data)
    {
        do
        {
            if(isset($data['wishSID'])) // if wishSID is set check if it is not taken already
            {
                $data['sid']=$data['wishSID'];
                unset($data['wishSID']);
            }
            else
            {
                $data['sid'] = sRandomChars(6,'123456789');
            }
            $isquery = 'SELECT sid FROM {{surveys}} WHERE sid='.$data['sid'];
            $isresult = Yii::app()->db->createCommand($isquery)->query(); // Checked
        }
        while ($isresult->count()>0);

        $data['datecreated']=date("Y-m-d");
        if (isset($data['startdate']) && trim($data['startdate'])=='')
        {
            $data['startdate']=null;
        }
        if (isset($data['expires']) && trim($data['expires'])=='')
        {
            $data['expires']=null;
        }

        try {
			Yii::app()->db->createCommand()->insert('{{surveys}}', $data);
        } catch(CDbException $e) {
			return false;
        }
		return $data['sid'];
    }

    public function updateSurvey($data,$condition)
    {
        $this->db->where($condition);
        return $this->db->update('surveys', $data);
    }

    public function getSurveyNames()
    {
        $this->db->select('surveyls_survey_id,surveyls_title');
        $this->db->from('surveys_languagesettings');
        $this->db->join('surveys','surveys_languagesettings.surveyls_survey_id = surveys.sid');
        $this->db->where('owner_id',$this->session->userdata('loginID'));
        //$this->db->where('usetokens','Y'); // Will be done later
        $query=$this->db->get();
        return $query->result_array();
    }
    
	public function getAllSurveyNames()
    {
        return Yii::app()->db->createCommand()->select('surveyls_survey_id,surveyls_title')->from('{{surveys_languagesettings}}')->join('{{surveys}}','{{surveys_languagesettings}}.surveyls_survey_id = {{surveys}}.sid')->queryAll();
    }

    public function deleteSurvey($iSurveyID, $recursive=true)
    {
        $this->db->delete('surveys', array('sid' => $iSurveyID));

        if ($recursive)
        {

            $this->load->helper('database');
            $this->load->dbforge();
            $this->load->model('questions_model');
            if (tableExists("survey_{$iSurveyID}"))  //delete the survey_$iSurveyID table
            {
                $dsresult = $this->dbforge->drop_table('survey_'.$iSurveyID) or safe_die ("Couldn't drop table survey_".$iSurveyID);
            }

            if (tableExists("survey_{$iSurveyID}_timings"))  //delete the survey_$iSurveyID_timings table
            {
                $dsresult = $this->dbforge->drop_table('survey_'.$iSurveyID.'_timings') or safe_die ("Couldn't drop table survey_".$iSurveyID."_timings");
            }

            if (tableExists("tokens_$iSurveyID")) //delete the tokens_$iSurveyID table
            {
                $dsresult = $this->dbforge->drop_table('tokens_'.$iSurveyID) or safe_die ("Couldn't drop table token_".$iSurveyID);
            }

            $oResult=$this->questions_model->getSomeRecords(array('qid'),array('sid'=>$iSurveyID));
            foreach ($oResult->result_array() as $aRow)
            {
                $this->db->delete('answers', array('qid' => $aRow['qid']));
                $this->db->delete('conditions', array('qid' => $aRow['qid']));
                $this->db->delete('question_attributes', array('qid' => $aRow['qid']));

            }

            $this->db->delete('questions', array('sid' => $iSurveyID));
            $this->db->delete('assessments', array('sid' => $iSurveyID));
            $this->db->delete('groups', array('sid' => $iSurveyID));
            $this->db->delete('surveys_languagesettings', array('surveyls_survey_id' => $iSurveyID));
            $this->db->delete('survey_permissions', array('sid' => $iSurveyID));
            $this->db->delete('saved_control', array('sid' => $iSurveyID));
            $this->load->model('survey_url_parameters_model');
            $this->survey_url_parameters_model->deleteRecords(array('sid'=>$iSurveyID));

            $this->load->model('quota_model');
            $this->quota_model->deleteQuota(array('sid'=>$iSurveyID));
        }

    }
}
