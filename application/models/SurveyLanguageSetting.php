<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Class SurveyLanguageSetting
 *
 * @property integer $surveyls_survey_id Survey ID
 * @property string $surveyls_language Language code eg "en"
 * @property string $surveyls_title Survey title in this language
 * @property string $surveyls_description Survey description in this language
 * @property string $surveyls_welcometext Survey welcome-text in this language
 * @property string $surveyls_endtext Survey end-text in this language
 * @property string $surveyls_policy_notice The survey data-security notice
 * @property string $surveyls_url Survey end-url for this language
 * @property string $surveyls_urldescription Survey end-url description for this language
 * @property string $surveyls_email_invite_subj Survey inivitation e-mail subject for this language
 * @property string $surveyls_email_invite Survey inivitation e-mail body in this language
 * @property string $surveyls_email_remind_subj Survey reminder e-mail subject for this language
 * @property string $surveyls_email_remind Survey reminder e-mail body in this language
 * @property string $surveyls_email_register_subj Survey registration e-mail subject for this language
 * @property string $surveyls_email_register Survey registration e-mail body in this language
 * @property string $surveyls_email_confirm_subj Survey confirmation e-mail subject for this language
 * @property string $surveyls_email_confirm Survey confitmation e-mail body in this language
 * @property string $surveyls_dateformat
 * @property string $surveyls_attributecaptions
 * @property string $surveyls_admin_notification_subj Subject of basic admin notification e-mail in this language
 * @property string $surveyls_admin_notification Body of basic admin notification e-mail in this language
 * @property string $surveyls_admin_responses_subj Subject of detailed admin notification e-mail in this language
 * @property string $surveyls_admin_responses Body of detailed admin notification e-mail in this language
 * @property integer $surveyls_numberformat Survey decimal mark for this language (0: '.', 1: ',')
 * @property string $attachments
 *
 * @property Survey $survey
 * @property User $owner
 */
class SurveyLanguageSetting extends LSActiveRecord
{
    private $oldSurveyId;
    private $oldAlias;

    public $surveyls_language = "";
    public $surveyls_title = "";
    public $surveyls_description = "";
    public $surveyls_welcometext = "";
    public $surveyls_endtext = "";
    public $surveyls_policy_notice = "";
    public $surveyls_policy_notice_label = "";
    public $surveyls_url = "";
    public $surveyls_urldescription = "";

    /** @inheritdoc */
    public function tableName()
    {
        return '{{surveys_languagesettings}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return array('surveyls_survey_id', 'surveyls_language');
    }

    /**
     * @inheritdoc
     * @return SurveyLanguageSetting
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    public function relations()
    {
        $alias = $this->getTableAlias();
        return array(
            'survey' => array(self::BELONGS_TO, 'Survey', '', 'on' => "$alias.surveyls_survey_id = survey.sid"),
            'owner' => array(self::BELONGS_TO, 'User', '', 'on' => 'survey.owner_id = owner.uid'),
        );
    }


    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('surveyls_email_invite_subj', 'lsdefault'),
            array('surveyls_email_invite', 'lsdefault'),
            array('surveyls_email_remind_subj', 'lsdefault'),
            array('surveyls_email_remind', 'lsdefault'),
            array('surveyls_email_confirm_subj', 'lsdefault'),
            array('surveyls_email_confirm', 'lsdefault'),
            array('surveyls_email_register_subj', 'lsdefault'),
            array('surveyls_email_register', 'lsdefault'),
            array('email_admin_notification_subj', 'lsdefault'),
            array('email_admin_notification', 'lsdefault'),
            array('email_admin_responses_subj', 'lsdefault'),
            array('email_admin_responses', 'lsdefault'),

            array('surveyls_email_invite_subj', 'LSYii_Validators'),
            array('surveyls_email_invite_subj', 'length', 'min' => 0, 'max' => 255),
            array('surveyls_email_invite', 'LSYii_Validators'),
            array('surveyls_email_remind_subj', 'LSYii_Validators'),
            array('surveyls_email_remind_subj', 'length', 'min' => 0, 'max' => 255),
            array('surveyls_email_remind', 'LSYii_Validators'),
            array('surveyls_email_confirm_subj', 'LSYii_Validators'),
            array('surveyls_email_confirm_subj', 'length', 'min' => 0, 'max' => 255),
            array('surveyls_email_confirm', 'LSYii_Validators'),
            array('surveyls_email_register_subj', 'LSYii_Validators'),
            array('surveyls_email_register_subj', 'length', 'min' => 0, 'max' => 255),
            array('surveyls_email_register', 'LSYii_Validators'),
            array('email_admin_notification_subj', 'LSYii_Validators'),
            array('email_admin_notification_subj', 'length', 'min' => 0, 'max' => 255),
            array('email_admin_notification', 'LSYii_Validators'),
            array('email_admin_responses_subj', 'LSYii_Validators'),
            array('email_admin_responses_subj', 'length', 'min' => 0, 'max' => 255),
            array('email_admin_responses', 'LSYii_Validators'),

            array('surveyls_title', 'LSYii_Validators'),
            array('surveyls_title', 'length', 'min' => 0, 'max' => 200),
            array('surveyls_description', 'LSYii_Validators'),
            array('surveyls_welcometext', 'LSYii_Validators'),
            array('surveyls_endtext', 'LSYii_Validators'),
            array('surveyls_policy_notice', 'LSYii_Validators'),
            array('surveyls_policy_error', 'LSYii_Validators'),
            array('surveyls_policy_notice_label', 'LSYii_Validators'),
            array('surveyls_policy_notice_label', 'length', 'min' => 0, 'max' => 192),
            array('surveyls_url', 'LSYii_FilterValidator', 'filter' => 'trim', 'skipOnEmpty' => true),
            array('surveyls_url', 'LSYii_Validators', 'isUrl' => true),
            array('surveyls_urldescription', 'LSYii_Validators'),
            array('surveyls_urldescription', 'length', 'min' => 0, 'max' => 255),
            array('surveyls_alias', 'length', 'min' => 0, 'max' => 100),
            array('surveyls_alias', 'match', 'allowEmpty' => true, 'pattern' => '/^[^\d\W][\w\-]*$/u'), // Match alphanumeric strings, including "-" and unicode characters. Cannot be completely numeric.
            array('surveyls_alias', 'checkAliasUniqueness'),
            array('surveyls_alias', 'LSYii_ShortUrlValidator'),
            array('surveyls_alias', 'LSYii_Validators'), // The regex rule shouldn't allow any XSS, but we add LSYii_Validators to be sure.

            array('surveyls_dateformat', 'numerical', 'integerOnly' => true, 'min' => '1', 'max' => '12', 'allowEmpty' => true),
            array('surveyls_numberformat', 'numerical', 'integerOnly' => true, 'min' => '0', 'max' => '1', 'allowEmpty' => true),

            array('attachments', 'attachmentsInfo'),
        );
    }

    /**
     * @inheritdoc
     * Pass this to all findAll query : indexed by surveyls_language : return only one survey ID
     * @see https://www.yiiframework.com/doc/api/1.1/CActiveRecord#defaultScope-detail
     * Remind to use resetScope if you need to disable this behaviour
     * @see https://www.yiiframework.com/doc/api/1.1/CActiveRecord#resetScope-detail
     */
    public function defaultScope()
    {
        return array('index' => 'surveyls_language');
    }

    /**
     * Defines the customs validation rule lsdefault
     *
     * @param mixed $attribute
     */
    public function lsdefault($attribute)
    {
        $oSurvey = Survey::model()->findByPk($this->surveyls_survey_id);
        $sEmailFormat = $oSurvey->isHtmlEmail ? 'html' : '';
        $aDefaultTexts = templateDefaultTexts($this->surveyls_language, 'unescaped', $sEmailFormat);

            $aDefaultTextData = array('surveyls_email_invite_subj' => $aDefaultTexts['invitation_subject'],
                'surveyls_email_invite' => $aDefaultTexts['invitation'],
                'surveyls_email_remind_subj' => $aDefaultTexts['reminder_subject'],
                'surveyls_email_remind' => $aDefaultTexts['reminder'],
                'surveyls_email_confirm_subj' => $aDefaultTexts['confirmation_subject'],
                'surveyls_email_confirm' => $aDefaultTexts['confirmation'],
                'surveyls_email_register_subj' => $aDefaultTexts['registration_subject'],
                'surveyls_email_register' => $aDefaultTexts['registration'],
                'email_admin_notification_subj' => $aDefaultTexts['admin_notification_subject'],
                'email_admin_notification' => $aDefaultTexts['admin_notification'],
                'email_admin_responses_subj' => $aDefaultTexts['admin_detailed_notification_subject'],
                'email_admin_responses' => $aDefaultTexts['admin_detailed_notification']
            );
            if ($sEmailFormat == "html") {
                $aDefaultTextData['admin_detailed_notification'] = $aDefaultTexts['admin_detailed_notification_css'] . $aDefaultTexts['admin_detailed_notification'];
            }

            if (empty($this->$attribute)) {
                $this->$attribute = $aDefaultTextData[$attribute];
            }
    }

    /**
     * Defines the customs validation rule attachmentsInfo
     *
     * @param mixed $attribute
     */
    public function attachmentsInfo($attribute)
    {
        if (empty($this->$attribute)) {
            return;
        }

        $value = [];
        $attachmentsByType = unserialize($this->$attribute);
        if (is_array($attachmentsByType)) {
            foreach ($attachmentsByType as $type => $attachments) {
                if (is_array($attachments)) {
                    foreach ($attachments as $key => $attachment) {
                        if (isset($attachment['url']) && isset($attachment['size']) && isset($attachment['relevance'])) {
                            $value[$type][$key] = $attachment;
                        }
                    }
                }
            }
        }
        return serialize($value);
    }

    /**
     * Returns the token's captions
     *
     * @access public
     * @return array
     */
    public function getAttributeCaptions()
    {
        $captions = @json_decode($this->surveyls_attributecaptions, true);
        return $captions !== false ? $captions : array();
    }

    /**
     * @param integer $surveyid
     * @param string $languagecode
     * @return mixed
     */
    public function getDateFormat($surveyid, $languagecode)
    {
        return Yii::app()->db->createCommand()->select('surveyls_dateformat')
            ->from('{{surveys_languagesettings}}')
            ->join('{{surveys}}', '{{surveys}}.sid = {{surveys_languagesettings}}.surveyls_survey_id AND surveyls_survey_id = :surveyid')
            ->where('surveyls_language = :langcode')
            ->bindParam(":langcode", $languagecode, PDO::PARAM_STR)
            ->bindParam(":surveyid", $surveyid, PDO::PARAM_INT)
            ->queryScalar();
    }

    /**
     * @param bool $hasPermission
     * @return mixed
     */
    public function getAllSurveys($hasPermission = false)
    {
        $this->db->select('a.*, surveyls_title, surveyls_description, surveyls_welcometext, surveyls_url');
        $this->db->from('surveys AS a');
        $this->db->join('surveys_languagesettings', 'surveyls_survey_id=a.sid AND surveyls_language=a.language');

        if ($hasPermission) {
            $this->db->where('a.sid IN (SELECT sid FROM {{permissions}} WHERE uid=:uid AND permission=\'survey\' and read_p=1) ')->bindParam(":uid", $this->session->userdata("loginID"), PDO::PARAM_INT);
        }
        $this->db->order_by('active DESC, surveyls_title');
        return $this->db->get();
    }


    /**
     * @param array $data
     * @todo : rename and fix this
     * @return bool
     */
    public function insertNewSurvey($data)
    {
        return $this->insertSomeRecords($data);
    }

    /**
     * Updates a single record identified by $condition with the
     * key/value pairs in the $data array.
     *
     * @param array $data
     * @param string|array $condition
     * @param bool $xssfiltering
     * @return bool
     */
    public function updateRecord($data, $condition = '', $xssfiltering = false)
    {
        $record = $this->findByPk($condition);
        foreach ($data as $key => $value) {
            $record->$key = $value;
        }
        $record->save($xssfiltering);

        return true;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function insertSomeRecords($data)
    {
        $lang = new self();
        foreach ($data as $k => $v) {
            $lang->$k = $v;
        }
        return $lang->save();
    }

    /**
     * Validates that the alias is not used in another survey
     */
    public function checkAliasUniqueness()
    {
        if (empty($this->surveyls_alias)) {
            return;
        }
        if ($this->surveyls_alias !== $this->oldAlias || $this->surveyls_survey_id != $this->oldSurveyId) {
            $model = self::model()->find(
                'surveyls_alias = ? AND surveyls_survey_id <> ?',
                [$this->surveyls_alias, $this->surveyls_survey_id]
            );
            if ($model != null) {
                $this->addError('surveyls_alias', gT('Alias must be unique'));
            }
        }
    }

    protected function afterFind()
    {
        parent::afterFind();
        $this->oldSurveyId = $this->surveyls_survey_id;
        if (isset($this->surveyls_alias)) {
            $this->oldAlias = $this->surveyls_alias;
        }
    }

    /**
     * Returns the array of email attachments data without exposing sensitive paths
     * @return array<string,array<string,mixed>>
     */
    public function getAttachmentsData()
    {
        if (empty($this->attachments)) {
            return [];
        }
        $attachments = unserialize($this->attachments);
        if (is_array($attachments)) {
            $uploadDir = realpath(Yii::app()->getConfig('uploaddir'));
            foreach ($attachments as &$template) {
                foreach ($template as &$attachment) {
                    if (substr($attachment['url'], 0, strlen($uploadDir)) == $uploadDir) {
                        $url = substr($attachment['url'], strlen($uploadDir));
                        $url = ltrim($url, "/\\");
                        $attachment['url'] = $url;
                    }
                }
            }
        }
        return $attachments;
    }
}
