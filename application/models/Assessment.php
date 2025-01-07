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
     *  Files Purpose: lots of common functions
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
    public function init()
    {
        parent::init();
        if ($this->isNewRecord) {
            // default values
            if (empty($this->scope)) {
                $this->scope = '0';
            }
        }
    }

    /**
     * @inheritdoc
     * @return Assessment
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('name,message', 'LSYii_Validators'),
            array('scope', 'in', 'range' => array('G', 'T'))
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

        $permission_assessment_edit = Permission::model()->hasSurveyPermission(
            $this->sid,
            'assessments',
            'update'
        );
        $permission_assessment_delete = Permission::model()->hasSurveyPermission(
            $this->sid,
            'assessments',
            'delete'
        );
        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Edit'),
            'tooltip'          => gT('Edit this assessment rule'),
            'iconClass'        => 'ri-pencil-fill',
            'enabledCondition' => $permission_assessment_edit,
            'linkClass'         => 'action_assessments_editModal',
            'linkId'            => 'loadEditUrl_forModalView',
            'linkAttributes'   => [
                'data-editurl' => App()->createUrl("assessment/edit/", ["surveyid" => $this->sid]),
                'data-assessment-id' => $this->id
            ]
        ];
        $dropdownItems[] = [
            'title'            => gT('Delete'),
            'tooltip'          => gT('Delete this assessment rule'),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => $permission_assessment_delete,
            'linkClass'         => 'action_assessments_deleteModal',
            'linkAttributes'   => [
                'data-assessment-id' => $this->id
            ]
        ];

        return App()->getController()->widget(
            'ext.admin.grid.GridActionsWidget.GridActionsWidget',
            ['dropdownItems' => $dropdownItems],
            true
        );
    }

    public function getColumns()
    {
        return array(
            array(
                'name'   => 'id',
                'filter' => false
            ),
            array(
                'name' => 'scope',
                'value' => '$data->scope == "G" ? eT("Group") : eT("Total")',
                'htmlOptions' => ['class' => ''],
                'filter' => TbHtml::dropDownList(
                    'Assessment[scope]',
                    $this->scope,
                    ['A' => gT('All'), 'T' => gT('Total'), 'G' => gT("Group")]
                )
            ),
            array(
                'name' => 'name',
                'htmlOptions' => ['class' => '']
            ),
            array(
                'name' => 'minimum',
                'htmlOptions' => ['class' => '']
            ),
            array(
                'name' => 'maximum',
                'htmlOptions' => ['class' => '']
            ),
            array(
                'name' => 'message',
                'htmlOptions' => ['class' => ''],
                'value' => 'viewHelper::flatEllipsizeText($data->message,true,0)',
                "type" => 'raw'
            ),
            array(
                "name"   => 'buttons',
                "type"   => 'raw',
                "header" => gT("Action"),
                'value'             => '$data->buttons',
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'text-center button-column ls-sticky-column'],
                "filter" => false
            ),
        );
    }

    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $survey = Survey::model()->findByPk($this->sid);

        $criteria = new LSDbCriteria();

        $criteria->compare('id', $this->id);
        $criteria->compare('sid', $this->sid);
        $criteria->compare('gid', $this->gid);
        if ($this->scope !== 'A') {
            $criteria->compare('scope', $this->scope);
        }
        $criteria->compare('name', $this->name, true);
        $criteria->compare('minimum', $this->minimum);
        $criteria->compare('maximum', $this->maximum);
        $criteria->compare('message', $this->message, true);
        $criteria->compare('language', $survey->language);

        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
        return new CActiveDataProvider(
            $this,
            array(
                'criteria' => $criteria,
                'pagination' => array(
                    'pageSize' => $pageSize
                )
            )
        );
    }

    /**
     * @param array $data
     * @return Assessment
     * @deprecated use model->attributes = $data && $model->save()
     */
    public static function insertRecords($data)
    {
        $assessment = new self();

        foreach ($data as $k => $v) {
                    $assessment->$k = $v;
        }
        $assessment->scope = $assessment->scope ?? 'T';
        $assessment->save();

        return $assessment;
    }

    /**
     * @param integer $id
     * @param integer $iSurveyID
     * @param string $language
     * @param array $data
     * @return bool True if the assessment could be updated. False if the assessment is not found of the update failed.
     */
    public static function updateAssessment($id, $iSurveyID, $language, array $data)
    {
        $assessment = self::model()->findByAttributes(array('id' => $id, 'sid' => $iSurveyID, 'language' => $language));
        if (!is_null($assessment)) {
            foreach ($data as $k => $v) {
                            $assessment->$k = $v;
            }
            return $assessment->save();
        }
        return false;
    }

    /**
     * Checks for a survey if it has asssessment activated. Checks also inherited status ('I')
     *
     * @param $surveyid
     * @return boolean true if it is actice, false otherwise and if survey does not exist
     */
    public static function isAssessmentActive($surveyid)
    {
        $bActive = false;
        $oSurvey = Survey::model()->findByPk($surveyid);
        if ($oSurvey !== null) {
            $assessmentActivated = $oSurvey->assessments; // colud be Y, N or I (check inheritance ...)
            if ($assessmentActivated === 'I') { //then value is inherited, check survey group value ...
                if ($oSurvey->gsid === 1) { //this is the default group (it's always set to 'N')
                    $bActive = false;
                } else {
                    $oSurveyGroupSettings = SurveysGroupsettings::model()->findByPk($oSurvey->gsid);
                    $isActiveSurveyGroup = $oSurveyGroupSettings->assessments;
                    $bActive = $isActiveSurveyGroup === 'Y';
                }
            } else {
                $bActive = $assessmentActivated === 'Y';
            }
        }

        return $bActive;
    }
}
