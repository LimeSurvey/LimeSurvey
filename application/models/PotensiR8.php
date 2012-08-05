<?php

/**
 * This is the model class for table "main_potensi_r8".
 *
 * The followings are the available columns in table 'main_potensi_r8':
 * @property string $DESAID
 * @property string $R803A
 * @property string $R803B
 * @property string $R803C
 * @property string $R803D
 * @property string $R803E
 * @property string $R803F
 * @property string $R803G
 * @property string $R803H
 * @property integer $R805A
 * @property integer $R805B
 * @property integer $R805C
 * @property integer $R805D
 * @property integer $R805E
 * @property integer $R805F
 * @property integer $R805G
 * @property integer $R805H
 * @property integer $R805I
 *
 * The followings are the available model relations:
 * @property Desa $dESA
 */
class PotensiR8 extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return PotensiR8 the static model class
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
		return 'main_potensi_r8';
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
			array('R805A, R805B, R805C, R805D, R805E, R805F, R805G, R805H, R805I', 'numerical', 'integerOnly'=>true),
			array('DESAID', 'length', 'max'=>10),
			array('R803A, R803B, R803C, R803D, R803E, R803F, R803G, R803H', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('DESAID, R803A, R803B, R803C, R803D, R803E, R803F, R803G, R803H, R805A, R805B, R805C, R805D, R805E, R805F, R805G, R805H, R805I', 'safe', 'on'=>'search'),
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
			'R803A' => 'R803 A',
			'R803B' => 'R803 B',
			'R803C' => 'R803 C',
			'R803D' => 'R803 D',
			'R803E' => 'R803 E',
			'R803F' => 'R803 F',
			'R803G' => 'R803 G',
			'R803H' => 'R803 H',
			'R805A' => 'Jumlah tunanetra (buta)',
			'R805B' => 'Jumlah tunarungu (tuli)',
			'R805C' => 'Jumlah tunawicara (bisu)',
			'R805D' => 'Jumlah tunarungu-wicara (tuli-bisu)',
			'R805E' => 'Jumlah tunadaksa (cacat tubuh)',
			'R805F' => 'Jumlah tunagrahita (cacat mental)',
			'R805G' => 'Jumlah tunalaras (eks sakit jiwa)',
			'R805H' => 'Jumlah cacat eks sakit kustaJumlah cacat eks sakit kusta',
			'R805I' => 'Jumlah cacat ganda (cacat fisik-mental)',
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
		$criteria->compare('R803A',$this->R803A,true);
		$criteria->compare('R803B',$this->R803B,true);
		$criteria->compare('R803C',$this->R803C,true);
		$criteria->compare('R803D',$this->R803D,true);
		$criteria->compare('R803E',$this->R803E,true);
		$criteria->compare('R803F',$this->R803F,true);
		$criteria->compare('R803G',$this->R803G,true);
		$criteria->compare('R803H',$this->R803H,true);
		$criteria->compare('R805A',$this->R805A);
		$criteria->compare('R805B',$this->R805B);
		$criteria->compare('R805C',$this->R805C);
		$criteria->compare('R805D',$this->R805D);
		$criteria->compare('R805E',$this->R805E);
		$criteria->compare('R805F',$this->R805F);
		$criteria->compare('R805G',$this->R805G);
		$criteria->compare('R805H',$this->R805H);
		$criteria->compare('R805I',$this->R805I);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}