<?php

/**
 * This is the model class for table "main_potensi_r10".
 *
 * The followings are the available columns in table 'main_potensi_r10':
 * @property string $DESAID
 * @property string $R1001A
 * @property string $R1001B1
 * @property string $R1001B2
 * @property string $R1002B
 * @property string $R1003A
 * @property string $R1003B
 * @property string $R1003D
 * @property string $R1004AK2
 * @property string $R1004AK3
 * @property string $R1004AK4
 * @property string $R1004AK5
 * @property string $R1004BK2
 * @property string $R1004BK3
 * @property string $R1004BK4
 * @property string $R1004BK5
 * @property string $R1004CK2
 * @property string $R1004CK3
 * @property string $R1004CK4
 * @property string $R1004CK5
 *
 * The followings are the available model relations:
 * @property Desa $dESA
 * @property KeteranganAdaTidakada $r1001B2
 */
class PotensiR10 extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return PotensiR10 the static model class
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
		return 'main_potensi_r10';
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
			array('DESAID', 'length', 'max'=>10),
			array('R1001A, R1001B1, R1002B, R1003A, R1003B, R1003D, R1004AK2, R1004AK3, R1004AK4, R1004AK5, R1004BK2, R1004BK3, R1004BK4, R1004BK5, R1004CK2, R1004CK3, R1004CK4, R1004CK5', 'length', 'max'=>255),
			array('R1001B2', 'length', 'max'=>1),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('DESAID, R1001A, R1001B1, R1001B2, R1002B, R1003A, R1003B, R1003D, R1004AK2, R1004AK3, R1004AK4, R1004AK5, R1004BK2, R1004BK3, R1004BK4, R1004BK5, R1004CK2, R1004CK3, R1004CK4, R1004CK5', 'safe', 'on'=>'search'),
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
			'r1001B2' => array(self::BELONGS_TO, 'KeteranganAdaTidakada', 'R1001B2'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'DESAID' => 'Nama Desa',
			'R1001A' => 'R1001 A',
			'R1001B1' => 'R1001 B1',
			'R1001B2' => 'Apakah desa/kelurahan dapat dilalui kendaraan bermotor roda 4 atau lebih sepanjang tahun',
			'R1002B' => 'R1002 B',
			'R1003A' => 'R1003 A',
			'R1003B' => 'R1003 B',
			'R1003D' => 'R1003 D',
			'R1004AK2' => 'R1004 Ak2',
			'R1004AK3' => 'R1004 Ak3',
			'R1004AK4' => 'R1004 Ak4',
			'R1004AK5' => 'R1004 Ak5',
			'R1004BK2' => 'R1004 Bk2',
			'R1004BK3' => 'R1004 Bk3',
			'R1004BK4' => 'R1004 Bk4',
			'R1004BK5' => 'R1004 Bk5',
			'R1004CK2' => 'R1004 Ck2',
			'R1004CK3' => 'R1004 Ck3',
			'R1004CK4' => 'R1004 Ck4',
			'R1004CK5' => 'R1004 Ck5',
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
		$criteria->compare('R1001A',$this->R1001A,true);
		$criteria->compare('R1001B1',$this->R1001B1,true);
		$criteria->compare('R1001B2',$this->R1001B2,true);
		$criteria->compare('R1002B',$this->R1002B,true);
		$criteria->compare('R1003A',$this->R1003A,true);
		$criteria->compare('R1003B',$this->R1003B,true);
		$criteria->compare('R1003D',$this->R1003D,true);
		$criteria->compare('R1004AK2',$this->R1004AK2,true);
		$criteria->compare('R1004AK3',$this->R1004AK3,true);
		$criteria->compare('R1004AK4',$this->R1004AK4,true);
		$criteria->compare('R1004AK5',$this->R1004AK5,true);
		$criteria->compare('R1004BK2',$this->R1004BK2,true);
		$criteria->compare('R1004BK3',$this->R1004BK3,true);
		$criteria->compare('R1004BK4',$this->R1004BK4,true);
		$criteria->compare('R1004BK5',$this->R1004BK5,true);
		$criteria->compare('R1004CK2',$this->R1004CK2,true);
		$criteria->compare('R1004CK3',$this->R1004CK3,true);
		$criteria->compare('R1004CK4',$this->R1004CK4,true);
		$criteria->compare('R1004CK5',$this->R1004CK5,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}