<?php

/**
 * This is the model class for table "{{tutorials}}".
 *
 * The followings are the available columns in table '{{tutorials}}':
 * @property integer $tid
 * @property string $name
 * @property string $description
 * @property integer $active
 * @property string $permission
 * @property string $permission_grade
 *
 * The followings are the available model relations:
 * @property TutorialEntry[] $tutorialEntries
 */
class Tutorials extends LSActiveRecord
{
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{tutorials}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, description, active, permission, permission_grade', 'required'),
            array('active', 'numerical', 'integerOnly'=>true),
            array('name, permission, permission_grade', 'length', 'max'=>128),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('tid, name, description, active, permission, permission_grade', 'safe', 'on'=>'search'),
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
            'tutorialEntryRelation' => array(self::HAS_MANY, 'TutorialEntryRelation', 'tid', 'together' => true),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'tid' => 'Tutorial ID',
            'name' => 'Name',
            'description' => 'Description',
            'active' => 'Active',
            'permission' => 'Permission',
            'permission_grade' => 'Permission Grade',
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

        $criteria = new CDbCriteria;

        $criteria->compare('tid', $this->tid);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('active', $this->active);
        $criteria->compare('permission', $this->permission, true);
        $criteria->compare('permission_grade', $this->permission_grade, true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    public function getPrebuilt($prebuiltName)
    {
        if (isset($this->preBuiltPackage[$prebuiltName])) {
            return $this->preBuiltPackage[$prebuiltName];
        }
        return [];
    }

    public function getTutorialDataArray($tutorialName){

        if($this->tid === null) { return []; }
        $aTutorialEntryRelations =  TutorialEntryRelation::model()->findAll('tid=:tid', [':tid'=>$this->tid]);
        $aSteps = [];
        foreach ($aTutorialEntryRelations as $oTutorialMapEntry) {
            $oTutorialEntry = $oTutorialMapEntry->tutorialEntry;
            $aSteps[] = $oTutorialEntry->getStepFromEntry();            
        }

        $aTutorialData = json_decode($this->settings,true);
        $aTutorialData['steps'] = $aSteps;

        return $aTutorialData;
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Tutorials the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var Tutorials $model */
        $model = parent::model($className);
        return $model;
    }
}
