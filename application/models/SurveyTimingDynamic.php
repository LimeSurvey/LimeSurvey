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
                $statistics['avgsec'] = $queryAvg['avg'] % 60;
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
             switchMSSQLIdentityInsert(trim($this->tableName(), "{}"), true);
        }
        try {
            $record->save();
            if (isset($data['id'])) {
                 switchMSSQLIdentityInsert(trim($this->tableName(), "{}"), false);
            }
            return $record->id;
        } catch (Exception $e) {
            if (isset($data['id'])) {
                 switchMSSQLIdentityInsert(trim($this->tableName(), "{}"), false);
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
     * Buttons for actions in the grid view
     *
     * @return string HTML
     */
    public function getButtons()
    {
        $buttons = "<div class='icon-btn-row'>";
        // Edit
        if (Permission::model()->hasSurveyPermission(self::$sid, 'responses', 'update')) {
            $editUrl = App()->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/" . self::$sid . "/id/" . $this->id);
            $buttons .= '<a class="btn btn-sm btn-default" href="' . $editUrl . '" role="button" data-toggle="tooltip" title="' . gT('Edit this response') . '"><span class="fa fa-pencil" ></span></a>';
        }
        // View details
        $viewUrl = App()->createUrl("responses/view/", ['surveyId' => self::$sid, 'id' => $this->id]);
        $buttons .= '<a class="btn btn-sm btn-default" href="' . $viewUrl . '" role="button" data-toggle="tooltip" title="' . gT('View response details') . '"><span class="fa fa-list-alt" ></span></a>';


        // Delete
        if (Permission::model()->hasSurveyPermission(self::$sid, 'responses', 'delete')) {
            $deleteUrl = App()->createUrl("admin/dataentry/sa/delete/subaction/edit/surveyid/" . self::$sid . "/id/" . $this->id);
            $buttons .= '<a class="btn btn-sm btn-default" data-target="#confirmation-modal" data-post-url="' . $deleteUrl . '" role="button" data-toggle="modal" data-tooltip="true" title="' . gT('Delete this response') . '"><span class="fa fa-trash text-danger" ></span></a>';
        }
        $buttons .= '</div>';
        return $buttons;
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
