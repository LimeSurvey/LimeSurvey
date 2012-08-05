<?php

/**
 * This is the model class for table "main_potensi_r9".
 *
 * The followings are the available columns in table 'main_potensi_r9':
 * @property string $DESAID
 * @property string $R901A
 * @property string $R901B
 * @property string $R903AK2
 * @property string $R903AK3
 * @property string $R903BK2
 * @property string $R903BK3
 * @property string $R903CK2
 * @property string $R903CK3
 * @property string $R903DK2
 * @property string $R903DK3
 * @property string $R903EK2
 * @property string $R903EK3
 * @property string $R903FK2
 * @property string $R903FK3
 * @property string $R903GK2
 * @property string $R903GK3
 * @property string $R903HK3
 * @property string $R903IK3
 * @property string $R903JK3
 *
 * The followings are the available model relations:
 * @property Desa $dESA
 * @property KeteranganAdaTidakada $r903AK2
 * @property KeteranganAdaTidakada $r903BK2
 * @property KeteranganAdaTidakada $r903CK2
 * @property KeteranganAdaTidakada $r903DK2
 * @property KeteranganAdaTidakada $r903EK2
 * @property KeteranganAdaTidakada $r903FK2
 */
class PotensiR9 extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return PotensiR9 the static model class
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
		return 'main_potensi_r9';
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
			array('R901A, R901B, R903AK3, R903BK3, R903CK3, R903DK3, R903EK3, R903FK3, R903GK2, R903GK3, R903HK3, R903IK3, R903JK3', 'length', 'max'=>255),
			array('R903AK2, R903BK2, R903CK2, R903DK2, R903EK2, R903FK2', 'length', 'max'=>1),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('DESAID, R901A, R901B, R903AK2, R903AK3, R903BK2, R903BK3, R903CK2, R903CK3, R903DK2, R903DK3, R903EK2, R903EK3, R903FK2, R903FK3, R903GK2, R903GK3, R903HK3, R903IK3, R903JK3', 'safe', 'on'=>'search'),
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
			'r903AK2' => array(self::BELONGS_TO, 'KeteranganAdaTidakada', 'R903AK2'),
			'r903BK2' => array(self::BELONGS_TO, 'KeteranganAdaTidakada', 'R903BK2'),
			'r903CK2' => array(self::BELONGS_TO, 'KeteranganAdaTidakada', 'R903CK2'),
			'r903DK2' => array(self::BELONGS_TO, 'KeteranganAdaTidakada', 'R903DK2'),
			'r903EK2' => array(self::BELONGS_TO, 'KeteranganAdaTidakada', 'R903EK2'),
			'r903FK2' => array(self::BELONGS_TO, 'KeteranganAdaTidakada', 'R903FK2'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'DESAID' => 'Nama Desa',
			'R901A' => 'R901 A',
			'R901B' => 'R901 B',
			'R903AK2' => 'Apakah ada Lapangan sepak bola',
			'R903AK3' => 'R903 Ak3',
			'R903BK2' => 'Apakah ada Lapangan bola voli',
			'R903BK3' => 'R903 Bk3',
			'R903CK2' => 'Apakah ada Lapangan bulu tangkis',
			'R903CK3' => 'R903 Ck3',
			'R903DK2' => 'Apakah ada Lapangan bola basket',
			'R903DK3' => 'R903 Dk3',
			'R903EK2' => 'Apakah ada Lapangan tenis (lapangan)',
			'R903EK3' => 'R903 Ek3',
			'R903FK2' => 'Apakah ada Lapangan futsal',
			'R903FK3' => 'R903 Fk3',
			'R903GK2' => 'R903 Gk2',
			'R903GK3' => 'R903 Gk3',
			'R903HK3' => 'R903 Hk3',
			'R903IK3' => 'R903 Ik3',
			'R903JK3' => 'R903 Jk3',
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
		$criteria->compare('R901A',$this->R901A,true);
		$criteria->compare('R901B',$this->R901B,true);
		$criteria->compare('R903AK2',$this->R903AK2,true);
		$criteria->compare('R903AK3',$this->R903AK3,true);
		$criteria->compare('R903BK2',$this->R903BK2,true);
		$criteria->compare('R903BK3',$this->R903BK3,true);
		$criteria->compare('R903CK2',$this->R903CK2,true);
		$criteria->compare('R903CK3',$this->R903CK3,true);
		$criteria->compare('R903DK2',$this->R903DK2,true);
		$criteria->compare('R903DK3',$this->R903DK3,true);
		$criteria->compare('R903EK2',$this->R903EK2,true);
		$criteria->compare('R903EK3',$this->R903EK3,true);
		$criteria->compare('R903FK2',$this->R903FK2,true);
		$criteria->compare('R903FK3',$this->R903FK3,true);
		$criteria->compare('R903GK2',$this->R903GK2,true);
		$criteria->compare('R903GK3',$this->R903GK3,true);
		$criteria->compare('R903HK3',$this->R903HK3,true);
		$criteria->compare('R903IK3',$this->R903IK3,true);
		$criteria->compare('R903JK3',$this->R903JK3,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}