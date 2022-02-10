<?php

/*
 * LimeSurvey (tm)
 * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *
 */

/**
 * Class LabelSet
 *
 * @property integer $lid ID (primary key)
 * @property string $label_name Label Name (max 100 chars)
 * @property string $languages
 */
class LabelSet extends LSActiveRecord
{
    /** @inheritdoc */
    public function tableName()
    {
        return '{{labelsets}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'lid';
    }

    /**
     * @inheritdoc
     * @return LabelSet
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
            array('label_name', 'required'),
            array('label_name', 'length', 'min' => 1, 'max' => 100),
            array('label_name', 'LSYii_Validators'),
            array('languages', 'required'),
            array('languages', 'LSYii_Validators', 'isLanguageMulti' => true),
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'labels' => array(self::HAS_MANY, 'Label', 'lid', 'order' => 'sortorder ASC')
        );
    }

    /**
     * Recursively deletes a label set including labels and localizations
     *
     * @param integer $id The label set ID
     *
     * @return bool
     * @throws CException
     */
    public function deleteLabelSet($id)
    {
        $arLabelSet = $this->findByPk($id);
        if (empty($arLabelSet)) {
            return false;
        }
        $oDB = App()->db;
        $oTransaction = $oDB->beginTransaction();
        try {
            $this->deleteLabelsForLabelSet();

            $bLabelSetDeleted = $arLabelSet->delete();
            $oTransaction->commit();
            return $bLabelSetDeleted;
        } catch (Exception $e) {
            $oTransaction->rollback();
            return false;
        }
    }

    /**
     * @param $data
     * @return bool|int
     * @deprecated at 2018-01-29 use $model->attributes = $data && $model->save()
     */
    public function insertRecords($data)
    {
        $lblset = new self();
        foreach ($data as $k => $v) {
                    $lblset->$k = $v;
        }
        if ($lblset->save()) {
            return $lblset->lid;
        }
        return false;
    }

    public function getLanguageArray()
    {
        return explode(' ', $this->languages);
    }

    /**
     * Returns all defined buttons for the gridview.
     * @return string
     */
    public function getbuttons()
    {
        $button = "<div class='icon-btn-row'>";
        // Edit labelset
        if (Permission::model()->hasGlobalPermission('labelsets', 'update')) {
            $url = Yii::app()->createUrl("admin/labels/sa/editlabelset/lid/$this->lid");
            $button .= ' <a class="btn btn-default btn-sm green-border" data-toggle="tooltip" data-placement="top" title="' . gT('Edit label set') . '" href="' . $url . '" role="button"><span class="fa fa-pencil" ></span></a>';
        }

        // View labelset
        $url = Yii::app()->createUrl("admin/labels/sa/view/lid/$this->lid");
        $button .= '<a class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="' . gT('View labels') . '" href="' . $url . '" role="button"><span class="fa fa-list-alt" ></span></a>';

        // Export labelset
        if (Permission::model()->hasGlobalPermission('labelsets', 'export')) {
            $url = Yii::app()->createUrl("admin/export/sa/dumplabel/lid/$this->lid");
            $button .= ' <a class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="' . gT('Export label set') . '" href="' . $url . '" role="button"><span class="icon-export" ></span></a>';
        }

        // Delete labelset
        if (Permission::model()->hasGlobalPermission('labelsets', 'delete')) {
            $url = Yii::app()->createUrl("admin/labels/sa/delete", ["lid" => $this->lid]);
            $message = gT("Are you sure you want to delete this label set?");
            $button .= '<span data-toggle="tooltip" data-placement="top" title="' . gT('Delete label set') . '"><a 
            class="btn btn-default btn-sm"  
            data-toggle="modal"
            data-post-url ="' . $url . '"
            data-message="' . $message . '"
            data-target="#confirmation-modal" 
            title="' . gT("Delete") . '" 
            href="#" >
                    <i class="fa fa-trash text-danger"></i>
                    </a></span>';
        }
        $button .= "</div>";
            return $button;
    }

    public function search()
    {
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);

        $sort = new CSort();
        $sort->attributes = array(
            'labelset_id' => array(
            'asc' => 'lid',
            'desc' => 'lid desc',
            ),
            'name' => array(
            'asc' => 'label_name',
            'desc' => 'label_name desc',
            ),
            'languages' => array(
            'asc' => 'languages',
            'desc' => 'languages desc',
            ),
        );

        $dataProvider = new CActiveDataProvider('LabelSet', array(
            'sort' => $sort,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));

        return $dataProvider;
    }

    /**
     * Delete all childs(Label and LabelL10n) for a LabelSet
     */
    public function deleteLabelsForLabelSet()
    {
        // delete old labels and translations before inserting the new values
        foreach ($this->labels as $oLabel) {
            LabelL10n::model()->deleteAllByAttributes([], 'id = :id', [':id' => $oLabel->id]);
            $oLabel->delete();
        }
        rmdirr(App()->getConfig('uploaddir') . '/labels/' . $this->lid);
    }
}
