<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
   * LimeSurvey
   * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
   * All rights reserved.
   * License: GNU/GPL License v2 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
     *	Files Purpose: lots of common functions
*/

class Quota extends LSActiveRecord
{
	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
     * @param string $class
	 * @return CActiveRecord
	 */
	public static function model($class = __CLASS__)
	{
		return parent::model($class);
	}

	/**
	 * Returns the setting's table name to be used by the model
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{quota}}';
	}

	/**
	 * Returns the relations
	 *
	 * @access public
	 * @return array
	 */
	public function relations()
	{
		$alias = $this->getTableAlias();
		return [
			'languagesettings' => [self::HAS_MANY, QuotaLanguageSetting::class, 'quotals_quota_id']
		];
	}

    /**
    * Returns this model's validation rules
    *
    */
    public function rules()
    {
        return array(
            array('name','LSYii_Validators'),// Maybe more restrictive 
            array('qlimit', 'numerical', 'integerOnly'=>true, 'min'=>'0', 'allowEmpty'=>true), 
            array('action', 'numerical', 'integerOnly'=>true, 'min'=>'1', 'max'=>'2', 'allowEmpty'=>true), // Default is null ?
            array('active', 'numerical', 'integerOnly'=>true, 'min'=>'0', 'max'=>'1', 'allowEmpty'=>true), 
            array('autoload_url', 'numerical', 'integerOnly'=>true, 'min'=>'0', 'max'=>'1', 'allowEmpty'=>true), 
        );
    }


    function deleteQuota($condition = false, $recursive = true)
    {
        if ($recursive == true)
        {
            $oResult = Quota::model()->findAllByAttributes($condition);
            foreach ($oResult as $aRow)
            {
                QuotaLanguageSetting::model()->deleteAllByAttributes(array('quotals_quota_id' => $aRow['id']));
                QuotaMember::model()->deleteAllByAttributes(array('quota_id' => $aRow['id']));
            }
        }

        Quota::model()->deleteAllByAttributes($condition);
    }


    /**
     * Returns the relations that map to dependent records.
     * Dependent records should be deleted when this object gets deleted.
     * @return string[]
     */
    public function dependentRelations() {
        return [
            'languagesettings',
        ];
    }

    /**
     * Deletes this record and all dependent records.
     * @throws CDbException
     */
    public function deleteDependent() {
        if (App()->db->getCurrentTransaction() == null) {
            $transaction = App()->db->beginTransaction();
        }
        foreach($this->dependentRelations() as $relation) {
            /** @var CActiveRecord $record */
            foreach($this->$relation as $record) {
                if (method_exists($record, 'deleteDependent')) {
                    $record->deleteDependent();
                } else {
                    $record->delete();
                }
            }
        }
        $this->delete();

        if (isset($transaction)) {
            $transaction->commit();
        }
    }
}

