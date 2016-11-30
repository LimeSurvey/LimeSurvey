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
*    Files Purpose: lots of common functions
*/
class QuestionGroup extends LSActiveRecord
{
    public $aQuestions; // to stock array of questions of the group
    /**
    * Returns the static model of Settings table
    *
    * @static
    * @access public
    * @param string $class
    * @return CActiveRecord
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
        return '{{groups}}';
    }

    /**
    * Returns the primary key of this table
    *
    * @access public
    * @return string[]
    */
    public function primaryKey()
    {
        return array('gid', 'language');
    }


    /**
    * Returns this model's validation rules
    *
    */
    public function rules()
    {
        return array(
            array('gid', 'unique', 'caseSensitive'=>true, 'criteria'=>array(
                'condition'=>'language=:language',
                'params'=>array(':language'=>$this->language)
                ),
                'message'=>'{attribute} "{value}" is already in use.'),
            array('language','length', 'min' => 2, 'max'=>20),// in array languages ?
            array('group_name,description','LSYii_Validators'),
            array('group_order','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
        );
    }


    public function attributeLabels()
    {
        return array(
            'language' => gt('Language'),
            'group_name' => gt('Group name')
        );
    }

    /**
    * Defines the relations for this model
    *
    * @access public
    * @return array
    */
    public function relations()
    {
        return array(
            'survey'    => array(self::BELONGS_TO, 'Survey', 'sid'),
            'questions' => array(self::HAS_MANY, 'Question', 'gid, language', 'condition'=>'parent_qid=0', 'order'=>'question_order ASC')
        );
    }

    function getAllRecords($condition=FALSE, $order=FALSE, $return_query = TRUE)
    {
        $query = Yii::app()->db->createCommand()->select('*')->from('{{groups}}');

        if ($condition != FALSE)
        {
            $query->where($condition);
        }

        if($order != FALSE)
        {
            $query->order($order);
        }

        return ( $return_query ) ? $query->queryAll() : $query;
    }

    function updateGroupOrder($sid,$lang,$position=0)
    {
        $data=Yii::app()->db->createCommand()->select('gid')
        ->where(array('and','sid=:sid','language=:language'))
        ->order('group_order, group_name ASC')
        ->from('{{groups}}')
        ->bindParam(':sid', $sid, PDO::PARAM_INT)
        ->bindParam(':language', $lang, PDO::PARAM_STR)
        ->query();

        $position = intval($position);
        foreach($data->readAll() as $row)
        {
            Yii::app()->db->createCommand()->update($this->tableName(),array('group_order' => $position),'gid='.$row['gid']);
            $position++;
        }
    }

    /**
    * Insert an array into the groups table
    * Returns false if insertion fails, otherwise the new GID
    *
    * @param array $data
    */
    public function insertRecords($data)
    {
        $group = new self;
        foreach ($data as $k => $v)
            $group->$k = $v;
        if  (!$group->save()) return false;
        else return $group->gid;
    }


    /**
    * This functions insert question group data in the form of array('<grouplanguage>'=>array( <array of fieldnames => values >))
    * It will take care of maintaining the group ID
    *
    * @param mixed $aQuestionGroupData
    */
    public function insertNewGroup($aQuestionGroupData)
    {
        $aFirstRecord=reset($aQuestionGroupData);
        $iSurveyID=$aFirstRecord['sid'];
        $sBaseLangauge = Survey::model()->findByPk($iSurveyID)->language;
        $aAdditionalLanguages = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
        $aSurveyLanguages=array($sBaseLangauge)+$aAdditionalLanguages;
        $bFirst = true;
        foreach ($aSurveyLanguages as $sLanguage)
        {
            if ($bFirst)
            {
                $iGroupID=$this->insertRecords($aQuestionGroupData[$sLanguage]);
                $bFirst = false;
            }
            else
            {
                $aQuestionGroupData[$sLanguage]['gid']=$iGroupID;
                switchMSSQLIdentityInsert('groups',true);
                $this->insertRecords($aQuestionGroupData[$sLanguage]);
                switchMSSQLIdentityInsert('groups',false);
            }
        }
        return $iGroupID;
    }

    function getGroups($surveyid) {
        $language = Survey::model()->findByPk($surveyid)->language;
        return Yii::app()->db->createCommand()
        ->select(array('gid', 'group_name'))
        ->from($this->tableName())
        ->where(array('and', 'sid=:surveyid', 'language=:language'))
        ->order('group_order asc')
        ->bindParam(":language", $language, PDO::PARAM_STR)
        ->bindParam(":surveyid", $surveyid, PDO::PARAM_INT)
        ->query()->readAll();
    }

    public static function deleteWithDependency($groupId, $surveyId)
    {
        // Abort if the survey is active
        $surveyIsActive = Survey::model()->findByPk($surveyId)->active !== 'N';
        if ($surveyIsActive)
        {
            Yii::app()->user->setFlash('error', gt("Can't delete question group when the survey is active"));
            return null;
        }

        $questionIds = QuestionGroup::getQuestionIdsInGroup($groupId);
        Question::deleteAllById($questionIds);
        Assessment::model()->deleteAllByAttributes(array('sid' => $surveyId, 'gid' => $groupId));
        return QuestionGroup::model()->deleteAllByAttributes(array('sid' => $surveyId, 'gid' => $groupId));
    }

    /**
    * Get group description
    *
    * @param int iGroupId
    * @param string sLanguage
    */
    public function getGroupDescription($iGroupId, $sLanguage)
    {
        $solover = $this->findByPk(array('gid' => $iGroupId, 'language' => $sLanguage))->description;
        return $this->findByPk(array('gid' => $iGroupId, 'language' => $sLanguage))->description;
    }

    private static function getQuestionIdsInGroup($groupId) {
        $questions = Yii::app()->db->createCommand()
        ->select('qid')
        ->from('{{questions}} q')
        ->join('{{groups}} g', 'g.gid=q.gid AND g.gid=:groupid AND q.parent_qid=0')
        ->group('qid')
        ->bindParam(":groupid", $groupId, PDO::PARAM_INT)
        ->queryAll();

        $questionIds = array();
        foreach ($questions as $question) {
            $questionIds[] = $question['qid'];
        }

        return $questionIds;
    }

    function getAllGroups($condition, $order=false)
    {
        $command = Yii::app()->db->createCommand()->where($condition)->select('*')->from($this->tableName());
        if ($order != FALSE)
        {
            $command->order($order);
        }
        return $command->query();
    }

    public function getbuttons()
    {
        // Find out if the survey is active to disable add-button
        $oSurvey=Survey::model()->findByPk($this->sid);
        $surveyIsActive = $oSurvey->active !== 'N';
        $button = '';

        // Add question to this group
        if (Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'update'))
        {
            $url = Yii::app()->createUrl("admin/questions/sa/newquestion/surveyid/$this->sid/gid/$this->gid");
            $button .= '<a class="btn btn-default list-btn ' . ($surveyIsActive ? 'disabled' : '') . ' "  data-toggle="tooltip"  data-placement="left" title="'.gT('Add new question to group').'" href="'.$url.'" role="button"><span class="glyphicon glyphicon-plus-sign " ></span></a>';
        }

        // Group edition
        // Edit
        if (Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'update'))
        {
            $url = Yii::app()->createUrl("admin/questiongroups/sa/edit/surveyid/$this->sid/gid/$this->gid");
            $button .= '  <a class="btn btn-default  list-btn" href="'.$url.'" role="button" data-toggle="tooltip" title="'.gT('Edit group').'"><span class="glyphicon glyphicon-pencil " ></span></a>';
        }

        // View summary
        if (Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'read'))
        {
            $url = Yii::app()->createUrl("/admin/questiongroups/sa/view/surveyid/");
            $url .= '/'.$this->sid.'/gid/'.$this->gid;
            $button .= '  <a class="btn btn-default  list-btn" href="'.$url.'" role="button" data-toggle="tooltip" title="'.gT('Group summary').'"><span class="glyphicon glyphicon-list-alt " ></span></a>';
        }

        // Delete
        if($oSurvey->active != "Y" && Permission::model()->hasSurveyPermission($this->sid,'surveycontent','delete' ))
        {
            $condarray = getGroupDepsForConditions($this->sid, "all", $this->gid, "by-targgid");
            if(is_null($condarray))
            {
                $confirm = 'if (confirm(\''.gT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js").'\')) { window.open(\''.Yii::app()->createUrl("admin/questiongroups/sa/delete/surveyid/$this->sid/gid/$this->gid").'\',\'_top\'); };';
                $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Delete").'" href="#" role="button"
                onclick="'.$confirm.'">
                <span class="text-danger glyphicon glyphicon-trash"></span>
                </a>';
            }
            else
            {
                $alert = 'alert(\''.gT("Impossible to delete this group because there is at least one question having a condition on its content","js").'\'); return false;';
                $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Delete").'" href="#" role="button"
                onclick="'.$alert.'">
                <span class="text-danger glyphicon glyphicon-trash"></span>
                </a>';
            }
        }

        return $button;
    }



    public function search()
    {
        $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);

        $sort = new CSort();
        $sort->defaultOrder = array('group_order'=> false);
        $sort->attributes = array(
            'group_id'=>array(
                'asc'=>'gid',
                'desc'=>'gid desc',
            ),
            'group_order'=>array(
                'asc'=>'group_order',
                'desc'=>'group_order desc',
            ),
            'group_name'=>array(
                'asc'=>'group_name',
                'desc'=>'group_name desc',
            ),
        );

        $criteria = new CDbCriteria;
        $criteria->condition='sid=:surveyid AND language=:language';
        $criteria->params=(array(':surveyid'=>$this->sid,':language'=>$this->language));
        $criteria->compare('group_name', $this->group_name, true);

        $dataProvider=new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,

            'sort'=>$sort,

            'pagination'=>array(
                'pageSize'=>$pageSize,
            ),
        ));
        return $dataProvider;
    }

    /**
    * Make sure we don't save a new question group
    * while the survey is active.
    *
    * @return bool
    */
    protected function beforeSave()
    {
        if (parent::beforeSave())
        {
            $surveyIsActive = Survey::model()->findByPk($this->sid)->active !== 'N';
            if ($surveyIsActive && $this->getIsNewRecord()) /* And for multi lingual, when add a new language ? */
            {
                $this->addError('gid',gT("You can not add a group if survey is active."));
                return false;
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Used in frontend helper, buildsurveysession.
     * @param int $surveyid
     * @return int
     */
    public static function getTotalGroupsWithoutQuestions($surveyid)
    {
        $sQuery= "select count(*) from {{groups}}
            left join {{questions}} on  {{groups}}.gid={{questions}}.gid
            where {{groups}}.sid={$surveyid} and qid is null";
        return Yii::app()->db->createCommand($sQuery)->queryScalar();
    }

}
