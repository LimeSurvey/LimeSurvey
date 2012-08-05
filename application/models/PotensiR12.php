<?php

/**
 * This is the model class for table "main_potensi_r12".
 *
 * The followings are the available columns in table 'main_potensi_r12':
 * @property string $DESAID
 * @property integer $R1205A
 * @property integer $R1205B
 * @property integer $R1206
 * @property integer $R1207
 * @property integer $R1208
 * @property integer $R1209
 * @property integer $R1210
 *
 * The followings are the available model relations:
 * @property Desa $dESA
 */
class PotensiR12 extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return PotensiR12 the static model class
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
		return 'main_potensi_r12';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('DESAID', 'required'),
			array('R1205A, R1205B, R1206, R1207, R1208, R1209, R1210', 'numerical', 'integerOnly'=>true),
			array('DESAID', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('DESAID, R1205A, R1205B, R1206, R1207, R1208, R1209, R1210', 'safe', 'on'=>'search'),
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
			'dESA' => array(self::BELONGS_TO, 'Desa', 'DESAID'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'DESAID' => 'Nama Desa',
			'R1205A' => 'Jumlah pasar dengan bangunan permanen/semi permanen',
			'R1205B' => 'Jika tidak ada pasar, maka jarak ke pasar terdekat adalah (km)',
			'R1206' => 'Jumlah lokasi pasar tanpa bangunan (termasuk pasar terapung)',
			'R1207' => 'Jumlah minimarket di desa/kelurahan',
			'R1208' => 'Jumlah toko/warung kelontong di desa/kelurahan',
			'R1209' => 'Jumlah warung/kedai manakan minuman di desa/kelurahan',
			'R1210' => 'Jumlah restoran/rumah makan di desa/kelurahan',
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

		$criteria->compare('DESAID',$this->DESAID,true);
		$criteria->compare('R1205A',$this->R1205A);
		$criteria->compare('R1205B',$this->R1205B);
		$criteria->compare('R1206',$this->R1206);
		$criteria->compare('R1207',$this->R1207);
		$criteria->compare('R1208',$this->R1208);
		$criteria->compare('R1209',$this->R1209);
		$criteria->compare('R1210',$this->R1210);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}