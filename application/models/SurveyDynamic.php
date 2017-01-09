<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
  * 	Files Purpose: lots of common functions
 */
class SurveyDynamic extends LSActiveRecord
{
    public  $completed_filter;
    public $firstname_filter;
    public $lastname_filter;
    public $email_filter;
    public $lastpage;

    protected static $sid = 0;
    protected $bHaveToken;

    /**
     * Returns the static model of Settings table
     *
     * @static
     * @access public
     * @return SurveyDynamic
     */
    public static function model($sid = NULL)
    {
        $refresh = false;
        if (!is_null($sid))
        {
            self::sid($sid);
            $refresh = true;
        }

        $model = parent::model(__CLASS__);

        //We need to refresh if we changed sid
        if ($refresh === true) $model->refreshMetaData();

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

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{survey_' . self::$sid . '}}';
    }

    /**
    * Returns this model's relations
    *
    * @access public
    * @return array
    */
    public function relations()
    {
        if($this->getbHaveToken())
        {
            TokenDynamic::sid(self::$sid);
            return array(
                'survey'   => array(self::HAS_ONE, 'Survey', array(), 'condition'=>('sid = '.self::$sid)),
                'tokens'   => array(self::HAS_ONE, 'TokenDynamic', array('token' => 'token'))
            );
        }
        else
        {
            return array();
        }
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * Insert records from $data array
     *
     * @access public
     * @param array $data
     * @return boolean
     */
    public function insertRecords($data)
    {
        $record = new self;
        foreach ($data as $k => $v)
        {
            $search = array('`', "'");
            $k = str_replace($search, '', $k);
            $v = str_replace($search, '', $v);
            $record->$k = $v;
        }

        try
        {
            $record->save();
            return $record->id;
        }
        catch(Exception $e)
        {
            return false;
        }

    }

    /**
     * Deletes some records from survey's table
     * according to specific condition
     *
     * @static
     * @access public
     * @param array $condition
     * @return int
     */
    public static function deleteSomeRecords($condition = FALSE)
    {
        $survey = new SurveyDynamic;
        $criteria = new CDbCriteria;

        if ($condition != FALSE)
        {
            foreach ($condition as $column => $value)
            {
                return $criteria->addCondition($column . "=`" . $value . "`");
            }
        }

        return $survey->deleteAll($criteria);
    }

    /**
     * Return criteria updated with the ones needed for including results from the timings table
     *
     *
     * @return CDbCriteria
     */
    public function addTimingCriteria($condition)
    {
        $newCriteria = new CDbCriteria();
        $criteria = $this->getCommandBuilder()->createCriteria($condition);

        if ($criteria->select == '*')
        {
            $criteria->select = 't.*';
        }
        $alias = $this->getTableAlias();

        $newCriteria->join = "LEFT JOIN {{survey_" . self::$sid . "_timings}} survey_timings ON $alias.id = survey_timings.id";
        $newCriteria->select = 'survey_timings.*';  // Otherwise we don't get records from the token table
        $newCriteria->mergeWith($criteria);

        return $newCriteria;
    }

    /**
     * Return criteria updated with the ones needed for including results from the token table
     *
     *
     * @return CDbCriteria
     */
    public function addTokenCriteria($condition)
    {
        $newCriteria = new CDbCriteria();
        $criteria = $this->getCommandBuilder()->createCriteria($condition);
        $aSelectFields=Yii::app()->db->schema->getTable('{{survey_' . self::$sid  . '}}')->getColumnNames();
        $aSelectFields=array_diff($aSelectFields, array('token'));
        $aSelect=array();
        $alias = $this->getTableAlias();
        foreach($aSelectFields as $sField)
            $aSelect[]="$alias.".Yii::app()->db->schema->quoteColumnName($sField);
        $aSelectFields=$aSelect;
        $aSelectFields[]="$alias.token";

        if ($criteria->select == '*')
        {
            $criteria->select = $aSelectFields;
        }

        $newCriteria->join = "LEFT JOIN {{tokens_" . self::$sid . "}} tokens ON $alias.token = tokens.token";

        $aTokenFields=Yii::app()->db->schema->getTable('{{tokens_' . self::$sid . '}}')->getColumnNames();
        $aTokenFields=array_diff($aTokenFields, array('token'));

        $newCriteria->select = $aTokenFields;  // Otherwise we don't get records from the token table
        $newCriteria->mergeWith($criteria);

        return $newCriteria;
    }

    public static function countAllAndPartial($sid)
    {
        $select = array(
            'count(*) AS cntall',
            'sum(CASE
                 WHEN '. Yii::app()->db->quoteColumnName('submitdate') . ' IS NULL THEN 1
                          ELSE 0
                 END) AS cntpartial',
            );
        $result = Yii::app()->db->createCommand()->select($select)->from('{{survey_' . $sid . '}}')->queryRow();
        return $result;
    }

    /**
     * Return true if actual survey is completed
     *
     * @param $srid : actual save survey id
     *
     * @return boolean
     */
    public function isCompleted($srid)
    {
        static $resultCache = array();

        $sid = self::$sid;
        if (array_key_exists($sid, $resultCache) && array_key_exists($srid, $resultCache[$sid])) {
            return $resultCache[$sid][$srid];
        }
        $completed=false;

        if(Yii::app()->db->schema->getTable($this->tableName())){
            $data=Yii::app()->db->createCommand()
                ->select("submitdate")
                ->from($this->tableName())
                ->where('id=:id', array(':id'=>$srid))
                ->queryRow();
            if($data && $data['submitdate'])
            {
                $completed=true;
            }
        }
        $resultCache[$sid][$srid] = $completed;
        return $completed;
    }

    /**
     * For grid list
     */
    public function getCompleted()
    {
        return ($this->submitdate != '')?'<span class="text-success fa fa-check"></span>':'<span class="text-warning fa fa-times"></span>';
    }

    public function getButtons()
    {
        $sViewUrl     = App()->createUrl("/admin/responses/sa/view/surveyid/".self::$sid."/id/".$this->id);
        $sEditUrl     = App()->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/".self::$sid."/id/".$this->id);
        $sDownloadUrl = App()->createUrl("admin/responses",array("sa"=>"actionDownloadfiles","surveyid"=>self::$sid,"sResponseId"=>$this->id));
        $sDeleteUrl   = App()->createUrl("admin/responses",array("sa"=>"actionDelete","surveyid"=>self::$sid));
        //$sDeleteUrl   = "#";
        $button       = "";

        // View detail icon
        $button .= '<a class="btn btn-default btn-xs" href="'.$sViewUrl.'" target="_blank" role="button" data-toggle="tooltip" title="'.gT("View response details").'"><span class="glyphicon glyphicon-list-alt" ></span></a>';

        // Edit icon
        if (Permission::model()->hasSurveyPermission(self::$sid,'responses','update'))
        {
            $button .= '<a class="btn btn-default btn-xs" href="'.$sEditUrl.'" target="_blank" role="button" data-toggle="tooltip" title="'.gT("Edit this response").'"><span class="glyphicon glyphicon-pencil text-success" ></span></a>';
        }

        // Download icon
        if (hasFileUploadQuestion(self::$sid))
        {
            if (Response::model(self::$sid)->findByPk($this->id)->getFiles())
            {
                $button .= '<a class="btn btn-default btn-xs" href="'.$sDownloadUrl.'" target="_blank" role="button" data-toggle="tooltip" title="'.gT("Download all files in this response as a zip file").'"><span class="glyphicon glyphicon-download-alt downloadfile text-success" ></span></a>';
            }
        }

        // Delete icon
        if (Permission::model()->hasSurveyPermission(self::$sid,'responses','delete'))
        {
            $aPostDatas = json_encode(array('sResponseId'=>$this->id));
            //$button .= '<a class="deleteresponse btn btn-default btn-xs" href="'.$sDeleteUrl.'" role="button" data-toggle="modal" data-ajax="true" data-post="'.$aPostDatas.'" data-target="#confirmation-modal" data-tooltip="true" title="'. sprintf(gT('Delete response %s'),$this->id).'"><span class="glyphicon glyphicon-trash text-danger" ></span></a>';
            $button .= "<a class='deleteresponse btn btn-default btn-xs' data-ajax-url='".$sDeleteUrl."' data-gridid='responses-grid' role='button' data-toggle='modal' data-post='".$aPostDatas."' data-target='#confirmation-modal' data-tooltip='true' title='". sprintf(gT('Delete response %s'),$this->id)."'><span class='glyphicon glyphicon-trash text-danger' ></span></a>";
        }

        return $button;
    }


    public function getExtendedData($colName, $sLanguage, $base64jsonFieldMap)
    {
        $oFieldMap = json_decode( base64_decode($base64jsonFieldMap) );
        $value     = $this->$colName;

        $sFullValue      = strip_tags(getExtendedAnswer(self::$sid, $oFieldMap->fieldname, $value, $sLanguage));
        if (strlen($sFullValue) > 50)
        {
            $sElipsizedValue = ellipsize($sFullValue, $this->ellipsize_question_value );
            $sValue          = '<span data-toggle="tooltip" data-placement="left" title="'.quoteText($sFullValue).'">'.$sElipsizedValue.'</span>';
        }
        else
        {
            $sValue          = $sFullValue;
        }

        // Upload question
        if($oFieldMap->type =='|' && strpos($oFieldMap->fieldname,'filecount')===false)
        {

            $sSurveyEntry="<table class='table table-condensed upload-question'><tr>";
            $aQuestionAttributes = getQuestionAttributeValues($oFieldMap->qid);
            $aFilesInfo = json_decode_ls($this->$colName);
            for ($iFileIndex = 0; $iFileIndex < $aQuestionAttributes['max_num_of_files']; $iFileIndex++)
            {
                $sSurveyEntry .='<tr>';
                if (isset($aFilesInfo[$iFileIndex]))
                {
                    $sSurveyEntry.= '<td>'.CHtml::link(rawurldecode($aFilesInfo[$iFileIndex]['name']), App()->createUrl("/admin/responses",array("sa"=>"actionDownloadfile","surveyid"=>self::$sid,"iResponseId"=>$this->id,"sFileName"=>$aFilesInfo[$iFileIndex]['name'])) ).'</td>';
                    $sSurveyEntry.= '<td>'.sprintf('%s Mb',round($aFilesInfo[$iFileIndex]['size']/1000,2)).'</td>';

                    if ($aQuestionAttributes['show_title'])
                    {
                        if (!isset($aFilesInfo[$iFileIndex]['title'])) $aFilesInfo[$iFileIndex]['title']='';
                        $sSurveyEntry.= '<td>'.htmlspecialchars($aFilesInfo[$iFileIndex]['title'],ENT_QUOTES, 'UTF-8').'</td>';
                    }
                    if ($aQuestionAttributes['show_comment'])
                    {
                        if (!isset($aFilesInfo[$iFileIndex]['comment'])) $aFilesInfo[$iFileIndex]['comment']='';
                        $sSurveyEntry.= '<td>'.htmlspecialchars($aFilesInfo[$iFileIndex]['comment'],ENT_QUOTES, 'UTF-8').'</td>';
                    }
                }
                    $sSurveyEntry .='</tr>';
            }
            $sSurveyEntry.='</table>';
            $sValue = $sSurveyEntry;
        }

        return $sValue;
    }

    /**
     * Return true if actual respnse exist in database
     *
     * @param $srid : actual save survey id
     *
     * @return boolean
     */
    public function exist($srid)
    {
        $exist=false;

        if(Yii::app()->db->schema->getTable($this->tableName())){
            $data=Yii::app()->db->createCommand()
                ->select("id")
                ->from($this->tableName())
                ->where('id=:id', array(':id'=>$srid))
                ->queryRow();
            if($data)
            {
                $exist=true;
            }
        }
        return $exist;
    }

    /**
     * Return next id if next response exist in database
     *
     * @param integer $srid : actual save survey id
     * @param boolean $usefilterstate
     *
     * @return integer
     */
    public function next($srid,$usefilterstate=false)
    {
        $next=false;
        if ($usefilterstate && incompleteAnsFilterState() == 'incomplete')
            $wherefilterstate='submitdate IS NULL';
        elseif ($usefilterstate && incompleteAnsFilterState() == 'complete')
            $wherefilterstate='submitdate IS NOT NULL';
        else
            $wherefilterstate='1=1';

        if(Yii::app()->db->schema->getTable($this->tableName())){
            $data=Yii::app()->db->createCommand()
                ->select("id")
                ->from($this->tableName())
                ->where(array('and',$wherefilterstate,'id > :id'), array(':id'=>$srid))
                ->order('id ASC')
                ->queryRow();
            if($data)
            {
                $next=$data['id'];
            }
        }
        return $next;
    }

    /**
     * Return previous id if previous response exist in database
     *
     * @param integer $srid : actual save survey id
     * @param boolean $usefilterstate
     *
     * @return integer
     */
    public function previous($srid,$usefilterstate=false)
    {
        $previous=false;
        if ($usefilterstate && incompleteAnsFilterState() == 'incomplete')
            $wherefilterstate='submitdate IS NULL';
        elseif ($usefilterstate && incompleteAnsFilterState() == 'complete')
            $wherefilterstate='submitdate IS NOT NULL';
        else
            $wherefilterstate='1=1';

        if(Yii::app()->db->schema->getTable($this->tableName())){
            $data=Yii::app()->db->createCommand()
                ->select("id")
                ->from($this->tableName())
                ->where(array('and',$wherefilterstate,'id < :id'), array(':id'=>$srid))
                ->order('id DESC')
                ->queryRow();
            if($data)
            {
                $previous=$data['id'];
            }
        }
        return $previous;
    }

    /**
     * Function that returns a timeline of the surveys submissions
     *
     * @param string sType
     * @param string dStart
     * @param string dEnd
     * @param string $sType
     * @param string $dStart
     * @param string $dEnd
     *
     * @access public
     * @return array
     */
    public function timeline($sType, $dStart, $dEnd)
    {

        $sid = self::$sid;
        $oSurvey=Survey::model()->findByPk($sid);
        if ($oSurvey['datestamp']!='Y') {
               return false;
        }
        else
        {
            $criteria=new CDbCriteria;
            $criteria->select = 'submitdate';
            $criteria->addCondition('submitdate >= :dstart');
            $criteria->addCondition('submitdate <= :dend');
            $criteria->order="submitdate";

            $criteria->params[':dstart'] = $dStart;
            $criteria->params[':dend'] = $dEnd;
            $oResult = $this->findAll($criteria);

            if($sType=="hour")
                $dFormat = "Y-m-d_G";
            else
                $dFormat = "Y-m-d";

            foreach($oResult as $sResult)
            {
                $aRes[] = date($dFormat,strtotime($sResult['submitdate']));
            }

            return array_count_values($aRes);
        }
    }

    private function getbHaveToken()
    {
        if (!isset($this->bHaveToken))
        {
            $this->bHaveToken = tableExists('tokens_' . self::$sid) && Permission::model()->hasSurveyPermission(self::$sid,'tokens','read');// Boolean : show (or not) the token;
        }
        return $this->bHaveToken;
    }


    public function getFirstNameForGrid()
    {
        if(is_object($this->tokens))
        {
            return '<strong>'.$this->tokens->firstname.'</strong>';
        }

    }

    public function getLastNameForGrid()
    {
        if(is_object($this->tokens))
        {
            return '<strong>'.$this->tokens->lastname.'</strong>';
        }
    }

    public function getTokenForGrid()
    {
        $sToken = '';
        if(is_object($this->tokens) && ! is_null($this->tokens->tid) )
        {
            $sToken = "<a class='btn btn-default btn-xs edit-token' href='#' data-sid='".self::$sid."' data-tid='".$this->tokens->tid."'  data-url='".App()->createUrl("admin/tokens",array("sa"=>"edit","iSurveyId"=>self::$sid,"iTokenId"=>$this->tokens->tid, 'ajax'=>'true'))."' data-toggle='tooltip' title='".gT("Edit this survey participant")."'>".strip_tags($this->token)."&nbsp;&nbsp;&nbsp;<span class='glyphicon glyphicon-pencil'></span></a>";
        }
        else
        {
            $sToken = '<span class="badge badge-success">'.strip_tags($this->token).'</span>';
        }

        return $sToken;
    }

    // Get the list of default columns for surveys
    public function getDefaultColumns()
    {
        return array('id', 'token', 'submitdate', 'lastpage','startlanguage', 'completed', 'seed');
    }

    /**
     * Define what value to use to ellipsize the headers of the grid
     * It's using user state/default config, like for pagination
     * @see: http://www.yiiframework.com/wiki/324/cgridview-keep-state-of-page-and-sort/
     * @see: http://www.yiiframework.com/forum/index.php?/topic/8994-dropdown-for-pagesize-in-cgridview
     */
    public function getEllipsize_header_value()
    {
        return Yii::app()->user->getState('defaultEllipsizeHeaderValue',Yii::app()->params['defaultEllipsizeHeaderValue']);
    }

    /**
     * Define what value to use to ellipsize the question in the grid
     * It's using user state/default config, like for pagination.
     * @see: http://www.yiiframework.com/wiki/324/cgridview-keep-state-of-page-and-sort/
     * @see: http://www.yiiframework.com/forum/index.php?/topic/8994-dropdown-for-pagesize-in-cgridview
     */
    public function getEllipsize_question_value()
    {
        return Yii::app()->user->getState('defaultEllipsizeQuestionValue',Yii::app()->params['defaultEllipsizeQuestionValue']);
    }

    public function search()
    {
       $pageSize = Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);
       $criteria = new CDbCriteria;
       $sort     = new CSort;
       $sort->defaultOrder = 'id ASC';

       // Make all the model's columns sortable (default behaviour)
       $sort->attributes = array(
           '*',
       );

       // Join the token table and filter tokens if needed
       if ($this->bHaveToken && $this->survey->anonymized != 'Y')
       {
           $this->joinWithToken($criteria, $sort);
       }

       // Basic filters
       $criteria->compare('t.lastpage',empty($this->lastpage) ? null : $this->lastpage, false);
       $criteria->compare('t.id',empty($this->id) ? null : $this->id, false);
       $criteria->compare('t.submitdate',$this->submitdate, true);
       $criteria->compare('t.startlanguage',$this->startlanguage, true);

       // Completed filters
       if($this->completed_filter == "Y")
       {
           $criteria->addCondition('t.submitdate IS NOT NULL');
       }

       if($this->completed_filter == "N")
       {
           $criteria->addCondition('t.submitdate IS NULL');
       }

       $this->filterColumns($criteria);

       $dataProvider=new CActiveDataProvider('SurveyDynamic', array(
           'sort'=>$sort,
           'criteria'=>$criteria,
           'pagination'=>array(
               'pageSize'=>$pageSize,
           ),
       ));

       return $dataProvider;
    }

    /**
     * @param CDbCriteria $criteria
     * @param CSort $sort
     * @return void
     */
    protected function joinWithToken(CDbCriteria $criteria, CSort $sort)
    {
        $criteria->compare('t.token',$this->token, true);
        $criteria->join = "LEFT JOIN {{tokens_" . self::$sid . "}} as tokens ON t.token = tokens.token";
        $criteria->compare('tokens.firstname',$this->firstname_filter, true);
        $criteria->compare('tokens.lastname',$this->lastname_filter, true);
        $criteria->compare('tokens.email',$this->email_filter, true);

        // Add the related token model's columns sortable
        $aSortVirtualAttributes = array(
            'tokens.firstname'=>array(
                'asc'=>'tokens.firstname ASC',
                'desc'=>'tokens.firstname DESC',
            ),
            'tokens.lastname' => array(
                'asc'=>'lastname ASC',
                'desc'=>'lastname DESC'
            ),
            'tokens.email' => array(
                'asc'=>'email ASC',
                'desc'=>'email DESC'
            ),
        );

        $sort->attributes = array_merge($sort->attributes, $aSortVirtualAttributes);
    }

    /**
     * Loop through columns and add filter if any value is given for this column
     * Used in responses grid
     * @param CdbCriteria $criteria
     * @return void
     */
    protected function filterColumns(CDbCriteria $criteria)
    {
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        // Filters for responses
        foreach($this->metaData->columns as $column)
        {
            $isNotDefaultColumn = !in_array($column->name, $this->defaultColumns);
            if ($isNotDefaultColumn)
            {
                $c1 = (string) $column->name;
                $columnHasValue = !empty($this->$c1);
                if ($columnHasValue)
                {
                    $isDatetime = strpos($column->dbType, 'timestamp') !== false || strpos($column->dbType, 'datetime') !== false;
                    if ($column->dbType == 'decimal')
                    {
                        $this->$c1 = (float)$this->$c1;
                        $criteria->compare( Yii::app()->db->quoteColumnName($c1), $this->$c1, false);
                    }
                    else if ($isDatetime)
                    {
                        $s = DateTime::createFromFormat($dateformatdetails['phpdate'], $this->$c1);
                        if ($s === false)
                        {
                            // This happens when date is in wrong format
                            continue;
                        }
                        $s2 = $s->format('Y-m-d');
                        $criteria->addCondition('cast(' . Yii::app()->db->quoteColumnName($c1) . ' as date) = \'' . $s2 . '\'');
                    }
                    else
                    {
                        $criteria->compare( Yii::app()->db->quoteColumnName($c1), $this->$c1, true);
                    }
                }
            }
        }
    }
}
