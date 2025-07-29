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
 */

/**
 * Class SurveyTimingDynamic
 *
 */
class SurveyTimingDynamic extends LSActiveRecord
{
    /** @var int $sid Survey id */
    protected static $sid = 0;

    /**
     * @inheritdoc
     * @param string $sid
     * @return SurveyTimingDynamic
     * @psalm-suppress ParamNameMismatch
     */
    public static function model($sid = null)
    {
        $refresh = false;
        $survey = Survey::model()->findByPk($sid);
        if ($survey) {
            /** @var boolean $refresh */
            $refresh = self::$sid !== $survey->sid;
            self::sid($survey->sid);
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
    public function primaryKey()
    {
        return 'id';
    }

    /** @inheritdoc */
    public function relations()
    {
        return array(
        'id' => array(self::BELONGS_TO, 'SurveyDynamic', 'id'),
        );
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{survey_' . intval(self::$sid) . '_timings}}';
    }

    /**
     * Returns Time statistics for this answer table
     *
     * @access public
     * @return array
     */
    public function statistics()
    {
        $sid = self::$sid;
        if (Yii::app()->db->schema->getTable($this->tableName())) {
            $queryAvg = Yii::app()->db->createCommand()
                ->select("AVG(interviewtime) AS avg, COUNT(*) as count")
                ->from($this->tableName() . " t")
                ->join("{{survey_{$sid}}} s", "t.id = s.id")
                ->where("s.submitdate IS NOT NULL")
                ->queryRow();
            if ($queryAvg['count']) {
                $statistics['avgmin'] = (int) ($queryAvg['avg'] / 60);
                $statistics['avgsec'] = ((int)$queryAvg['avg']) % 60;
                $statistics['count'] = $queryAvg['count'];
                $queryAll = Yii::app()->db->createCommand()
                    ->select("interviewtime")
                    ->from($this->tableName() . " t")
                    ->join("{{survey_{$sid}}} s", "t.id = s.id")
                    ->where("s.submitdate IS NOT NULL")
                    ->order("t.interviewtime")
                    ->queryAll();
                $middleval = intval($statistics['count'] / 2);
                $statistics['middleval'] = $middleval;
                if ($statistics['count'] % 2 && $statistics['count'] > 1) {
                    $median = ($queryAll[$middleval]['interviewtime'] + $queryAll[$middleval - 1]['interviewtime']) / 2;
                } else {
                    $median = $queryAll[$middleval]['interviewtime'];
                }
                $statistics['median'] = $median;
                $statistics['allmin'] = (int) ($median / 60);
                $statistics['allsec'] = $median % 60;
            } else {
                $statistics['count'] = 0;
            }
        } else {
            $statistics['count'] = 0;
        }
        return $statistics;
    }

    /**
     * @param array $data
     * @throws \CException
     * @return false|integer
     */
    public function insertRecords($data)
    {
        $record = new self();
        foreach ($data as $k => $v) {
            $record->$k = $v;
        }

        if (isset($data['id'])) {
             switchMSSQLIdentityInsert(trim((string) $this->tableName(), "{}"), true);
        }
        try {
            $record->save();
            if (isset($data['id'])) {
                 switchMSSQLIdentityInsert(trim((string) $this->tableName(), "{}"), false);
            }
            return $record->id;
        } catch (Exception $e) {
            if (isset($data['id'])) {
                 switchMSSQLIdentityInsert(trim((string) $this->tableName(), "{}"), false);
            }
            if (App()->getConfig('debug') > 1) {
                throw new \CException($e->getMessage());
            }
            return false;
        }
    }


    /**
     * Search function, used by TbGridView
     * @param integer $iSurveyID
     * @param string $language
     * @return CActiveDataProvider
     * // TODO $language is not used locally
     */
    public function search($iSurveyID, $language)
    {
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);

        $oCriteria = new CdbCriteria();
        $oCriteria->join = "INNER JOIN {{survey_{$iSurveyID}}} s ON t.id=s.id";
        $oCriteria->condition = 'submitdate IS NOT NULL';
        $oCriteria->order = "s.id " . (Yii::app()->request->getParam('order') == 'desc' ? 'desc' : 'asc');
        //$oCriteria->offset = $start;
        //$oCriteria->limit = $limit;

        $dataProvider = new CActiveDataProvider('SurveyTimingDynamic', array(
            //'sort'=>$sort,
            'criteria' => $oCriteria,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));

        return $dataProvider;
    }

    /**
     * Generates the possible Actions for each row
     *
     * @return string HTML
     * @throws Exception
     */
    public function getActions()
    {
        $permission_responses_read = Permission::model()->hasSurveyPermission(self::$sid, 'responses', 'read');
        $permission_responses_update = Permission::model()->hasSurveyPermission(self::$sid, 'responses', 'update');
        $permission_responses_delete = Permission::model()->hasSurveyPermission(self::$sid, 'responses', 'delete');

        $dropdownItems = [];
        // Edit
        $dropdownItems[] = [
            'title'            => gT('Edit this response'),
            'url'              => App()->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/" . self::$sid . "/id/" . $this->id),
            'iconClass'        => 'ri-pencil-fill',
            'enabledCondition' => $permission_responses_update
        ];
        // View details
        $dropdownItems[] = [
            'title'            => gT('View response details'),
            'url'              => App()->createUrl("responses/view/", ['surveyId' => self::$sid, 'id' => $this->id]),
            'iconClass'        => 'ri-list-unordered',
            'enabledCondition' => $permission_responses_read
        ];
        // Delete
        $dropdownItems[] = [
            'title'            => gT('Delete this response'),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-bs-target' => '#confirmation-modal',
                'data-post-url'  => App()->createUrl("admin/dataentry/sa/delete/subaction/edit/surveyid/" . self::$sid . "/id/" . $this->id),
                'data-message'   => gT("Do you want to delete this response?"),
            ],
            'enabledCondition' => $permission_responses_delete
        ];
        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
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
