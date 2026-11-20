<?php

/*
 * LimeSurvey
 * Copyright (C) 2013-2026 The LimeSurvey Project Team
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
 * Dynamic model for the timings of the responses of a survey (table timings_<survey ID>).
 * Usage: Timing::model($surveyId) for queries, Timing::create($surveyId) for a new record.
 *
 * @property integer $id ID of the response the timings belong to
 * @property float $interviewtime Total time of the response in seconds
 *
 * Relations
 * @property Response $response
 */
abstract class Timing extends Dynamic
{
    /**
     * @inheritdoc
     * @param mixed $className Either the classname or the survey ID.
     * @return Timing
     */
    public static function model($className = null)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /**
     * @param int $id Survey ID
     * @param string $scenario
     * @return Timing
     */
    public static function create($id, $scenario = 'insert')
    {
        return parent::create($id, $scenario);
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
            'response' => array(self::BELONGS_TO, 'Response_' . $this->dynamicId, 'id')
        );
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{timings_' . intval($this->dynamicId) . '}}';
    }

    /**
     * Get current surveyId for other model/function
     * @return int
     */
    public function getSurveyId()
    {
        return $this->getDynamicId();
    }

    /**
     * Returns the time statistics of the completed responses of this survey
     *
     * @return array 'count' is the number of completed responses with timings. If it is not 0, there also are
     *     the average time ('avgmin', 'avgsec') and the median time ('median' in seconds, 'allmin', 'allsec')
     */
    public function statistics()
    {
        $statistics = array('count' => 0);
        if (!Yii::app()->db->schema->getTable($this->tableName())) {
            return $statistics;
        }
        $responsesTable = '{{responses_' . intval($this->dynamicId) . '}}';
        $queryAvg = Yii::app()->db->createCommand()
            ->select("AVG(interviewtime) AS avg, COUNT(*) as count")
            ->from($this->tableName() . " t")
            ->join($responsesTable . " s", "t.id = s.id")
            ->where("s.submitdate IS NOT NULL")
            ->queryRow();
        if (!$queryAvg['count']) {
            return $statistics;
        }
        $statistics['avgmin'] = (int) ($queryAvg['avg'] / 60);
        $statistics['avgsec'] = ((int) $queryAvg['avg']) % 60;
        $statistics['count'] = $queryAvg['count'];
        // Only the one or two middle values are read for the median, the table can be huge
        $middleval = intval($statistics['count'] / 2);
        $statistics['middleval'] = $middleval;
        $isEvenCount = $statistics['count'] % 2 == 0;
        $middleValues = Yii::app()->db->createCommand()
            ->select("t.interviewtime")
            ->from($this->tableName() . " t")
            ->join($responsesTable . " s", "t.id = s.id")
            ->where("s.submitdate IS NOT NULL")
            ->order("t.interviewtime")
            // Even number of values: The median is the mean of the two middle values
            ->limit($isEvenCount ? 2 : 1, $isEvenCount ? $middleval - 1 : $middleval)
            ->queryColumn();
        // No value can only happen if responses were deleted since the count was read
        $median = empty($middleValues) ? 0 : (int) (array_sum($middleValues) / count($middleValues));
        $statistics['median'] = $median;
        $statistics['allmin'] = (int) ($median / 60);
        $statistics['allsec'] = ((int) $median) % 60;
        return $statistics;
    }

    /**
     * Inserts a new timings record
     *
     * @param array $data Values indexed by column name
     * @throws \CException
     * @return false|integer The ID of the new record, false if it could not be saved
     */
    public function insertRecords($data)
    {
        $record = self::create($this->dynamicId);
        foreach ($data as $k => $v) {
            $record->$k = $v;
        }

        $rawTableName = trim((string) $this->tableName(), "{}");
        if (isset($data['id'])) {
            switchMSSQLIdentityInsert($rawTableName, true);
        }
        try {
            $record->save();
            if (isset($data['id'])) {
                switchMSSQLIdentityInsert($rawTableName, false);
            }
            return $record->id;
        } catch (Exception $e) {
            if (isset($data['id'])) {
                switchMSSQLIdentityInsert($rawTableName, false);
            }
            if (App()->getConfig('debug') > 1) {
                throw new \CException($e->getMessage());
            }
            return false;
        }
    }

    /**
     * Search function, used by the grid of the time statistics
     * @return CActiveDataProvider
     */
    public function search()
    {
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);

        $criteria = new CDbCriteria();
        $criteria->join = "INNER JOIN {{responses_" . intval($this->dynamicId) . "}} s ON t.id=s.id";
        $criteria->condition = 'submitdate IS NOT NULL';
        $criteria->order = "s.id " . (Yii::app()->request->getParam('order') == 'desc' ? 'desc' : 'asc');

        return new CActiveDataProvider(get_class($this), array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));
    }

    /**
     * Generates the possible Actions for each row
     *
     * @return string HTML
     * @throws Exception
     */
    public function getActions()
    {
        $surveyId = $this->dynamicId;
        $permissionResponsesRead = Permission::model()->hasSurveyPermission($surveyId, 'responses', 'read');
        $permissionResponsesUpdate = Permission::model()->hasSurveyPermission($surveyId, 'responses', 'update');
        $permissionResponsesDelete = Permission::model()->hasSurveyPermission($surveyId, 'responses', 'delete');

        $dropdownItems = [];
        // Edit
        $dropdownItems[] = [
            'title'            => gT('Edit this response'),
            'url'              => App()->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/" . $surveyId . "/id/" . $this->id),
            'iconClass'        => 'ri-pencil-fill',
            'enabledCondition' => $permissionResponsesUpdate
        ];
        // View details
        $dropdownItems[] = [
            'title'            => gT('View response details'),
            'url'              => App()->createUrl("responses/view/", ['surveyId' => $surveyId, 'id' => $this->id]),
            'iconClass'        => 'ri-list-unordered',
            'enabledCondition' => $permissionResponsesRead
        ];
        // Delete
        $dropdownItems[] = [
            'title'            => gT('Delete this response'),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-bs-target' => '#confirmation-modal',
                'data-post-url'  => App()->createUrl("admin/dataentry/sa/delete/subaction/edit/surveyid/" . $surveyId . "/id/" . $this->id),
                'data-message'   => gT("Do you want to delete this response?"),
            ],
            'enabledCondition' => $permissionResponsesDelete
        ];
        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
    }
}
