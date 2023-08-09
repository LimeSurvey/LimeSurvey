<?php

namespace ls\tests;

use Yii;

class SurveyTest extends TestBaseClass
{
    protected $modelClassName = \Survey::class;
    private static $intervals;

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
        \SettingGlobal::setSetting('timeadjust', '+0 minutes');
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

        $twoDaysAgo = date_create()->sub(self::$intervals['twoDays'])->format('Y-m-d H:i:s');
        $survey->expires = $twoDaysAgo;

        $state = $survey->getState();

        $this->assertSame('expired', $state, 'Survey expires property is ' . $survey->expires);

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+420 minutes');

        $state = $survey->getState();

        $this->assertSame('expired', $state, 'Survey expires property is ' . $survey->expires . ' (time adjust test)');
    }

    /**
     * Survey state: willRun.
     */
    public function testWillRunSurveyState()
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $inFourDays = date_create()->add(self::$intervals['fourDays'])->format('Y-m-d H:i:s');
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

        $inFiveDays = date_create()->add(self::$intervals['fiveDays'])->format('Y-m-d H:i:s');
        $survey->expires = $inFiveDays;

        $state = $survey->getState();

        $this->assertSame('willExpire', $state, 'Survey expires property is ' . $survey->expires);

        // Testing for both start and expire date.
        $inSevenDays = date_create()->add(self::$intervals['sevenDays'])->format('Y-m-d H:i:s');
        $oneDayAgo = date_create()->sub(self::$intervals['oneDay'])->format('Y-m-d H:i:s');

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

        $threeDaysAgo = date_create()->sub(self::$intervals['threeDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $threeDaysAgo;

        $icon = $survey->getRunning();

        $this->assertStringContainsString(gT('End: Never'), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-play-fill text-primary', $icon, 'The icon link does not have the right css classes.');

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+420 minutes');

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

        $inFourDays = date_create()->add(self::$intervals['fourDays'])->format('Y-m-d H:i:s');

        $survey->expires = $inFourDays;

        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));

        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('End: %s'), $sExpires), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-play-fill text-primary', $icon, 'The icon link does not have the right css classes.');

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+120 minutes');

        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));

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

        $oneDayAgo = date_create()->sub(self::$intervals['oneDay'])->format('Y-m-d H:i:s');
        $inFiveDays = date_create()->add(self::$intervals['fiveDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $oneDayAgo;
        $survey->expires = $inFiveDays;

        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));

        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('End: %s'), $sExpires), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-play-fill text-primary', $icon, 'The icon link does not have the right css classes.');

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+180 minutes');

        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));

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

        $inSixDays = date_create()->add(self::$intervals['sixDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $inSixDays;

        $sStart = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->startdate))));
        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Start: %s'), $sStart), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-time-line text-secondary', $icon, 'The icon link does not have the right css classes.');

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+240 minutes');

        $sStart = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->startdate))));

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

        $inFourDays = date_create()->add(self::$intervals['fourDays'])->format('Y-m-d H:i:s');
        $inSevenDays = date_create()->add(self::$intervals['sevenDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $inFourDays;
        $survey->expires = $inSevenDays;

        $sStart = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->startdate))));
        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Start: %s'), $sStart), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-time-line text-secondary', $icon, 'The icon link does not have the right css classes.');

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+300 minutes');

        $sStart = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->startdate))));

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

        $threeDaysAgo = date_create()->sub(self::$intervals['threeDays'])->format('Y-m-d H:i:s');

        $survey->expires = $threeDaysAgo;

        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));
        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Expired: %s'), $sExpires), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-skip-forward-fill text-secondary', $icon, 'The icon link does not have the right css classes.');

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+360 minutes');
        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));

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

        $fiveDaysAgo = date_create()->sub(self::$intervals['fiveDays'])->format('Y-m-d H:i:s');
        $sevenDaysAgo = date_create()->sub(self::$intervals['sevenDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $sevenDaysAgo;
        $survey->expires = $fiveDaysAgo;

        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));
        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Expired: %s'), $sExpires), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-skip-forward-fill text-secondary', $icon, 'The icon link does not have the right css classes.');

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+60 minutes');

        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Expired: %s'), $sExpires), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('ri-skip-forward-fill text-secondary', $newIcon, 'The icon link does not have the right css classes.');
    }

    /**
     * Get the survey url using the language by default.
     */
    public function testGetDefaultLanguageSurveyUrl()
    {
        $tmpPublicUrl = Yii::app()->getConfig('publicurl');
        Yii::app()->setConfig('publicurl', 'http://example.com');

        $url = self::$testSurvey->getSurveyUrl();
        $expectedRelativeUrl = Yii::app()->createUrl('survey/index', array('sid' => self::$surveyId, 'lang' => 'en'));

        $this->assertSame($url, 'http://example.com' . $expectedRelativeUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        // Reset original value.
        Yii::app()->setConfig('publicurl', $tmpPublicUrl);
    }

    /**
     * Get the survey url with additional parameters.
     */
    public function testGetSurveyUrlWithParameters()
    {
        $tmpPublicUrl = Yii::app()->getConfig('publicurl');
        Yii::app()->setConfig('publicurl', 'http://example.com');

        $params = array('param_1' => 1, 'param_2' => 2);
        $url = self::$testSurvey->getSurveyUrl(null, $params);
        $urlParams = array_merge($params, ['sid' => self::$surveyId, 'lang' => 'en']);
        $expectedUrl = App()->createPublicUrl('survey/index', $urlParams);

        $this->assertSame($url, $expectedUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        // Reset original value.
        Yii::app()->setConfig('publicurl', $tmpPublicUrl);
    }

    /**
     * Get the survey url for a specific language.
     */
    public function testGetSurveyUrlSpecificLanguage()
    {
        $tmpPublicUrl = Yii::app()->getConfig('publicurl');
        Yii::app()->setConfig('publicurl', 'http://example.com');

        $url = self::$testSurvey->getSurveyUrl('de');
        $expectedRelativeUrl = Yii::app()->createUrl('survey/index', array('sid' => self::$surveyId, 'lang' => 'de'));

        $this->assertSame($url, 'http://example.com' . $expectedRelativeUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        $url = self::$testSurvey->getSurveyUrl('sv');
        $expectedRelativeUrl = Yii::app()->createUrl('survey/index', array('sid' => self::$surveyId, 'lang' => 'sv'));

        $this->assertSame($url, 'http://example.com' . $expectedRelativeUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        // Reset original value.
        Yii::app()->setConfig('publicurl', $tmpPublicUrl);
    }

    /**
     * Get the alias survey url for a specific language.
     * Using the get format.
     */
    public function testGetAliasSurveyUrlGet()
    {
        $urlManager = Yii::app()->getUrlManager();

        $tmpArSurveyAlias = self::$testSurvey->languagesettings['ar']->surveyls_alias;
        $tmpUrlFormat = $urlManager->getUrlFormat();

        $urlManager->urlFormat = \CUrlManager::GET_FORMAT;

        self::$testSurvey->languagesettings['ar']->surveyls_alias = 'my-arabic-survey';

        $tmpPublicUrl = Yii::app()->getConfig('publicurl');
        Yii::app()->setConfig('publicurl', 'http://example.com');

        $url = self::$testSurvey->getSurveyUrl('ar');
        $this->assertSame($url, 'http://example.com?r=my-arabic-survey', 'Unexpected url. The url does not correspond with a public survey url.');

        // No alias assert.
        $url = self::$testSurvey->getSurveyUrl('ar', array(), false);
        $expectedRelativeUrl = Yii::app()->createUrl('survey/index', array('sid' => self::$surveyId, 'lang' => 'ar'));
        $this->assertSame($url, 'http://example.com' . $expectedRelativeUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        // Reset original value.
        self::$testSurvey->languagesettings['ar']->surveyls_alias = $tmpArSurveyAlias;
        Yii::app()->setConfig('publicurl', $tmpPublicUrl);
        $urlManager->urlFormat = $tmpUrlFormat;
    }

    /**
     * Get the alias survey url for a specific language.
     * Using the path format.
     */
    public function testGetAliasSurveyUrlPath()
    {
        $urlManager = Yii::app()->getUrlManager();

        $tmpArSurveyAlias = self::$testSurvey->languagesettings['ar']->surveyls_alias;
        $tmpUrlFormat = $urlManager->getUrlFormat();

        $urlManager->urlFormat = \CUrlManager::PATH_FORMAT;

        self::$testSurvey->languagesettings['ar']->surveyls_alias = 'my-arabic-survey';

        $tmpPublicUrl = Yii::app()->getConfig('publicurl');
        Yii::app()->setConfig('publicurl', 'http://example.com');

        $url = self::$testSurvey->getSurveyUrl('ar');
        $this->assertSame($url, 'http://example.com/my-arabic-survey', 'Unexpected url. The url does not correspond with a public survey url.');

        // No alias assert.
        $url = self::$testSurvey->getSurveyUrl('ar', array(), false);
        $expectedRelativeUrl = Yii::app()->createUrl('survey/index', array('sid' => self::$surveyId, 'lang' => 'ar'));
        $this->assertSame($url, 'http://example.com' . $expectedRelativeUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        // Reset original value.
        self::$testSurvey->languagesettings['ar']->surveyls_alias = $tmpArSurveyAlias;
        Yii::app()->setConfig('publicurl', $tmpPublicUrl);
        $urlManager->urlFormat = $tmpUrlFormat;
    }

    /**
     * Get the alias survey url for a specific language.
     * Using the get format.
     *
     * There are two languages with the same alias in this case.
     */
    public function testGetAliasSurveyUrlGetRepeatedAlias()
    {
        $urlManager = Yii::app()->getUrlManager();

        $tmpArSurveyAlias = self::$testSurvey->languagesettings['ar']->surveyls_alias;
        $tmpDeSurveyAlias = self::$testSurvey->languagesettings['de']->surveyls_alias;
        $tmpUrlFormat = $urlManager->getUrlFormat();

        $urlManager->urlFormat = \CUrlManager::GET_FORMAT;

        self::$testSurvey->languagesettings['ar']->surveyls_alias = 'my-survey';
        self::$testSurvey->languagesettings['de']->surveyls_alias = 'my-survey';

        $tmpPublicUrl = Yii::app()->getConfig('publicurl');
        Yii::app()->setConfig('publicurl', 'http://example.com');

        $arAliasUrl = self::$testSurvey->getSurveyUrl('ar');
        $this->assertSame($arAliasUrl, 'http://example.com?r=my-survey&lang=ar', 'Unexpected url. The url does not correspond with a public survey url.');

        $deAliasUrl = self::$testSurvey->getSurveyUrl('de');
        $this->assertSame($deAliasUrl, 'http://example.com?r=my-survey&lang=de', 'Unexpected url. The url does not correspond with a public survey url.');

        // No alias assert.
        $arUrl = self::$testSurvey->getSurveyUrl('ar', array(), false);
        $expectedRelativeArUrl = Yii::app()->createUrl('survey/index', array('sid' => self::$surveyId, 'lang' => 'ar'));
        $this->assertSame($arUrl, 'http://example.com' . $expectedRelativeArUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        $deUrl = self::$testSurvey->getSurveyUrl('de', array(), false);
        $expectedRelativeDeUrl = Yii::app()->createUrl('survey/index', array('sid' => self::$surveyId, 'lang' => 'de'));
        $this->assertSame($deUrl, 'http://example.com' . $expectedRelativeDeUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        // Reset original value.
        self::$testSurvey->languagesettings['ar']->surveyls_alias = $tmpArSurveyAlias;
        self::$testSurvey->languagesettings['de']->surveyls_alias = $tmpDeSurveyAlias;
        Yii::app()->setConfig('publicurl', $tmpPublicUrl);
        $urlManager->urlFormat = $tmpUrlFormat;
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

        self::$testSurvey->languagesettings['ar']->surveyls_alias = 'my-survey';
        self::$testSurvey->languagesettings['de']->surveyls_alias = 'my-survey';

        $tmpPublicUrl = Yii::app()->getConfig('publicurl');
        Yii::app()->setConfig('publicurl', 'http://example.com');

        $arAliasUrl = self::$testSurvey->getSurveyUrl('ar');
        $this->assertSame($arAliasUrl, 'http://example.com/my-survey?lang=ar', 'Unexpected url. The url does not correspond with a public survey url.');

        $deAliasUrl = self::$testSurvey->getSurveyUrl('de');
        $this->assertSame($deAliasUrl, 'http://example.com/my-survey?lang=de', 'Unexpected url. The url does not correspond with a public survey url.');

        // No alias assert.
        $arUrl = self::$testSurvey->getSurveyUrl('ar', array(), false);
        $expectedRelativeArUrl = Yii::app()->createUrl('survey/index', array('sid' => self::$surveyId, 'lang' => 'ar'));
        $this->assertSame($arUrl, 'http://example.com' . $expectedRelativeArUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        $deUrl = self::$testSurvey->getSurveyUrl('de', array(), false);
        $expectedRelativeDeUrl = Yii::app()->createUrl('survey/index', array('sid' => self::$surveyId, 'lang' => 'de'));
        $this->assertSame($deUrl, 'http://example.com' . $expectedRelativeDeUrl, 'Unexpected url. The url does not correspond with a public survey url.');

        // Reset original value.
        self::$testSurvey->languagesettings['ar']->surveyls_alias = $tmpArSurveyAlias;
        self::$testSurvey->languagesettings['de']->surveyls_alias = $tmpDeSurveyAlias;
        Yii::app()->setConfig('publicurl', $tmpPublicUrl);
        $urlManager->urlFormat = $tmpUrlFormat;
    }
}
