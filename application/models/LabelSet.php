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
class LabelSet extends LSActiveRecord implements PermissionInterface
{
    use PermissionTrait;

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
            array('owner_id', 'numerical', 'integerOnly' => true),
            array('label_name', 'required'),
            array('label_name', 'filter', 'filter' => array(self::class, 'sanitizeAttribute')),
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
            'labels' => array(self::HAS_MANY, 'Label', 'lid', 'order' => 'sortorder ASC'),
            'owner' => array(self::BELONGS_TO, 'User', 'owner_id', 'together' => true),
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
        $permissions = [
            'read' => $this->hasPermission('labelset', 'read'),
            'edit' => $this->hasPermission('labelset', 'update'),
            'export' => $this->hasPermission('labelset', 'export'),
            'delete' => $this->hasPermission('labelset', 'delete'),
        ];

        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Edit label set'),
            'iconClass'        => 'ri-pencil-fill',
            'url'              => App()->createUrl("admin/labels/sa/editlabelset/lid/$this->lid"),
            'enabledCondition' => $permissions['edit']
        ];
        $dropdownItems[] = [
            'title'     => gT('View labels'),
            'iconClass' => 'ri-list-unordered',
            'url'       => App()->createUrl("admin/labels/sa/view/lid/$this->lid"),
            'enabledCondition' => $permissions['read'] // Must not appear, filtered by seacrh criteria
        ];
        $dropdownItems[] = [
            'title'            => gT('Export label set'),
            'iconClass'        => 'ri-download-fill',
            'url'              => App()->createUrl("admin/export/sa/dumplabel/lid/$this->lid"),
            'enabledCondition' => $permissions['export']
        ];
        $dropdownItems[] = [
            'title'            => gT('Delete'),
            'tooltip'          => gT('Delete label sets'),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => $permissions['delete'],
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-post-url'  => App()->createUrl("admin/labels/sa/delete", ["lid" => $this->lid]),
                'data-message'   => gT("Are you sure you want to delete this label set?"),
                'data-bs-target' => "#confirmation-modal"
            ]
        ];

        return App()->getController()->widget(
            'ext.admin.grid.GridActionsWidget.GridActionsWidget',
            ['dropdownItems' => $dropdownItems],
            true
        );
    }

    public function search()
    {
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);

        $criteria = new LSDbCriteria();
        // Permission : do not use for list : All labelSets can be used by anyone currently.
        $criteriaPerm = self::getPermissionCriteria();
        $criteria->mergeWith($criteriaPerm, 'AND');

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
            'criteria' => $criteria,
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
            LabelL10n::model()->deleteAllByAttributes([], 'label_id = :id', [':id' => $oLabel->id]);
            $oLabel->delete();
        }
        rmdirr(App()->getConfig('uploaddir') . '/labels/' . $this->lid);
    }

    /**
     * Get criteria from Permission
     * If currrent user didn't have global permission (read) : add Permission criteria, currentky only owner_id check
     * @param int|null $userid for this user id , if not set : get current one
     * @return CDbCriteria
     */
    protected static function getPermissionCriteria($userid = null)
    {
        if (!$userid) {
            $userid = Yii::app()->user->id;
        }
        $criteriaPerm = new CDbCriteria();
        if (!Permission::model()->hasGlobalPermission("labelsets", 'read')) {
            /* owner of labelsets */
            $criteriaPerm->compare('t.owner_id', intval($userid), false);
        }
        return $criteriaPerm;
    }

    /**
     * permission scope for this model
     * Actually only test if user have access to LabelSets (read)
     * Usage don't need read permission
     * @param int|null $userid
     * @return self
     */
    public function permission($userid = null)
    {
        if (!$userid) {
            $userid = Yii::app()->user->id;
        }
        $criteria = $this->getDBCriteria();
        $criteriaPerm = self::getPermissionCriteria($userid);
        $criteria->mergeWith($criteriaPerm, 'AND');
        return $this;
    }

    /**
     * Get the owner id of this Survey group Used for Permission
     * @return integer
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * @inheritdoc
     */
    public function hasPermission($sPermission, $sCRUD = 'read', $iUserID = null)
    {
        /* If have global : return true */
        if (Permission::model()->hasPermission(0, 'global', 'labelsets', $sCRUD, $iUserID)) {
            return true;
        }
        /* Specific need primaryKey */
        if (!$this->primaryKey) {
            return false;
        }
        /* Finally : return specific one : always false if not  */
        return Permission::model()->hasPermission($this->lid, 'labelset', $sPermission, $sCRUD, $iUserID);
    }

    /**
     * Sanitize string for any attribute, XSS and XSS in javascript function too.
     * @todo create a Validator to be used for all such element : no need HTML, informative input used only for admin purpose.
     * @param string $attribute to sanitize
     * @return string sanitized attribute
     */
    public static function sanitizeAttribute($attribute)
    {
        return str_replace(['<','>','&','\'','"'], "", $attribute);
    }
}
