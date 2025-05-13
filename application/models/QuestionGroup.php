<?php

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

use LimeSurvey\Models\Services\Exception\{
    NotFoundException,
    BadRequestException
};

/**
 * Class QuestionGroup
 *
 * @property integer $gid ID
 * @property integer $sid Survey ID
 * @property integer $group_order Group order number (max 100 chars)
 * @property string $randomization_group  Randomization group
 * @property string $grelevance Group's relevane equation
 *
 * @property Survey $survey
 * @property Question[] $questions Questions without subquestions
 * @property QuestionGroupL10n[] $questiongroupl10ns
 */
class QuestionGroup extends LSActiveRecord
{
    public $aQuestions; // to stock array of questions of the group

    public $group_name;

    public $language;

    public $description;

    /**
     * @inheritdoc
     * @return QuestionGroup
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{groups}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'gid';
    }


    /** @inheritdoc */
    public function rules()
    {
        return [
            ['group_order', 'numerical', 'integerOnly' => true, 'allowEmpty' => true],
            ['grelevance', 'LSYii_FilterValidator', 'filter' => 'trim', 'skipOnEmpty' => true],
            ['randomization_group', 'safe']
        ];
    }

    /** @inheritdoc */
    public function relations()
    {
        return array(
            'survey'    => array(self::BELONGS_TO, 'Survey', 'sid'),
            'questions' => array(
                self::HAS_MANY,
                'Question',
                'gid',
                'condition' => 'questions.parent_qid = 0',
                'order' => 'questions.question_order ASC',
                'together' => false
            ),
            'questiongroupl10ns' => array(self::HAS_MANY, 'QuestionGroupL10n', 'gid', 'together' => true)
        );
    }


    /**
     * @param integer $iSurveyId
     * @param int $position
     */
    public function updateGroupOrder($iSurveyId, $position = 0)
    {
        $iSurveyId = (int) $iSurveyId;
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        $language = $oSurvey->language;
        $data = Yii::app()->db->createCommand()->select('g.gid')
            ->where('g.sid=:sid AND gl.language = :language')
            ->order('group_order, group_name ASC')
            ->from('{{groups}} g')
            ->join('{{group_l10ns}} gl', 'g.gid=gl.gid')
            ->bindParam(':sid', $iSurveyId, PDO::PARAM_INT)
            ->bindParam(':language', $language, PDO::PARAM_STR)
            ->query();

        $position = intval($position);
        foreach ($data->readAll() as $row) {
            Yii::app()->db->createCommand()->update($this->tableName(), array('group_order' => $position), 'gid=' . $row['gid']);
            $position++;
        }
    }

    public function cleanOrder($surveyid)
    {
        $iSurveyId = (int) $surveyid;
        $oSurvey = Survey::model()->findByPk($iSurveyId);

        $aSurveyLanguages = array_merge([$oSurvey->language], explode(" ", (string) $oSurvey->additional_languages));

        foreach ($aSurveyLanguages as $sSurveyLanguage) {
            $oCriteria = new CDbCriteria();
            $oCriteria->compare('sid', $iSurveyId);
            $oCriteria->order = 'group_order ASC';

            $aQuestiongroups = QuestionGroup::model()->findAll($oCriteria);
            foreach ($aQuestiongroups as $itrt => $oQuestiongroup) {
                $iQuestionGroupOrder = $itrt + 1;
                $oQuestiongroup->group_order = $iQuestionGroupOrder;
                $oQuestiongroup->save();

                $aQuestions = $oQuestiongroup->questions;
                foreach ($aQuestions as $qitrt => $oQuestion) {
                    $iQuestionOrder = $qitrt + 1;
                    $oQuestion->question_order = $iQuestionOrder;
                    $oQuestion->save(true);
                }
            }
        }
    }
    /**
     * Insert an array into the groups table
     * Returns false if insertion fails, otherwise the new GID
     *
     * @param array $data
     * @return bool|int
     * @deprecated at 2018-02-03 use $model->attributes = $data && $model->save()
     */
    public function insertRecords($data)
    {
        $group = new self();
        foreach ($data as $k => $v) {
            $group->$k = $v;
        }
        if (!$group->save()) {
            return false;
        } else {
            return $group->gid;
        }
    }

    /**
     * Deletes a question group and all its dependencies.
     * Returns affected rows of question group table (should be 1 or null)
     * @param integer $groupId
     * @param integer|null $surveyId deprecated
     * @throw Exception
     * @return int|null
     */
    public static function deleteWithDependency($groupId, $surveyId = null)
    {
        $QuestionGroup = self::model()->findByPk($groupId);
        if (empty($QuestionGroup)) {
            throw new NotFoundException(gT('Group not found'));
        }
        // Abort if the survey is active
        $surveyIsActive = Survey::model()->findByPk($QuestionGroup->sid)->active !== 'N';
        if ($surveyIsActive) {
            throw new BadRequestException(gT("Can't delete question group when the survey is active"));
        }
        $surveyId = $QuestionGroup->sid;
        $questionIds = QuestionGroup::getQuestionIdsInGroup($groupId);
        Question::deleteAllById($questionIds);
        Assessment::model()->deleteAllByAttributes(array('sid' => $surveyId, 'gid' => $groupId));
        QuestionGroupL10n::model()->deleteAllByAttributes(array('gid' => $groupId));
        return QuestionGroup::model()->deleteAllByAttributes(array('sid' => $surveyId, 'gid' => $groupId));
    }

    /**
     * Get group description
     *
     * @param int $iGroupId
     * @param string $sLanguage
     * @return string
     */
    public function getGroupDescription($iGroupId, $sLanguage)
    {
        return $this->findByPk($iGroupId)->getGroupDescriptionI10N($sLanguage);
    }

    /**
     * Get the internationalized group name from the L10N Table
     *
     * @param string $sLanguage
     * @return string
     */
    public function getGroupNameI10N($sLanguage)
    {
        if (isset($this->questiongroupl10ns[$sLanguage])) {
            return $this->questiongroupl10ns[$sLanguage]->group_name;
        }
        return '';
    }

    /**
     * Get the internationalized group description from the L10N Table
     *
     * @param string $sLanguage
     * @return string
     */
    public function getGroupDescriptionI10N($sLanguage)
    {
        if (isset($this->questiongroupl10ns[$sLanguage])) {
            return $this->questiongroupl10ns[$sLanguage]->description;
        }
        return '';
    }

    /**
     * @param integer $groupId
     * @return array
     */
    private static function getQuestionIdsInGroup($groupId)
    {
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

    /**
     * @param mixed|array $condition
     * @param string[]|false $order
     * @return CDbDataReader
     */
    public function getAllGroups($condition, $order = false)
    {
        $command = Yii::app()->db->createCommand()
            ->where($condition)
            ->select('*')
            ->from($this->tableName());
        if ($order != false) {
            $command->order($order);
        }
        return $command->query();
    }

    /**
     * @return string
     */
    public function getbuttons()
    {
        // Find out if the survey is active to disable add-button
        $oSurvey = Survey::model()->findByPk($this->sid);
        $surveyIsNotActive = $oSurvey->active !== 'Y';

        $permission_groups_edit = Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'update');
        $permission_add_question_to_group = Permission::model()->hasSurveyPermission(
            $this->sid,
            'surveycontent',
            'update'
        );
        $permission_summary_group = Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'read');
        $permission_delete_group = Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'delete');

        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Edit group'),
            'iconClass'        => 'ri-pencil-fill',
            'url'              => Yii::app()->createUrl(
                "questionGroupsAdministration/edit/surveyid/$this->sid/gid/$this->gid"
            ),
            'enabledCondition' => $permission_groups_edit,
            'linkAttributes'   => [
                'data-bs-toggle' => "tooltip",
            ]
        ];
        $dropdownItems[] = [
            'title'            => gT('Add new question to group'),
            'iconClass'        => 'ri-add-line',
            'url'              => Yii::app()->createUrl(
                "questionAdministration/create/surveyid/$this->sid/gid/$this->gid"
            ),
            'enabledCondition' => $surveyIsNotActive && $permission_add_question_to_group,
            'linkAttributes'   => [
                'data-bs-toggle' => "tooltip",
            ]
        ];
        $url = Yii::app()->createUrl("/questionGroupsAdministration/view/surveyid/");
        $url .= '/' . $this->sid . '/gid/' . $this->gid;
        $dropdownItems[] = [
            'title'            => gT('Group summary'),
            'iconClass'        => 'ri-list-unordered',
            'url'              => $url,
            'enabledCondition' => $permission_summary_group,
            'linkAttributes'   => [
                'data-bs-toggle' => "tooltip",
            ]
        ];

        $condarray = getGroupDepsForConditions($this->sid, "all", $this->gid, "by-targgid");
        //group can only be deleted if there is still more than 1 group and there are no depending conditions
        $groupIsDeletable = $oSurvey->groupsCount > 1 &&  is_null($condarray);
        $msgNotDeletable = '';
        if ($oSurvey->groupsCount == 1) {
            $msgNotDeletable = gT("Cannot delete this group because it's the only group in the survey.");
        }
        if (!is_null($condarray)) {
            $msgNotDeletable = gT('Group can not be deleted, because of depending conditions');
        }

        $dropdownItems[] = [
            'title'            => gT('Delete question group'),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'tooltip'          => $msgNotDeletable,
            'enabledCondition' => $surveyIsNotActive && $permission_delete_group && $groupIsDeletable,
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-bs-target' => '#confirmation-modal',
                'data-message'   => gT(
                    "Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?"
                ),
                'data-btnclass'  => 'btn-danger',
                'data-btntext'   => gT('Delete'),
                'data-onclick'  => '(function() { ' . CHtml::encode(convertGETtoPOST(
                        App()->createUrl(
                            "questionGroupsAdministration/delete/",
                            ["gid" => $this->gid, "surveyid" => $this->sid]
                        )
                )) . '})'
            ]
        ];
        return App()->getController()->widget(
            'ext.admin.grid.GridActionsWidget.GridActionsWidget',
            ['dropdownItems' => $dropdownItems],
            true
        );
    }


    /**
     * @return CActiveDataProvider
     */
    public function search()
    {
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);

        $sort = new CSort();
        $sort->defaultOrder = array('group_order' => false);
        $sort->attributes = array(
            'group_id' => array(
                'asc' => 't.gid',
                'desc' => 't.gid desc',
            ),
            'group_order' => array(
                'asc' => 'group_order',
                'desc' => 'group_order desc',
            ),
            'group_name' => array(
                'asc' => 'group_name',
                'desc' => 'group_name desc',
            ),
        );

        $criteria = new LSDbCriteria();
        $criteria->with = array('questiongroupl10ns' => array("select" => "group_name, description"));
        $criteria->together = true;
        $criteria->condition = 'sid=:surveyid AND language=:language';
        $criteria->params = (array(':surveyid' => $this->sid, ':language' => $this->language));
        $criteria->compare('group_name', $this->group_name, true);

        $dataProvider = new CActiveDataProvider(get_class($this), array(
            'criteria' => $criteria,

            'sort' => $sort,

            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));
        return $dataProvider;
    }

    /*
     * Get primary Question group title
     */
    public function getPrimaryTitle()
    {
        $survey = Survey::model()->findByPk($this->sid);
        $baselang = $survey->language;
        $oQuestionGroup = $this->with('questiongroupl10ns')->find('t.gid = :gid AND language = :language', array(':gid' => $this->gid, ':language' => $baselang));
        return $oQuestionGroup->questiongroupl10ns[$baselang]->group_name;
    }

    /*
     * Get primary Question group description
     */
    public function getPrimaryDescription()
    {
        $survey = Survey::model()->findByPk($this->sid);
        $baselang = $survey->language;
        $oQuestionGroup = $this->with('questiongroupl10ns')->find('t.gid = :gid AND language = :language', array(':gid' => $this->gid, ':language' => $baselang));
        return $oQuestionGroup->questiongroupl10ns[$baselang]->description;
    }

    /**
     * Make sure we don't save a new question group
     * while the survey is active.
     *
     * @inheritdoc
     */
    protected function beforeSave()
    {
        if (parent::beforeSave()) {
            $survey = Survey::model()->findByPk($this->sid);
            if (!empty($survey) && $survey->isActive && $this->getIsNewRecord()) {
                /* And for multi lingual, when add a new language ? */
                $this->addError('gid', gT("You can not add a group if survey is active."));
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the first question group in the survey
     * @param int $surveyId
     * @return QuestionGroup
     */
    public static function getFirstGroup($surveyId)
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('sid = ' . $surveyId);
        $criteria->mergeWith(array(
            'order' => 'gid DESC'
        ));
        return self::model()->find($criteria);
    }

    /*
     * Used in frontend helper, buildsurveysession.
     * @param int $surveyid
     * @return int
     */
    public static function getTotalGroupsWithoutQuestions($surveyid)
    {
        $cacheKey = 'getTotalGroupsWithoutQuestions_' . $surveyid;
        $value = EmCacheHelper::get($cacheKey);
        if ($value !== false) {
            return $value;
        }

        $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
        $sQuery = "select count(*) from $quotedGroups
            left join {{questions}} on  $quotedGroups.gid={{questions}}.gid
            where $quotedGroups.sid={$surveyid} and qid is null";
        $result =  Yii::app()->db->createCommand($sQuery)->queryScalar();

        EmCacheHelper::set($cacheKey, $result);

        return $result;
    }

    /**
     * Used in frontend helper, buildsurveysession.
     * @param int $surveyid
     * @return int
     */
    public static function getTotalGroupsWithQuestions($surveyid)
    {
        $cacheKey = 'getTotalGroupsWithQuestions_' . $surveyid;
        $value = EmCacheHelper::get($cacheKey);
        if ($value !== false) {
            return $value;
        }

        $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
        $sQuery = "select count(DISTINCT $quotedGroups.gid) from $quotedGroups
            left join {{questions}} on  $quotedGroups.gid={{questions}}.gid
            where $quotedGroups.sid ={$surveyid} and qid is not null";
        $result = Yii::app()->db->createCommand($sQuery)->queryScalar();

        EmCacheHelper::set($cacheKey, $result);

        return $result;
    }

    /**
     * Returns all group questions (including subquestions)
     * @return Question[]
     */
    public function getAllQuestions()
    {
        /** @var Question[] $questions */
        $questions = Question::model()->findAllByAttributes(['gid' => $this->gid]);
        return $questions;
    }
}
