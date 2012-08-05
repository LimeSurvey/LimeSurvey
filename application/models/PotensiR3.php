<?php

/**
 * This is the model class for table "main_potensi_r3".
 *
 * The followings are the available columns in table 'main_potensi_r3':
 * @property string $DESAID
 * @property string $R301
 * @property string $R302A
 * @property string $NAMA_PULAU
 * @property string $R304A
 * @property string $R305A
 * @property string $R305B
 * @property string $R305D
 * @property string $R305E1
 * @property string $R305E2A
 * @property string $R305E2B
 * @property string $R305E2C
 * @property string $R305E2D
 * @property string $R305E2E
 * @property string $R305E3
 * @property string $R306A
 * @property string $R306B
 *
 * The followings are the available model relations:
 * @property Desa $dESA
 * @property KeteranganR301 $r301
 * @property KeteranganR305a $r305A
 * @property KeteranganR305b $r305B
 * @property KeteranganYaTidak $r305D
 * @property KeteranganAdaTidakada $r305E3
 */
class PotensiR3 extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return PotensiR3 the static model class
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
		return 'main_potensi_r3';
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
			array('R301, R305A, R305B, R305D, R305E3', 'length', 'max'=>1),
			array('R302A, NAMA_PULAU, R304A, R305E1, R305E2A, R305E2B, R305E2C, R305E2D, R305E2E, R306A, R306B', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('DESAID, R301, R302A, NAMA_PULAU, R304A, R305A, R305B, R305D, R305E1, R305E2A, R305E2B, R305E2C, R305E2D, R305E2E, R305E3, R306A, R306B', 'safe', 'on'=>'search'),
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
			'r301' => array(self::BELONGS_TO, 'KeteranganR301', 'R301'),
			'r305A' => array(self::BELONGS_TO, 'KeteranganR305a', 'R305A'),
			'r305B' => array(self::BELONGS_TO, 'KeteranganR305b', 'R305B'),
			'r305D' => array(self::BELONGS_TO, 'KeteranganYaTidak', 'R305D'),
			'r305E3' => array(self::BELONGS_TO, 'KeteranganAdaTidakada', 'R305E3'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'DESAID' => 'Nama Desa',
			'R301' => 'Status Pemerintahan',
			'R302A' => 'R302 A',
			'NAMA_PULAU' => 'Nama Pulau',
			'R304A' => 'R304 A',
			'R305A' => 'Lokasi desa/kelurahan',
			'R305B' => 'Kemiringan lahan',
			'R305D' => 'Ada wilayah desa/kelurahan yang berbatasan langsung dengan laut',
			'R305E1' => 'R305 E1',
			'R305E2A' => 'R305 E2 A',
			'R305E2B' => 'R305 E2 B',
			'R305E2C' => 'R305 E2 C',
			'R305E2D' => 'R305 E2 D',
			'R305E2E' => 'R305 E2 E',
			'R305E3' => 'Hutan mangrove (misalnya: bakau, api-api, pedada, tanjang, dll) di wilayah desa/kelurahan',
			'R306A' => 'R306 A',
			'R306B' => 'R306 B',
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
		$criteria->compare('R301',$this->R301,true);
		$criteria->compare('R302A',$this->R302A,true);
		$criteria->compare('NAMA_PULAU',$this->NAMA_PULAU,true);
		$criteria->compare('R304A',$this->R304A,true);
		$criteria->compare('R305A',$this->R305A,true);
		$criteria->compare('R305B',$this->R305B,true);
		$criteria->compare('R305D',$this->R305D,true);
		$criteria->compare('R305E1',$this->R305E1,true);
		$criteria->compare('R305E2A',$this->R305E2A,true);
		$criteria->compare('R305E2B',$this->R305E2B,true);
		$criteria->compare('R305E2C',$this->R305E2C,true);
		$criteria->compare('R305E2D',$this->R305E2D,true);
		$criteria->compare('R305E2E',$this->R305E2E,true);
		$criteria->compare('R305E3',$this->R305E3,true);
		$criteria->compare('R306A',$this->R306A,true);
		$criteria->compare('R306B',$this->R306B,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}