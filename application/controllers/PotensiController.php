<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * the Potensi class, this class is used to serve ajax request 
 * regarding province, district, sub-district, and village
 * @package incubatorsurvey
 * @subpackage controllers
 */
class PotensiController extends CController {	
	public function run($action = 'index')
    {
        switch ($action) {
			/**
			 * Fungsi untuk mengambil data kabupaten dari Ajax berdasarkan ID provinsi
			 */ 		
			case 'getkabupaten':
				if(!Yii::app()->request->isAjaxRequest)
				throw new CHttpException(404);
				
				$data=Kabupaten::model()->findAll(
					'provinsiid=:provinsiid',
					array(':provinsiid'=>(int)$_POST['provinsiid'])
				);
				
				$data=CHtml::listData($data, 'id', 'nama');
				foreach ($data as $value=>$nama)
				{
					echo CHtml::tag('option',array('value'=>$value), CHtml::encode($nama), true);
				}
				break;
			/**
			 * Fungsi untuk mengambil data kecamatan dari Ajax berdasarkan ID kabupaten
			 */
			case 'getkecamatan':
				if(!Yii::app()->request->isAjaxRequest)
					throw new CHttpException(404);
			
				$data=Kecamatan::model()->findAll(
					'kabupatenid=:kabupatenid',
					array(':kabupatenid'=>(int)$_POST['kabupatenid'])
				);
				
				$data=CHtml::listData($data, 'id', 'nama');
				foreach ($data as $value=>$nama)
				{
					echo CHtml::tag('option',
						array('value'=>$value), CHtml::encode($nama), true);
				}
				break;
			/**
			 * Fungsi untuk mengambil data desa dari Ajax berdasarkan ID kecamatan
			 */
			case 'getdesa':
				if(!Yii::app()->request->isAjaxRequest)
					throw new CHttpException(404);
			
				$data=Desa::model()->findAll(
					'kecamatanid=:kecamatanid',
					array(':kecamatanid'=>(int)$_POST['kecamatanid'])
				);
				
				$data=CHtml::listData($data, 'id', 'nama');
				foreach ($data as $value=>$nama)
				{
					echo CHtml::tag('option',
						array('value'=>$value), CHtml::encode($nama), true);
				} 
				break;
			case 'index' :
			
			/* simple redirect */
			default :
				header( 'Location: ./index.php/admin' ) ;
			break;
       }
	}
}