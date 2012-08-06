<?php
class CompareForm extends CFormModel {
	public $provinsiid1;
	public $kabupatenid1;
	public $kecamatanid1;
	public $provinsiid2;
	public $kabupatenid2;
	public $kecamatanid2;
	public $provinsiid3;
	public $kabupatenid3;
	public $kecamatanid3;
	public $desaid1;
	public $desaid2;
	public $desaid3;
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
			array('provinsiid1, kabupatenid1, kecamatanid1','numerical','integerOnly'=>true),			
			array('provinsiid2, kabupatenid2, kecamatanid2','numerical','integerOnly'=>true),	
			array('provinsiid3, kabupatenid3, kecamatanid3','numerical','integerOnly'=>true),			
			array('katAll, kat3, kat4, kat5, kat6, kat7, kat8, kat9, kat10, kat12','boolean','allowEmpty'=>true),
			array('provinsiid1, kabupatenid1, kecamatanid1, desaid1','required'),
			array('desaid2, desaid3','default','setOnEmpty'=>true, 'value'=>0),
		);
	}
	
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'provinsiid1' => 'Provinsi',
			'kabupatenid1' => 'Kabupaten',
			'kecamatanid1' => 'Kecamatan',
			'provinsiid2' => 'Provinsi',
			'kabupatenid2' => 'Kabupaten',
			'kecamatanid2' => 'Kecamatan',
			'provinsiid3' => 'Provinsi',
			'kabupatenid3' => 'Kabupaten',
			'kecamatanid3' => 'Kecamatan',
			'desaid1' => 'Desa',
			'desaid2' => 'Desa',
			'desaid3' => 'Desa',
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