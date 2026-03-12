<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
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
 * Class SavedControl
 * @property integer $scid Primary key
 * @property integer $sid Survey id
 * @property integer $srid
 * @property string $identifier
 * @property string $access_code
 * @property string $email
 * @property string $ip
 * @property string $saved_thisstep
 * @property string $status
 * @property string $saved_date
 * @property string $refurl
 */
class SavedControl extends LSActiveRecord
{
    /**
     * Set defaults
     * @inheritdoc
     */
    public function init()
    {
        $this->ip = "";
    }
    /** @inheritdoc */
    public function tableName()
    {
        return '{{saved_control}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'scid';
    }

    /**
     * @inheritdoc
     * @return CActiveRecord
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function getAllRecords($condition = false)
    {
        if ($condition != false) {
            $this->db->where($condition);
        }

        $data = $this->db->get('saved_control');

        return $data;
    }

    /**
     * @param int $sid
     * @return mixed
     */
    public function getCountOfAll($sid)
    {
        $data = Yii::app()->db->createCommand("SELECT COUNT(*) AS countall FROM {{saved_control}} WHERE sid=:sid")->bindParam(":sid", $sid, PDO::PARAM_INT)->query();
        $row = $data->read();

        return $row['countall'];
    }

    /**
     * Deletes some records meeting specified condition
     *
     * @access public
     * @param array $condition
     * @return int (rows deleted)
     */
    public function deleteSomeRecords($condition)
    {
        $record = new self();
        $criteria = new CDbCriteria();

        if ($condition != false) {
            foreach ($condition as $column => $value) {
                $criteria->addCondition("$column='$value'");
            }
        }

        return $record->deleteAll($criteria);
    }

    /**
     * @param $data
     * @return mixed
     * @deprecated at 2018-02-03 use $model->attributes = $data && $model->save()
     */
    public function insertRecords($data)
    {
        return $this->db->insert('saved_control', $data);
    }

    public function getButtons()
    {
        $permission_respones_update = Permission::model()->hasSurveyPermission($this->sid, 'responses', 'update');

        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Edit response'),
            'url'              => App()->createUrl("admin/dataentry/sa/editdata/subaction/edit", ["surveyid" => $this->sid, "id" => $this->srid]),
            'iconClass'        => 'ri-pencil-fill',
            'enabledCondition' => $permission_respones_update
        ];
        // TODO: this is a unfinished functionality resendAccesscode
//        $dropdownItems[] = [
//            'title'            => gT('Resend access code'),
//            'url'              => App()->createUrl("admin/saved/sa/resend_accesscode",array("surveyid"=>$this->sid,"id"=>$this->srid)),
//            'iconClass'        => 'ri-refresh-line',
//            'enabledCondition' => Permission::model()->hasSurveyPermission($this->sid, 'responses', 'update')
//        ];
        $dropdownItems[] = [
            'title'            => gT('Delete this entry and related response'),
            'url'              => App()->createUrl("admin/saved/sa/actionDelete", ["surveyid" => $this->sid, "scid" => $this->scid, "srid" => $this->srid]),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => $permission_respones_update,
            'linkAttributes'   => [
                'onclick' => "window.LS.gridButton.confirmGridAction(event,$(this));",
                'data-confirm-text' => gT('Delete this entry and related response'),
                'data-gridid' => 'saved-grid',
            ]
        ];

        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
    }

    public function getColumns()
    {
        return array(

            array(
                'header' => gT("ID"),
                'name' => 'scid',
                'filter' => false,
            ),
            array(
                'header' => gT("Identifier"),
                'name' => 'identifier',
            ),
            array(
                'header' => gT("IP address"),
                'name' => 'ip',
            ),
            array(
                'header' => gT("Date saved"),
                'name' => 'saved_date',
            ),
            array(
                'header' => gT("Email address"),
                'name' => 'email',
            ),
            array(
                'name' => 'buttons',
                'type' => 'raw',
                'header' => gT('Action'),
                'filter' => false,
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column']
            ),
        );
    }

    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.
        $criteria = new CDbCriteria();
        $criteria->compare('sid', $this->sid, false); //will not be searchable
        $criteria->compare('srid', $this->srid, true);
        $criteria->compare('access_code', $this->access_code, true);

        $criteria->compare('scid', $this->scid);
        $criteria->compare('identifier', $this->identifier, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('ip', $this->ip, true);
        $criteria->compare('saved_thisstep', $this->saved_thisstep, true);
        $criteria->compare('status', $this->status, true);
        $criteria->compare('saved_date', $this->saved_date, true);
        $criteria->compare('refurl', $this->refurl, true);
        $pageSize = Yii::app()->user->getState('savedResponsesPageSize', Yii::app()->params['defaultPageSize']);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));
    }
}
