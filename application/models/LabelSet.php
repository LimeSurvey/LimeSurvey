<?php if (!defined('BASEPATH')) {
    die('No direct script access allowed');
}
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
            array('label_name', 'required'),
            array('label_name', 'length', 'min' => 1, 'max'=>100),
            array('label_name', 'LSYii_Validators'),
            array('languages', 'required'),
            array('languages', 'LSYii_Validators', 'isLanguageMulti'=>true),
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'labels' => array(self::HAS_MANY, 'Label', 'lid', 'order'=>'language ASC, sortorder ASC')
        );
    }

    /**
     * @param mixed|bool $condition
     * @return static[]
     */
    public function getAllRecords($condition = false)
    {
        $criteria = new CDbCriteria;
        if ($condition != false) {
            foreach ($condition as $item => $value) {
                $criteria->addCondition($item.'="'.$value.'"');
            }
        }

        return $this->findAll($criteria);
    }

    /**
     * @return array
     */
    public function getLID()
    {
        return Yii::app()->db->createCommand()->select('lid')->order('lid asc')->from('{{labelsets}}')->query()->readAll();
    }

    public function insertRecords($data)
    {
        $lblset = new self;
        foreach ($data as $k => $v) {
                    $lblset->$k = $v;
        }
        if ($lblset->save()) {
            return $lblset->lid;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getbuttons()
    {

            // View labelset
            $url = Yii::app()->createUrl("admin/labels/sa/view/lid/$this->lid");
            $button = '<a class="btn btn-default list-btn" data-toggle="tooltip" data-placement="left" title="'.gT('View labels').'" href="'.$url.'" role="button"><span class="fa fa-list-alt" ></span></a>';

            // Edit labelset
            if (Permission::model()->hasGlobalPermission('labelsets', 'update')) {
                $url = Yii::app()->createUrl("admin/labels/sa/editlabelset/lid/$this->lid");
                $button .= ' <a class="btn btn-default list-btn" data-toggle="tooltip" data-placement="left" title="'.gT('Edit label set').'" href="'.$url.'" role="button"><span class="fa fa-pencil" ></span></a>';
            }

            // Export labelset
            if (Permission::model()->hasGlobalPermission('labelsets', 'export')) {
                $url = Yii::app()->createUrl("admin/export/sa/dumplabel/lid/$this->lid");
                $button .= ' <a class="btn btn-default list-btn" data-toggle="tooltip" data-placement="left" title="'.gT('Export label set').'" href="'.$url.'" role="button"><span class="icon-export" ></span></a>';
            }

            // Delete labelset
            if (Permission::model()->hasGlobalPermission('labelsets', 'delete')) {
                $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Delete label set").'" href="#" role="button"'
                    ." onclick='$.bsconfirm(\"".CHtml::encode(gT("Are you sure you want to delete this label set?"))
                                ."\", {\"confirm_ok\": \"".gT("Yes")."\", \"confirm_cancel\": \"".gT("No")."\"}, function() {"
                                . convertGETtoPOST(Yii::app()->createUrl("admin/labels/sa/delete", ["lid" => $this->lid]))
                            ."});'>"
                        .' <i class="text-danger fa fa-trash"></i>
                    </a>';
            }
            return $button;
        }

    public function search()
    {
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);

        $sort = new CSort();
        $sort->attributes = array(
            'labelset_id'=>array(
            'asc'=>'lid',
            'desc'=>'lid desc',
            ),
            'name'=>array(
            'asc'=>'label_name',
            'desc'=>'label_name desc',
            ),
            'languages'=>array(
            'asc'=>'languages',
            'desc'=>'languages desc',
            ),
        );

        $dataProvider = new CActiveDataProvider('LabelSet', array(
            'sort'=>$sort,
            'pagination'=>array(
                'pageSize'=>$pageSize,
            ),
        ));

        return $dataProvider;
    }

    /** @inheritdoc
     * But delete related label sets and directory
     * @return boolean
     */
    public function delete()
    {
        if(parent::delete()) {
            Label::model()->findAll("lid = :lid",array(":lid"=>$this->getPrimaryKey()));
            rmdirr(Yii::app()->getConfig('uploaddir').'/labels/'.$this->getPrimaryKey());
            return true;
        }
        return false;
    }
}
