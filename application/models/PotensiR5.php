<?php

/**
 * This is the model class for table "main_potensi_r5".
 *
 * The followings are the available columns in table 'main_potensi_r5':
 * @property string $DESAID
 * @property string $R501A
 * @property string $R501B
 * @property string $R504
 * @property string $R505A
 * @property string $R505B
 * @property string $R506AK2
 * @property string $R506B1K2
 * @property string $R506B2K2
 * @property string $R506B3K2
 * @property string $R506B4K2
 * @property string $R506B5K2
 * @property string $R506AK3
 * @property string $R506AK4
 * @property string $R506B1K3
 * @property string $R506B1K4
 * @property string $R506B2K3
 * @property string $R506B2K4
 * @property string $R506B3K3
 * @property string $R506B3K4
 * @property string $R506B4K3
 * @property string $R506B4K4
 * @property string $R506B5K4
 * @property string $R508A
 * @property integer $R508B
 * @property integer $R508C
 * @property integer $R508D
 * @property string $R510A
 * @property integer $R510B1
 * @property integer $R510B2
 * @property integer $R510B3
 * @property string $R511AK2
 * @property string $R511AK3
 * @property string $R511AK4
 * @property string $R511BK2
 * @property string $R511BK3
 * @property string $R511BK4
 *
 * The followings are the available model relations:
 * @property Desa $dESA
 * @property KeteranganYaTidak $r506B2K2
 * @property KeteranganYaTidak $r506B1K3
 * @property KeteranganYaTidak $r506B2K3
 * @property KeteranganAdaTidak $r506AK4
 * @property KeteranganYaTidak $r506B1K4
 * @property KeteranganYaTidak $r506B2K4
 * @property KeteranganAdaTidak $r508A
 * @property KeteranganAdaTidakada $r510A
 * @property KeteranganAdaTidak $r511AK2
 * @property KeteranganR511ak3 $r511AK3
 * @property KeteranganR504 $r504
 * @property KeteranganAdaTidak $r511BK2
 * @property KeteranganR511bk3 $r511BK3
 * @property KeteranganR505a $r505A
 * @property KeteranganAdaTidakada $r505B
 * @property KeteranganAdaTidak $r506AK2
 * @property KeteranganAdaTidak $r506AK3
 * @property KeteranganYaTidak $r506B1K2
 */
class PotensiR5 extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return PotensiR5 the static model class
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
		return 'main_potensi_r5';
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
			array('R508B, R508C, R508D, R510B1, R510B2, R510B3', 'numerical', 'integerOnly'=>true),
			array('DESAID', 'length', 'max'=>10),
			array('R501A, R501B, R506B3K2, R506B4K2, R506B5K2, R506B3K3, R506B3K4, R506B4K3, R506B4K4, R506B5K4, R511AK4, R511BK4', 'length', 'max'=>255),
			array('R504, R505A, R505B, R506AK2, R506B1K2, R506B2K2, R506AK3, R506AK4, R506B1K3, R506B1K4, R506B2K3, R506B2K4, R508A, R510A, R511AK2, R511AK3, R511BK2, R511BK3', 'length', 'max'=>1),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('DESAID, R501A, R501B, R504, R505A, R505B, R506AK2, R506B1K2, R506B2K2, R506B3K2, R506B4K2, R506B5K2, R506AK3, R506AK4, R506B1K3, R506B1K4, R506B2K3, R506B2K4, R506B3K3, R506B3K4, R506B4K3, R506B4K4, R506B5K4, R508A, R508B, R508C, R508D, R510A, R510B1, R510B2, R510B3, R511AK2, R511AK3, R511AK4, R511BK2, R511BK3, R511BK4', 'safe', 'on'=>'search'),
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
			'r506B2K2' => array(self::BELONGS_TO, 'KeteranganYaTidak', 'R506B2K2'),
			'r506B1K3' => array(self::BELONGS_TO, 'KeteranganYaTidak', 'R506B1K3'),
			'r506B2K3' => array(self::BELONGS_TO, 'KeteranganYaTidak', 'R506B2K3'),
			'r506AK4' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R506AK4'),
			'r506B1K4' => array(self::BELONGS_TO, 'KeteranganYaTidak', 'R506B1K4'),
			'r506B2K4' => array(self::BELONGS_TO, 'KeteranganYaTidak', 'R506B2K4'),
			'r508A' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R508A'),
			'r510A' => array(self::BELONGS_TO, 'KeteranganAdaTidakada', 'R510A'),
			'r511AK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R511AK2'),
			'r511AK3' => array(self::BELONGS_TO, 'KeteranganR511ak3', 'R511AK3'),
			'r504' => array(self::BELONGS_TO, 'KeteranganR504', 'R504'),
			'r511BK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R511BK2'),
			'r511BK3' => array(self::BELONGS_TO, 'KeteranganR511bk3', 'R511BK3'),
			'r505A' => array(self::BELONGS_TO, 'KeteranganR505a', 'R505A'),
			'r505B' => array(self::BELONGS_TO, 'KeteranganAdaTidakada', 'R505B'),
			'r506AK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R506AK2'),
			'r506AK3' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R506AK3'),
			'r506B1K2' => array(self::BELONGS_TO, 'KeteranganYaTidak', 'R506B1K2'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'DESAID' => 'Nama Desa',
			'R501A' => 'R501 A',
			'R501B' => 'R501 B',
			'R504' => 'Tempat buang air besar sebagian besar keluarga ',
			'R505A' => 'Tempat buang sampah sebagian besar keluarga',
			'R505B' => 'Tempat penampungan sampah sementara (TPS) ',
			'R506AK2' => 'Apakah ada SUNGAI di wilayah desa/kelurahan',
			'R506B1K2' => 'Apakah SUNGAI digunakan untuk MANDI',
			'R506B2K2' => 'Apakah SUNGAI digungakan untuk MINUM',
			'R506B3K2' => 'R506 B3 K2',
			'R506B4K2' => 'R506 B4 K2',
			'R506B5K2' => 'R506 B5 K2',
			'R506AK3' => 'Apakah ada SALURAN IRIGASI di desa/kelurahan',
			'R506AK4' => 'Apakah ada WADUK/SITU di wilayah desa/kelurahan',
			'R506B1K3' => 'Apakah SALURAN IRIGASI digungakan untuk MANDI',
			'R506B1K4' => 'Apakah WADUK/SITU digungakan untuk MANDI',
			'R506B2K3' => 'Apakah SALURAN IRIGASI digungakan untuk MINUM',
			'R506B2K4' => 'Apakah WADUK/DANAU digungakan untuk MINUM',
			'R506B3K3' => 'R506 B3 K3',
			'R506B3K4' => 'R506 B3 K4',
			'R506B4K3' => 'R506 B4 K3',
			'R506B4K4' => 'R506 B4 K4',
			'R506B5K4' => 'R506 B5 K4',
			'R508A' => 'Jika ada sungai, apakah ada permukiman di bantaran sungai',
			'R508B' => 'Jumlah permukiman di bantaran sungai',
			'R508C' => 'Jumlah bangunan rumah di bantaran sungai',
			'R508D' => 'Jumlah keluarga di permukiman bantaran sungai',
			'R510A' => 'Apakah ada permukiman kumuh (bangunan padat, tidak layak huni, sanitasi buruk)',
			'R510B1' => 'Jumlah permukiman kumuh',
			'R510B2' => 'Jumlah bangunan rumah di permukiman kumuh',
			'R510B3' => 'Jumlah keluarga di permukiman kumuh',
			'R511AK2' => 'Apakah ada pencemaran AIR',
			'R511AK3' => 'Sumber pencemaran lingkungan yang paling utama',
			'R511AK4' => 'R511 Ak4',
			'R511BK2' => 'Apakah ada pencemaran TANAH',
			'R511BK3' => 'Sumber pencemaran lingkungan yang paling utama',
			'R511BK4' => 'R511 Bk4',
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
		$criteria->compare('R501A',$this->R501A,true);
		$criteria->compare('R501B',$this->R501B,true);
		$criteria->compare('R504',$this->R504,true);
		$criteria->compare('R505A',$this->R505A,true);
		$criteria->compare('R505B',$this->R505B,true);
		$criteria->compare('R506AK2',$this->R506AK2,true);
		$criteria->compare('R506B1K2',$this->R506B1K2,true);
		$criteria->compare('R506B2K2',$this->R506B2K2,true);
		$criteria->compare('R506B3K2',$this->R506B3K2,true);
		$criteria->compare('R506B4K2',$this->R506B4K2,true);
		$criteria->compare('R506B5K2',$this->R506B5K2,true);
		$criteria->compare('R506AK3',$this->R506AK3,true);
		$criteria->compare('R506AK4',$this->R506AK4,true);
		$criteria->compare('R506B1K3',$this->R506B1K3,true);
		$criteria->compare('R506B1K4',$this->R506B1K4,true);
		$criteria->compare('R506B2K3',$this->R506B2K3,true);
		$criteria->compare('R506B2K4',$this->R506B2K4,true);
		$criteria->compare('R506B3K3',$this->R506B3K3,true);
		$criteria->compare('R506B3K4',$this->R506B3K4,true);
		$criteria->compare('R506B4K3',$this->R506B4K3,true);
		$criteria->compare('R506B4K4',$this->R506B4K4,true);
		$criteria->compare('R506B5K4',$this->R506B5K4,true);
		$criteria->compare('R508A',$this->R508A,true);
		$criteria->compare('R508B',$this->R508B);
		$criteria->compare('R508C',$this->R508C);
		$criteria->compare('R508D',$this->R508D);
		$criteria->compare('R510A',$this->R510A,true);
		$criteria->compare('R510B1',$this->R510B1);
		$criteria->compare('R510B2',$this->R510B2);
		$criteria->compare('R510B3',$this->R510B3);
		$criteria->compare('R511AK2',$this->R511AK2,true);
		$criteria->compare('R511AK3',$this->R511AK3,true);
		$criteria->compare('R511AK4',$this->R511AK4,true);
		$criteria->compare('R511BK2',$this->R511BK2,true);
		$criteria->compare('R511BK3',$this->R511BK3,true);
		$criteria->compare('R511BK4',$this->R511BK4,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}