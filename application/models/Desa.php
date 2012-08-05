<?php

/**
 * This is the model class for table "main_desa".
 *
 * The followings are the available columns in table 'main_desa':
 * @property string $id
 * @property string $kecamatanid
 * @property string $nama
 *
 * The followings are the available model relations:
 * @property Kecamatan $kecamatan
 * @property PotensiR10 $potensiR10
 * @property PotensiR11 $potensiR11
 * @property PotensiR12 $potensiR12
 * @property PotensiR14 $potensiR14
 * @property PotensiR3 $potensiR3
 * @property PotensiR4 $potensiR4
 * @property PotensiR5 $potensiR5
 * @property PotensiR6 $potensiR6
 * @property PotensiR7 $potensiR7
 * @property PotensiR8 $potensiR8
 * @property PotensiR9 $potensiR9
 */
class Desa extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Desa the static model class
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
		return 'main_desa';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, kecamatanid, nama', 'required'),
			array('id', 'length', 'max'=>10),
			array('kecamatanid', 'length', 'max'=>7),
			array('nama', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, kecamatanid, nama', 'safe', 'on'=>'search'),
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
			'kecamatan' => array(self::BELONGS_TO, 'Kecamatan', 'kecamatanid'),
			'potensiR10' => array(self::HAS_ONE, 'PotensiR10', 'DESAID'),
			'potensiR11' => array(self::HAS_ONE, 'PotensiR11', 'DESAID'),
			'potensiR12' => array(self::HAS_ONE, 'PotensiR12', 'DESAID'),
			'potensiR14' => array(self::HAS_ONE, 'PotensiR14', 'DESAID'),
			'potensiR3' => array(self::HAS_ONE, 'PotensiR3', 'DESAID'),
			'potensiR4' => array(self::HAS_ONE, 'PotensiR4', 'DESAID'),
			'potensiR5' => array(self::HAS_ONE, 'PotensiR5', 'DESAID'),
			'potensiR6' => array(self::HAS_ONE, 'PotensiR6', 'DESAID'),
			'potensiR7' => array(self::HAS_ONE, 'PotensiR7', 'DESAID'),
			'potensiR8' => array(self::HAS_ONE, 'PotensiR8', 'DESAID'),
			'potensiR9' => array(self::HAS_ONE, 'PotensiR9', 'DESAID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'kecamatanid' => 'Kecamatanid',
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
		$criteria->compare('kecamatanid',$this->kecamatanid,true);
		$criteria->compare('nama',$this->nama,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}