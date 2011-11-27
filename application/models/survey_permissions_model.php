<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Survey_permissions_model extends CI_Model {


    /**
    * This function gives back an array that defines which survey permissions and what part of the CRUD+Import+Export subpermissions is available.
    * - for example it would not make sense to have  a 'create' permissions for survey locale settings as they exist in every survey
    *  so the editor for survey permission should not show a checkbox here, therfore the create element of that permission is set to 'false'
    *  If you want to generally add a new permission just add it here.
    *
    */

    function getBaseSurveyPermissions()
    {
        $CI =& get_instance();
        $clang = $CI->limesurvey_lang;

        $aPermissions=array(
        'assessments'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Assessments"),'description'=>$clang->gT("Permission to create/view/update/delete assessments rules for a survey"),'img'=>'assessments'),  // Checked
        'quotas'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Quotas"),'description'=>$clang->gT("Permission to create/view/update/delete quota rules for a survey"),'img'=>'quota'), // Checked
        'responses'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Responses"),'description'=>$clang->gT("Permission to create(data entry)/view/update/delete/import/export responses"),'img'=>'browse'),
        'statistics'=>array('create'=>false,'read'=>true,'update'=>false,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Statistics"),'description'=>$clang->gT("Permission to view statistics"),'img'=>'statistics'),    //Checked
        'survey'=>array('create'=>false,'read'=>true,'update'=>false,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey deletion"),'description'=>$clang->gT("Permission to delete a survey"),'img'=>'delete'),   //Checked
        'surveyactivation'=>array('create'=>false,'read'=>false,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey activation"),'description'=>$clang->gT("Permission to activate/deactivate a survey"),'img'=>'activate_deactivate'),  //Checked
        'surveycontent'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Survey content"),'description'=>$clang->gT("Permission to create/view/update/delete/import/export the questions, groups, answers & conditions of a survey"),'img'=>'add'),
        'surveylocale'=>array('create'=>false,'read'=>true,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey locale settings"),'description'=>$clang->gT("Permission to view/update the survey locale settings"),'img'=>'edit'),
        'surveysecurity'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey security"),'description'=>$clang->gT("Permission to modify survey security settings"),'img'=>'survey_security'),
        'surveysettings'=>array('create'=>false,'read'=>true,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey settings"),'description'=>$clang->gT("Permission to view/update the survey settings including token table creation"),'img'=>'survey_settings'),
        'tokens'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Tokens"),'description'=>$clang->gT("Permission to create/update/delete/import/export token entries"),'img'=>'tokens'),
        'translations'=>array('create'=>false,'read'=>true,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Quick translation"),'description'=>$clang->gT("Permission to view & update the translations using the quick-translation feature"),'img'=>'translate')
        );

        uasort($aPermissions,"aComparePermission");
        return $aPermissions;
    }

    function getAllRecords($condition=FALSE)
    {
        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }

        $data = $this->db->get('survey_permissions');

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

        $data = $this->db->get('survey_permissions');

        return $data;
    }

    function deleteSomeRecords($condition)
    {
        $this->db->delete('survey_permissions', $condition);
    }

    function insertSomeRecords($data)
    {
        $this->db->insert('survey_permissions', $data);
    }

	function specificQuery($surveyid, $postusergroupid)
	{
		//Create sub-query
		$this->db->select('uid');
		$this->db->from('survey_permissions');
		$this->db->where(array('sid' => $surveyid));

		$subQuery = $this->db->get_compile_select();
		$this->db->_reset_select();

		// Main query
		$this->db->select('b.id');
		$thid->db->from('('.$subQuery.') AS c');
		$this->db->join('user_in_groups AS b', 'b.uid = c.uid', 'right');
		$this->db->where(array('c.uid' => NULL));
		$this->db->where(array('b.ugid' => $postusergroupid));
		return $this->db->get();
	}

	function joinQuery($what, $from, $where = array(), $join = array(), $order = NULL, $group = NULL)
	{
		$this->db->select($what);
		$this->db->from($from);
		$this->db->where($where);

		if (isset($join['table'], $join['on'], $join['type']))
			$this->db->join($join['table'], $join['on'], $join['type']);

		if ( ! empty($order)) $this->db->order_by($order);
		if ( ! empty($group)) $this->db->group_by($group);
		return $this->db->get();
	}


    /** giveAllSurveyPermissions
    * Gives all available survey permissions for a certain survey to a user
    *
    * @param mixed $iUserID  The User ID
    * @param mixed $iSurveyID The Survey ID
    */
    function giveAllSurveyPermissions($iUserID, $iSurveyID)
    {
        $aPermissions=$this->getBaseSurveyPermissions();

        $aPermissionsToSet=array();
        foreach ($aPermissions as $sPermissionName=>$aPermissionDetails)
        {
            foreach ($aPermissionDetails as $sPermissionDetailKey=>$sPermissionDetailValue)
            {
                if (in_array($sPermissionDetailKey,array('create','read','update','delete','import','export')) && $sPermissionDetailValue==true)
                {
                    $aPermissionsToSet[$sPermissionName][$sPermissionDetailKey]=1;
                }

            }
        }

        $this->setSurveyPermissions($iUserID, $iSurveyID, $aPermissionsToSet);
    }

    /** setSurveyPermissions
    * Set the survey permissions for a user. Beware that all survey permissions for the particual survey are removed before the new ones are written.
    *
    * @param int $iUserID The User ID
    * @param int $iSurveyID The Survey ID
    * @param array $aPermissions  Array with permissions in format <permissionname>=>array('create'=>0/1,'read'=>0/1,'update'=>0/1,'delete'=>0/1)
    */
    function setSurveyPermissions($iUserID, $iSurveyID, $aPermissions)
    {
        $iUserID=sanitize_int($iUserID);
        $condition = array('sid' => $iSurveyID, 'uid' => $iUserID);
        $this->deleteSomeRecords($condition);
        $bResult=true;

        foreach($aPermissions as $sPermissionname=>$aPermissions)
        {
            if (!isset($aPermissions['create'])) {$aPermissions['create']=0;}
            if (!isset($aPermissions['read'])) {$aPermissions['read']=0;}
            if (!isset($aPermissions['update'])) {$aPermissions['update']=0;}
            if (!isset($aPermissions['delete'])) {$aPermissions['delete']=0;}
            if (!isset($aPermissions['import'])) {$aPermissions['import']=0;}
            if (!isset($aPermissions['export'])) {$aPermissions['export']=0;}
            if ($aPermissions['create']==1 || $aPermissions['read']==1 ||$aPermissions['update']==1 || $aPermissions['delete']==1  || $aPermissions['import']==1  || $aPermissions['export']==1)
            {

                $data = array();
                $data = array(
                'sid' => $iSurveyID,
                'uid' => $iUserID,
                'permission' => $sPermissionname,
                'create_p' => $aPermissions['create'],
                'read_p' => $aPermissions['read'],
                'update_p' => $aPermissions['update'],
                'delete_p' => $aPermissions['delete'],
                'import_p' => $aPermissions['import'],
                'export_p' => $aPermissions['export']
                );
                $this->insertSomeRecords($data);
            }
        }
    }

}