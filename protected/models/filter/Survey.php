<?php

namespace ls\models\filter;

/**
 * Filter model for survey class.
 * @package ls\models\filter
 */
class Survey extends \ls\models\Survey {
    public $status;
    public $localizedTitle;
    public $bool_usetokens;
    public $sid;

    protected function beforeDelete()
    {
        throw new \Exception("Filter models should not be deleted.");
    }

    protected function beforeSave()
    {
        throw new \Exception("Filter models should not be saved.");
    }


    public function rules() {
        return [
            [['status'], 'safe'],
            ['localizedTitle', 'length', 'min' => 1],
            ['bool_usetokens', 'boolean'],
            ['sid', 'numerical', 'integerOnly' => true]
        ];
    }

    public function search() {
        $criteria = new \CDbCriteria();
        if ($this->validate()) {
            switch ($this->status) {
                case self::STATUS_ACTIVE:
                    $criteria->addColumnCondition(['active' => 'Y']);
                    break;
                case self::STATUS_INACTIVE:
                    $criteria->addColumnCondition(['active' => 'N']);
                    break;
                case self::STATUS_EXPIRED:
                    $criteria->addColumnCondition(['active' => 'Y']);
                    $criteria->addCondition("expires > " . (new \DateTime()));
                    $criteria->addCondition("startdate <= " . (new \DateTime()));
                    break;
            }

            if (isset($this->localizedTitle)) {
//            die('ok');
                $table = \ls\models\SurveyLanguageSetting::model()->tableName();
                // Search in all languages.
                $criteria->addCondition("EXISTS(SELECT * FROM $table WHERE surveyls_title LIKE :keyword AND sid = surveyls_survey_id)");
                // Escape code from CDbCriteria::addSearchCondition
                $criteria->params[':keyword'] = '%' . strtr($this->localizedTitle,
                        ['%' => '\%', '_' => '\_', '\\' => '\\\\']) . '%';

            }

            if ($this->bool_usetokens != '') {
                $criteria->addColumnCondition(['usetokens' => $this->bool_usetokens ? 'Y' : 'N']);
            }

            if (!empty($this->sid)) {
                $criteria->addSearchCondition('sid', $this->sid);

            }
        }
        return $criteria;
    }
}