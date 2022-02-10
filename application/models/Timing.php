<?php

/**
 * Dynamic response timing model.
 */
class Timing extends LSActiveRecord
{
    /** @var array $models */
    private static $models = array();

    /** @var CActiveRecordMetaData $md meta data*/
    private $md;

    /** @var int|null $surveyId */
    protected $surveyId;

    /** @var Survey $survey */
    protected $survey;
    /**
     * @param int $iSurveyId
     * @param string $scenario
     */
    public function __construct($iSurveyId, $scenario = null)
    {
        if (is_null($scenario)) {
            $scenario = 'insert';
        }
        $survey = Survey::model()->findByPk($iSurveyId);
        if ($survey) {
            $this->surveyId = $iSurveyId;
            $this->survey = $survey;
            parent::__construct($scenario);
        }
    }

    /** @inheritdoc */
    protected function instantiate($attributes)
    {
        $class = get_class($this);
        $model = new $class($this->surveyId, null);
        return $model;
    }

    /**
     * We have a custom implementation here since the parents' implementation
     * does not create a new model for each table name.
     *
     * @param int $iSurveyId
     * @return Response
     * @throws Exception
     * @psalm-suppress ParamNameMismatch Ignore that $iSurveyId is $className in parent class
     */
    public static function model($iSurveyId = null)
    {
        if (is_numeric($iSurveyId)) {
            if (!isset(self::$models[$iSurveyId])) {
                $model = self::$models[$iSurveyId] = new self($iSurveyId, null);
                $model->md = new CActiveRecordMetaData($model);
                $model->attachBehaviors($model->behaviors());
            }
            return self::$models[$iSurveyId];
        }
        throw new Exception('iSurveyId missing in static call.');
    }


    /** @inheritdoc */
    public function relations()
    {
        return array(
            'response' => array(self::BELONGS_TO, 'Response', 'id')
        );
    }

    /** @inheritdoc */
    public function tableName()
    {
        return $this->survey->timingsTableName;
    }

    /**
     * Override
     * @inheritdoc
     */
    public function getMetaData()
    {
        if (isset($this->md)) {
            return $this->md;
        } else {
            /** @var CActiveRecordMetaData $md */
            $md = self::model($this->surveyId)->md;
            return $this->md = $md;
        }
    }

    /**
     * Get current surveyId for other model/function
     * @return int
     */
    public function getSurveyId()
    {
        return $this->surveyId;
    }
}
