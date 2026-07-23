<?php

namespace ls\tests;

use Yii;

class SurveyTest extends TestBaseClass
{
    protected $modelClassName = \Survey::class;
    private static $intervals;
    private $oldDisplayTimezone;

    public static function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();

        \Yii::import('application.helpers.surveytranslator_helper', true);

        \Yii::app()->session['dateformat'] = 6;

        //Set time intervals.
        self::$intervals = array(
            'oneDay'    => \DateInterval::createFromDateString('1 days'),
            'twoDays'   => \DateInterval::createFromDateString('2 days'),
            'threeDays' => \DateInterval::createFromDateString('3 days'),
            'fourDays'  => \DateInterval::createFromDateString('4 days'),
            'fiveDays'  => \DateInterval::createFromDateString('5 days'),
            'sixDays'   => \DateInterval::createFromDateString('6 days'),
            'sevenDays' => \DateInterval::createFromDateString('7 days'),
        );

        $filename = self::$surveysFolder . '/limesurvey_survey_161359_quickTranslation.lss';
        self::importSurvey($filename);
    }

    public function setUp(): void
    {
        $this->oldDisplayTimezone = \Yii::app()->getConfig('displayTimezone');
        \SettingGlobal::setSetting('displayTimezone', 'UTC');
        \Yii::app()->setConfig('displayTimezone', 'UTC');
    }

    public function tearDown(): void
    {
        \SettingGlobal::setSetting('displayTimezone', $this->oldDisplayTimezone ?? '');
        \Yii::app()->setConfig('displayTimezone', $this->oldDisplayTimezone ?? '');
    }

    /**
     * Survey state: inactive.
     */
    public function testInactiveSurveyState()
    {
        $survey = new \Survey();
        $survey->active = 'N';

        $state = $survey->getState();

        $this->assertSame('inactive', $state, 'Survey active property is ' . $survey->active);
    }

    /**
     * Survey state: expired.
     */
    public function testExpiredSurveyState()
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $twoDaysAgo = (new \DateTime('now', new \DateTimeZone('UTC')))->sub(self::$intervals['twoDays'])->format('Y-m-d H:i:s');
        $survey->expires = $twoDaysAgo;

        $state = $survey->getState();

        $this->assertSame('expired', $state, 'Survey expires property is ' . $survey->expires);

        //Test with display timezone.
        \Yii::app()->setConfig('displayTimezone', 'Pacific/Auckland');

        $state = $survey->getState();

        $this->assertSame('expired', $state, 'Survey expires property is ' . $survey->expires . ' (display timezone test)');
    }

    /**
     * Survey state: willRun.
     */
    public function testWillRunSurveyState()
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $inFourDays = (new \DateTime('now', new \DateTimeZone('UTC')))->add(self::$intervals['fourDays'])->format('Y-m-d H:i:s');
        $survey->startdate = $inFourDays;

        $state = $survey->getState();

        $this->assertSame('willRun', $state, 'Survey startdate property is ' . $survey->startdate);
    }

    /**
     * Survey state: willExpire (the survey is active and it has an expiredate).
     */
    public function testWillExpireSurveyState()
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $inFiveDays = (new \DateTime('now', new \DateTimeZone('UTC')))->add(self::$intervals['fiveDays'])->format('Y-m-d H:i:s');
        $survey->expires = $inFiveDays;

        $state = $survey->getState();

        $this->assertSame('willExpire', $state, 'Survey expires property is ' . $survey->expires);

        // Testing for both start and expire date.
        $inSevenDays = (new \DateTime('now', new \DateTimeZone('UTC')))->add(self::$intervals['sevenDays'])->format('Y-m-d H:i:s');
        $oneDayAgo = (new \DateTime('now', new \DateTimeZone('UTC')))->sub(self::$intervals['oneDay'])->format('Y-m-d H:i:s');

        $survey->startdate = $oneDayAgo;
        $survey->expires = $inSevenDays;

        $state = $survey->getState();

        $this->assertSame('willExpire', $state, 'Survey expires property is ' . $survey->expires . '. Survey startdate property is ' . $survey->startdate);
    }

    /**
     * Survey state: running (the survey is active but it does not have an expire date).
     */
    public function testRunningSurveyState()
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $state = $survey->getState();

        $this->assertSame('running', $state, 'Survey active property is ' . $survey->active . ', no dates set.');
    }

    /**
     * The survey is not active.
     */
    public function testInactiveSurveyIcon()
    {
        $survey = new \Survey();
        $survey->active = 'N';

        $icon = $survey->getRunning();

        $this->assertStringContainsString(gT('Inactive'), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-stop-fill text-secondary', $icon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey is active but it has no start or expire dates set.
     */
    public function testActiveSurveyIconNoDates()
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $icon = $survey->getRunning();

        $this->assertStringContainsString(gT('Active'), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-play-fill text-primary', $icon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey is active, it has a start date in the past but no expire date.
     */
    public function testActiveSurveyIconNoExpireDate()
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $threeDaysAgo = (new \DateTime('now', new \DateTimeZone('UTC')))->sub(self::$intervals['threeDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $threeDaysAgo;

        $icon = $survey->getRunning();

        $this->assertStringContainsString(gT('End: Never'), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-play-fill text-primary', $icon, 'The icon link does not have the right css classes.');

        //Test with display timezone.
        \Yii::app()->setConfig('displayTimezone', 'Pacific/Auckland');

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(gT('End: Never'), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-play-fill text-primary', $newIcon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey is active, it has an expire date in the future but no start date.
     */
    public function testActiveSurveyIconNoStartDate()
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $inFourDays = (new \DateTime('now', new \DateTimeZone('UTC')))->add(self::$intervals['fourDays'])->format('Y-m-d H:i:s');

        $survey->expires = $inFourDays;

        $sExpires = convertToGlobalSettingFormat(dateShift($survey->expires, "Y-m-d H:i:s"));

        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('End: %s'), $sExpires), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-play-fill text-primary', $icon, 'The icon link does not have the right css classes.');

        //Test with display timezone.
        \Yii::app()->setConfig('displayTimezone', 'Pacific/Auckland');

        $sExpires = convertToGlobalSettingFormat(dateShift($survey->expires, "Y-m-d H:i:s"));

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('End: %s'), $sExpires), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-play-fill text-primary', $newIcon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey is active, it has an expire date in the future and a start date in the past.
     */
    public function testActiveSurveyIconExpireDateInTheFutureStartDateInThePast()
    {

        $survey = new \Survey();
        $survey->active = 'Y';

        $oneDayAgo = (new \DateTime('now', new \DateTimeZone('UTC')))->sub(self::$intervals['oneDay'])->format('Y-m-d H:i:s');
        $inFiveDays = (new \DateTime('now', new \DateTimeZone('UTC')))->add(self::$intervals['fiveDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $oneDayAgo;
        $survey->expires = $inFiveDays;

        $sExpires = convertToGlobalSettingFormat(dateShift($survey->expires, "Y-m-d H:i:s"));

        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('End: %s'), $sExpires), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-play-fill text-primary', $icon, 'The icon link does not have the right css classes.');

        //Test with display timezone.
        \Yii::app()->setConfig('displayTimezone', 'Pacific/Auckland');

        $sExpires = convertToGlobalSettingFormat(dateShift($survey->expires, "Y-m-d H:i:s"));

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('End: %s'), $sExpires), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-play-fill text-primary', $newIcon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey has a start date in the future and no expire date.
     */
    public function testSurveyIconWillStartNoExpireDate()
    {

        $survey = new \Survey();
        $survey->active = 'Y';

        $inSixDays = (new \DateTime('now', new \DateTimeZone('UTC')))->add(self::$intervals['sixDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $inSixDays;

        $sStart = convertToGlobalSettingFormat(dateShift($survey->startdate, "Y-m-d H:i:s"));
        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Start: %s'), $sStart), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-time-line text-secondary', $icon, 'The icon link does not have the right css classes.');

        //Test with display timezone.
        \Yii::app()->setConfig('displayTimezone', 'Pacific/Auckland');

        $sStart = convertToGlobalSettingFormat(dateShift($survey->startdate, "Y-m-d H:i:s"));

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Start: %s'), $sStart), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-time-line text-secondary', $newIcon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey has a start date in the future and an expire date in the future.
     */
    public function testSurveyIconWillStart()
    {

        $survey = new \Survey();
        $survey->active = 'Y';

        $inFourDays = (new \DateTime('now', new \DateTimeZone('UTC')))->add(self::$intervals['fourDays'])->format('Y-m-d H:i:s');
        $inSevenDays = (new \DateTime('now', new \DateTimeZone('UTC')))->add(self::$intervals['sevenDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $inFourDays;
        $survey->expires = $inSevenDays;

        $sStart = convertToGlobalSettingFormat(dateShift($survey->startdate, "Y-m-d H:i:s"));
        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Start: %s'), $sStart), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-time-line text-secondary', $icon, 'The icon link does not have the right css classes.');

        //Test with display timezone.
        \Yii::app()->setConfig('displayTimezone', 'Pacific/Auckland');

        $sStart = convertToGlobalSettingFormat(dateShift($survey->startdate, "Y-m-d H:i:s"));

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Start: %s'), $sStart), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-time-line text-secondary', $newIcon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey has an expire date in the past and no start date.
     */
    public function testSurveyIconExpiredNoStartdate()
    {

        $survey = new \Survey();
        $survey->active = 'Y';

        $threeDaysAgo = (new \DateTime('now', new \DateTimeZone('UTC')))->sub(self::$intervals['threeDays'])->format('Y-m-d H:i:s');

        $survey->expires = $threeDaysAgo;

        $sExpires = convertToGlobalSettingFormat(dateShift($survey->expires, "Y-m-d H:i:s"));
        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Expired: %s'), $sExpires), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-skip-forward-fill text-secondary', $icon, 'The icon link does not have the right css classes.');

        //Test with display timezone.
        \Yii::app()->setConfig('displayTimezone', 'Pacific/Auckland');
        $sExpires = convertToGlobalSettingFormat(dateShift($survey->expires, "Y-m-d H:i:s"));

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Expired: %s'), $sExpires), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-skip-forward-fill text-secondary', $newIcon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey has an expire date in the past and a start date in the past.
     */
    public function testSurveyIconExpired()
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $fiveDaysAgo = (new \DateTime('now', new \DateTimeZone('UTC')))->sub(self::$intervals['fiveDays'])->format('Y-m-d H:i:s');
        $sevenDaysAgo = (new \DateTime('now', new \DateTimeZone('UTC')))->sub(self::$intervals['sevenDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $sevenDaysAgo;
        $survey->expires = $fiveDaysAgo;

        $sExpires = convertToGlobalSettingFormat(dateShift($survey->expires, "Y-m-d H:i:s"));
        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Expired: %s'), $sExpires), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-skip-forward-fill text-secondary', $icon, 'The icon link does not have the right css classes.');

        //Test with display timezone.
        \Yii::app()->setConfig('displayTimezone', 'Pacific/Auckland');

        $sExpires = convertToGlobalSettingFormat(dateShift($survey->expires, "Y-m-d H:i:s"));

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Expired: %s'), $sExpires), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-skip-forward-fill text-secondary', $newIcon, 'The icon link does not have the right css classes.');
    }

    /**
     * Get the survey url using the language by default.
     */
    public function testGetDefaultLanguageSurveyUrl()
    {
        self::$testHelper::saveUrlSettings();
        self::$testHelper::setToExpectedDefault();
        Yii::app()->setConfig('publicurl', 'http://example.com');

        $url = self::$testSurvey->getSurveyUrl();
        $this->assertSame("http://example.com/index.php/survey/index/sid/" . self::$surveyId . "/lang/en", $url, 'Unexpected url. The url does not correspond with a public survey url.');

        self::$testHelper::resetUrlSettings();
    }

    /**
     * Get the survey url with additional parameters.
     */
    public function testGetSurveyUrlWithParameters()
    {
        self::$testHelper::saveUrlSettings();
        self::$testHelper::setToExpectedDefault();
        Yii::app()->setConfig('publicurl', 'http://example.com');

        $params = array('param_1' => 1, 'param_2' => 2);
        $url = self::$testSurvey->getSurveyUrl(null, $params);
        $urlParams = array_merge($params, ['sid' => self::$surveyId, 'lang' => 'en']);
        $expectedUrl = App()->createPublicUrl('survey/index', $urlParams);

        $this->assertSame($url, $expectedUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        self::$testHelper::resetUrlSettings();
    }

    /**
     * Get the survey url for a specific language.
     */
    public function testGetSurveyUrlSpecificLanguage()
    {
        self::$testHelper::saveUrlSettings();
        self::$testHelper::setToExpectedDefault();
        Yii::app()->setConfig('publicurl', 'http://example.com');

        $url = self::$testSurvey->getSurveyUrl('de');
        $this->assertSame('http://example.com/index.php/survey/index/sid/' . self::$surveyId . '/lang/de', $url, 'Unexpected url. The url does not correspond with a public survey url.');

        $url = self::$testSurvey->getSurveyUrl('sv');
        $this->assertSame('http://example.com/index.php/survey/index/sid/' . self::$surveyId . '/lang/sv', $url, 'Unexpected url. The url does not correspond with a public survey url.');

        self::$testHelper::resetUrlSettings();
    }

    /**
     * Get the alias survey url for a specific language.
     * Using the get format.
     */
    public function testGetAliasSurveyUrlGet()
    {
        self::$testHelper::saveUrlSettings();
        self::$testHelper::setToExpectedDefault();
        $urlManager = Yii::app()->getUrlManager();
        $urlManager->urlFormat = \CUrlManager::GET_FORMAT;

        $arLanguageSetting = self::$testSurvey->languagesettings['ar'];
        $tmpArSurveyAlias = $arLanguageSetting->surveyls_alias;
        $arLanguageSetting->surveyls_alias = 'my-arabic-survey';
        Yii::app()->setConfig('publicurl', 'http://example.com');
        $url = self::$testSurvey->getSurveyUrl('ar');
        $this->assertSame('http://example.com?r=my-arabic-survey', $url, 'Unexpected url. The url does not correspond with a public survey url.');

        // No alias assert.
        $url = self::$testSurvey->getSurveyUrl('ar', array(), false);
        $this->assertSame('http://example.com/index.php?r=survey/index&sid=' . self::$surveyId . '&lang=ar', $url, 'Unexpected url. The url does not correspond with a public survey url.');

        // Reset original value.
        $arLanguageSetting->surveyls_alias = $tmpArSurveyAlias;
        self::$testHelper::resetUrlSettings();
    }

    /**
     * Get the alias survey url for a specific language.
     * Using the path format.
     */
    public function testGetAliasSurveyUrlPath()
    {
        self::$testHelper::saveUrlSettings();
        self::$testHelper::setToExpectedDefault();
        $urlManager = Yii::app()->getUrlManager();
        $urlManager->urlFormat = \CUrlManager::PATH_FORMAT;

        $tmpArSurveyAlias = self::$testSurvey->languagesettings['ar']->surveyls_alias;

        // Get the language setting object, modify it, and set it back
        $arLanguageSetting = self::$testSurvey->languagesettings['ar'];
        $arLanguageSetting->surveyls_alias = 'my-arabic-survey';

        $tmpPublicUrl = Yii::app()->getConfig('publicurl');
        Yii::app()->setConfig('publicurl', 'http://example.com');

        $url = self::$testSurvey->getSurveyUrl('ar');
        $this->assertSame('http://example.com/my-arabic-survey', $url, 'Unexpected url. The url does not correspond with a public survey url.');

        // No alias assert.
        $url = self::$testSurvey->getSurveyUrl('ar', array(), false);
        $this->assertSame('http://example.com/index.php/survey/index/sid/' . self::$surveyId . '/lang/ar', $url, 'Unexpected url. The url does not correspond with a public survey url.');

        // Reset original value.
        $arLanguageSetting->surveyls_alias = $tmpArSurveyAlias;
        self::$testHelper::resetUrlSettings();
    }

    /**
     * Get the alias survey url for a specific language.
     * Using the get format.
     *
     * There are two languages with the same alias in this case.
     */
    public function testGetAliasSurveyUrlGetRepeatedAlias()
    {
        self::$testHelper::saveUrlSettings();
        self::$testHelper::setToExpectedDefault();
        $urlManager = Yii::app()->getUrlManager();

        $tmpArSurveyAlias = self::$testSurvey->languagesettings['ar']->surveyls_alias;
        $tmpDeSurveyAlias = self::$testSurvey->languagesettings['de']->surveyls_alias;

        $urlManager->urlFormat = \CUrlManager::GET_FORMAT;

        $arLanguageSetting = self::$testSurvey->languagesettings['ar'];
        $arLanguageSetting->surveyls_alias = 'my-survey';

        $deLanguageSetting = self::$testSurvey->languagesettings['de'];
        $deLanguageSetting->surveyls_alias = 'my-survey';

        Yii::app()->setConfig('publicurl', 'http://example.com');

        $arAliasUrl = self::$testSurvey->getSurveyUrl('ar');
        $this->assertSame('http://example.com?r=my-survey&lang=ar', $arAliasUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        $deAliasUrl = self::$testSurvey->getSurveyUrl('de');
        $this->assertSame('http://example.com?r=my-survey&lang=de', $deAliasUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        // No alias assert.
        $arUrl = self::$testSurvey->getSurveyUrl('ar', array(), false);
        $this->assertSame('http://example.com/index.php?r=survey/index&sid=' . self::$surveyId . '&lang=ar', $arUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        $deUrl = self::$testSurvey->getSurveyUrl('de', array(), false);
        $this->assertSame('http://example.com/index.php?r=survey/index&sid=' . self::$surveyId . '&lang=de', $deUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        // Reset original value.
        $arLanguageSetting->surveyls_alias = $tmpArSurveyAlias;
        $deLanguageSetting->surveyls_alias = $tmpDeSurveyAlias;
        self::$testHelper::resetUrlSettings();
    }

    /**
     * Get the alias survey url for a specific language.
     * Using the path format.
     *
     * There are two languages with the same alias in this case.
     */
    public function testGetAliasSurveyUrlPathRepeatedAlias()
    {
        $urlManager = Yii::app()->getUrlManager();

        $tmpArSurveyAlias = self::$testSurvey->languagesettings['ar']->surveyls_alias;
        $tmpDeSurveyAlias = self::$testSurvey->languagesettings['de']->surveyls_alias;
        $tmpUrlFormat = $urlManager->getUrlFormat();

        $urlManager->urlFormat = \CUrlManager::PATH_FORMAT;

        $arLanguageSetting = self::$testSurvey->languagesettings['ar'];
        $arLanguageSetting->surveyls_alias = 'my-survey';

        $deLanguageSetting = self::$testSurvey->languagesettings['de'];
        $deLanguageSetting->surveyls_alias = 'my-survey';

        $tmpPublicUrl = Yii::app()->getConfig('publicurl');
        Yii::app()->setConfig('publicurl', 'http://example.com');

        $arAliasUrl = self::$testSurvey->getSurveyUrl('ar');
        $this->assertSame($arAliasUrl, 'http://example.com/my-survey?lang=ar', 'Unexpected url. The url does not correspond with a public survey url.');

        $deAliasUrl = self::$testSurvey->getSurveyUrl('de');
        $this->assertSame($deAliasUrl, 'http://example.com/my-survey?lang=de', 'Unexpected url. The url does not correspond with a public survey url.');

        // No alias assert.
        $arUrl = self::$testSurvey->getSurveyUrl('ar', array(), false);
        $this->assertSame('http://example.com/index.php/survey/index/sid/' . self::$surveyId . '/lang/ar', $arUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        $deUrl = self::$testSurvey->getSurveyUrl('de', array(), false);
        $this->assertSame('http://example.com/index.php/survey/index/sid/' . self::$surveyId . '/lang/de', $deUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        // Reset original value.
        $arLanguageSetting->surveyls_alias = $tmpArSurveyAlias;
        $deLanguageSetting->surveyls_alias = $tmpDeSurveyAlias;
        Yii::app()->setConfig('publicurl', $tmpPublicUrl);
        $urlManager->urlFormat = $tmpUrlFormat;
    }
}
