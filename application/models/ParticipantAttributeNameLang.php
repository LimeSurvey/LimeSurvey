<?php
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
 * This is the model class for table "{{{{participant_attribute_names}}}}".
 *
 * The followings are the available columns in table '{{{{participant_attribute_names}}}}':
 * @property integer $attribute_id
 * @property string $attribute_type
 * @property string $visible
 */
class ParticipantAttributeNameLang extends LSActiveRecord
{
	/**
	 * Returns the static model of Participant Attribute Names Lang table
	 *
	 * @static
	 * @access public
     * @param string $class
	 * @return CActiveRecord
	 */

    /**
    * Returns the primary key of this table
    *
    * @access public
    * @return string
    */
    public function primaryKey()
    {
        return 'attribute_id';
    }

	public static function model($class = __CLASS__)
	{
		return parent::model($class);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{participant_attribute_names_lang}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that will receive user inputs.
		return array(
            array('attribute_name','filter','filter' => 'strip_tags'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
			array('attribute_id, attribute_name, lang', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
            'participant_attribute_names'=>array(self::BELONGS_TO, 'ParticipantAttributeName', 'attribute_id')
		);
	}

}
