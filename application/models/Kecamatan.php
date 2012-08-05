<?php

/**
 * This is the model class for table "main_kecamatan".
 *
 * The followings are the available columns in table 'main_kecamatan':
 * @property string $id
 * @property string $kabupatenid
 * @property string $nama
 *
 * The followings are the available model relations:
 * @property Desa[] $desas
 * @property Kabupaten $kabupaten
 */
class Kecamatan extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Kecamatan the static model class
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
		return 'main_kecamatan';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, kabupatenid, nama', 'required'),
			array('id', 'length', 'max'=>7),
			array('kabupatenid', 'length', 'max'=>4),
			array('nama', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, kabupatenid, nama', 'safe', 'on'=>'search'),
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
			'desas' => array(self::HAS_MANY, 'Desa', 'kecamatanid'),
			'kabupaten' => array(self::BELONGS_TO, 'Kabupaten', 'kabupatenid'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'kabupatenid' => 'Kabupatenid',
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
		$criteria->compare('kabupatenid',$this->kabupatenid,true);
		$criteria->compare('nama',$this->nama,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}