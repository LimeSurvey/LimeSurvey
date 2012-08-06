<?php

/**
 * This is the model class for table "main_kabupaten".
 *
 * The followings are the available columns in table 'main_kabupaten':
 * @property string $id
 * @property string $provinsiid
 * @property string $nama
 *
 * The followings are the available model relations:
 * @property Provinsi $provinsi
 * @property Kecamatan[] $kecamatans
 */
class Kabupaten extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Kabupaten the static model class
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
		return 'main_kabupaten';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, provinsiid, nama', 'required'),
			array('id', 'length', 'max'=>4),
			array('provinsiid', 'length', 'max'=>2),
			array('nama', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, provinsiid, nama', 'safe', 'on'=>'search'),
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
			'provinsi' => array(self::BELONGS_TO, 'Provinsi', 'provinsiid'),
			'kecamatans' => array(self::HAS_MANY, 'Kecamatan', 'kabupatenid'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'provinsiid' => 'Provinsiid',
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
		$criteria->compare('provinsiid',$this->provinsiid,true);
		$criteria->compare('nama',$this->nama,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}