<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
   * LimeSurvey
   * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
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
 * Class TokenDynamic
 *
 * @property integer $tid
 * @property string $participant_id
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $emailstatus
 * @property string $token
 * @property string $language
 * @property string $blacklisted
 * @property string $sent
 * @property string $remindersent
 * @property integer $remindercount
 * @property string $completed
 * @property integer $usesleft
 * @property string $validfrom
 * @property string $validuntil
 * @property integer $mpid //TODO Describe me!
 *
 * @property Survey $survey
 * @property SurveyDynamic[] $responses
 *
 * @property array $standardCols
 * @property array $standardColsForGrid
 */
class TokenDynamic extends LSActiveRecord
{
    /** @var int $sid */
    protected static $sid = 0;

    /** @var  string $emailstatus Default value for email status */
    public $emailstatus;

    /**
     * @inheritdoc
     * @return TokenDynamic
     */
    public static function model($sid = null)
    {
        $refresh = false;
        if (!is_null($sid)) {
            self::sid($sid);
            $refresh = true;
        }

        /** @var self $model */
        $model = parent::model(__CLASS__);

        //We need to refresh if we changed sid
        if ($refresh === true) {
            $model->refreshMetaData();
        }
        return $model;
    }

    /**
     * Sets the survey ID for the next model
     *
     * @static
     * @access public
     * @param int $sid
     * @return void
     */
    public static function sid($sid)
    {
        self::$sid = (int) $sid;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{tokens_'.self::$sid.'}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'tid';
    }

    /**
     * @inheritdoc
     * @see \Token::model()->rules
     **/
    public function rules()
    {
        $aRules = array(
            array('token', 'unique', 'allowEmpty' => true),
            array('firstname', 'LSYii_Validators'),
            array('lastname', 'LSYii_Validators'),
            array(implode(',', $this->tableSchema->columnNames), 'safe'),
            array('remindercount', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
            array('email', 'filter', 'filter'=>'trim'),
            array('email', 'LSYii_EmailIDNAValidator', 'allowEmpty'=>true, 'allowMultiple'=>true, 'except'=>'allowinvalidemail'),
            array('usesleft', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true, 'min'=>-2147483647, 'max'=>2147483647),
            array('mpid', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
            array('blacklisted', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('emailstatus', 'default', 'value' => 'OK'),
        );
        foreach (decodeTokenAttributes($this->survey->attributedescriptions) as $key => $info) {
            $aRules[] = array($key, 'LSYii_Validators', 'except'=>'FinalSubmit');
        }
        return $aRules;
    }

    /** @inheritdoc */
    public function relations()
    {
        SurveyDynamic::sid(self::$sid);
        return array(
            'survey'      => array(self::BELONGS_TO, 'Survey', array(), 'condition'=>'sid='.self::$sid, 'together' => true),
            'responses'   => array(self::HAS_MANY, 'SurveyDynamic', array('token' => 'token'))
        );
    }

    /**
     * Checks to make sure that all required columns exist in this tokens table
     * (some older tokens tables dont' get udated properly)
     *
     * This method should be moved to db update for 2.05 version so it runs only
     * once per survey participants table / backup survey participants table
     */
    public function checkColumns()
    {
        $sid = self::$sid;
        $sTableName = '{{tokens_'.$sid.'}}';
        $columncheck = array("tid", "participant_id", "firstname", "lastname", "email", "emailstatus", "token", "language", "blacklisted", "sent", "remindersent", "completed", "usesleft", "validfrom", "validuntil");
        $tableSchema = Yii::app()->db->schema->getTable($sTableName);
        $columns = $tableSchema->getColumnNames();
        $missingcolumns = array_diff($columncheck, $columns);
        //Some columns are missing - we need to create them
        if (count($missingcolumns) > 0) {
            Yii::app()->loadHelper('update/updatedb'); //Load the admin helper to allow column creation
            $columninfo = array(
                    'validfrom'=>'datetime',
                    'validuntil'=>'datetime',
                    'blacklisted'=> 'string(17)',
                    'participant_id'=> 'string(50)',
                    'remindercount'=>"integer DEFAULT '0'",
                    'usesleft'=>'integer NOT NULL default 1'
                ); //Not sure if any other fields would ever turn up here - please add if you can think of any others
            foreach ($missingcolumns as $columnname) {
                addColumn($sTableName, $columnname, $columninfo[$columnname]);
            }
            Yii::app()->db->schema->getTable($sTableName, true); // Refresh schema cache just in case the table existed in the past
        } else {
            // On some installs we have created not null for participant_id and blacklisted fix this
            $columns = array('blacklisted', 'participant_id');

            foreach ($columns as $columnname) {
                $definition = $tableSchema->getColumn($columnname);
                if ($definition->allowNull != true) {
                    Yii::app()->loadHelper('update/updatedb'); //Load the admin helper to allow column creation
                    Yii::app()->db->createCommand()->alterColumn($sTableName, $columnname, "string({$definition->size}})");
                    Yii::app()->db->schema->getTable($sTableName, true); // Refresh schema cache just in case the table existed in the past
                }
            }
        }
    }

    /**
     * @param int[]|bool $aTokenIds
     * @param int $iMaxEmails
     * @param bool $bEmail
     * @param string $SQLemailstatuscondition
     * @param string $SQLremindercountcondition
     * @param string $SQLreminderdelaycondition
     * @return CActiveRecord[]
     */
    public function findUninvited($aTokenIds = false, $iMaxEmails = 0, $bEmail = true, $SQLemailstatuscondition = '', $SQLremindercountcondition = '', $SQLreminderdelaycondition = '')
    {
        $command = new CDbCriteria;
        $command->condition = '';
        $command->addCondition("(completed ='N') or (completed='')");
        $command->addCondition("token <> ''");
        $command->addCondition("email <> ''");

        if ($bEmail) {
            $command->addCondition("(sent = 'N') or (sent = '')");
        } else {
            $command->addCondition("(sent <> 'N') AND (sent <> '')");
        }

        if ($SQLemailstatuscondition) {
            $command->addCondition($SQLemailstatuscondition);
        }

        if ($SQLremindercountcondition) {
            $command->addCondition($SQLremindercountcondition);
        }

        if ($SQLreminderdelaycondition) {
            $command->addCondition($SQLreminderdelaycondition);
        }

        if ($aTokenIds) {
            $command->addCondition("tid IN ('".implode("', '", $aTokenIds)."')");
        }

        if ($iMaxEmails) {
            $command->limit = $iMaxEmails;
        }

        $command->order = 'tid';

        $oResult = TokenDynamic::model()->findAll($command);
        return $oResult;
    }

    /**
     * @param int[]|bool $aTokenIds
     * @param int $iMaxEmails
     * @param bool $bEmail
     * @param string $SQLemailstatuscondition
     * @param string $SQLremindercountcondition
     * @param string $SQLreminderdelaycondition
     * @return array|CDbDataReader
     */
    public function findUninvitedIDs($aTokenIds = false, $iMaxEmails = 0, $bEmail = true, $SQLemailstatuscondition = '', $SQLremindercountcondition = '', $SQLreminderdelaycondition = '')
    {
        $command = new CDbCriteria;
        $command->condition = '';
        $command->addCondition("(completed ='N') or (completed='')");
        $command->addCondition("token <> ''");
        $command->addCondition("email <> ''");
        if ($bEmail) {
            $command->addCondition("(sent = 'N') or (sent = '')");
        } else {
            $command->addCondition("(sent <> 'N') AND (sent <> '')");
        }

        if ($SQLemailstatuscondition) {
            $command->addCondition($SQLemailstatuscondition);
        }

        if ($SQLremindercountcondition) {
            $command->addCondition($SQLremindercountcondition);
        }

        if ($SQLreminderdelaycondition) {
            $command->addCondition($SQLreminderdelaycondition);
        }

        if ($aTokenIds) {
            $command->addCondition("tid IN ('".implode("', '", $aTokenIds)."')");
        }

        if ($iMaxEmails) {
            $command->limit = $iMaxEmails;
        }

        $command->order = 'tid';

        $oResult = $this->getCommandBuilder()
            ->createFindCommand($this->getTableSchema(), $command)
            ->select('tid')
            ->queryColumn();
        return $oResult;
    }

    /**
     * @param $data
     * @return bool|int
     */
    public function insertParticipant($data)
    {
        $token = new self;
        foreach ($data as $k => $v) {
            $token->$k = $v;
        }
        try {
            $token->save();
            return $token->tid;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param integer $iSurveyID
     * @param array $data
     * @return integer
     */
    public function insertToken($iSurveyID, $data)
    {
        self::sid($iSurveyID);
        return Yii::app()->db->createCommand()->insert(self::tableName(), $data);
    }


    /**
     * @param integer $tid
     * @param string $newToken
     * @return integer
     */
    public function updateToken($tid, $newToken)
    {
        return Yii::app()->db->createCommand("UPDATE {$this->tableName()} SET token = :newtoken WHERE tid = :tid")
            ->bindParam(":newtoken", $newToken, PDO::PARAM_STR)
            ->bindParam(":tid", $tid, PDO::PARAM_INT)
            ->execute();
    }

    /**
     * Retrieve an array of records with an empty token, in the result is just the id (tid)
     *
     * @param int $iSurveyID
     * @return array
     */
    public function selectEmptyTokens($iSurveyID)
    {
        $survey = Survey::model()->findByPk($iSurveyID);
        return Yii::app()->db->createCommand("SELECT tid FROM ".$survey->tokensTableName." WHERE token IS NULL OR token=''")->queryAll();
    }

    /**
     * @param integer $sid Survey ID
     * @return mixed
     */
    public static function countAllAndCompleted($sid)
    {
        $select = array(
            'count(*) AS cntall',
            'sum(CASE '.Yii::app()->db->quoteColumnName('completed').'
                 WHEN '.Yii::app()->db->quoteValue('N').' THEN 0
                          ELSE 1
                 END) AS cntcompleted',
            );
        $result = Yii::app()->db->createCommand()->select($select)->from('{{tokens_'.$sid.'}}')->queryRow();
        return $result;
    }

    /**
     * Creates and inserts token for a specific token record and returns the token string created
     *
     * @param int $iTokenID
     * @return string  token string
     */
    public function createToken($iTokenID)
    {
        //get token length from survey settings
        $tlrow = Survey::model()->findByAttributes(array("sid"=>self::$sid));
        $iTokenLength = $tlrow->tokenlength;

        //get all existing tokens
        $criteria = $this->getDbCriteria();
        $criteria->select = 'token';
        $ntresult = $this->findAllAsArray($criteria);
        foreach ($ntresult as $tkrow) {
            $existingtokens[] = $tkrow['token'];
        }
        //create new_token
        $bIsValidToken = false;
        while ($bIsValidToken == false) {
            $newtoken = randomChars($iTokenLength);
            if (!in_array($newtoken, $existingtokens)) {
                $existingtokens[] = $newtoken;
                $bIsValidToken = true;
            }
        }
        //update specific token row
        $this->updateToken($iTokenID, $newtoken);
        return $newtoken;
    }

    /**
     * Creates tokens for all token records that have empty token fields and returns the number
     * of tokens created
     *
     * @param int $iSurveyID
     * @return integer[] ( int number of created tokens, int number to be created tokens)
     */
    public function createTokens($iSurveyID)
    {
        $tkresult = $this->selectEmptyTokens($iSurveyID);
        //Exit early if there are not empty tokens
        if (count($tkresult) === 0) {
            return array(0, 0);
        }

        //get token length from survey settings
        $tlrow = Survey::model()->findByAttributes(array("sid"=>$iSurveyID));
        $iTokenLength = $tlrow->tokenlength;

        //if tokenlength is not set or there are other problems use the default value (15)
        if (empty($iTokenLength)) {
            $iTokenLength = 15;
        }
        //Add some criteria to select only the token field
        $criteria = $this->getDbCriteria();
        $criteria->select = 'token';
        $ntresult = $this->findAllAsArray($criteria); //Use AsArray to skip active record creation

        // select all existing tokens
        foreach ($ntresult as $tkrow) {
            $existingtokens[$tkrow['token']] = true;
        }

        $newtokencount = 0;
        $invalidtokencount = 0;
        foreach ($tkresult as $tkrow) {
            $bIsValidToken = false;
            while ($bIsValidToken == false && $invalidtokencount < 50) {
                $newtoken = randomChars($iTokenLength);
                if (!isset($existingtokens[$newtoken])) {
                    $existingtokens[$newtoken] = true;
                    $bIsValidToken = true;
                    $invalidtokencount = 0;
                } else {
                    $invalidtokencount++;
                }
            }
            if ($bIsValidToken) {
                $this->updateToken($tkrow['tid'], $newtoken);
                $newtokencount++;
            } else {
                break;
            }
        }

        return array($newtokencount, count($tkresult));
    }

    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        if ($this->usesleft > 0) {
            $this->completed = 'N';
        }
        return parent::beforeSave();
    }

    /**
     * @param integer $tokenid
     * @return integer the number of rows deleted
     */
    public function deleteToken($tokenid)
    {
        return Token::model(self::$sid)->deleteByPk($tokenid);
    }


    /**
     * @param integer[] $iTokenIds
     * @return integer the number of rows deleted
     */
    public function deleteRecords($iTokenIds)
    {
        return Token::model(self::$sid)->deleteAllByAttributes(array('tid'=>$iTokenIds));
    }

    /**
     * @param string $token
     * @return mixed
     */
    public function getHasResponses($sToken)
    {
        $oSurvey = Survey::model()->findByPk(intval(self::$sid));
        if (!$oSurvey->hasResponsesTable) {
            return false;
        }        
        $command = Yii::app()->db->createCommand()
            ->select('COUNT(token)')
            ->from('{{survey_'.intval(self::$sid).'}}')
            ->where('token=:token')
            ->bindParam(':token', $sToken, PDO::PARAM_STR);

        return ((int)$command->queryScalar()>0);
    }

    
    /**
     * @param string $token
     * @return mixed
     */
    public function getEmailStatus($token)
    {
        $command = Yii::app()->db->createCommand()
            ->select('emailstatus')
            ->from('{{tokens_'.intval(self::$sid).'}}')
            ->where('token=:token')
            ->bindParam(':token', $token, PDO::PARAM_STR);

        return $command->queryRow();
    }

    /**
     * @param string $token
     * @param string $status
     * @return integer
     */
    public function updateEmailStatus($token, $status)
    {
        return Yii::app()->db->createCommand()->update('{{tokens_'.intval(self::$sid).'}}', array('emailstatus' => $status), 'token = :token', array(':token' => $token));
    }

    /**
     * @return string[]
     */
    public function getStandardCols()
    {
        return array(
            "tid",
            "participant_id",
            "firstname",
            "lastname",
            "email",
            "emailstatus",
            "token",
            "language",
            "blacklisted",
            "sent",
            "remindersent",
            "remindercount",
            "completed",
            "usesleft",
            "validfrom",
            "validuntil",
            "mpid",
        );
    }

    /**
     * @return array
     */
    public function getCustom_attributes()
    {
        $columns = $this->getMetaData()->columns;
        $attributes = array();

        foreach ($columns as $sColName => $oColumn) {
            if (!in_array($sColName, $this->standardCols)) {
                $attributes[$sColName] = $oColumn;
            }
        }

        return $attributes;
    }

    /**
     * @return string
     */
    public function getSentFormated()
    {
        $field = $this->sent;
        return $this->getYesNoDateFormated($field);
    }

    /**
     * @return string
     */
    public function getRemindersentFormated()
    {
        $field = $this->remindersent;
        return $this->getYesNoDateFormated($field);
    }

    /**
     * @return string
     */
    public function getCompletedFormated()
    {
        $field = $this->completed;
        return $this->getYesNoDateFormated($field);
    }

    /**
     * @return string
     */
    public function getValidfromFormated()
    {
        $field = $this->validfrom;
        return $this->getYesNoDateFormated($field);
    }

    /**
     * @return string
     */
    public function getValiduntilFormated()
    {
        $field = $this->validuntil;
        return $this->getYesNoDateFormated($field);
    }

    /**
     * @param string $field
     * @return string
     */
    private function getYesNoDateFormated($field)
    {
        if ($field != 'N' && $field != '') {
            if ($field == 'Q') {
                $field     = '<span class="text-warning">'.gT('Quota out').'</span>';
            } elseif ($field != 'Y') {
                $fieldDate = convertToGlobalSettingFormat($field);
                $field     = '<span class="text-success">'.$fieldDate.'</span>';
            } else {
                $field     = '<span class="text-success fa fa-check"></span>';
            }
        } elseif ($field != '') {
            $field = '<i class="fa fa-minus text-warning"></i>';
        }
        return $field;
    }

    /**
     * @return string
     */
    public function getEmailFormated()
    {
        if ($this->emailstatus == "bounced") {
            return '<span class="text-warning"><strong> '.$this->email.'</strong></span>';
        } else {
            return $this->email;
        }
    }

    /**
     * @return string
     */
    public function getEmailstatusFormated()
    {
        if ($this->emailstatus == "bounced") {
            return '<span class="text-warning"><strong> '.$this->emailstatus.'</strong></span>';
        } else {
            return $this->emailstatus;
        }
    }

    /**
     * @return array
     */
    public function getStandardColsForGrid()
    {
        return array(
            array(
                'id'=>'tid',
                'class'=>'CCheckBoxColumn',
                'selectableRows' => '100',
            ),
            array(
                'header' => gT('Action'),
                'class'=>'bootstrap.widgets.TbButtonColumn',
                'template'=>'{viewresponse}{spacerviewresponse}{previewsurvey}{previewsurveyspacer}{mail}{remind}{mailspacer}{edit}{deletetoken}{viewparticipant}{viewparticipantspacer}',
                'buttons'=> $this->getGridButtons(),
            ),

            array(
                'header' => gT('ID'),
                'name' => 'tid',
                'value'=>'$data->tid',
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs text-right'),
            ),


            array(
                'header' => gT('First name'),
                'name' => 'firstname',
                'value'=>'$data->firstname',
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs name'),
            ),

            array(
                'header' => gT('Last name'),
                'name' => 'lastname',
                'value'=>'$data->lastname',
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs name'),
            ),

            array(
                'header' => gT('Email address'),
                'name' => 'email',
                'type' => 'raw',
                'value'=>'$data->emailFormated',
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs name'),
            ),

            array(
                'header' => gT('Email status'),
                'name' => 'emailstatus',
                'value'=>'$data->emailstatusFormated',
                'type' => 'raw',
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs'),
            ),

            array(
                'header' => gT('Token'),
                'name' => 'token',
                'value'=>'$data->token',
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs'),
            ),

            array(
                'header' => gT('Language'),
                'name' => 'language',
                'value'=>'$data->language',
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs'),
            ),

            array(
                'header' => gT('Invitation sent?'),
                'name' => 'sent',
                'type'=>'raw',
                'value'=>'$data->sentFormated',
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs  text-center'),
            ),

            array(
                'header' => gT('Reminder sent?'),
                'name' => 'remindersent',
                'type'=>'raw',
                'value'=>'$data->remindersentFormated',
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs text-center'),
            ),

            array(
                'header' => gT('Reminder count'),
                'name' => 'remindercount',
                'value'=>'$data->remindercount',
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs text-right'),
            ),

            array(
                'header' => gT('Completed?'),
                'name' => 'completed',
                'type'=>'raw',
                'value'=>'$data->completedFormated',
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs text-center'),
            ),

            array(
                'header' => gT('Uses left'),
                'name' => 'usesleft',
                'value'=>'$data->usesleft',
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs text-right'),
            ),
            array(
                'header' => gT('Valid from'),
                'name' => 'validfrom',
                'type'=>'raw',
                'value'=>'$data->validfromFormated',
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs name'),
            ),
            array(
                'header' => gT('Valid until'),
                'type'=>'raw',
                'name' => 'validuntil',
                'value'=>'$data->validuntilFormated',
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs name'),
            ),
        );
    }

    /**
     * @return array
     */
    public function getAttributesForGrid()
    {
        $aCustomAttributesCols = array();
        //$aCustomAttributes = $this->custom_attributes;

        $oSurvey = Survey::model()->findByAttributes(array("sid"=>self::$sid));
        $aCustomAttributes = $oSurvey->tokenAttributes;

        // Custom attributes
        foreach ($aCustomAttributes as $sColName => $oColumn) {
            $desc = ($oColumn['description'] != '') ? $oColumn['description'] : $sColName;
            $aCustomAttributesCols[] = array(
                'header' => $desc, // $aAttributedescriptions->$sColName->description,
                'name' => $sColName,
                'value'=>'$data->'.$sColName,
                'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs'),
            );
        }

        return array_merge($this->standardColsForGrid, $aCustomAttributesCols);
    }

    /**
     * Return the buttons columns
     * @see https://www.yiiframework.com/doc/api/1.1/CButtonColumn
     * @see https://bugs.limesurvey.org/view.php?id=14219
     * @see https://bugs.limesurvey.org/view.php?id=14222: When deleting a single response : all page is reloaded (not only grid)
     * @return array
     */
    public function getGridButtons()
    {
        /* viewresponse button */
        $baseView = intval(Permission::model()->hasSurveyPermission(self::$sid, 'responses', 'read') && $this->survey->active == "Y" && $this->survey->anonymized != "Y");
        $gridButtons['viewresponse'] = array(
            'label'=>'<span class="sr-only">'.gT("View response details").'</span><span class="fa fa-list-alt" aria-hidden="true"></span>',
            'imageUrl'=>false,
            'url' => 'App()->createUrl("admin/responses/sa/viewbytoken",array("surveyid"=>'.self::$sid.',"token"=>$data->token));',
            'options' => array(
                'class'=>"btn btn-default btn-xs",
                'data-toggle'=>"tooltip",
                'title'=>gT("View response details")
            ),
            'visible'=> $baseView .' && $data->getHasResponses($data->token)',
        );
        $gridButtons['spacerviewresponse'] = array(
            'label'=>'<span class="fa fa-list-alt text-muted" aria-hidden="true"></span>',
            'imageUrl'=>false,
            'url' => '#',
            'options' => array(
                'class'=>"btn btn-default btn-xs invisible",
                'disabled' => 'disabled',
                'title'=>''
            ),
            'visible'=> $baseView .'&& !$data->getHasResponses($data->token)',
            'click' => 'function(event){ window.LS.gridButton.noGridAction(event,$(this)); }',
        );
        /* previewsurvey button */
        $baseView = intval(Permission::model()->hasSurveyPermission(self::$sid, 'responses', 'create'));
        $gridButtons['previewsurvey'] = array(
            'label'=>'<span class="sr-only">'.gT("Launch the survey with this token").'</span><span class="fa fa-cog" aria-hidden="true"></span>',
            'imageUrl'=>false,
            'url' => 'App()->createUrl("/survey/index",array("sid"=>'.self::$sid.',"token"=>$data->token,"newtest"=>"Y"));',
            'options' => array(
                'class'=>"btn btn-default btn-xs",
                'target'=>"_blank",
                'data-toggle'=>"tooltip",
                'title'=>gT("Launch the survey with this token")
            ),
            'visible'=> $baseView . ' && !empty($data->token) && ( $data->completed == "N" || empty($data->completed) || $data->survey->alloweditaftercompletion == "Y")'
        );
        $gridButtons['previewsurveyspacer'] = array(
            'label'=>'<span class="fa fa-cog  text-muted" aria-hidden="true"></span>',
            'imageUrl'=>false,
            'url' => '#',
            'options' => array(
                'class'=>"btn btn-default btn-xs invisible",
                'disabled' => 'disabled',
                'title'=> ''
            ),
            'visible'=> $baseView . ' && (empty($data->token) || !( $data->completed == "N" || empty($data->completed) || $data->survey->alloweditaftercompletion == "Y"))',
            'click' => 'function(event){ window.LS.gridButton.noGridAction(event,$(this)); }',
        );
        /* mail button */
        $baseView = Permission::model()->hasSurveyPermission(self::$sid, 'tokens', 'update');
        /* mailing mail button */
        $gridButtons['mail'] = array(
            'label'=>'<span class="sr-only">'.gT("Send email invitation").'</span><span class="icon-invite" aria-hidden="true"></span>',// fa-enveloppe-o
            'imageUrl'=>false,
            'url' => 'App()->createUrl("/admin/tokens/sa/email",array("surveyid"=>'.self::$sid.',"tokenids"=>$data->tid,));',
            'options' => array(
                'class'=>"btn btn-default btn-xs btn-email",
                'data-toggle'=>"tooltip",
                'title'=>gT("Send email invitation")
            ),
            'visible'=> $baseView . ' && !empty($data->token) && ($data->sent== "N" || empty($data->sent)) && $data->emailstatus == "OK" && $data->email && $data->completed == "N" && ($data->usesleft > 0 || $data->survey->alloweditaftercompletion == "Y")',
        );
        /* mailing remind button */
        $gridButtons['remind'] = array(
            'label'=>'<span class="sr-only">'.gT("Send email reminder").'</span><span class="icon-remind" aria-hidden="true"></span>',
            'imageUrl'=>false,
            'url' => 'App()->createUrl("/admin/tokens/sa/email/action/remind",array("surveyid"=>'.self::$sid.',"tokenids"=>$data->tid));',
            'options' => array(
                'class'=>"btn btn-default btn-xs btn-email",
                'data-toggle'=>"tooltip",
                'title'=>gT("Send email reminder")
            ),
            'visible'=> $baseView . ' && !empty($data->token) && !($data->sent== "N" || empty($data->sent)) && $data->emailstatus == "OK" && $data->email && $data->completed == "N" && ($data->usesleft > 0 || $data->survey->alloweditaftercompletion == "Y")',
        );
        $gridButtons['mailspacer'] = array(
            'label'=>'<span class="fa fa-envelope-o text-muted" aria-hidden="true"></span>',
            'imageUrl'=>false,
            'url' => '#',
            'options' => array(
                'class'=>"btn btn-default btn-xs invisible",
                'disabled' => 'disabled',
                'title'=> ''
            ),
            'visible'=> $baseView . ' && (empty($data->token) || ($data->emailstatus != "OK" || empty($data->email) || $data->completed != "N" || ($data->usesleft <= 0 && $data->survey->alloweditaftercompletion != "Y")))',
        );
        /* edit button button */
        $gridButtons['edit'] = array(
            'label'=>'<span class="sr-only">'.gT('Edit this survey participant').'</span><span class="fa fa-edit" aria-hidden="true"></span>',
            'imageUrl'=>false,
            'url' => 'App()->createUrl("/admin/tokens/sa/edit",array("iSurveyId"=>'.self::$sid.',"iTokenId"=>$data->tid,"ajax"=>"true"));',
            'options' => array(
                'class'=>"btn btn-default btn-xs btn-edit",
                'data-toggle'=>"tooltip",
                'title'=>gT('Edit this survey participant'),
                'data-sid' => self::$sid
            ),
            'visible' => ''.Permission::model()->hasSurveyPermission(self::$sid, 'tokens', 'update'),
            'click' => 'startEditToken'
        );
        /* delete button */
        $gridButtons['deletetoken'] = array(
            'label'=>'<span class="sr-only">'.gT('Delete survey participant').'</span><span class="text-warning fa fa-trash" aria-hidden="true"></span>',
            'imageUrl'=>false,
            'url' => 'App()->createUrl("/admin/tokens/sa/deleteToken",array("sid"=>'.self::$sid.',"sItem"=>$data->tid,"ajax"=>"true"));',
            'options' => array(
                'class'=>"btn btn-default btn-xs btn-delete",
                'data-toggle'=>"tooltip",
                'title'=>gT('Delete survey participant'),
            ),
            'visible' => ''.Permission::model()->hasSurveyPermission(self::$sid, 'tokens', 'delete'),
            'click' => 'function(event){ window.LS.gridButton.confirmGridAction(event,$(this)); }',
        );
        /* CPDB link */
        $baseVisible = intval(Permission::model()->hasGlobalPermission('participantpanel', 'read') && self::model(self::$sid)->count("participant_id is not null"));
        $gridButtons['viewparticipant'] = array(
            'label'=>'<span class="sr-only">'.gT('View this participant in the central participants database').'</span><span class="icon-cpdb" aria-hidden="true"></span>',
            'imageUrl'=>false,
            'url' => 'App()->createUrl("admin/participants/sa/displayParticipants",array("#" => json_encode(["searchcondition"=>"participant_id||equal||".$data->participant_id],JSON_FORCE_OBJECT)))',
            'options' => array(
                'class'=>"btn btn-default btn-xs btn-participant",
                'data-toggle'=>"tooltip",
                'title'=>gT('View this participant in the central participants database'),
            ),
            'click' => 'function(event){ window.LS.gridButton.postGridAction(event,$(this)); }',
            'visible' => $baseVisible.' && $data->participant_id',
        );
        $gridButtons['viewparticipantspacer'] = array(
            'label'=>'<span class="icon-cpdb text-muted" aria-hidden="true"></span>',
            'imageUrl'=>false,
            'url' => '#',
            'options' => array(
                'class'=>"btn btn-default btn-xs invisible",
                'data-toggle'=>"tooltip",
                'title'=>"",
            ),
            'visible' => $baseVisible.' && empty($data->participant_id)',
            'click' => 'function(event){ window.LS.gridButton.noGridAction(event,$(this)); }',
        );
        return $gridButtons;
    }

    /**
     * @deprecated
     * @return string
     */
    public function getbuttons()
    {
        return "";
    }

    /**
     * @return CActiveDataProvider
     */
    public function search()
    {
        $pageSizeTokenView = Yii::app()->user->getState('pageSizeTokenView', Yii::app()->params['defaultPageSize']);

        $sort = new CSort();
        $sort->defaultOrder = 'tid ASC';
        $sort->attributes = array(
            'tid'=>array(
            'asc'=>'tid',
            'desc'=>'tid desc',
            ),
            'partcipant'=>array(
            'asc'=>'partcipant',
            'desc'=>'partcipant desc',
            ),

            'firstname'=>array(
            'asc'=>'firstname',
            'desc'=>'firstname desc',
            ),

            'lastname'=>array(
            'asc'=>'lastname',
            'desc'=>'lastname desc',
            ),

            'email'=>array(
            'asc'=>'email',
            'desc'=>'email desc',
            ),

            'emailstatus'=>array(
            'asc'=>'emailstatus',
            'desc'=>'emailstatus desc',
            ),

            'token'=>array(
            'asc'=>'token',
            'desc'=>'token desc',
            ),

            'language'=>array(
            'asc'=>'language',
            'desc'=>'language desc',
            ),

            'blacklisted'=>array(
            'asc'=>'blacklisted',
            'desc'=>'blacklisted desc',
            ),

            'sent'=>array(
            'asc'=>'sent',
            'desc'=>'sent desc',
            ),

            'remindersent'=>array(
            'asc'=>'remindersent',
            'desc'=>'remindersent desc',
            ),

            'remindercount'=>array(
                'asc' => 'remindercount',
                'desc' => 'remindercount desc',
            ),

            'completed'=>array(
            'asc'=>'completed',
            'desc'=>'completed desc',
            ),

            'usesleft'=>array(
            'asc'=>'usesleft',
            'desc'=>'usesleft desc',
            ),

            'validfrom'=>array(
            'asc'=>'validfrom',
            'desc'=>'validfrom desc',
            ),

            'validuntil'=>array(
            'asc'=>'validuntil',
            'desc'=>'validuntil desc',
            ),
        );

        // Make sortable custom attributes
        foreach ($this->custom_attributes as $sColName => $oColumn) {
            $sort->attributes[$sColName] = array(
                'asc'=>$sColName,
                'desc'=>$sColName.' desc',
            );
        }

        $criteria = new LSDbCriteria;
        $criteria->compare('tid', $this->tid, false);
        $criteria->compare('token', $this->token, true);
        $criteria->compare('firstname', $this->firstname, true);
        $criteria->compare('lastname', $this->lastname, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('emailstatus', $this->emailstatus, true);
        $criteria->compare('token', $this->token, true);
        $criteria->compare('language', $this->language, true);
        $criteria->compare('sent', $this->sent, true);
        $criteria->compare('remindersent', $this->remindersent, true);
        $criteria->compare('remindercount', $this->remindercount, false);
        $criteria->compare('completed', $this->completed, true);
        $criteria->compare('usesleft', $this->usesleft, false);

        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        if ($this->validfrom) {
            $s = DateTime::createFromFormat($dateformatdetails['phpdate'].' H:i', $this->validfrom);
            $s2 = $s->format('Y-m-d H:i');
            $criteria->addCondition('validfrom <= \''.$s2.'\'');
        }

        if ($this->validuntil) {
            $s = DateTime::createFromFormat($dateformatdetails['phpdate'].' H:i', $this->validuntil);
            $s2 = $s->format('Y-m-d H:i');
            $criteria->addCondition('validuntil >= \''.$s2.'\'');
        }

        foreach ($this->custom_attributes as $sColName => $oColumn) {
            $criteria->compare($sColName, $this->$sColName, true);
        }

        $dataProvider = new CActiveDataProvider('TokenDynamic', array(
            'sort'=>$sort,
            'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=>$pageSizeTokenView,
            ),
        ));

        return $dataProvider;
    }

    /**
     * Get current surveyId for other model/function
     * @return int
     */
    public function getSurveyId()
    {
        return self::$sid;
    }
}
