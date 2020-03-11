<?php if (!defined('BASEPATH')) {
    die('No direct script access allowed');
}
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
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
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
        $record = new self;
        $criteria = new CDbCriteria;

        if ($condition != false) {
            foreach ($condition as $column => $value) {
                $criteria->addCondition("$column='$value'");
            }
        }

        return $record->deleteAll($criteria);
    }

    public function insertRecords($data)
    {
        return $this->db->insert('saved_control', $data);
    }

    public function getGridButtons()
    {
        $gridButtons = array();
        $gridButtons['editresponse'] = array(
            'label' => '<span class="sr-only">' . gT("Edit") . '</span><span class="fa fa-list-alt" aria-hidden="true"></span>',
            'imageUrl' => false,
            'url' => 'App()->createUrl("admin/dataentry/sa/editdata/subaction/edit",array("surveyid"=>$data->sid,"id"=>$data->srid));',
            'options' => array(
                'class' => "btn btn-default btn-xs btn-edit",
                'data-toggle' => "tooltip",
                'title' => gT("Edit response"),
            ),
            'visible' => 'boolval(' . Permission::model()->hasSurveyPermission($this->sid, 'responses', 'update') . ')',
        );
        $gridButtons['resend_accesscode'] = array(
            'label' => '<span class="sr-only">' . gT("Edit") . '</span><span class="fa fa-refresh" aria-hidden="true"></span>',
            'imageUrl' => false,
            'url' => 'App()->createUrl("admin/saved/sa/resend_accesscode",array("surveyid"=>$data->sid,"id"=>$data->srid));',
            'options' => array(
                'class' => "btn btn-default btn-xs btn-edit",
                'data-toggle' => "tooltip",
                'title' => gT("Resend access code"),
            ),
            // 'visible'=> 'boolval('.Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update').')',
            'visible' => false,
        );
        $gridButtons['delete'] = array(
            'label' => '<span class="sr-only">' . gT("Delete") . '</span><span class="text-warning fa fa-trash" aria-hidden="true"></span>',
            'imageUrl' => false,
            'icon' => false,
            'url' => 'App()->createUrl("admin/saved/sa/actionDelete",array("surveyid"=>$data->sid,"scid"=>$data->scid,"srid"=>$data->srid));',
            'options' => array(
                'class' => "btn btn-default btn-xs btn-delete",
                'data-toggle' => "tooltip",
                'title' => gT("Delete this entry and related response"),
            ),
            'visible' => 'boolval(' . Permission::model()->hasSurveyPermission($this->sid, 'responses', 'delete') . ')',
            'click' => 'function(event){ window.LS.gridButton.confirmGridAction(event,$(this)); }',
        );
        return $gridButtons;
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
                'class' => 'bootstrap.widgets.TbButtonColumn',
                'template' => '{editresponse}{delete}',
                //~ 'htmlOptions' => array('class' => 'text-left response-buttons'),
                'buttons' => $this->gridButtons,
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
        );
    }

    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.
        $criteria = new CDbCriteria;
        $criteria->compare('sid', $this->sid, false); //will not be searchable
        $criteria->compare('srid', $this->srid, true);
        $criteria->compare('access_code', $this->access_code, true);

        $criteria->compare('scid', $this->scid);
        $criteria->compare('identifier', $this->identifier, true);
        $criteria->compare('email', $this->email);
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
