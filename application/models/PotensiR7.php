<?php

/**
 * This is the model class for table "main_potensi_r7".
 *
 * The followings are the available columns in table 'main_potensi_r7':
 * @property string $DESAID
 * @property integer $R701AK2
 * @property integer $R701AK3
 * @property double $R701AK4
 * @property integer $R701BK2
 * @property integer $R701BK3
 * @property double $R701BK4
 * @property integer $R701CK2
 * @property integer $R701CK3
 * @property double $R701CK4
 * @property integer $R701DK2
 * @property integer $R701DK3
 * @property double $R701DK4
 * @property integer $R701EK2
 * @property integer $R701EK3
 * @property double $R701EK4
 * @property integer $R701FK2
 * @property integer $R701FK3
 * @property integer $R701GK2
 * @property integer $R701GK3
 * @property integer $R701HK3
 * @property integer $R701IK3
 * @property integer $R701JK3
 * @property string $R704AK2
 * @property integer $R704AK3
 * @property double $R704AK4
 * @property string $R704AK5
 * @property string $R704BK2
 * @property integer $R704BK3
 * @property double $R704BK4
 * @property string $R704BK5
 * @property string $R704CK2
 * @property integer $R704CK3
 * @property double $R704CK4
 * @property string $R704CK5
 * @property string $R704DK2
 * @property integer $R704DK3
 * @property double $R704DK4
 * @property string $R704DK5
 * @property string $R704EK2
 * @property integer $R704EK3
 * @property double $R704EK4
 * @property string $R704EK5
 * @property string $R704FK2
 * @property integer $R704FK3
 * @property double $R704FK4
 * @property string $R704FK5
 * @property string $R704GK2
 * @property integer $R704GK3
 * @property double $R704GK4
 * @property string $R704GK5
 * @property string $R704HK2
 * @property integer $R704HK3
 * @property double $R704HK4
 * @property string $R704HK5
 * @property string $R704IK2
 * @property integer $R704IK3
 * @property double $R704IK4
 * @property string $R704IK5
 * @property string $R704JK2
 * @property integer $R704JK3
 * @property string $R704KK2
 * @property integer $R704KK3
 * @property double $R704KK4
 * @property string $R704KK5
 * @property string $R704LK2
 * @property double $R704LK4
 * @property string $R704LK5
 * @property string $R705A
 * @property string $R705B
 * @property string $R705C
 * @property string $R706AK2
 * @property string $R706AK3
 * @property string $R706AK4
 * @property string $R706BK2
 * @property integer $R707A1
 * @property integer $R707A2
 * @property integer $R707B
 * @property integer $R707C
 * @property integer $R707D
 * @property integer $R707E
 * @property string $R708AK2
 * @property string $R708AK3
 * @property string $R708AK4
 * @property string $R708BK2
 * @property string $R708BK3
 * @property string $R708BK4
 * @property string $R708CK2
 * @property string $R708CK3
 * @property string $R708CK4
 * @property string $R708DK2
 * @property string $R708DK3
 * @property string $R708DK4
 * @property string $R708EK2
 * @property string $R708EK3
 * @property string $R708EK4
 * @property string $R708FK2
 * @property string $R708FK3
 * @property string $R708FK4
 * @property string $R708GK2
 * @property string $R708GK3
 * @property string $R708GK4
 * @property string $R708HK2
 * @property string $R708HK3
 * @property string $R708HK4
 * @property string $R709
 * @property string $R713A
 * @property string $R713B
 * @property integer $R713C
 * @property string $R713D
 * @property string $R713E
 *
 * The followings are the available model relations:
 * @property Desa $dESA
 * @property KeteranganAdaTidak $r704IK2
 * @property KeteranganAdaTidak $r704JK2
 * @property KeteranganAdaTidak $r704KK2
 * @property KeteranganAdaTidak $r704LK2
 * @property KeteranganR713a $r713A
 * @property KeteranganAdaTidakada $r713D
 * @property KeteranganAdaTidak $r704AK2
 * @property KeteranganAdaTidak $r704BK2
 * @property KeteranganAdaTidak $r704CK2
 * @property KeteranganAdaTidak $r704DK2
 * @property KeteranganAdaTidak $r704EK2
 * @property KeteranganAdaTidak $r704FK2
 * @property KeteranganAdaTidak $r704GK2
 * @property KeteranganAdaTidak $r704HK2
 */
class PotensiR7 extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return PotensiR7 the static model class
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
		return 'main_potensi_r7';
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
			array('R701AK2, R701AK3, R701BK2, R701BK3, R701CK2, R701CK3, R701DK2, R701DK3, R701EK2, R701EK3, R701FK2, R701FK3, R701GK2, R701GK3, R701HK3, R701IK3, R701JK3, R704AK3, R704BK3, R704CK3, R704DK3, R704EK3, R704FK3, R704GK3, R704HK3, R704IK3, R704JK3, R704KK3, R707A1, R707A2, R707B, R707C, R707D, R707E, R713C', 'numerical', 'integerOnly'=>true),
			array('R701AK4, R701BK4, R701CK4, R701DK4, R701EK4, R704AK4, R704BK4, R704CK4, R704DK4, R704EK4, R704FK4, R704GK4, R704HK4, R704IK4, R704KK4, R704LK4', 'numerical'),
			array('DESAID', 'length', 'max'=>10),
			array('R704AK2, R704BK2, R704CK2, R704DK2, R704EK2, R704FK2, R704GK2, R704HK2, R704IK2, R704JK2, R704KK2, R704LK2, R713A, R713D', 'length', 'max'=>1),
			array('R704AK5, R704BK5, R704CK5, R704DK5, R704EK5, R704FK5, R704GK5, R704HK5, R704IK5, R704KK5, R704LK5, R705A, R705B, R705C, R706AK2, R706AK3, R706AK4, R706BK2, R708AK2, R708AK3, R708AK4, R708BK2, R708BK3, R708BK4, R708CK2, R708CK3, R708CK4, R708DK2, R708DK3, R708DK4, R708EK2, R708EK3, R708EK4, R708FK2, R708FK3, R708FK4, R708GK2, R708GK3, R708GK4, R708HK2, R708HK3, R708HK4, R709, R713B, R713E', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('DESAID, R701AK2, R701AK3, R701AK4, R701BK2, R701BK3, R701BK4, R701CK2, R701CK3, R701CK4, R701DK2, R701DK3, R701DK4, R701EK2, R701EK3, R701EK4, R701FK2, R701FK3, R701GK2, R701GK3, R701HK3, R701IK3, R701JK3, R704AK2, R704AK3, R704AK4, R704AK5, R704BK2, R704BK3, R704BK4, R704BK5, R704CK2, R704CK3, R704CK4, R704CK5, R704DK2, R704DK3, R704DK4, R704DK5, R704EK2, R704EK3, R704EK4, R704EK5, R704FK2, R704FK3, R704FK4, R704FK5, R704GK2, R704GK3, R704GK4, R704GK5, R704HK2, R704HK3, R704HK4, R704HK5, R704IK2, R704IK3, R704IK4, R704IK5, R704JK2, R704JK3, R704KK2, R704KK3, R704KK4, R704KK5, R704LK2, R704LK4, R704LK5, R705A, R705B, R705C, R706AK2, R706AK3, R706AK4, R706BK2, R707A1, R707A2, R707B, R707C, R707D, R707E, R708AK2, R708AK3, R708AK4, R708BK2, R708BK3, R708BK4, R708CK2, R708CK3, R708CK4, R708DK2, R708DK3, R708DK4, R708EK2, R708EK3, R708EK4, R708FK2, R708FK3, R708FK4, R708GK2, R708GK3, R708GK4, R708HK2, R708HK3, R708HK4, R709, R713A, R713B, R713C, R713D, R713E', 'safe', 'on'=>'search'),
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
			'r704IK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R704IK2'),
			'r704JK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R704JK2'),
			'r704KK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R704KK2'),
			'r704LK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R704LK2'),
			'r713A' => array(self::BELONGS_TO, 'KeteranganR713a', 'R713A'),
			'r713D' => array(self::BELONGS_TO, 'KeteranganAdaTidakada', 'R713D'),
			'r704AK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R704AK2'),
			'r704BK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R704BK2'),
			'r704CK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R704CK2'),
			'r704DK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R704DK2'),
			'r704EK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R704EK2'),
			'r704FK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R704FK2'),
			'r704GK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R704GK2'),
			'r704HK2' => array(self::BELONGS_TO, 'KeteranganAdaTidak', 'R704HK2'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'DESAID' => 'Nama Desa',
			'R701AK2' => 'Jumlah TK Negeri',
			'R701AK3' => 'Jumlah TK Swasta',
			'R701AK4' => 'Jarak terdekat ke TK (km)',
			'R701BK2' => 'Jumlah SD Negeri',
			'R701BK3' => 'Jumlah SD Swasta',
			'R701BK4' => 'Jarak terdekat ke SD (km)',
			'R701CK2' => 'Jumlah SMP Negeri',
			'R701CK3' => 'Jumlah SMP Swasta',
			'R701CK4' => 'Jarak terdekat ke SMP (km)',
			'R701DK2' => 'Jumlah SMU Negeri',
			'R701DK3' => 'Jumlah SMU Swasta',
			'R701DK4' => 'Jarak terdekat ke SMU (km)',
			'R701EK2' => 'Jumlah SMK Negeri',
			'R701EK3' => 'Jumlah SMK Swasta',
			'R701EK4' => 'Jarak terdekat ke SMK (km)',
			'R701FK2' => 'Jumlah PT Negeri',
			'R701FK3' => 'Jumlah PT Swasta',
			'R701GK2' => 'Jumlah SLB Negeri',
			'R701GK3' => 'Jumlah SLB Swasta',
			'R701HK3' => 'Jumlah PonPes Swasta',
			'R701IK3' => 'Jumlah Madrasah diniyah Swasta',
			'R701JK3' => 'Jumlah Seminari Swasta',
			'R704AK2' => 'Apakah ada Rumah Sakit',
			'R704AK3' => 'Jumlah rumah sakit',
			'R704AK4' => 'Jarak ke rumah sakit terdekat (km)',
			'R704AK5' => 'R704 Ak5',
			'R704BK2' => 'Apakah ada Rumah Sakit bersalin',
			'R704BK3' => 'Jumlah rumah sakit bersalin',
			'R704BK4' => 'Jarak ke rumah sakit bersalin terdekat (km)',
			'R704BK5' => 'R704 Bk5',
			'R704CK2' => 'Apakah ada poliklinik/balai pengobatan',
			'R704CK3' => 'Jumlah poliklinik/balai pengobatan',
			'R704CK4' => 'Jarak ke poliklinik/balai pengobatan terdekat (km)',
			'R704CK5' => 'R704 Ck5',
			'R704DK2' => 'Apakah ada puskesmas',
			'R704DK3' => 'Jumlah puskesmas',
			'R704DK4' => 'Jarak ke puskesmas terdekat (km)',
			'R704DK5' => 'R704 Dk5',
			'R704EK2' => 'Apakah ada puskesmas pembantu',
			'R704EK3' => 'Jumlah puskesmas pembantu (km)',
			'R704EK4' => 'Jarak ke puskesmas pembantu terdekat (km)',
			'R704EK5' => 'R704 Ek5',
			'R704FK2' => 'Apakah ada tempat praktek dokter',
			'R704FK3' => 'Jumlah tempat praktek dokter',
			'R704FK4' => 'Jarak ke tempat praktek dokter terdekat (km)',
			'R704FK5' => 'R704 Fk5',
			'R704GK2' => 'Apakah ada tempat praktek bidan',
			'R704GK3' => 'Jumlah tempat praktek bidan',
			'R704GK4' => 'Jarak ke tempat praktek bidan terdekat (km)',
			'R704GK5' => 'R704 Gk5',
			'R704HK2' => 'Apakah ada poskesdes',
			'R704HK3' => 'Jumlah poskesdes',
			'R704HK4' => 'Jarak ke poskesdes terdekat (km)',
			'R704HK5' => 'R704 Hk5',
			'R704IK2' => 'Apakah ada polindes',
			'R704IK3' => 'Jumlah polindes',
			'R704IK4' => 'Jarak ke polindes terdekat (km)',
			'R704IK5' => 'R704 Ik5',
			'R704JK2' => 'Apakah ada posyandu',
			'R704JK3' => 'Jumlah posyandu',
			'R704KK2' => 'Apakah ada apotek',
			'R704KK3' => 'Jumlah apotek',
			'R704KK4' => 'Jarak ke apotek terdekat (km)',
			'R704KK5' => 'R704 Kk5',
			'R704LK2' => 'Apakah ada toko khusus obat/jamu',
			'R704LK4' => 'Jarak ke toko khusus obat/jamu terdekat (km)',
			'R704LK5' => 'R704 Lk5',
			'R705A' => 'R705 A',
			'R705B' => 'R705 B',
			'R705C' => 'R705 C',
			'R706AK2' => 'R706 Ak2',
			'R706AK3' => 'R706 Ak3',
			'R706AK4' => 'R706 Ak4',
			'R706BK2' => 'R706 Bk2',
			'R707A1' => 'Jumlah dokter pria yang tinggal di desa/kelurahan',
			'R707A2' => 'Jumlah dokter wanita yang tinggal di desa/kelurahan',
			'R707B' => 'Jumlah dokter gigi yang tinggal di desa/kelurahan',
			'R707C' => 'Jumlah bidan yang tinggal di desa/kelurahan',
			'R707D' => 'Jumlah tenaga kesehatan lainnya yang tinggal di desa/kelurahan',
			'R707E' => 'Jumlah dukun bayi yang tinggal di desa/kelurahan',
			'R708AK2' => 'R708 Ak2',
			'R708AK3' => 'R708 Ak3',
			'R708AK4' => 'R708 Ak4',
			'R708BK2' => 'R708 Bk2',
			'R708BK3' => 'R708 Bk3',
			'R708BK4' => 'R708 Bk4',
			'R708CK2' => 'R708 Ck2',
			'R708CK3' => 'R708 Ck3',
			'R708CK4' => 'R708 Ck4',
			'R708DK2' => 'R708 Dk2',
			'R708DK3' => 'R708 Dk3',
			'R708DK4' => 'R708 Dk4',
			'R708EK2' => 'R708 Ek2',
			'R708EK3' => 'R708 Ek3',
			'R708EK4' => 'R708 Ek4',
			'R708FK2' => 'R708 Fk2',
			'R708FK3' => 'R708 Fk3',
			'R708FK4' => 'R708 Fk4',
			'R708GK2' => 'R708 Gk2',
			'R708GK3' => 'R708 Gk3',
			'R708GK4' => 'R708 Gk4',
			'R708HK2' => 'R708 Hk2',
			'R708HK3' => 'R708 Hk3',
			'R708HK4' => 'R708 Hk4',
			'R709' => 'R709',
			'R713A' => 'Sumber air untuk minum/memasak sebagian besar keluarga berasal dari',
			'R713B' => 'R713 B',
			'R713C' => 'Waktu tempuh ke sumber air (menit PP)',
			'R713D' => 'Apakah ada keluarga di desa/kelurahan ini membeli air untuk mi-num/memasak',
			'R713E' => 'R713 E',
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
		$criteria->compare('R701AK2',$this->R701AK2);
		$criteria->compare('R701AK3',$this->R701AK3);
		$criteria->compare('R701AK4',$this->R701AK4);
		$criteria->compare('R701BK2',$this->R701BK2);
		$criteria->compare('R701BK3',$this->R701BK3);
		$criteria->compare('R701BK4',$this->R701BK4);
		$criteria->compare('R701CK2',$this->R701CK2);
		$criteria->compare('R701CK3',$this->R701CK3);
		$criteria->compare('R701CK4',$this->R701CK4);
		$criteria->compare('R701DK2',$this->R701DK2);
		$criteria->compare('R701DK3',$this->R701DK3);
		$criteria->compare('R701DK4',$this->R701DK4);
		$criteria->compare('R701EK2',$this->R701EK2);
		$criteria->compare('R701EK3',$this->R701EK3);
		$criteria->compare('R701EK4',$this->R701EK4);
		$criteria->compare('R701FK2',$this->R701FK2);
		$criteria->compare('R701FK3',$this->R701FK3);
		$criteria->compare('R701GK2',$this->R701GK2);
		$criteria->compare('R701GK3',$this->R701GK3);
		$criteria->compare('R701HK3',$this->R701HK3);
		$criteria->compare('R701IK3',$this->R701IK3);
		$criteria->compare('R701JK3',$this->R701JK3);
		$criteria->compare('R704AK2',$this->R704AK2,true);
		$criteria->compare('R704AK3',$this->R704AK3);
		$criteria->compare('R704AK4',$this->R704AK4);
		$criteria->compare('R704AK5',$this->R704AK5,true);
		$criteria->compare('R704BK2',$this->R704BK2,true);
		$criteria->compare('R704BK3',$this->R704BK3);
		$criteria->compare('R704BK4',$this->R704BK4);
		$criteria->compare('R704BK5',$this->R704BK5,true);
		$criteria->compare('R704CK2',$this->R704CK2,true);
		$criteria->compare('R704CK3',$this->R704CK3);
		$criteria->compare('R704CK4',$this->R704CK4);
		$criteria->compare('R704CK5',$this->R704CK5,true);
		$criteria->compare('R704DK2',$this->R704DK2,true);
		$criteria->compare('R704DK3',$this->R704DK3);
		$criteria->compare('R704DK4',$this->R704DK4);
		$criteria->compare('R704DK5',$this->R704DK5,true);
		$criteria->compare('R704EK2',$this->R704EK2,true);
		$criteria->compare('R704EK3',$this->R704EK3);
		$criteria->compare('R704EK4',$this->R704EK4);
		$criteria->compare('R704EK5',$this->R704EK5,true);
		$criteria->compare('R704FK2',$this->R704FK2,true);
		$criteria->compare('R704FK3',$this->R704FK3);
		$criteria->compare('R704FK4',$this->R704FK4);
		$criteria->compare('R704FK5',$this->R704FK5,true);
		$criteria->compare('R704GK2',$this->R704GK2,true);
		$criteria->compare('R704GK3',$this->R704GK3);
		$criteria->compare('R704GK4',$this->R704GK4);
		$criteria->compare('R704GK5',$this->R704GK5,true);
		$criteria->compare('R704HK2',$this->R704HK2,true);
		$criteria->compare('R704HK3',$this->R704HK3);
		$criteria->compare('R704HK4',$this->R704HK4);
		$criteria->compare('R704HK5',$this->R704HK5,true);
		$criteria->compare('R704IK2',$this->R704IK2,true);
		$criteria->compare('R704IK3',$this->R704IK3);
		$criteria->compare('R704IK4',$this->R704IK4);
		$criteria->compare('R704IK5',$this->R704IK5,true);
		$criteria->compare('R704JK2',$this->R704JK2,true);
		$criteria->compare('R704JK3',$this->R704JK3);
		$criteria->compare('R704KK2',$this->R704KK2,true);
		$criteria->compare('R704KK3',$this->R704KK3);
		$criteria->compare('R704KK4',$this->R704KK4);
		$criteria->compare('R704KK5',$this->R704KK5,true);
		$criteria->compare('R704LK2',$this->R704LK2,true);
		$criteria->compare('R704LK4',$this->R704LK4);
		$criteria->compare('R704LK5',$this->R704LK5,true);
		$criteria->compare('R705A',$this->R705A,true);
		$criteria->compare('R705B',$this->R705B,true);
		$criteria->compare('R705C',$this->R705C,true);
		$criteria->compare('R706AK2',$this->R706AK2,true);
		$criteria->compare('R706AK3',$this->R706AK3,true);
		$criteria->compare('R706AK4',$this->R706AK4,true);
		$criteria->compare('R706BK2',$this->R706BK2,true);
		$criteria->compare('R707A1',$this->R707A1);
		$criteria->compare('R707A2',$this->R707A2);
		$criteria->compare('R707B',$this->R707B);
		$criteria->compare('R707C',$this->R707C);
		$criteria->compare('R707D',$this->R707D);
		$criteria->compare('R707E',$this->R707E);
		$criteria->compare('R708AK2',$this->R708AK2,true);
		$criteria->compare('R708AK3',$this->R708AK3,true);
		$criteria->compare('R708AK4',$this->R708AK4,true);
		$criteria->compare('R708BK2',$this->R708BK2,true);
		$criteria->compare('R708BK3',$this->R708BK3,true);
		$criteria->compare('R708BK4',$this->R708BK4,true);
		$criteria->compare('R708CK2',$this->R708CK2,true);
		$criteria->compare('R708CK3',$this->R708CK3,true);
		$criteria->compare('R708CK4',$this->R708CK4,true);
		$criteria->compare('R708DK2',$this->R708DK2,true);
		$criteria->compare('R708DK3',$this->R708DK3,true);
		$criteria->compare('R708DK4',$this->R708DK4,true);
		$criteria->compare('R708EK2',$this->R708EK2,true);
		$criteria->compare('R708EK3',$this->R708EK3,true);
		$criteria->compare('R708EK4',$this->R708EK4,true);
		$criteria->compare('R708FK2',$this->R708FK2,true);
		$criteria->compare('R708FK3',$this->R708FK3,true);
		$criteria->compare('R708FK4',$this->R708FK4,true);
		$criteria->compare('R708GK2',$this->R708GK2,true);
		$criteria->compare('R708GK3',$this->R708GK3,true);
		$criteria->compare('R708GK4',$this->R708GK4,true);
		$criteria->compare('R708HK2',$this->R708HK2,true);
		$criteria->compare('R708HK3',$this->R708HK3,true);
		$criteria->compare('R708HK4',$this->R708HK4,true);
		$criteria->compare('R709',$this->R709,true);
		$criteria->compare('R713A',$this->R713A,true);
		$criteria->compare('R713B',$this->R713B,true);
		$criteria->compare('R713C',$this->R713C);
		$criteria->compare('R713D',$this->R713D,true);
		$criteria->compare('R713E',$this->R713E,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}