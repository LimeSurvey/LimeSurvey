<?php

namespace LimeSurvey\Datavalueobjects;

/**
 * Class SimpleSurveyValues
 *
 * This class owns the basic values to create a survey
 *   -- the base language
 *   -- the survey title
 *   -- if createExamples (questions and groups) should be created at the beginning
 *   -- the survey group id the survey should belong to (or/and inherit values from)
 *
 * @package LimeSurvey\DataValueObject
 */
class SimpleSurveyValues
{

    /** @var string language selected by user */
    public $baseLanguage;

    /** @var string title of the survey */
    public $title;

    /** @var  int the surveygroup from which the new survey will inherit values */
    public $surveyGroupId;

    /** @var string administrator name */
    public $admin = 'inherit';

    /** @var string administrator email */
    public $adminEmail = 'inherit';
}
