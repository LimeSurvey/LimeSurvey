<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');
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

class LabelSet extends LSActiveRecord
{
	/**
	 * Returns the table's name
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{labelsets}}';
	}

	/**
	 * Returns the table's primary key
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'lid';
	}

	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
     * @param string $class
	 * @return CActiveRecord
	 */
	public static function model($class = __CLASS__)
	{
		return parent::model($class);
	}
    /**
    * Returns this model's validation rules
    *
    */
    public function rules()
    {
        return array(
            array('label_name','required'),
            array('label_name','length', 'min' => 1, 'max'=>100),
            array('label_name','LSYii_Validators'),
            array('languages','required'),
            array('languages','LSYii_Validators','isLanguageMulti'=>true),
        );
    }

	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
        {
		    foreach ($condition as $item => $value)
			{
				$criteria->addCondition($item.'="'.$value.'"');
			}
        }

		$data = $this->findAll($criteria);

        return $data;
	}

    function getLID()
    {
		return Yii::app()->db->createCommand()->select('lid')->order('lid asc')->from('{{labelsets}}')->query()->readAll();
    }

	function insertRecords($data)
    {
        $lblset = new self;
		foreach ($data as $k => $v)
			$lblset->$k = $v;
		if ($lblset->save())
        {
            return $lblset->lid;
        }
        return false;
    }

    public function getbuttons()
        {

            // View labelset
            $url = Yii::app()->createUrl("admin/labels/sa/view/lid/$this->lid");
            $button = '<a class="btn btn-default list-btn" data-toggle="tooltip" data-placement="left" title="'.gT('View labels').'" href="'.$url.'" role="button"><span class="glyphicon glyphicon-list-alt" ></span></a>';

            // Edit labelset
            if(Permission::model()->hasGlobalPermission('labelsets','update'))
            {
                $url = Yii::app()->createUrl("admin/labels/sa/editlabelset/lid/$this->lid");
                $button .= ' <a class="btn btn-default list-btn" data-toggle="tooltip" data-placement="left" title="'.gT('Edit label set').'" href="'.$url.'" role="button"><span class="glyphicon glyphicon-pencil" ></span></a>';
            }

            // Export labelset
            if(Permission::model()->hasGlobalPermission('labelsets','export'))
            {
                $url = Yii::app()->createUrl("admin/export/sa/dumplabel/lid/$this->lid");
                $button .= ' <a class="btn btn-default list-btn" data-toggle="tooltip" data-placement="left" title="'.gT('Export label set').'" href="'.$url.'" role="button"><span class="icon-export" ></span></a>';
            }

            // Delete labelset
            if(Permission::model()->hasGlobalPermission('labelsets','delete'))
            {
                $url = Yii::app()->createUrl("admin/labels/sa/delete/lid/$this->lid");
                $button .= ' <a class="btn btn-default list-btn" data-toggle="tooltip" data-placement="left" title="'.gT('Delete label set').'" href="'.$url.'" role="button" data-confirm="'.gT('Are you sure you want to delete this label set?').'"><span class="glyphicon glyphicon-trash text-warning"></span></a>';
            }

            return $button;
        }

    public function search()
    {
        $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);

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

        $dataProvider=new CActiveDataProvider('LabelSet', array(
            'sort'=>$sort,
            'pagination'=>array(
                'pageSize'=>$pageSize,
            ),
        ));

        return $dataProvider;
    }
}
