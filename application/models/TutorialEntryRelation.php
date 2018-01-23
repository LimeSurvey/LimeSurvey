<?php

/**
 * This is the model class for table "{{tutorial_entry_groups}}".
 *
 * The followings are the available columns in table '{{tutorial_entry_groups}}':
 * @property integer $teid
 * @property integer $tid
 * @property integer $uid
 * @property integer $sid
 */
class TutorialEntryRelation extends LSActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{tutorial_entry_relation}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('teid, tid', 'required'),
            array('teid, tid, uid, sid', 'numerical', 'integerOnly'=>true),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('teid, tid, uid, sid', 'safe', 'on'=>'search'),
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
            'tutorials' => array(self::HAS_ONE, 'Tutorial', 'tid', 'together' => true),
            'tutorialEntry' => array(self::HAS_ONE, 'TutorialEntry', 'teid', 'order'=>'ordering ASC', 'together' => true),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'teid' => 'Tutorial Entry ID',
            'tid' => 'Tutorial ID',
            'uid' => 'User ID',
            'sid' => 'Survey ID',
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

        $criteria->compare('teid', $this->teid);
        $criteria->compare('tid', $this->tid);
        $criteria->compare('uid', $this->uid);
        $criteria->compare('sid', $this->sid);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return TutorialEntryRelation the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
