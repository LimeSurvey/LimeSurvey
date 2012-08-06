<?php

/**
 * This is the model class for table "keterangan_ada_tidak".
 *
 * The followings are the available columns in table 'keterangan_ada_tidak':
 * @property string $id
 * @property string $nama
 *
 * The followings are the available model relations:
 * @property MainPotensiR5[] $mainPotensiR5s
 * @property MainPotensiR5[] $mainPotensiR5s1
 * @property MainPotensiR5[] $mainPotensiR5s2
 * @property MainPotensiR5[] $mainPotensiR5s3
 * @property MainPotensiR5[] $mainPotensiR5s4
 * @property MainPotensiR5[] $mainPotensiR5s5
 * @property MainPotensiR7[] $mainPotensiR7s
 * @property MainPotensiR7[] $mainPotensiR7s1
 * @property MainPotensiR7[] $mainPotensiR7s2
 * @property MainPotensiR7[] $mainPotensiR7s3
 * @property MainPotensiR7[] $mainPotensiR7s4
 * @property MainPotensiR7[] $mainPotensiR7s5
 * @property MainPotensiR7[] $mainPotensiR7s6
 * @property MainPotensiR7[] $mainPotensiR7s7
 * @property MainPotensiR7[] $mainPotensiR7s8
 * @property MainPotensiR7[] $mainPotensiR7s9
 * @property MainPotensiR7[] $mainPotensiR7s10
 * @property MainPotensiR7[] $mainPotensiR7s11
 */
class KeteranganAdaTidak extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return KeteranganAdaTidak the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'keterangan_ada_tidak';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, nama', 'required'),
			array('id', 'length', 'max'=>1),
			array('nama', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, nama', 'safe', 'on'=>'search'),
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
			'mainPotensiR5s' => array(self::HAS_MANY, 'MainPotensiR5', 'R506AK4'),
			'mainPotensiR5s1' => array(self::HAS_MANY, 'MainPotensiR5', 'R508A'),
			'mainPotensiR5s2' => array(self::HAS_MANY, 'MainPotensiR5', 'R511AK2'),
			'mainPotensiR5s3' => array(self::HAS_MANY, 'MainPotensiR5', 'R511BK2'),
			'mainPotensiR5s4' => array(self::HAS_MANY, 'MainPotensiR5', 'R506AK2'),
			'mainPotensiR5s5' => array(self::HAS_MANY, 'MainPotensiR5', 'R506AK3'),
			'mainPotensiR7s' => array(self::HAS_MANY, 'MainPotensiR7', 'R704IK2'),
			'mainPotensiR7s1' => array(self::HAS_MANY, 'MainPotensiR7', 'R704JK2'),
			'mainPotensiR7s2' => array(self::HAS_MANY, 'MainPotensiR7', 'R704KK2'),
			'mainPotensiR7s3' => array(self::HAS_MANY, 'MainPotensiR7', 'R704LK2'),
			'mainPotensiR7s4' => array(self::HAS_MANY, 'MainPotensiR7', 'R704AK2'),
			'mainPotensiR7s5' => array(self::HAS_MANY, 'MainPotensiR7', 'R704BK2'),
			'mainPotensiR7s6' => array(self::HAS_MANY, 'MainPotensiR7', 'R704CK2'),
			'mainPotensiR7s7' => array(self::HAS_MANY, 'MainPotensiR7', 'R704DK2'),
			'mainPotensiR7s8' => array(self::HAS_MANY, 'MainPotensiR7', 'R704EK2'),
			'mainPotensiR7s9' => array(self::HAS_MANY, 'MainPotensiR7', 'R704FK2'),
			'mainPotensiR7s10' => array(self::HAS_MANY, 'MainPotensiR7', 'R704GK2'),
			'mainPotensiR7s11' => array(self::HAS_MANY, 'MainPotensiR7', 'R704HK2'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'nama' => 'Nama',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('nama',$this->nama,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}