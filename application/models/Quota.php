<?php

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
 * @property integer $id ID (primary key)
 * @property integer $sid Survey ID
 * @property string $name Quota name (max 255 chars)
 * @property integer $qlimit Quota limit
 * @property integer $active Whether quota is active (0/1)
 * @property integer $action
 * @property integer $autoload_url Whether URL is automatically redirected if quota is triggered (0/1)
 *
 * @property QuotaLanguageSetting[] $languagesettings Indexed by language code
 * @property QuotaLanguageSetting $mainLanguagesetting
 * @property QuotaLanguageSetting $currentLanguageSetting
 * @property Survey $survey
 * @property QuotaMember[] $quotaMembers
 *
 * @property integer $completeCount Count of completed interviews for this quota
 */
class Quota extends LSActiveRecord
{
    public const TERMINATE_AFTER_ALL_VISIBLE_QUOTA_QUESTION = 1;
    public const SOFT_TERMINATE_AFTER_ALL_VISIBLE_QUOTA_QUESTION = 2;
    public const TERMINATE_AFTER_ALL_QUOTA_QUESTIONS = 3;
    public const TERMINATE_AFTER_ALL_GROUPS = 4;

    /* Default attributes */
    public $active = 1;
    public $action = self::TERMINATE_AFTER_ALL_VISIBLE_QUOTA_QUESTION;

    /**
     * Returns the static model of Settings table
     *
     * @param string $className
     * @return CActiveRecord
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
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
        return array(
            'survey' => array(self::BELONGS_TO, 'Survey', 'sid'),
            'languagesettings' => array(self::HAS_MANY, 'QuotaLanguageSetting', 'quotals_quota_id', 'index' => 'quotals_language'),
            'quotaMembers' => array(self::HAS_MANY, 'QuotaMember', 'quota_id'),
        );
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('name,qlimit,action', 'required'),
            array('name', 'LSYii_Validators'), // Maybe more restrictive
            array('name', 'LSYii_NonFormulaValidator'), // Avoid CSV injection
            array('qlimit', 'numerical', 'integerOnly' => true, 'min' => '0', 'max' => 2147483647, 'allowEmpty' => true),
            array('action', 'numerical', 'integerOnly' => true, 'min' => '1', 'max' => '4', 'allowEmpty' => true), // Default is null ?
            array('active', 'numerical', 'integerOnly' => true, 'min' => '0', 'max' => '1', 'allowEmpty' => true),
            array('autoload_url', 'numerical', 'integerOnly' => true, 'min' => '0', 'max' => '1', 'allowEmpty' => true),
            array('name', 'length', 'min' => 0, 'max' => 255),
        );
    }

    public function attributeLabels()
    {
        return array(
            'name' => gT("Quota name"),
            'active' => gT("Active"),
            'qlimit' => gT("Limit"),
            'autoload_url' => gT("Autoload URL"),
            'action' => gT("Quota action"),
        );
    }

    /**
     * @param $data
     * @return bool|int
     * @deprecated at 2018-01-29 use $model->attributes = $data && $model->save()
     */
    public function insertRecords($data)
    {
        $quota = new self();
        foreach ($data as $k => $v) {
            $quota->$k = $v;
        }
        try {
            $quota->save();
            return $quota->id;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param mixed|bool $condition
     * @param bool $recursive
     */
    public function deleteQuota($condition = false, $recursive = true)
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

    /**
     * @return QuotaLanguageSetting
     */
    public function getMainLanguagesetting()
    {
        return $this->languagesettings[$this->survey->language];
    }

    public function getCompleteCount()
    {
        if (!tableExists("survey_{$this->sid}")) {
            return null;
        }
        /* Must control if column name exist (@todo : move this to QuotaMember::model(), even with deactivated survey*/
        $aExistingColumnName = SurveyDynamic::model($this->sid)->getTableSchema()->getColumnNames();
        if (count($this->quotaMembers) > 0) {
            // Keep a list of fields for easy reference
            $aQuotaColumns = array();
            foreach ($this->quotaMembers as $member) {
                if (!in_array($member->memberInfo['fieldname'], $aExistingColumnName)) {
                    \Yii::log(
                        sprintf(
                            "Invalid quota member %s",
                            $member->memberInfo['fieldname']
                        ),
                        'warning',
                        'application.model.Quota'
                    );
                    return null;
                }
                $aQuotaColumns[$member->memberInfo['fieldname']][] = $member->memberInfo['value'];
            }

            $oCriteria = new CDbCriteria();
            $oCriteria->condition = new CDbExpression("submitdate IS NOT NULL");
            foreach ($aQuotaColumns as $sColumn => $aValue) {
                if (count($aValue) == 1) {
                    $oCriteria->compare(Yii::app()->db->quoteColumnName($sColumn), $aValue); // NO need params : compare bind
                } else {
                    $oCriteria->addInCondition(Yii::app()->db->quoteColumnName($sColumn), $aValue); // NO need params : addInCondition bind
                }
            }
            $return = SurveyDynamic::model($this->sid)->count($oCriteria);
            return $return;
        } else {
            return 0;
        }
    }

    public function getViewArray()
    {
        $languageSettings = $this->currentLanguageSetting;
        $members = array();
        foreach ($this->quotaMembers as $quotaMember) {
            $members[] = $quotaMember->memberInfo;
        }
        $attributes = $this->attributes;

        return array_merge(array(), $languageSettings->attributes, array('members' => $members), $attributes);
    }

    /**
     * Get the QuotaLanguageSetting for current language
     * @return QuotaLanguageSetting
     */
    public function getCurrentLanguageSetting()
    {
        $oQuotaLanguageSettings = QuotaLanguageSetting::model()
            ->findByAttributes(array(
                'quotals_quota_id' => $this->id,
                'quotals_language' => Yii::app()->getLanguage(),
            ));
        if ($oQuotaLanguageSettings) {
            return $oQuotaLanguageSettings;
        }
        /* If not exist or found, return the one from survey base languague */
        return $this->getMainLanguagesetting();
    }

    public function getButtons()
    {
        $permissionQuotaEdit = Permission::model()->hasSurveyPermission($this->sid, 'quotas', 'update');
        $permissionQuotaDelete = Permission::model()->hasSurveyPermission($this->sid, 'quotas', 'delete');
        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Edit quota'),
            'iconClass'        => 'ri-pencil-fill',
            'url'              => App()->createUrl(
                "quotas/editQuota/surveyid/" . $this->survey->sid,
                array(
                    'quota_id' => $this->primaryKey,
                    )
            ),
            'enabledCondition' => $permissionQuotaEdit
        ];
        $deletePostData = json_encode(['surveyid' => $this->sid, 'quota_id' => $this->primaryKey]);
        $dropdownItems[] = [
            'title'            => gT('Delete'),
            'tooltip'          => gT('Delete quotas'),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => $permissionQuotaDelete,
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-post-url'  => App()->createUrl("quotas/deleteQuota/"),
                'data-message'   => gT("Are you sure you want to delete the selected quotas?"),
                'data-bs-target' => "#confirmation-modal",
                'data-btnclass'  => 'btn-danger',
                'data-btntext'   => gt('Delete'),
                'data-post-datas' => $deletePostData
            ]
        ];
        $dropdownItems[] = [
            'title'            => gT('Validation'),
            'tooltip'          => sprintf(gT("Validation of quota %s"), htmlentities($this->name)),
            'iconClass'        => 'ri-bar-chart-horizontal-fill',
            'enabledCondition' => $permissionQuotaEdit,
            'linkClass'             => 'selector__quota_open_validation',
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-message'   => gT("Are you sure you want to delete the selected quotas?"),
                'data-bs-target' => "quotaValidation",
                'data-remote-link' => App()->createUrl(
                    'admin/validate/',
                    [
                        "sa" => 'quota',
                        'sid' => $this->sid,'quota' => $this->id
                    ]
                )
            ]
        ];
        return App()->getController()->widget(
            'ext.admin.grid.GridActionsWidget.GridActionsWidget',
            ['dropdownItems' => $dropdownItems],
            true
        );
    }
}
