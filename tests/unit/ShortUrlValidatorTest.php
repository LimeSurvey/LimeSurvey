<?php

namespace ls\tests;

use CUrlManager;

/**
 * Survey aliases may not collide with URLs that are served by something else.
 */
class ShortUrlValidatorTest extends TestBaseClass
{
    /**
     * @inheritdoc
     * Save the URL settings, they are changed by the tests
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$testHelper::saveUrlSettings();
    }

    /**
     * @inheritdoc
     * Put the URL settings back
     */
    public static function tearDownAfterClass(): void
    {
        self::$testHelper::resetUrlSettings();
        parent::tearDownAfterClass();
    }

    /**
     * Whether the alias passes the validation rules of the language settings
     *
     * @param string $alias
     * @return bool
     */
    private function isValidAlias($alias)
    {
        $languageSetting = new \SurveyLanguageSetting();
        $languageSetting->surveyls_survey_id = 1;
        $languageSetting->surveyls_language = 'en';
        $languageSetting->surveyls_alias = $alias;
        return $languageSetting->validate(['surveyls_alias']);
    }

    /**
     * In path format the administration controllers live under the "admin/" prefix, their names are free
     */
    public function testAdminControllerNamesAreAllowedInPathFormat()
    {
        self::$testHelper::setUrlToExpectedDefault(CUrlManager::PATH_FORMAT);
        foreach (['quotas', 'dashboard', 'responses', 'surveyAdministration', 'editor', 'my-survey'] as $alias) {
            $this->assertTrue($this->isValidAlias($alias), "$alias should be allowed");
        }
    }

    /**
     * Everything that still answers at the unprefixed URL stays reserved
     */
    public function testReservedNamesAreRejectedInPathFormat()
    {
        self::$testHelper::setUrlToExpectedDefault(CUrlManager::PATH_FORMAT);
        foreach (['admin', 'survey', 'surveys', 'optout', 'rest', 'uploader', 'assets', 'upload', 'tmp'] as $alias) {
            $this->assertFalse($this->isValidAlias($alias), "$alias should be rejected");
        }
    }

    /**
     * In GET format the administration is not prefixed, so its controller names stay reserved
     */
    public function testAdminControllerNamesAreRejectedInGetFormat()
    {
        self::$testHelper::setUrlToExpectedDefault(CUrlManager::GET_FORMAT);
        foreach (['quotas', 'dashboard', 'admin', 'survey'] as $alias) {
            $this->assertFalse($this->isValidAlias($alias), "$alias should be rejected");
        }
        $this->assertTrue($this->isValidAlias('my-survey'));
    }
}
