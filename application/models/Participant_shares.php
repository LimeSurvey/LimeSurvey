<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Participant_shares extends CActiveRecord
{
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
		return '{{participant_shares}}';
	}
    
    public function storeParticipantShare($data)
    {
       
       $pdata =Yii::app()->db->createCommand()->where('participant_id="'.$data['participant_id'].'"')->from('{{participants}}')->select('*')->query();
       $ownerid = $pdata->read();
              //var_dump ($ownerid); die();
       if($ownerid['owner_uid'] != $data['share_uid'])// A check to ensure that the participant is not added to it's owner
       {
          Yii::app()->db->createCommand()->insert('{{participant_shares}}',$data);
       }
    }
    public function updateShare($data)
    {
       $query=Yii::app()->db->createCommand()->update('{{participant_shares}}', $data, 'share_uid="'.$data['share_uid'].'" and participant_id="'.$data['participant_id'].'"');
    }
    public function deleteRow($rows)
    {
        // Converting the comma seperated id's to an array to delete multiple rows
        $rowid=explode(",",$rows['id']);
        foreach($rowid as $row)
        {		
            Yii::app()->db->createCommand()->delete('{{participant_shares}}', 'participant_id="'.$row.'"'); 
        }	
    }
}
?>
