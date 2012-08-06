<?php

/**
 * This is the model class for table "keterangan_ada_tidakada".
 *
 * The followings are the available columns in table 'keterangan_ada_tidakada':
 * @property string $id
 * @property string $nama
 *
 * The followings are the available model relations:
 * @property MainPotensiR10[] $mainPotensiR10s
 * @property MainPotensiR3[] $mainPotensiR3s
 * @property MainPotensiR5[] $mainPotensiR5s
 * @property MainPotensiR5[] $mainPotensiR5s1
 * @property MainPotensiR7[] $mainPotensiR7s
 * @property MainPotensiR9[] $mainPotensiR9s
 * @property MainPotensiR9[] $mainPotensiR9s1
 * @property MainPotensiR9[] $mainPotensiR9s2
 * @property MainPotensiR9[] $mainPotensiR9s3
 * @property MainPotensiR9[] $mainPotensiR9s4
 * @property MainPotensiR9[] $mainPotensiR9s5
 */
class KeteranganAdaTidakada extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return KeteranganAdaTidakada the static model class
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
		return 'keterangan_ada_tidakada';
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
			'mainPotensiR10s' => array(self::HAS_MANY, 'MainPotensiR10', 'R1001B2'),
			'mainPotensiR3s' => array(self::HAS_MANY, 'MainPotensiR3', 'R305E3'),
			'mainPotensiR5s' => array(self::HAS_MANY, 'MainPotensiR5', 'R510A'),
			'mainPotensiR5s1' => array(self::HAS_MANY, 'MainPotensiR5', 'R505B'),
			'mainPotensiR7s' => array(self::HAS_MANY, 'MainPotensiR7', 'R713D'),
			'mainPotensiR9s' => array(self::HAS_MANY, 'MainPotensiR9', 'R903AK2'),
			'mainPotensiR9s1' => array(self::HAS_MANY, 'MainPotensiR9', 'R903BK2'),
			'mainPotensiR9s2' => array(self::HAS_MANY, 'MainPotensiR9', 'R903CK2'),
			'mainPotensiR9s3' => array(self::HAS_MANY, 'MainPotensiR9', 'R903DK2'),
			'mainPotensiR9s4' => array(self::HAS_MANY, 'MainPotensiR9', 'R903EK2'),
			'mainPotensiR9s5' => array(self::HAS_MANY, 'MainPotensiR9', 'R903FK2'),
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