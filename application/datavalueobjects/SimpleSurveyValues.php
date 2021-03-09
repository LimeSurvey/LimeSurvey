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

    /**
     * @return string
     */
    public function getBaseLanguage(): string
    {
        return $this->baseLanguage;
    }

    /**
     * @param string $baseLanguage
     */
    public function setBaseLanguage(string $baseLanguage)
    {
        $this->baseLanguage = $baseLanguage;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getSurveyGroupId(): int
    {
        return $this->surveyGroupId;
    }

    /**
     * @param int $surveyGroupId
     */
    public function setSurveyGroupId(int $surveyGroupId)
    {
        $this->surveyGroupId = $surveyGroupId;
    }

    /**
     * @return string
     */
    public function getAdmin(): string
    {
        return $this->admin;
    }

    /**
     * @param string $admin
     */
    public function setAdmin(string $admin)
    {
        $this->admin = $admin;
    }

    /**
     * @return string
     */
    public function getAdminEmail(): string
    {
        return $this->adminEmail;
    }

    /**
     * @param string $adminEmail
     */
    public function setAdminEmail(string $adminEmail)
    {
        $this->adminEmail = $adminEmail;
    }
}
