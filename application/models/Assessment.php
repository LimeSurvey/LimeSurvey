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
     *	Files Purpose: lots of common functions
*/

/**
 * Class Assessment
 *
 * @property integer $id Primary key
 * @property integer $sid Survey id
 * @property integer $gid Group id
 * @property string $scope
 * @property string $name
 * @property string $minimum
 * @property string $maximum
 * @property string $message
 * @property string $language
 */
class Assessment extends LSActiveRecord
{
    /**
     * @inheritdoc
     * @return Assessment
     */
    public static function model($class = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($class);
        return $model;
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('name,message', 'LSYii_Validators'),
        );
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{assessments}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return array('id', 'language');
    }

        /**
         * @return array customized attribute labels (name=>label)
         */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'scope' => gT("Scope"),
            'name' => gT("Name"),
            'minimum' => gT("Minimum"),
            'maximum' => gT("Maximum"),
            'message' => gT("Message"),
            'language' => gT("Language"),
        );
    }

    public function getButtons()
    {
        $buttons = "<div style='white-space: nowrap'>";
        $raw_button_template = ""
            . "<button class='btn btn-default btn-xs %s %s' role='button' title='%s' type='button'>" //extra class //title
            . "<i class='fa fa-%s' aria-hidden='true' ></i><span class='sr-only'>%s</span>" //icon class
            . "</button>";
        $editData = array(
            'action_assessments_editModal',
            'text-info',
            gT("Edit this assessment rule"),
            'edit',
            gT("Edit")
        );
        $deleteData = array(
            'action_assessments_deleteModal',
            'text-danger',
            gT("Delete this assessment rule"),
            'trash text-danger',
            gT("Delete")
        );
        if (Permission::model()->hasSurveyPermission($this->sid,'assessments', 'delete')) {
            $buttons .= vsprintf($raw_button_template, $deleteData);
        }
        if (Permission::model()->hasSurveyPermission($this->sid,'assessments', 'update')) {
            $buttons .= vsprintf($raw_button_template, $editData);
        }
        $buttons .= '</div>';

        return $buttons;
    }

    public function getColumns()
    {
        return array(
            array(
                'name' => 'id',
                'filter' => false
                ),
            array(
                "name" => 'buttons',
                "type" => 'raw',
                "header" => gT("Action"),
                "filter" => false
            ),
            array(
                'name' => 'scope',
                'value' => '$data->scope == "G" ? eT("Group") : eT("Total")',
                'htmlOptions' => ['class' => 'col-sm-1'],
                'filter' => TbHtml::dropDownList('Assessment[scope]', 'scope', ['' => gT('All'), 'T' => gT('Total'), 'G' => gT("Group")])
            ),
            array(
                'name' => 'name',
                'htmlOptions' => ['class' => 'col-sm-2']
            ),
            array(
                'name' => 'minimum',
                'htmlOptions' => ['class' => 'col-sm-1']
            ),
            array(
                'name' => 'maximum',
                'htmlOptions' => ['class' => 'col-sm-1']
            ),
            array(
                'name' => 'message',
                'htmlOptions' => ['class' => 'col-sm-5'],
                "type" => 'html', // This show all html (filtered, raw show unfiltered html) … maybe need another type , something like strip tag and ellipsize ?
            )
        );
    }

    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $survey = Survey::model()->findByPk($this->sid);

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('sid', $this->sid);
        $criteria->compare('gid', $this->gid);
        $criteria->compare('scope', $this->scope);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('minimum', $this->minimum);
        $criteria->compare('maximum', $this->maximum);
        $criteria->compare('message', $this->message, true);
        $criteria->compare('language', $survey->language);

        $pageSize = Yii::app()->user->getState('pageSizeParticipantView', Yii::app()->params['defaultPageSize']);
        return new CActiveDataProvider(
            $this,
            array(
                'criteria'=>$criteria,
                'pagination' => array(
                    'pageSize' => $pageSize
                )
            )
        );
    }

    /**
     * @param array $data
     * @return Assessment
     */
    public static function insertRecords($data)
    {
        $assessment = new self;

        foreach ($data as $k => $v) {
                    $assessment->$k = $v;
        }
        $assessment->scope = isset($assessment->scope) ? $assessment->scope : '0';
        $assessment->save();

        return $assessment;
    }

    /**
     * @param integer $id
     * @param integer $iSurveyID
     * @param string $language
     * @param array $data
     */
    public static function updateAssessment($id, $iSurveyID, $language, array $data)
    {
        $assessment = self::model()->findByAttributes(array('id' => $id, 'sid'=> $iSurveyID, 'language' => $language));
        if (!is_null($assessment)) {
            foreach ($data as $k => $v) {
                            $assessment->$k = $v;
            }
            $assessment->save();
        }
    }
}
