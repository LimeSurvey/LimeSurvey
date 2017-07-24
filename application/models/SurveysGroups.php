<?php

/**
 * This is the model class for table "{{surveys_groups}}".
 *
 * The followings are the available columns in table '{{surveys_groups}}':
 * @property integer $gsid
 * @property string $name
 * @property string $title
 * @property string $description
 * @property integer $order
 * @property integer $owner_uid
 * @property integer $parent_id
 * @property string $created
 * @property string $modified
 * @property integer $created_by
 */
class SurveysGroups extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{surveys_groups}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, order, created_by', 'required'),
            array('order, owner_uid, parent_id, created_by', 'numerical', 'integerOnly'=>true),
            array('name', 'length', 'max'=>45),
            array('title', 'length', 'max'=>100),
            array('description, created, modified', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('gsid, name, title, description, owner_uid, parent_id, created, modified, created_by', 'safe', 'on'=>'search'),
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
            'parentgroup' => array(self::BELONGS_TO, 'SurveysGroups', array('parent_id' => 'gsid'), 'together' => true),
            'owner'       => array(self::BELONGS_TO, 'User', 'owner_uid', 'together' => true),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'gsid' => 'Gsid',
            'name' => 'Name',
            'title' => 'Title',
            'description' => 'Description',
            'order' => 'Order',
            'owner_uid' => 'Owner Uid',
            'parent_id' => 'Parent',
            'created' => 'Created',
            'modified' => 'Modified',
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

        $criteria->compare('gsid',$this->gsid);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('title',$this->title,true);
        $criteria->compare('description',$this->description,true);
        $criteria->compare('order',$this->order);
        $criteria->compare('owner_uid',$this->owner_uid);
        $criteria->compare('parent_id',$this->parent_id);
        $criteria->compare('created',$this->created,true);
        $criteria->compare('modified',$this->modified,true);
        $criteria->compare('created_by',$this->created_by);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    public function getParentTitle()
    {
        // "(gsid: ".$data->parent_id.")"." ".$data->parentgroup->title,
        if (empty($this->parent_id)){
            return "";
        }else{
            return $this->parentgroup->title;
        }
    }

    public function getHasSurveys()
    {
        $nbSurvey =  Survey::model()->countByAttributes(array("gsid"=>$this->gsid));
        return $nbSurvey > 0;
    }

    /**
     * @return string
     */
    public function getButtons()
    {
        $sDeleteUrl     = App()->createUrl("admin/surveysgroups/sa/delete", array("id"=>$this->gsid));
        $button         = '';

        if (! $this->hasSurveys){
            $button .= '<a class="btn btn-default" href="'.$sDeleteUrl.'" role="button" data-toggle="tooltip" title="'.gT('Delete survey group').'"><span class="fa fa-trash text-danger " ></span><span class="sr-only">'.gT('Delete survey group').'</span></a>';
        }

        return $button;
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return SurveysGroups the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
