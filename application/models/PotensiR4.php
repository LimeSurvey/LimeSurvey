<?php

/**
 * This is the model class for table "main_potensi_r4".
 *
 * The followings are the available columns in table 'main_potensi_r4':
 * @property string $DESAID
 * @property integer $R401A
 * @property integer $R401B
 * @property integer $R401C
 * @property integer $R401D
 * @property integer $R401E
 * @property string $R403A
 * @property string $R403B
 *
 * The followings are the available model relations:
 * @property MainDesa $dESA
 * @property KeteranganR403a $r403A
 * @property KeteranganR403b $r403B
 */
class PotensiR4 extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return PotensiR4 the static model class
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
		return 'main_potensi_r4';
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
			array('R401A, R401B, R401C, R401D, R401E', 'numerical', 'integerOnly'=>true),
			array('DESAID', 'length', 'max'=>10),
			array('R403A, R403B', 'length', 'max'=>1),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('DESAID, R401A, R401B, R401C, R401D, R401E, R403A, R403B', 'safe', 'on'=>'search'),
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
			'r403A' => array(self::BELONGS_TO, 'KeteranganR403a', 'R403A'),
			'r403B' => array(self::BELONGS_TO, 'KeteranganR403b', 'R403B'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'DESAID' => 'Nama Desa',
			'R401A' => 'Jumlah penduduk laki-laki',
			'R401B' => 'Jumlah penduduk perempuan',
			'R401C' => 'Jumlah keluarga',
			'R401D' => 'Jumlah keluarga pertanian',
			'R401E' => 'Jumlah keluarga yang ada anggota keluarganya menjadi buruh tani',
			'R403A' => 'Sumber penghasilan utama sebagian besar penduduk ',
			'R403B' => 'Jenis komoditi/sub sektor',
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
		$criteria->compare('R401A',$this->R401A);
		$criteria->compare('R401B',$this->R401B);
		$criteria->compare('R401C',$this->R401C);
		$criteria->compare('R401D',$this->R401D);
		$criteria->compare('R401E',$this->R401E);
		$criteria->compare('R403A',$this->R403A,true);
		$criteria->compare('R403B',$this->R403B,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}