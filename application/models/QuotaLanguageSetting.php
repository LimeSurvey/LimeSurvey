<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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

/**
 * @property integer $quotals_id ID (primary key)
 * @property integer $quotals_quota_id Quota ID
 * @property string $quotals_language Language code eg: 'en'
 * @property string $quotals_name Quota display name for this language
 * @property string $quotals_message Quota message for this language
 * @property string $quotals_url Quota end-URL for this language
 * @property string $quotals_urldescrip Quota end-URL description for this language
 *
 * @property Quota $quota
 */
class QuotaLanguageSetting extends LSActiveRecord
{
    /**
     * @inheritdoc
     * @return QuotaLanguageSetting
     */
    public static function model($class = __CLASS__)
    {
        /** @var QuotaLanguageSetting $model */
        $model = parent::model($class);
        return $model;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{quota_languagesettings}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'quotals_id';
    }

    /**
     * Returns the relations
     *
     * @access public
     * @return array
     */
    public function relations()
    {
        return array(
            'quota' => array(self::BELONGS_TO, 'Quota', 'quotals_quota_id'),
        );
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('quotals_message', 'required'),
            array('quotals_name', 'LSYii_Validators'), // No access in quota editor, set to quota.name
            array('quotals_message', 'LSYii_Validators'),
            array('quotals_url', 'LSYii_Validators', 'isUrl'=>true),
            array('quotals_urldescrip', 'LSYii_Validators'),
            array('quotals_url', 'filter', 'filter'=>'trim'),
            array('quotals_url', 'urlValidator'),
        );
    }
    public function urlValidator()
    {
        if ($this->quota->autoload_url == 1 && !$this->quotals_url) {
            $this->addError('quotals_url', gT('URL must be set if autoload URL is turned on!'));
        }
    }

    public function attributeLabels()
    {
        return array(
            'quotals_message'=> gT("Quota message:"),
            'quotals_url'=> gT("URL:"),
            'quotals_urldescrip'=> gT("URL Description:"),
        );
    }

    public function insertRecords($data)
    {
        $settings = new self;
        foreach ($data as $k => $v) {
            if ($k === 'autoload_url'){
                $settings->quota->autoload_url = $v;
            } else {
                $settings->$k = $v;
            }
        }
        return $settings->save();
    }
}
