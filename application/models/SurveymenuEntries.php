<?php

/**
 * This is the model class for table "{{surveymenu_entries}}".
 *
 * The followings are the available columns in table '{{surveymenu_entries}}':
 * @property integer $id
 * @property integer $menu_id
 * @property integer $user_id
 * @property integer $order
 * @property string $title
 * @property string $name
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
 * @property string $permission_grade
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
			array('menu_id, user_id, order, changed_by, created_by', 'numerical', 'integerOnly'=>true),
			array('title, menu_title, menu_icon, menu_icon_type, menu_class, menu_link, action, template, partial, permission, permission_grade, classes, getdatamethod', 'length', 'max'=>255),
			array('name, menu_description, language, data, created_at', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, menu_id, user_id, order, title, name, menu_title, menu_description, menu_icon, menu_icon_type, menu_class, menu_link, action, template, partial, language, permission, permission_grade, classes, data, getdatamethod, changed_at, changed_by, created_at, created_by', 'safe', 'on'=>'search'),
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
			'user' => array(self::BELONGS_TO, 'Users', 'uid'),
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
			'user_id' => 'User',
			'order' => 'Order',
			'title' => 'Title',
			'name' => 'name',
			'menu_title' => 'Menu title',
			'menu_description' => 'Menu name',
			'menu_icon' => 'Menu icon',
			'menu_icon_type' => 'Menu icon type',
			'menu_class' => 'Menu class',
			'menu_link' => 'Menu link',
			'action' => 'Action',
			'template' => 'Template',
			'partial' => 'Partial',
			'language' => 'Language',
			'permission' => 'Permission',
			'permission_grade' => 'Permission grade',
			'classes' => 'Classes',
			'data' => 'Data',
			'getdatamethod' => 'Get data method',
			'changed_at' => 'Changed At',
			'changed_by' => 'Changed By',
			'created_at' => 'Created At',
			'created_by' => 'Created By',
		);
	}

	public static function returnCombinedMenuLink($data){
		if($data->menu_link){
			return $data->menu_link;
		} else {
			return gt('Action: ').$data->action.', <br/>'
			.gt('Template: ').$data->template.', <br/>'
			.gt('Partial: ').$data->partial;
		}
	}

	public static function returnMenuIcon($data){
		if($data->menu_icon_type == 'fontawesome'){
			return "<i class='fa fa-".$data->menu_icon."'></i>";
		} else if($data->menu_icon_type == 'image'){
			return '<img width="60px" src="'.$data->menu_icon.'" />';
		} else {
			return $data->menu_icon_type.'|'.$data->menu_icon;
		}
	}

	public function getMenuIdOptions (){
		$oSurveymenus = Surveymenu::model()->findAll('id != 1',[]);
		$options = [];
		foreach($oSurveymenus as $oSurveymenu){
			//$options[] = "<option value='".$oSurveymenu->id."'>".$oSurveymenu->title."</option>";
			$options[$oSurveymenu->id] = $oSurveymenu->title;
		}
		//return join('\n',$options);
		return $options;
	}

	public function getMenuIconTypeOptions (){
		return [
			'fontawesome'	=> gT('Fontawesome icon'),
			'image'			=> gT('Image'),
		];
		// return "<option value='fontawesome'>".gT("FontAwesome icon")."</option>"
		// 		."<option value='image'>".gT('Image')."</option>";
	}
	
	/**
     * @return array
     */
    public function getColumns(){
        $cols = array(
			array(
			'name' => 'id',
			'value' => '\'<input type="checkbox" name="selectMenuToEdit" class="action_selectthisentry" value="\'.$data->id.\'" />\'',
			'type' => 'raw'
			),
			array(
				'name' => 'title',
				'type' => 'raw'
			),
			array(
				'name' => 'name',
			),
			array(
				'name' => 'order',
			),
			array(
				'name' => 'level',
			),
			array(
				'name' => 'menu_title',
			),
			array(
				'name' => 'menu_description',
			),
			array(
				'name' => 'menu_icon',
				'value' => 'SurveymenuEntries::returnMenuIcon($data)',
				'type' => 'raw'
			),
			array(
				'name' => 'menu_class',
			),
			array(
				'name' => 'menu_link',
				'value' => 'SurveymenuEntries::returnCombinedMenuLink($data)',
				'type' => 'raw'
			),
			array(
				'name' => 'language',
			),
			array(
				'name' => 'permission',
				'value' => '$data->permission ? $data->permission."<br/> => ". $data->permission_grade : ""',
				'type' => 'raw'
			),
			array(
				'name' => 'classes',
				'htmlOptions'=>array('style'=>'white-space: prewrap;'),
				'headerHtmlOptions'=>array('style'=>'white-space: prewrap;'),
			),
			array(
				'name' => 'data',
				'value' => '$data->data ? "<i class=\'fa fa-information bigIcons\' title=\'".$data->data."\'></i>" 
				: ( $data->getdatamethod ? gT("Get data method:")."<br/>".$data->getdatamethod : "")',
				'type' => 'raw'
			),
			array(
				'name' => 'menu_id',
				'value' => '$data->menu->title',
			),
			array(
				'name' => 'user_id',
				'value' => '$data->user_id ? $data->user->full_name : "<i class=\'fa fa-minus\'></i>"',
				'type' => 'raw'
			)
		);

		return $cols;
	}

	/**
     * @return array
     */
    public function getShortListColumns(){
        $cols = array(
			array(
			'name' => 'id',
			),
			array(
				'name' => 'title',
				'type' => 'raw'
			),
			array(
				'name' => 'name',
			),
			array(
				'name' => 'order',
			),
			array(
				'header' => gT('Menu'),
				'value' => ''
				.'"<a class=\"".$data->menu_class."\" title=\"".$data->menu_description."\" data-toggle="tooltip" >'
				.'".SurveymenuEntries::returnMenuIcon($data)." ".$data->menu_title."</a>"',
				'type' => 'raw'
			),
			array(
				'name' => 'menu_link',
				'value' => 'SurveymenuEntries::returnCombinedMenuLink($data)',
				'type' => 'raw'
			),
			array(
				'name' => 'language',
			),
			array(
				'name' => 'permission',
				'value' => '$data->permission ? $data->permission."<br/> => ". $data->permission_grade : ""',
				'type' => 'raw'
			),
			array(
				'name' => 'menu_id',
				'value' => '$data->menu->title',
			)
		);

		return $cols;
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

		//Don't show main menu when not superadmin
		if(Yii::app()->getConfig('demoMode') || !Permission::model()->hasGlobalPermission('superadmin','read'))
		{
			$criteria->compare('menu_id','<> 1');
			$criteria->compare('menu_id','<> 2');
		}
		
		$criteria->compare('id',$this->id);
		$criteria->compare('menu_id',$this->menu_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('order',$this->order);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('name',$this->name,true);
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
		$criteria->compare('permission_grade',$this->permission_grade,true);
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
