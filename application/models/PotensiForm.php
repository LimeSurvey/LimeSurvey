<?php
class PotensiForm extends CFormModel {
	public $provinsiid;
	public $kabupatenid;
	public $kecamatanid;
	public $desaid;
	public $katAll;
	public $kat3;
	public $kat4;
	public $kat5;
	public $kat6;
	public $kat7;
	public $kat8;
	public $kat9;
	public $kat10;
	public $kat12;
	
	public function rules()
	{
		return array(
			array('provinsiid, kabupatenid, kecamatanid, desaid','numerical','integerOnly'=>true),
			array('katAll, kat3, kat4, kat5, kat6, kat7, kat8, kat9, kat10, kat12','boolean','allowEmpty'=>true),
			array('provinsiid, kabupatenid, kecamatanid, desaid','required'),
		);
	}
	
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'provinsiid' => 'Provinsi',
			'kabupatenid' => 'Kabupaten',
			'kecamatanid' => 'Kecamatan',
			'desaid' => 'Desa',
			'katAll'=>'All',
			'kat3'=>'III. Keterangan Umum Desa/Kelurahan',
			'kat4'=>'IV. Kependudukan dan Ketenagakerjaan',
			'kat5'=>'V. Perumahan dan Lingkungan Hidup',
			'kat6'=>'VI. Bencana Alam dan Penanganan Bencana Alam',
			'kat7'=>'VII. Pendidikan dan Kesehatan',
			'kat8'=>'VIII. Sosial dan Budaya',
			'kat9'=>'IX. Hiburan dan Olah Raga',
			'kat10'=>'X. Angkutan, Komunikasi dan Informasi',
			'kat12'=>'XII. Penggunaan Lahan',
		);
	}
	
}