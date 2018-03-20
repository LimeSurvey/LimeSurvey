<?php

/**
 * Class SurveyAnonymizer will overwrite the possibly personal data with un-identifiable data
 */
class SurveyAnonymizer
{
    /** @var Survey */
    protected $survey;

    /** @var string */
    public $error;

    /** @var bool $includeOldTables whether also deleted and old table versions will be anonymized */
    public $includeOldTables = true;

    const ANONYMIZED_STRING = "anonymized";
    const ANONYMIZED_EMAIL = "anonymized@example.com";

    public function __construct($survey)
    {
        if (!($survey instanceof Survey)){
            throw new \Exception("Survey must be an instance of Survey");
        }

        $this->survey = $survey;
    }

    /**
     * @return bool
     */
    public function anonymize(){
        if ($this->survey->canBeAnonymized) {
            $this->anonymizeTokensTables();
            $this->anonymizeSurveyTables();
            return true;
        }
        return false;
    }



    private function anonymizeTokensTables() {
        if ($this->survey->hasTokensTable) {
            $this->anonymizeTokensTable($this->survey->tokensTableName);
        }
        if ($this->includeOldTables && !empty($this->survey->oldTokensTableNames)) {
            foreach ($this->survey->oldTokensTableNames as $tableName) {
                $this->anonymizeTokensTable($tableName);
            }
        }
    }

    private function anonymizeSurveyTables() {
        if ($this->survey->hasResponsesTable) {
            $this->anonymizeResponsesTable($this->survey->responsesTableName);
        }

        if ($this->includeOldTables && !empty($this->survey->oldResponsesTableNames)) {
            foreach ($this->survey->oldResponsesTableNames as $tableName) {
                $this->anonymizeResponsesTable($tableName);
            }
        }
    }


    /*
     * @return bool
     */
    private function anonymizeResponsesTable($tableName){
        return $this->anonymizeDynamicTable(SurveyDynamic::class, $tableName);
    }


    /*
     * @return bool
     */
    private function anonymizeTokensTable($tableName){
        return $this->anonymizeDynamicTable(TokenDynamic::class, $tableName);
    }

    /**
     * @param string $dynamicClass Dynamic model classname
     * @param string $tableName
     * @return bool
     */
    private function anonymizeDynamicTable($dynamicClass, $tableName){
        /** @var LSDynamicRecordInterface $dynamicModel */
        $dynamicModel = $dynamicClass::model($this->survey->primaryKey);
        $tableColumnNames = Yii::app()->db->schema->getTable($tableName)->columnNames;
        $valueMap = [];

        foreach ($dynamicModel->personalFieldNames as $fieldName) {
            if (in_array($fieldName,$tableColumnNames)) {
                $valueMap[$fieldName] = self::ANONYMIZED_STRING;
                if ($fieldName == 'email') {
                    $valueMap[$fieldName] = self::ANONYMIZED_EMAIL;
                }
            }
        }
        if (!empty($valueMap)) {
            Yii::app()->db->createCommand()->update($tableName,$valueMap);
        }
        return true;
    }


}