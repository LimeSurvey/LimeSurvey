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
   *    Files Purpose: lots of common functions
*/

/**
 * Class Quota
 *
 * @property integer $id
 * @property integer $sid
 * @property string $name
 * @property integer $qlimit
 * @property integer $active
 * @property integer $action
 * @property integer $autoload_url
 *
 * @property QuotaLanguageSetting[] $languagesettings Indexed by language code
 */
class Quota extends LSActiveRecord
{

    /* Default attributes */
    public $active=1;

    /**
     * @inheritdoc
     * @return Quota
     */
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{quota}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
    }

    /** @inheritdoc */
    public function relations()
    {
        $alias = $this->getTableAlias();
        return array(
            'languagesettings' => array(self::HAS_MANY, 'QuotaLanguageSetting', '',
                'on' => "$alias.id = languagesettings.quotals_quota_id"),
        );
    }

    /** @inheritdoc */
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

    /**
     * @param array $data
     * @return bool|int
     */
    public function insertRecords($data)
    {
        $quota = new self;
        foreach ($data as $k => $v) {
            $quota->$k = $v;
        }
        try {
            $quota->save();
            return $quota->id;
        }
        catch(Exception $e) {
            return false;
        }
    }

    /**
     * @param mixed|bool $condition
     * @param bool $recursive
     */
    function deleteQuota($condition = false, $recursive = true)
    {
        if ($recursive == true) {
            $oResult = Quota::model()->findAllByAttributes($condition);
            foreach ($oResult as $aRow) {
                QuotaLanguageSetting::model()->deleteAllByAttributes(array('quotals_quota_id' => $aRow['id']));
                QuotaMember::model()->deleteAllByAttributes(array('quota_id' => $aRow['id']));
            }
        }

        Quota::model()->deleteAllByAttributes($condition);
    }
}
