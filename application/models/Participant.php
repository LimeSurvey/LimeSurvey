<?php

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

/**
 * This is the model class for table "{{participants}}".
 *
 * The followings are the available columns in table '{{participants}}':
 * @property string $participant_id
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $language
 * @property string $blacklisted
 * @property integer $owner_uid
 */
class Participant extends LSActiveRecord
{

    /**
     * Returns the static model of Settings table
     *
     * @static
     * @access public
     * @param string $class
     * @return Participants
     */
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{participants}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('participant_id, blacklisted, owner_uid', 'required'),
            array('owner_uid', 'numerical', 'integerOnly' => true),
            array('participant_id', 'length', 'max' => 50),
            array('firstname, lastname, language', 'length', 'max' => 40),
            array('firstname, lastname, language', 'LSYii_Validators'),
            array('email', 'length', 'max' => 254),
            array('blacklisted', 'length', 'max' => 1),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('participant_id, firstname, lastname, email, language, blacklisted, owner_uid', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'participant_id' => 'Participant',
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'email' => 'Email',
            'language' => 'Language',
            'blacklisted' => 'Blacklisted',
            'owner_uid' => 'Owner Uid',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('participant_id', $this->participant_id, true);
        $criteria->compare('firstname', $this->firstname, true);
        $criteria->compare('lastname', $this->lastname, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('language', $this->language, true);
        $criteria->compare('blacklisted', $this->blacklisted, true);
        $criteria->compare('owner_uid', $this->owner_uid);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /*
     * funcion for generation of unique id
     */

    function gen_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /*
     * This function is responsible for adding the participant to the database
     * Parameters : participant data
     * Return Data : true on success, false on failure
     */
     function insertParticipant($aData)
     {
         $oParticipant = new self;
         foreach ($aData as $sField => $sValue){
             $oParticipant->$sField = $sValue;
         }
         try
         {
             $oParticipant->save();
             return true;
         }
         catch(Exception $e)
         {
             return false;
         }
     }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey() {
        return 'participant_id';
    }

    /**
     * This function updates the data edited in the jqgrid
     *
     * @param aray $data
     */
    function updateRow($data)
    {
        $record = $this->findByPk($data['participant_id']);
        foreach ($data as $key => $value)
        {
            $record->$key = $value;
        }
        $record->save();
    }

    /*
     * This function returns a list of participants who are either owned or shared
     * with a specific user
     *
     * @params int $userid The ID of the user that we are listing participants for
     *
     * @return object containing all the users
     */
    function getParticipantsOwner($userid)
    {
        $subquery = Yii::app()->db->createCommand()
            ->select('{{participants}}.participant_id,{{participant_shares}}.can_edit')
            ->from('{{participants}}')
            ->leftJoin('{{participant_shares}}', ' {{participants}}.participant_id={{participant_shares}}.participant_id')
            ->where('owner_uid = :userid1 OR share_uid = :userid2')
            ->group('{{participants}}.participant_id,{{participant_shares}}.can_edit');

        $command = Yii::app()->db->createCommand()
                ->select('p.*, ps.can_edit')
                ->from('{{participants}} p')
                ->join('(' . $subquery->getText() . ') ps', 'ps.participant_id = p.participant_id')
                ->bindParam(":userid1", $userid, PDO::PARAM_INT)
                ->bindParam(":userid2", $userid, PDO::PARAM_INT);

        return $command->queryAll();
    }

    function getParticipantsOwnerCount($userid)
    {
        $command = Yii::app()->db->createCommand()
                        ->select('count(*)')
                        ->from('{{participants}} p')
                        ->leftJoin('{{participant_shares}} ps', 'ps.participant_id = p.participant_id')
                        ->where('p.owner_uid = :userid1 OR ps.share_uid = :userid2')
                        ->bindParam(":userid1", $userid, PDO::PARAM_INT)
                        ->bindParam(":userid2", $userid, PDO::PARAM_INT);
        return $command->queryScalar();
    }

    /**
     * Get the number of participants, no restrictions
     *
     * @return int
     */
    function getParticipantsCountWithoutLimit()
    {
        return Participant::model()->count();
    }

    function getParticipantsWithoutLimit()
    {
        return Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
    }

    /**
     * This function combines the shared participant and the central participant
     * table and searches for any reference of owner id in the combined record
     * of the two tables
     *
     * @param  int $userid The id of the owner
     * @return int The number of participants owned by $userid who are shared
     */
    function getParticipantsSharedCount($userid)
    {
        $count = Yii::app()->db->createCommand()->select('count(*)')->from('{{participants}}')->join('{{participant_shares}}', '{{participant_shares}}.participant_id = {{participants}}.participant_id')->where('owner_uid = :userid')->bindParam(":userid", $userid, PDO::PARAM_INT)->queryScalar();
        return $count;
    }

    function getParticipants($page, $limit,$attid, $order = null, $search = null, $userid = null)
    {
        $data = $this->getParticipantsSelectCommand(false, $attid, $search, $userid, $page, $limit, $order);

        $allData = $data->queryAll();

        return $allData;
    }

    /**
     * Duplicated from getparticipants, only to have a count
     *
     * @param type $attid
     * @param type $order
     * @param CDbCriteria $search
     * @param type $userid
     * @return type
     */
    function getParticipantsCount($attid, $search = null, $userid = null) {
        $data = $this->getParticipantsSelectCommand(true, $attid, $search, $userid);

        return $data->queryScalar();
    }

    private function getParticipantsSelectCommand($count = false, $attid, $search = null, $userid = null, $page = null, $limit = null, $order = null)
    {
        $selectValue = array();
        $joinValue = array();

        $selectValue[] = "p.*";
        $selectValue[] = "luser.full_name as ownername";
        $selectValue[] = "luser.users_name as username";

        $aAllAttributes = ParticipantAttributeName::model()->getAllAttributes();
        foreach ($aAllAttributes as $aAttribute)
        {
            if(!is_null($search) && strpos($search->condition,'attribute'.$aAttribute['attribute_id'])!==false)
            {
               $attid[$aAttribute['attribute_id']]=$aAttribute;
            }
        }
        // Add survey count subquery
        $subQuery = Yii::app()->db->createCommand()
                ->select('count(*) survey')
                ->from('{{survey_links}} sl')
                ->where('sl.participant_id = p.participant_id');
        $selectValue[] = sprintf('(%s) survey',$subQuery->getText());
        array_push($joinValue,"left join {{users}} luser ON luser.uid=p.owner_uid");
        foreach($attid as $iAttributeID=>$aAttributeDetails)
        {
            if ($iAttributeID==0) continue;
            $sDatabaseType = Yii::app()->db->getDriverName();
            if ($sDatabaseType=='mssql' || $sDatabaseType=="sqlsrv" || $sDatabaseType == 'dblib')
            {
                $selectValue[]= "cast(attribute".$iAttributeID.".value as varchar(max)) as a".$iAttributeID;
            } else {
                $selectValue[]= "attribute".$iAttributeID.".value as a".$iAttributeID;
            }
            array_push($joinValue,"LEFT JOIN {{participant_attribute}} attribute".$iAttributeID." ON attribute".$iAttributeID.".participant_id=p.participant_id AND attribute".$iAttributeID.".attribute_id=".$iAttributeID);
        }

        $aConditions = array(); // this wil hold all conditions
        $aParams = array();
        if (!is_null($userid)) {
            // We are not superadmin so we need to limit to our own or shared with us
            $selectValue[] = '{{participant_shares}}.can_edit';
            $joinValue[]   = 'LEFT JOIN {{participant_shares}} ON p.participant_id={{participant_shares}}.participant_id';
            $aConditions[] = 'p.owner_uid = :userid1 OR {{participant_shares}}.share_uid = :userid2';
        }

        if ($count) {
            $selectValue = 'count(*) as cnt';
        }

        $data = Yii::app()->db->createCommand()
                ->select($selectValue)
                ->from('{{participants}} p');
        $data->setJoin($joinValue);

        if (!empty($search)) {
            /* @var $search CDbCriteria */
             $aSearch = $search->toArray();
             $aConditions[] = $aSearch['condition'];
             $aParams = $aSearch['params'];
        }
        if (Yii::app()->getConfig('hideblacklisted')=='Y')
        {
            $aConditions[]="blacklisted<>'Y'";
        }
        $condition = ''; // This will be the final condition
        foreach ($aConditions as $idx => $newCondition) {
            if ($idx>0) {
                $condition .= ' AND ';
            }
            $condition .= '(' . $newCondition . ')';
        }

        if (!empty($condition)) {
            $data->setWhere($condition);
        }

        if (!$count) {
            // Apply order and limits
            if (!empty($order)) {
                $data->setOrder($order);
            }

            if ($page <> 0) {
                $offset = ($page - 1) * $limit;
                $data->offset($offset)
                     ->limit($limit);
            }
        }

        $data->bindValues($aParams);

        if (!is_null($userid)) {
            $data->bindParam(":userid1", $userid, PDO::PARAM_INT)
                 ->bindParam(":userid2", $userid, PDO::PARAM_INT);
        }

        return $data;
    }

    function getSurveyCount($participant_id)
    {
        $count = Yii::app()->db->createCommand()->select('count(*)')->from('{{survey_links}}')->where('participant_id = :participant_id')->bindParam(":participant_id", $participant_id, PDO::PARAM_INT)->queryScalar();
        return $count;
    }

    /**
     * This function deletes the participant from the participants table,
     * references in the survey_links table (but not in matching tokens tables)
     * and then all the participants attributes.
     * @param $rows Participants ID separated by comma
     * @return void
     **/
    function deleteParticipants($rows, $bFilter=true)
    {
        // Converting the comma separated IDs to an array and assign chunks of 100 entries to have a reasonable query size
        $aParticipantsIDChunks = array_chunk(explode(",", $rows),100);
        foreach ($aParticipantsIDChunks as $aParticipantsIDs)
        {

            if ($bFilter)
            {
                $aParticipantsIDs=$this->filterParticipantIDs($aParticipantsIDs);
            }
            foreach($aParticipantsIDs as $aID){
                $oParticipant=Participant::model()->findByPk($aID);
                if ($oParticipant)
                {
                    $oParticipant->delete();
                }
            }

            Yii::app()->db->createCommand()->delete(Participant::model()->tableName(), array('in', 'participant_id', $aParticipantsIDs));

            // Delete survey links
            Yii::app()->db->createCommand()->delete(SurveyLink::model()->tableName(), array('in', 'participant_id', $aParticipantsIDs));
            // Delete participant attributes
            Yii::app()->db->createCommand()->delete(ParticipantAttribute::model()->tableName(), array('in', 'participant_id', $aParticipantsIDs));
        }
    }


    /**
    * Filter an array of participants IDs according to permissions of the person being logged in
    *
    * @param mixed $aParticipantsIDs
    */
    function filterParticipantIDs($aParticipantIDs)
    {
            if (!Permission::model()->hasGlobalPermission('superadmin','read')) // If not super admin filter the participant IDs first to owner only
            {
                $aCondition=array('and','owner_uid=:owner_uid',array('in', 'participant_id', $aParticipantIDs));
                $aParameter=array(':owner_uid'=>Yii::app()->session['loginID']);
                $aParticipantIDs=Yii::app()->db->createCommand()->select('participant_id')->from(Participant::model()->tableName())->where($aCondition, $aParameter)->queryColumn();
            }
            return $aParticipantIDs;
    }

    /**
    * Deletes CPDB participants identified by their participant ID from token tables
    *
    * @param mixed $sParticipantsIDs
    */
    function deleteParticipantToken($sParticipantsIDs)
    {
        /* This function deletes the participant from the participants table,
           the participant from any tokens table they're in (using the survey_links table to find them)
           and then all the participants attributes. */
        $aParticipantsIDChunks = array_chunk(explode(",", $sParticipantsIDs),100);
        foreach ($aParticipantsIDChunks as $aParticipantsIDs)
        {
            $aParticipantsIDs=$this->filterParticipantIDs($aParticipantsIDs);
            $aSurveyIDs = Yii::app()->db->createCommand()->selectDistinct('survey_id')->from('{{survey_links}}')->where(array('in', 'participant_id', $aParticipantsIDs))->queryColumn();
            foreach ($aSurveyIDs as $iSurveyID)
            {
                if (Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'delete'))
                {
                    $sTokenTable='{{tokens_'.intval($iSurveyID).'}}';
                    if (Yii::app()->db->schema->getTable($sTokenTable))
                    {
                        Yii::app()->db->createCommand()->delete($sTokenTable, array('in', 'participant_id', $aParticipantsIDs));
                    }
                }
            }
            $this->deleteParticipants($sParticipantsIDs, false);
        }
    }

    /**
    * This function deletes the participant from the participants table,
    * the participant from any tokens table they're in (using the survey_links table to find them),
    * all responses in surveys they've been linked to,
    * and then all the participants attributes.
    *
    * @param mixed $sParticipantsIDs
    */
    function deleteParticipantTokenAnswer($sParticipantsIDs)
    {
        $aParticipantsIDs = explode(",", $sParticipantsIDs);
        $aParticipantsIDs=$this->filterParticipantIDs($aParticipantsIDs);

        foreach ($aParticipantsIDs as $row)
        {
            $tokens = Yii::app()->db->createCommand()
                                    ->select('*')
                                    ->from('{{survey_links}}')
                                    ->where('participant_id = :row')
                                    ->bindParam(":row", $row, PDO::PARAM_INT)
                                    ->queryAll();

            foreach ($tokens as $key => $value)
            {
                $tokentable='{{tokens_'.intval($value['survey_id']).'}}';
                if (Yii::app()->db->schema->getTable($tokentable))
                {
                    $tokenid = Yii::app()->db->createCommand()
                                             ->select('token')
                                             ->from('{{tokens_' . intval($value['survey_id']) . '}}')
                                             ->where('participant_id = :pid')
                                             ->bindParam(":pid", $value['participant_id'], PDO::PARAM_INT)
                                             ->queryAll();
                    $token = $tokenid[0];
                    $surveytable='{{survey_'.intval($value['survey_id']).'}}';
                    if ($datas=Yii::app()->db->schema->getTable($surveytable))
                    {
                        if (!empty($token['token']) && isset($datas->columns['token']) && Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'delete')) //Make sure we have a token value, and that tokens are used to link to the survey
                        {
                            $gettoken = Yii::app()->db->createCommand()
                                                      ->select('*')
                                                      ->from('{{survey_' . intval($value['survey_id']) . '}}')
                                                      ->where('token = :token')
                                                      ->bindParam(":token", $token['token'], PDO::PARAM_STR)
                                                      ->queryAll();
                            $gettoken = $gettoken[0];
                            Yii::app()->db->createCommand()
                                          ->delete('{{survey_' . intval($value['survey_id']) . '}}', 'token = :token')
                                          ->bindParam(":token", $gettoken['token'], PDO::PARAM_STR); // Deletes matching responses from surveys
                        }
                    }
                    if (Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'delete'))
                    {

                        Yii::app()->db->createCommand()
                                      ->delete('{{tokens_' . intval($value['survey_id']) . '}}', 'participant_id = :pid' , array(':pid'=>$value['participant_id'])); // Deletes matching token table entries
                    }
                }
            }
            $this->deleteParticipants($sParticipantsIDs, false);
        }
    }

    /*
     * Function builds a select query for searches through participants using the $condition field passed
     * which is in the format "firstfield||sqloperator||value||booleanoperator||secondfield||sqloperator||value||booleanoperator||etc||etc||etc"
     * for example: "firstname||equal||Jason||and||lastname||equal||Cleeland" will produce SQL along the lines of "WHERE firstname = 'Jason' AND lastname=='Cleeland'"
     *
     * @param array $condition an array containing the search string exploded using || so that "firstname||equal||jason" is $condition(1=>'firstname', 2=>'equal', 3=>'jason')
     * @param int $page Which page number to display
     * @param in $limit The limit/number of reords to return
     *
     * @returns array $output
     *
     *
     * */
    function getParticipantsSearchMultiple($condition, $page, $limit)
    {
        //http://localhost/limesurvey_yii/admin/participants/getParticipantsResults_json/search/email||contains||gov||and||firstname||contains||AL
        //First contains fieldname, second contains method, third contains value, fourth contains BOOLEAN SQL and, or

        //As we iterate through the conditions we build up the $command query by adding conditions to it
        //
        $i = 0;
        $tobedonelater = array();
        $start = $limit * $page - $limit;
        $command = new CDbCriteria;
        $command->condition = '';

        //The following code performs an IN-SQL order, but this only works for standard participant fields
        //For the time being, lets stick to just sorting the collected results, some thinking
        //needs to be done about how we can sort the actual fullo query when combining with calculated
        //or attribute based fields. I've switched this off, but left the code for future reference. JC
        if(1==2)
        {
            $sord = Yii::app()->request->getPost('sord'); //Sort order
            $sidx = Yii::app()->request->getPost('sidx'); //Sort index
            if(is_numeric($sidx) || $sidx=="survey") {
                $sord=""; $sidx="";
            }
            if(!empty($sidx)) {
                $sortorder="$sidx $sord";
            } else {
                $sortorder="";
            }
            if(!empty($sortorder))
            {
                $command->order=$sortorder;
            }
        }

        $con = count($condition);
        while ($i < $con && $con > 2)
        {
            if ($i < 3) //Special set just for the first query/condition
            {
                if(is_numeric($condition[2])) $condition[2]=intval($condition[2]);
                switch($condition[1])
                {
                    case 'equal':
                        $operator="=";
                        break;
                    case 'contains':
                        $operator="LIKE";
                        $condition[2]="%".$condition[2]."%";
                        break;
                    case 'beginswith':
                        $operator="LIKE";
                        $condition[2]=$condition[2]."%";
                        break;
                    case 'notequal':
                        $operator="!=";
                        break;
                    case 'notcontains':
                        $operator="NOT LIKE";
                        $condition[2]="%".$condition[2]."%";
                        break;
                    case 'greaterthan':
                        $operator=">";
                        break;
                    case 'lessthan':
                        $operator="<";
                }
                if($condition[0]=="survey")
                {
                    $lang = Yii::app()->session['adminlang'];
                    $command->addCondition('participant_id IN (SELECT distinct {{survey_links}}.participant_id FROM {{survey_links}}, {{surveys_languagesettings}} WHERE {{survey_links}}.survey_id = {{surveys_languagesettings}}.surveyls_survey_id AND {{surveys_languagesettings}}.surveyls_language=:lang AND {{survey_links}}.survey_id '.$operator.' :param)');
                    $command->params=array(':lang'=>$lang,  ':param'=>$condition[2]);
                }
                elseif($condition[0]=="surveys") //Search by quantity of linked surveys
                {
                    $addon = ($operator == "<") ? " OR participant_id NOT IN (SELECT distinct participant_id FROM {{survey_links}})" : "";
                    $command->addCondition('participant_id IN (SELECT participant_id FROM {{survey_links}} GROUP BY participant_id HAVING count(*) '.$operator.' :param2 ORDER BY count(*))'.$addon);
                    $command->params=array(':param2'=>$condition[2]);
                }
                elseif($condition[0]=="owner_name")
                {
                    $userid = Yii::app()->db->createCommand()
                                        ->select('uid')
                                        ->where('full_name '.$operator.' :condition_2')
                                        ->from('{{users}}')
                                        ->bindParam("condition_2", $condition[2], PDO::PARAM_STR)
                                        ->queryAll();
                    $uid = $userid[0];
                    $command->addCondition('owner_uid = :uid');
                    $command->params=array(':uid'=>$uid['uid']);
                }
                elseif (is_numeric($condition[0])) //Searching for an attribute
                {
                    $command->addCondition('participant_id IN (SELECT distinct {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = :condition_0 AND {{participant_attribute}}.value '.$operator.' :condition_2)');
                    $command->params=array(':condition_0'=>$condition[0], ':condition_2'=>$condition[2]);
                }
                else
                {
                    $command->addCondition($condition[0] . ' '.$operator.' :condition_2');
                    $command->params=array(':condition_2'=>$condition[2]);
                }
                 $i+=3;
            }
            else if ($condition[$i] != '') //This section deals with subsequent filter conditions that have boolean joiner
            {
                if(is_numeric($condition[$i+3])) $condition[$i+3]=intval($condition[$i+3]); //Force the type of numeric values to be numeric
                $booloperator=strtoupper($condition[$i]);
                $condition1name=":condition_".($i+1);
                $condition2name=":condition_".($i+3);
                switch($condition[$i+2])
                    {
                        case 'equal':
                            $operator="=";
                            break;
                        case 'contains':
                            $operator="LIKE";
                            $condition[$i+3]="%".$condition[$i+3]."%";
                            break;
                        case 'beginswith':
                            $operator="LIKE";
                            $condition[$i+3]=$condition[$i+3]."%";
                            break;
                        case 'notequal':
                            $operator="!=";
                            break;
                        case 'notcontains':
                            $operator="NOT LIKE";
                            $condition[$i+3]="%".$condition[$i+3]."%";
                            break;
                        case 'greaterthan':
                            $operator=">";
                            break;
                        case 'lessthan':
                            $operator="<";
                    }
                if($condition[$i+1]=="survey")
                {
                    $lang = Yii::app()->session['adminlang'];
                    $command->addCondition('participant_id IN (SELECT distinct {{survey_links}}.participant_id FROM {{survey_links}}, {{surveys_languagesettings}} WHERE {{survey_links}}.survey_id = {{surveys_languagesettings}}.surveyls_survey_id AND {{surveys_languagesettings}}.surveyls_language=:lang AND ({{surveys_languagesettings}}.surveyls_title '.$operator.' '.$condition2name.'1 OR {{survey_links}}.survey_id '.$operator.' '.$condition2name.'2))', $booloperator);
                    $command->params=array_merge($command->params, array(':lang'=>$lang, $condition2name.'1'=>$condition[$i+3], $condition2name.'2'=>$condition[$i+3]));
                } elseif ($condition[$i+1]=="surveys") //search by quantity of linked surveys
                {
                    $addon = ($operator == "<") ? " OR participant_id NOT IN (SELECT distinct participant_id FROM {{survey_links}})" : "";
                    $command->addCondition('participant_id IN (SELECT participant_id FROM {{survey_links}} GROUP BY participant_id HAVING count(*) '.$operator.' '.$condition2name.' ORDER BY count(*))'.$addon);
                    $command->params=array_merge($command->params, array($condition2name=>$condition[$i+3]));
                }
                elseif($condition[$i+1]=="owner_name")
                {
                    $userid = Yii::app()->db->createCommand()
                                        ->select('uid')
                                        ->where('full_name '.$operator.' '.$condition2name)
                                        ->from('{{users}}')
                                        ->bindParam($condition2name, $condition[$i+3], PDO::PARAM_STR)
                                        ->queryAll();
                    $uid=array();
                    foreach($userid as $row) {$uid[]=$row['uid'];}
                    $command->addInCondition('owner_uid', $uid, $booloperator);
                }
                elseif (is_numeric($condition[$i+1])) //Searching for an attribute
                {
                    $command->addCondition('participant_id IN (SELECT distinct {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition1name.' AND {{participant_attribute}}.value '.$operator.' '.$condition2name.')', $booloperator);
                    $command->params=array_merge($command->params, array($condition1name=>$condition[$i+1], $condition2name=>$condition[$i+3]));
                }
                else
                {
                    $command->addCondition($condition[$i+1] . ' '.$operator.' '.$condition2name, $booloperator);
                    $command->params=array_merge($command->params, array($condition2name=>$condition[$i+3]));
                }
                $i = $i + 4;
            }
            else
            {
                $i = $i + 4;
            }
        }

        if ($page == 0 && $limit == 0)
        {
            $arr = Participant::model()->findAll($command);
            $data = array();
            foreach ($arr as $t)
            {
                $data[$t->participant_id] = $t->attributes;
            }
        }
        else
        {
            $command->limit = $limit;
            $command->offset = $start;
            $arr = Participant::model()->findAll($command);
            $data = array();
            foreach ($arr as $t)
            {
                $data[$t->participant_id] = $t->attributes;
            }
        }

        return $data;
    }

    /**
     * Function builds a select query for searches through participants using the $condition field passed
     * which is in the format "firstfield||sqloperator||value||booleanoperator||secondfield||sqloperator||value||booleanoperator||etc||etc||etc"
     * for example: "firstname||equal||Jason||and||lastname||equal||Cleeland" will produce SQL along the lines of "WHERE firstname = 'Jason' AND lastname=='Cleeland'"
     *
     * @param array $condition an array containing the search string exploded using || so that "firstname||equal||jason" is $condition(1=>'firstname', 2=>'equal', 3=>'jason')
     * @param int $page Which page number to display
     * @param in $limit The limit/number of reords to return
     *
     * @returns CDbCriteria $output
     */
    function getParticipantsSearchMultipleCondition($condition)
    {
        //http://localhost/limesurvey_yii/admin/participants/getParticipantsResults_json/search/email||contains||gov||and||firstname||contains||AL
        //First contains fieldname, second contains method, third contains value, fourth contains BOOLEAN SQL and, or

        //As we iterate through the conditions we build up the $command query by adding conditions to it
        //
        $i = 0;
        $command = new CDbCriteria;
        $command->condition = '';
        $aParams = array();

        $iNumberOfConditions = (count($condition)+1)/4;
        while ($i < $iNumberOfConditions)
        {
            $sFieldname=$condition[$i*4];
            $sOperator=$condition[($i*4)+1];
            $sValue=$condition[($i*4)+2];
            $param = ':condition_'.$i;
            switch ($sOperator)
            {
                case 'equal':
                    $operator = '=';
                    $aParams[$param] = $sValue;
                    break;
                case 'contains':
                    $operator = 'LIKE';
                    $aParams[$param] = '%'.$sValue.'%';
                    break;
                case 'beginswith':
                    $operator = 'LIKE';
                    $aParams[$param] = $sValue.'%';
                    break;
                case 'notequal':
                    $operator = '!=';
                    $aParams[$param] = $sValue;
                    break;
                case 'notcontains':
                    $operator = 'NOT LIKE';
                    $aParams[$param] = '%'.$sValue.'%';
                    break;
                case 'greaterthan':
                    $operator = '>';
                    $aParams[$param] = $sValue;
                    break;
                case 'lessthan':
                    $operator = '<';
                    $aParams[$param] = $sValue;
                    break;
            }
            if (isset($condition[(($i-1)*4)+3]))
            {
                $booloperator=  strtoupper($condition[(($i-1)*4)+3]);
            }
            else
            {
                $booloperator='AND';
            }

            if($sFieldname=="email")
            {
                $command->addCondition('p.email ' . $operator . ' '.$param, $booloperator);
            }
            elseif($sFieldname=="survey")
            {
                $subQuery = Yii::app()->db->createCommand()
                ->select('sl.participant_id')
                ->from('{{survey_links}} sl')
                ->join('{{surveys_languagesettings}} sls', 'sl.survey_id = sls.surveyls_survey_id')
                ->where('sls.surveyls_title '. $operator.' '.$param)
                ->group('sl.participant_id');
                $command->addCondition('p.participant_id IN ('.$subQuery->getText().')', $booloperator);
            }
            elseif($sFieldname=="surveyid")
            {
                $subQuery = Yii::app()->db->createCommand()
                ->select('sl.participant_id')
                ->from('{{survey_links}} sl')
                ->where('sl.survey_id '. $operator.' '.$param)
                ->group('sl.participant_id');
                $command->addCondition('p.participant_id IN ('.$subQuery->getText().')', $booloperator);
            }
            elseif($sFieldname=="surveys") //Search by quantity of linked surveys
            {
                $subQuery = Yii::app()->db->createCommand()
                ->select('sl.participant_id')
                ->from('{{survey_links}} sl')
                ->having('count(*) '. $operator.' '.$param)
                ->group('sl.participant_id');
                $command->addCondition('p.participant_id IN ('.$subQuery->getText().')', $booloperator);
            }
            elseif($sFieldname=="owner_name")
            {
                $command->addCondition('full_name ' . $operator . ' '.$param, $booloperator);
            }
            elseif($sFieldname=="participant_id")
            {
                $command->addCondition('p.participant_id ' . $operator . ' '.$param, $booloperator);
            }
            elseif (is_numeric($sFieldname)) //Searching for an attribute
            {
                $command->addCondition('attribute'. $sFieldname . '.value ' . $operator . ' '.$param, $booloperator);
//                $command->addCondition('(attribute_id='. $sFieldname . ' AND value ' . $operator . ' '.$param.' )', $booloperator);
            }
            else
            {
                $command->addCondition($sFieldname . ' '.$operator.' '.$param, $booloperator);
            }

            $i++;
        }

        if (count($aParams)>0)
        {
            $command->params = $aParams;
        }

        return $command;
    }

    /**
    * Returns true if participant_id has ownership or shared rights over this participant false if not
    *
    * @param mixed $participant_id
    * @returns bool true/false
    */
    function is_owner($participant_id)
    {
        $userid = Yii::app()->session['loginID'];
        $is_owner = Yii::app()->db->createCommand()->select('count(*)')->where('participant_id = :participant_id AND owner_uid = :userid')->from('{{participants}}')->bindParam(":participant_id", $participant_id, PDO::PARAM_STR)->bindParam(":userid", $userid, PDO::PARAM_INT)->queryScalar();
        $is_shared = Yii::app()->db->createCommand()->select('count(*)')->where('participant_id = :participant_id AND share_uid = :userid')->from('{{participant_shares}}')->bindParam(":participant_id", $participant_id, PDO::PARAM_STR)->bindParam(":userid", $userid, PDO::PARAM_INT)->queryScalar();
        if ($is_shared > 0 || $is_owner > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /*
     * This funciton is responsible for showing all the participant's shared by a particular user based on the user id
     */

    function getParticipantShared($userid)
    {
        return Yii::app()->db->createCommand()->select('{{participants}}.*, {{participant_shares}}.*')->from('{{participants}}')->join('{{participant_shares}}', '{{participant_shares}}.participant_id = {{participants}}.participant_id')->where('owner_uid = :userid')->bindParam(":userid", $userid, PDO::PARAM_INT)->queryAll();
    }

    /*
     * This funciton is responsible for showing all the participant's shared to the superadmin
     */

    function getParticipantSharedAll()
    {
        return Yii::app()->db->createCommand()->select('{{participants}}.*,{{participant_shares}}.*')->from('{{participants}}')->join('{{participant_shares}}', '{{participant_shares}}.participant_id = {{participants}}.participant_id')->queryAll();
    }

    /*
     * Copies central attributes/participants to an individual survey token table
     *
     * @param int $surveyid The survey id
     * @param array $mapped An array containing a list of already existing/mapped attributes in the form of "token_field_name"=>"participant_attribute_id"
     * @param array $newcreate An array containing new attributes to create in the tokens table
     * @param string $participantid A comma separated string containing the participant ids of the participants we are adding
     * @param bool $overwriteauto If true, overwrite automatically mapped data
     * @param bool $overwriteman If true, overwrite manually mapped data
     * @param bool $overwritest If true, overwrite standard fields (ie: names, email, participant_id, token)
     * @param bool $createautomap If true, rename the fieldnames of automapped attributes so that in future they are automatically mapped
     * */
    function copyCPBDAttributesToTokens($surveyid, $mapped, $newcreate, $participantid, $overwriteauto=false, $overwriteman=false, $overwritest=false, $createautomap=true)
    {
        Yii::app()->loadHelper('common');
        $duplicate = 0;
        $sucessfull = 0;
        $iBlacklistSkipped = 0;
        $participantid = explode(",", $participantid); //List of participant ids to add to tokens table
        if ($participantid[0] == "") { $participantid = array_slice($participantid, 1); }
        $number2add = sanitize_int(count($newcreate)); //Number of tokens being created
        $tokenattributefieldnames=array(); //Will contain descriptions of existing token attribute fields
        $tokenfieldnames=array(); //Will contain the actual field names of existing token attribute fields
        $attributesadded = array(); //Will contain the actual field name of any new token attribute fields
        $attributeidadded = array(); //Will contain the description of any new token attribute fields
        $fieldcontents = array(); //Will contain serialised info for the surveys.attributedescriptions field
        $surveyinfo=getSurveyInfo($surveyid);
        $defaultsurveylang=$surveyinfo['surveyls_language'];

        $arr = Yii::app()->db
                         ->createCommand()
                         ->select('*')
                         ->from("{{tokens_$surveyid}}")
                         ->queryRow();
        if (is_array($arr))
        {
            $tokenfieldnames = array_keys($arr);
            $tokenattributefieldnames = array_filter($tokenfieldnames, 'filterForAttributes');
        }

        // If automapping is enabled then update the token field properties with the mapped CPDB field ID

        if($createautomap=="true") {
            foreach($mapped as $key=>$iIDAttributeCPDB) {
                if(is_numeric($iIDAttributeCPDB)) {
                    /* Update the attributedescriptions info */
                    $aTokenAttributes=Survey::model()->findByPk($surveyid)->tokenattributes;
                    $aTokenAttributes[$key]['cpdbmap']=$iIDAttributeCPDB;
                    Yii::app()->db
                    ->createCommand()
                    ->update('{{surveys}}', array("attributedescriptions" => json_encode($aAttributes)), 'sid = '.$surveyid);                }
            }
        }
        foreach ($tokenattributefieldnames as $key => $value)
        {
            $mapped[$key]=$value;
        }
        if (!empty($newcreate)) //Create new fields in the tokens table
        {
            foreach ($newcreate as $key => $value)
            {
                $newfieldname='attribute_'.$value;
                $fields[$newfieldname] = array('type' => 'string');
                $attname = Yii::app()->db
                                     ->createCommand()
                                     ->select('{{participant_attribute_names_lang}}.attribute_name, {{participant_attribute_names_lang}}.lang')
                                     ->from('{{participant_attribute_names}}')
                                     ->join('{{participant_attribute_names_lang}}', '{{participant_attribute_names}}.attribute_id = {{participant_attribute_names_lang}}.attribute_id')
                                     ->where('{{participant_attribute_names}}.attribute_id = :attrid ')
                                     ->bindParam(":attrid", $value, PDO::PARAM_INT);
                $attributename = $attname->queryAll();
                foreach($attributename as $att) {
                    $languages[$att['lang']]=$att['attribute_name'];
                }
                //Check first for the default survey language
                if(isset($languages[$defaultsurveylang])) {
                    $newname=$languages[$defaultsurveylang];
                } elseif (isset($language[Yii::app()->session['adminlang']])) {
                    $newname=$languages[Yii::app()->session['adminlang']];
                } else {
                    $newname=$attributename[0]['attribute_name']; //Choose the first item in the list
                }
                $tokenattributefieldnames[] = $newfieldname;
                $fieldcontents[$newfieldname]=array("description"=>$newname,
                                                    "mandatory"=>"N",
                                                    "show_register"=>"N");
                array_push($attributeidadded, 'attribute_' . $value);
                array_push($attributesadded, $value);
            }
            //Update the attributedescriptions in the survey table to include the newly created attributes
            $previousatt = Yii::app()->db
                                     ->createCommand()
                                     ->select('attributedescriptions')
                                     ->where("sid = :sid")
                                     ->from('{{surveys}}')
                                     ->bindParam(":sid", $surveyid, PDO::PARAM_INT);
            $aTokenAttributes = $previousatt->queryRow();
            $aTokenAttributes = decodeTokenAttributes($aTokenAttributes['attributedescriptions'],true);
            foreach($fieldcontents as $key=>$iIDAttributeCPDB) {
                $aTokenAttributes[$key]=$iIDAttributeCPDB;
            }
            $aTokenAttributes = serialize($aTokenAttributes);
            Yii::app()->db
                      ->createCommand()
                      ->update('{{surveys}}',
                                array("attributedescriptions" => $aTokenAttributes), 'sid = '.intval($surveyid)); // load description in the surveys table

            //Actually create the fields in the tokens table
            Yii::app()->loadHelper('update/updatedb');
            foreach ($fields as $key => $value)
            {
                addColumn("{{tokens_$surveyid}}", $key, $value['type']);
            }
            Yii::app()->db->schema->getTable("{{tokens_$surveyid}}", true); // Refresh schema cache just

        }

        //Write each participant to the survey token table
        foreach ($participantid as $key => $sParticipantUID)
        {
            $writearray = array();
            $participantdata = Yii::app()->db->createCommand()->select('firstname,lastname,email,language,blacklisted')->where('participant_id = :pid')->from('{{participants}}')->bindParam(":pid", $sParticipantUID, PDO::PARAM_INT);
            $tobeinserted = $participantdata->queryRow();

            if (Yii::app()->getConfig('blockaddingtosurveys')=='Y' && $tobeinserted=='Y')
            {
                $iBlacklistSkipped++;
                continue;
            }

            /* Search for matching participant name/email in the survey token table */
            $sQuery = Yii::app()->db->createCommand()->select('*')->from('{{tokens_' . $surveyid . '}}')
            ->where('firstname = :firstname AND lastname = :lastname AND email = :email AND participant_id = :participant_id')
            ->bindParam(":firstname", $tobeinserted['firstname'], PDO::PARAM_STR)
            ->bindParam(":lastname", $tobeinserted['lastname'], PDO::PARAM_STR)
            ->bindParam(":email", $tobeinserted['email'], PDO::PARAM_STR)
            ->bindParam(":participant_id", $sParticipantUID, PDO::PARAM_STR)
            ->queryAll();
            if (count($sQuery) > 0)
            {
                //Participant already exists in token table - don't copy
                $duplicate++;
                // Here is where we can put code for overwriting the attribute data if so required
                if($overwriteauto=="true") {
                    //If there are new attributes created, add those values to the token entry for this participant
                    if (!empty($newcreate))
                    {
                        $numberofattributes = count($attributesadded);
                        for ($a = 0; $a < $numberofattributes; $a++)
                        {
                            Participant::model()->updateTokenAttributeValue($surveyid, $sParticipantUID,$attributesadded[$a],$attributeidadded[$a]);
                        }
                    }
                    //If there are automapped attributes, add those values to the token entry for this participant
                    if (!empty($mapped))
                    {
                        foreach ($mapped as $key => $value)
                        {
                            if ($key[10] == 'c') { //We know it's automapped because the 11th letter is 'c'
                                Participant::model()->updateTokenAttributeValue($surveyid, $sParticipantUID, $value, $key);
                            }
                        }
                    }
                }
                if($overwriteman=="true") {
                    //If there are any manually mapped attributes, add those values to the token entry for this participant
                    if (!empty($mapped))
                    {
                        foreach ($mapped as $key => $value)
                        {
                            if ($key[10] != 'c' && $key[9]=='_') { //It's not an auto field because it's 11th character isn't 'c'
                                Participant::model()->updateTokenAttributeValue($surveyid, $sParticipantUID, $value, $key);
                            }
                        }
                    }
                }
                if($overwritest=="true") {
                    if(!empty($mapped))
                    {
                        foreach($mapped as $key=>$value)
                        {
                            if((strlen($key) > 8 && $key[10] != 'c' && $key[9] !='_') || strlen($key) < 9) {
                                Participant::model()->updateTokenAttributeValue($surveyid, $sParticipantUID, $value, $key);
                            }
                        }
                    }
                }
            }
            else
            {
                //Create a new token entry for this participant
                $writearray = array('participant_id' => $sParticipantUID,
                                    'firstname' => $tobeinserted['firstname'],
                                    'lastname' => $tobeinserted['lastname'],
                                    'email' => $tobeinserted['email'],
                                    'emailstatus' => 'OK',
                                    'language' => $tobeinserted['language']);
                Yii::app()->db
                          ->createCommand()
                          ->insert('{{tokens_' . $surveyid . '}}', $writearray);
                $insertedtokenid = getLastInsertID('{{tokens_' . $surveyid . '}}');

                $time = time();

                //Create a survey link for the new token entry
                $data = array(
                    'participant_id' => $sParticipantUID,
                    'token_id' => $insertedtokenid,
                    'survey_id' => $surveyid,
                    'date_created' => date('Y-m-d H:i:s', $time));
                Yii::app()->db->createCommand()->insert('{{survey_links}}', $data);

                //If there are new attributes created, add those values to the token entry for this participant
                if (!empty($newcreate))
                {
                    $numberofattributes = count($attributesadded);
                    for ($a = 0; $a < $numberofattributes; $a++)
                    {
                        Participant::model()->updateTokenAttributeValue($surveyid, $sParticipantUID,$attributesadded[$a],$attributeidadded[$a]);
                    }
                }
                //If there are any automatically mapped attributes, add those values to the token entry for this participant
                if (!empty($mapped))
                {
                    foreach ($mapped as $key => $value)
                    {
                        Participant::model()->updateTokenAttributeValue($surveyid, $sParticipantUID, $value, $key);
                    }
                }
                $sucessfull++;
            }
        }
        $returndata = array('success' => $sucessfull, 'duplicate' => $duplicate, 'blacklistskipped'=>$iBlacklistSkipped, 'overwriteauto'=>$overwriteauto, 'overwriteman'=>$overwriteman);
        return $returndata;
    }

    /**
     * Updates a field in the token table with a value from the participant attributes table
     *
     * @param int $surveyId Survey ID number
     * @param int $participantId unique key for the participant
     * @param int $participantAttributeId the unique key for the participant_attribute table
     * @param int $tokenFieldName fieldname in the token table
     *
     * @return bool true/false
     */
    function updateTokenAttributeValue($surveyId, $participantId, $participantAttributeId, $tokenFieldname) {
        //Get the value from the participant_attribute field
        $val = Yii::app()->db
                         ->createCommand()
                         ->select('value')
                         ->where('participant_id = :participant_id AND attribute_id = :attrid')
                         ->from('{{participant_attribute}}')
                         ->bindParam("participant_id", $participantId, PDO::PARAM_STR)
                         ->bindParam("attrid", $participantAttributeId, PDO::PARAM_INT);
        $value = $val->queryRow();
        //Update the token entry with those values
        if (isset($value['value']))
        {
            $data = array($tokenFieldname => $value['value']);
            Yii::app()->db
                      ->createCommand()
                      ->update("{{tokens_$surveyId}}", $data, "participant_id = '$participantId'");
        }
        return true;
    }

    /**
     * Updates or creates a field in the token table with a value from the participant attributes table
     *
     * @param int $surveyId Survey ID number
     * @param int $participantId unique key for the participant
     * @param int $participantAttributeId the unique key for the participant_attribute table
     * @param int $tokenFieldName fieldname in the token table
     *
     * @return bool true/false
     */
     function updateAttributeValueToken($surveyId, $participantId, $participantAttributeId, $tokenFieldname) {
        $val = Yii::app()->db
                         ->createCommand()
                         ->select($tokenFieldname)
                         ->where('participant_id = :participant_id')
                         ->from('{{tokens_' . intval($surveyId) . '}}')
                         ->bindParam("participant_id", $participantId, PDO::PARAM_STR);
        $value2 = $val->queryRow();

        if (!empty($value2[$tokenFieldname]))
        {
            $data = array('participant_id' => $participantId,
                          'value' => $value2[$tokenFieldname],
                          'attribute_id' => $participantAttributeId
                          );
            //Check if value already exists
            $test=Yii::app()->db
                            ->createCommand()
                            ->select('count(*) as count')
                            ->from('{{participant_attribute}}')
                            ->where('participant_id = :participant_id AND attribute_id= :attribute_id')
                            ->bindParam(":participant_id", $participantId, PDO::PARAM_STR)
                            ->bindParam(":attribute_id", $participantAttributeId, PDO::PARAM_INT)
                            ->queryRow();
            if($test['count'] > 0) {
                $sql=Yii::app()->db
                               ->createCommand()
                               ->update('{{participant_attribute}}', array("value"=>$value2[$tokenFieldname]), "participant_id='$participantId' AND attribute_id=$participantAttributeId");
            } else {
                $sql=Yii::app()->db
                               ->createCommand()
                               ->insert('{{participant_attribute}}', $data);
            }
        }
    }

    /*
     * Copies token participants to the central participants table, and also copies
     * token attribute values where applicable. It checks for matching entries using
     * firstname/lastname/email combination.
     *
     * TODO: Most of this belongs in the participantsaction.php controller file, not
     *       here in the model file. Portions of this should be moved out at some stage.
     *
     * @param int $surveyid The id of the survey, used to find the appropriate tokens table
     * @param array $aAttributesToBeCreated An array containing the names of token attributes that have to be created in the cpdb
     * @param array $aMapped An array containing the names of token attributes that are to be mapped to an existing cpdb attribute
     * @param bool $overwriteauto If true, overwrites existing automatically mapped attribute values
     * @param bool $overwriteman If true, overwrites manually mapped attribute values (where token fieldname=attribute_n)
     * @param bool $createautomap If true, updates tokendescription field with new mapping
     * @param array $tokenid is assumed, saved by an earlier script as a session string called "participantid". It holds a list of token_ids
     *                       for the token participants we are copying to the central db
     *
     * @return array An array contaning list of successful and list of failed ids
     */

    function copyToCentral($surveyid, $aAttributesToBeCreated, $aMapped, $overwriteauto=false, $overwriteman=false, $createautomap=true)
    {
        $tokenid = Yii::app()->session['participantid']; //List of token_id's to add to participants table
        $duplicate = 0;
        $sucessfull = 0;
        $writearray = array();
        $attid = array(); //Will store the CPDB attribute_id of new or existing attributes keyed by CPDB at
        $pid = "";

        /* Grab all the existing attribute field names from the tokens table */
        $arr = Yii::app()->db->createCommand()->select('*')->from("{{tokens_$surveyid}}")->queryRow();
        if (is_array($arr))
        {
            $tokenfieldnames = array_keys($arr);
            $tokenattributefieldnames = array_filter($tokenfieldnames, 'filterForAttributes');
        }
        else
        {
            $tokenattributefieldnames = array();
        }
        /* Create new CPDB attributes */
        if (!empty($aAttributesToBeCreated))
        {
            foreach ($aAttributesToBeCreated as $key => $value) //creating new central attribute
            {
                /* $key is the fieldname from the token table (ie "attribute_1")
                 * $value is the 'friendly name' for the attribute (ie "Gender")
                 */
                $insertnames = array('attribute_type' => 'TB', 'visible' => 'Y');
                Yii::app()->db
                          ->createCommand()
                          ->insert('{{participant_attribute_names}}', $insertnames);
                $attid[$key] = $aAttributesToBeCreated[$key]=getLastInsertID('{{participant_attribute_names}}'); /* eg $attid['attribute_1']='8372' */
                $insertnameslang = array(
                                         'attribute_id' => $attid[$key],
                                         'attribute_name' => urldecode($value),
                                         'lang' => Yii::app()->session['adminlang']
                                         );
                Yii::app()->db
                          ->createCommand()
                          ->insert('{{participant_attribute_names_lang}}', $insertnameslang);
            }
        }

        /* Add the participants to the CPDB = Iterate through each $tokenid and create the new CPDB id*/
        foreach ($tokenid as $key => $tid)
        {
            if (is_numeric($tid) && $tid != "")
            {
                /* Get the data for this participant from the tokens table */
                $tobeinserted = Yii::app()->db
                                          ->createCommand()
                                          ->select('participant_id,firstname,lastname,email,language')
                                          ->where('tid = :tid')
                                          ->from('{{tokens_' . intval($surveyid) . '}}')
                                          ->bindParam(":tid", $tid, PDO::PARAM_INT)
                                          ->queryRow();
                /* See if there are any existing CPDB entries that match on firstname,lastname and email */
                $query = Yii::app()->db
                                   ->createCommand()
                                   ->select('*')
                                   ->from('{{participants}}')
                                   ->where('firstname = :firstname AND lastname = :lastname AND email = :email')
                                   ->bindParam(":firstname", $tobeinserted['firstname'], PDO::PARAM_STR)
                                   ->bindParam(":lastname", $tobeinserted['lastname'], PDO::PARAM_STR)
                                   ->bindParam(":email", $tobeinserted['email'], PDO::PARAM_STR)
                                   ->queryAll();
                /* If there is already an existing entry, add to the duplicate count */
                if (count($query) > 0)
                {
                    $duplicate++;
                    if($overwriteman == "true" && !empty($aMapped))
                    {
                        foreach ($aMapped as $cpdbatt => $tatt)
                        {
                            Participant::model()->updateAttributeValueToken($surveyid, $query[0]['participant_id'], $cpdbatt, $tatt);
                        }
                    }
                }
                /* If there isn't an existing entry, create one! */
                else
                {
                    /* Create entry in participants table */
                    $black = !empty($tobeinserted['blacklisted']) ? $tobeinserted['blacklised'] : 'N';
                    $pid=!empty($tobeinserted['participant_id']) ? $tobeinserted['participant_id'] : $this->gen_uuid();
                    $writearray = array('participant_id' => $pid,
                                        'firstname' => $tobeinserted['firstname'],
                                        'lastname' => $tobeinserted['lastname'],
                                        'email' => $tobeinserted['email'],
                                        'language' => $tobeinserted['language'],
                                        'blacklisted' => $black,
                                        'owner_uid' => Yii::app()->session['loginID']);
                    Yii::app()->db
                              ->createCommand()
                              ->insert('{{participants}}', $writearray);
                    //Update token table and insert the new UUID
                    $data=array("participant_id"=>$pid);
                    Yii::app()->db
                              ->createCommand()
                              ->update('{{tokens_'.intval($surveyid).'}}', $data, "tid = $tid");

                    /* Now add any new attribute values */
                    if (!empty($aAttributesToBeCreated))
                    {
                        foreach ($aAttributesToBeCreated as $key => $value)
                        {
                            Participant::model()->updateAttributeValueToken($surveyid, $pid, $attid[$key], $key);
                        }
                    }
                    /* Now add mapped attribute values */
                    if (!empty($aMapped))
                    {
                        foreach ($aMapped as $cpdbatt => $tatt)
                        {
                            Participant::model()->updateAttributeValueToken($surveyid,$pid,$cpdbatt,$tatt);
                        }
                    }
                    $sucessfull++;

                    /* Create a survey_link */
                    $data = array (
                            'participant_id' => $pid,
                            'token_id' => $tid,
                            'survey_id' => $surveyid,
                            'date_created' => date('Y-m-d H:i:s', time())
                        );
                    Yii::app()->db
                              ->createCommand()
                              ->insert('{{survey_links}}', $data);
                }
            }
        }

        if ($createautomap=="true")
        {
            $aAttributes=Survey::model()->findByPk($surveyid)->tokenattributes;
            if (!empty($aAttributesToBeCreated))
            {
                // If automapping is enabled then update the token field properties with the mapped CPDB field ID
                foreach ($aAttributesToBeCreated as $tatt => $cpdbatt)
                {
                    $aAttributes[$tatt]['cpdbmap']=$cpdbatt;
                }
                Yii::app()->db
                ->createCommand()
                ->update('{{surveys}}', array("attributedescriptions" => json_encode($aAttributes)), 'sid = '.$surveyid);
            }
            if (!empty($aMapped))
            {
                foreach ($aMapped as $cpdbatt => $tatt)
                {
                    // Update the attributedescriptions so future mapping can be done automatically
                    $aAttributes[$tatt]['cpdbmap']=$cpdbatt;
                }
                Yii::app()->db
                ->createCommand()
                ->update('{{surveys}}', array("attributedescriptions" => json_encode($aAttributes)), 'sid = '.$surveyid);
            }
        }
        $returndata = array('success' => $sucessfull, 'duplicate' => $duplicate, 'overwriteauto'=>$overwriteauto, 'overwriteman'=>$overwriteman);
        return $returndata;
    }

    /*
     * The purpose of this function is to check for duplicate in participants
     */

    function checkforDuplicate($fields, $output="bool")
    {
        $query = Yii::app()->db->createCommand()->select('participant_id')->where($fields)->from('{{participants}}')->queryAll();
        if (count($query) > 0)
        {
            if($output=="bool") {return true;}
            return $query[0][$output];
        }
        else
        {
            return false;
        }
    }

    function insertParticipantCSV($data)
    {
        $insertData = array(
            'participant_id' => $data['participant_id'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'language' => $data['language'],
            'blacklisted' => $data['blacklisted'],
            'created_by' => $data['owner_uid'],
            'owner_uid' => $data['owner_uid']);
        Yii::app()->db->createCommand()->insert('{{participants}}', $insertData);
    }
}
