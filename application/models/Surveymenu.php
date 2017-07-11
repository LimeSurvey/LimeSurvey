<?php

/**
 * This is the model class for table "{{surveymenu}}".
 *
 * The followings are the available columns in table '{{surveymenu}}':
 * @property integer $id
 * @property integer $parent_id
 * @property integer $survey_id
 * @property integer $priority
 * @property integer $level
 * @property string $title
 * @property string $description
 * @property string $changed_at
 * @property integer $changed_by
 * @property string $created_at
 * @property integer $created_by
 *
 * The followings are the available model relations:
 * @property SurveymenuEntries[] $surveymenuEntries
 */
class Surveymenu extends LSActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{surveymenu}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('changed_at', 'required'),
			array('parent_id, survey_id, priority, level, changed_by, created_by', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>255),
			array('description, created_at', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, parent_id, survey_id, priority, level, title, description, changed_at, changed_by, created_at, created_by', 'safe', 'on'=>'search'),
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
			'surveymenuEntries' => array(self::HAS_MANY, 'SurveymenuEntries', 'menu_id'),
			'survey' => array(self::BELONGS_TO, 'Survey', ''),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'parent_id' => 'Parent',
			'survey_id' => 'Survey',
			'priority' => 'Priority',
			'level' => 'Level',
			'title' => 'Title',
			'description' => 'Description',
			'changed_at' => 'Changed At',
			'changed_by' => 'Changed By',
			'created_at' => 'Created At',
			'created_by' => 'Created By',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('parent_id',$this->parent_id);
		$criteria->compare('survey_id',$this->survey_id);
		$criteria->compare('priority',$this->priority);
		$criteria->compare('level',$this->level);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('changed_at',$this->changed_at,true);
		$criteria->compare('changed_by',$this->changed_by);
		$criteria->compare('created_at',$this->created_at,true);
		$criteria->compare('created_by',$this->created_by);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Surveymenu the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
