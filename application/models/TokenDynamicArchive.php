<?php

/*
 * extension of TokenDynamic class to handle archived versions
 */
class TokenDynamicArchive extends TokenDynamic
{
    /** @var int $timestamp */
    protected static $timestamp = 0;


     /**
     * Set the timestamp for next archive model.
     *
     * @param int $timestamp
     * @return void
     */
    public static function setTimestamp(int $timestamp): void
    {
        self::$timestamp = $timestamp;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{old_tokens_' . self::$sid . '_' . self::$timestamp . '}}';
    }

    /** @inheritdoc */
    public function relations()
    {
        SurveyDynamicArchive::sid(self::$sid);
        SurveyDynamicArchive::setTimestamp(self::$timestamp);
        return array(
            'survey'      => array(self::BELONGS_TO, 'Survey', array(), 'condition' => 'sid=' . self::$sid, 'together' => true),
            'responses'   => array(self::HAS_MANY, 'SurveyDynamicArchive', array('token' => 'token'))
        );
    }
}
