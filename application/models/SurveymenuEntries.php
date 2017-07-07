<?php

/**
 * This is the model class for table "{{surveymenu_entries}}".
 *
 * The followings are the available columns in table '{{surveymenu_entries}}':
 * @property integer $id
 * @property integer $menu_id
 * @property integer $priority
 * @property string $title
 * @property string $description
 * @property string $menu_title
 * @property string $menu_description
 * @property string $menu_icon
 * @property string $menu_class
 * @property string $menu_link
 * @property string $action
 * @property string $template
 * @property string $partial
 * @property string $language
 * @property string $permission
 * @property string $permissionGrade
 * @property string $classes
 * @property string $data
 * @property string $getdatamethod
 * @property string $changed_at
 * @property integer $changed_by
 * @property string $created_at
 * @property integer $created_by
 *
 * The followings are the available model relations:
 * @property Surveymenu $menu
 */
class SurveymenuEntries extends LSActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{surveymenu_entries}}';
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
			array('menu_id, priority, changed_by, created_by', 'numerical', 'integerOnly'=>true),
			array('title, menu_title, menu_icon, menu_class, menu_link, action, template, partial, permission, permissionGrade, classes, getdatamethod', 'length', 'max'=>255),
			array('description, menu_description, language, data, created_at', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, menu_id, priority, title, description, menu_title, menu_description, menu_icon, menu_class, menu_link, action, template, partial, language, permission, permissionGrade, classes, data, getdatamethod, changed_at, changed_by, created_at, created_by', 'safe', 'on'=>'search'),
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
			'menu' => array(self::BELONGS_TO, 'Surveymenu', 'menu_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'menu_id' => 'Menu',
			'priority' => 'Priority',
			'title' => 'Title',
			'description' => 'Description',
			'menu_title' => 'Menu Title',
			'menu_description' => 'Menu Description',
			'menu_icon' => 'Menu Icon',
			'menu_class' => 'Menu Class',
			'menu_link' => 'Menu link',
			'action' => 'Action',
			'template' => 'Template',
			'partial' => 'Partial',
			'language' => 'Language',
			'permission' => 'Permission',
			'permissionGrade' => 'Permission Grade',
			'classes' => 'Classes',
			'data' => 'Data',
			'getdatamethod' => 'Getdatamethod',
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
		$criteria->compare('menu_id',$this->menu_id);
		$criteria->compare('priority',$this->priority);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('menu_title',$this->menu_title,true);
		$criteria->compare('menu_description',$this->menu_description,true);
		$criteria->compare('menu_icon',$this->menu_icon,true);
		$criteria->compare('menu_class',$this->menu_class,true);
		$criteria->compare('menu_link',$this->menu_link,true);
		$criteria->compare('action',$this->action,true);
		$criteria->compare('template',$this->template,true);
		$criteria->compare('partial',$this->partial,true);
		$criteria->compare('language',$this->language,true);
		$criteria->compare('permission',$this->permission,true);
		$criteria->compare('permissionGrade',$this->permissionGrade,true);
		$criteria->compare('classes',$this->classes,true);
		$criteria->compare('data',$this->data,true);
		$criteria->compare('getdatamethod',$this->getdatamethod,true);
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
	 * @return SurveymenuEntries the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
