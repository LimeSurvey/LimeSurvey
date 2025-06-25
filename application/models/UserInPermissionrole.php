<?php

/**
 * This is the model class for table "{{user_in_permissionrole}}".
 *
 * The following are the available columns in table '{{user_in_permissionrole}}':
 * @property integer $ptid
 * @property integer $uid
 */
class UserInPermissionrole extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{user_in_permissionrole}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('ptid, uid', 'required'),
            array('ptid, uid', 'numerical', 'integerOnly' => true),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('ptid, uid', 'safe', 'on' => 'search'),
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
            'role' => array(self::BELONGS_TO, 'Permissiontemplates', ['ptid']),
            'user' => array(self::BELONGS_TO, 'User', 'uid'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'ptid' => 'Ptid',
            'uid' => 'Uid',
            'name' => gT('Name'),
            'description' => gT('Description')
        );
    }

    /**
     * @param integer $iUserID user id
     * @param boolean $single , if tru retuirn the 1st user role
     * @return array|array[] of UserInPermissionrole records
     */
    public function getRoleForUser($userId, $single = false)
    {
        $aRoles = self::model()->findAllByAttributes(['uid' => $userId]);
        if ($single) {
            return $aRoles[0];
        }
        return $aRoles;
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

        $criteria = new CDbCriteria();

        $criteria->compare('ptid', $this->ptid);
        $criteria->compare('uid', $this->uid);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return UserInPermissionrole the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
