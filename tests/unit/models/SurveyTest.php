<?php

namespace ls\tests;

class SurveyTest extends BaseModelTestCase
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
    }

    public function setUp(): void
    {
        \SettingGlobal::setSetting('timeadjust', '+0 minutes');
    }

    /**
     * Survey state: inactive.
     */
    public function testInactiveSurveyState(): void
    {
        $survey = new \Survey();
        $survey->active = 'N';

        $state = $survey->getState();

        $this->assertSame('inactive', $state, 'Survey active property is ' . $survey->active);
    }

    /**
     * Survey state: expired.
     */
    public function testExpiredSurveyState(): void
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
    public function testWillRunSurveyState(): void
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
    public function testWillExpireSurveyState(): void
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
    public function testRunningSurveyState(): void
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $state = $survey->getState();

        $this->assertSame('running', $state, 'Survey active property is ' . $survey->active . ', no dates set.');
    }

    /**
     * The survey is not active.
     */
    public function testInactiveSurveyIcon(): void
    {
        $survey = new \Survey();
        $survey->active = 'N';

        $icon = $survey->getRunning();

        $this->assertStringContainsString(gT('Inactive'), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('fa fa-stop text-warning', $icon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey is active but it has no start or expire dates set.
     */
    public function testActiveSurveyIconNoDates(): void
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $icon = $survey->getRunning();

        $this->assertStringContainsString(gT('Active'), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('fa fa-play text-success', $icon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey is active, it has a start date in the past but no expire date.
     */
    public function testActiveSurveyIconNoExpireDate(): void
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $threeDaysAgo = date_create()->sub(self::$intervals['threeDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $threeDaysAgo;

        $icon = $survey->getRunning();

        $this->assertStringContainsString(gT('End: Never'), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('fa fa-play text-success', $icon, 'The icon link does not have the right css classes.');

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+420 minutes');

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(gT('End: Never'), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('fa fa-play text-success', $newIcon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey is active, it has an expire date in the future but no start date.
     */
    public function testActiveSurveyIconNoStartDate(): void
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $inFourDays = date_create()->add(self::$intervals['fourDays'])->format('Y-m-d H:i:s');

        $survey->expires = $inFourDays;

        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));

        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('End: %s'), $sExpires), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('fa fa-play text-success', $icon, 'The icon link does not have the right css classes.');

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+120 minutes');
        
        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('End: %s'), $sExpires), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('fa fa-play text-success', $newIcon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey is active, it has an expire date in the future and a start date in the past.
     */
    public function testActiveSurveyIconExpireDateInTheFutureStartDateInThePast(): void
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
        $this->assertStringContainsString('fa fa-play text-success', $icon, 'The icon link does not have the right css classes.');

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+180 minutes');
        
        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('End: %s'), $sExpires), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('fa fa-play text-success', $newIcon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey has a start date in the future and no expire date.
     */
    public function testSurveyIconWillStartNoExpireDate(): void
    {

        $survey = new \Survey();
        $survey->active = 'Y';

        $inSixDays = date_create()->add(self::$intervals['sixDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $inSixDays;

        $sStart = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->startdate))));
        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Start: %s'), $sStart), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('fa fa-clock-o text-warning', $icon, 'The icon link does not have the right css classes.');

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+240 minutes');
        
        $sStart = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->startdate))));

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Start: %s'), $sStart), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('fa fa-clock-o text-warning', $newIcon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey has a start date in the future and an expire date in the future.
     */
    public function testSurveyIconWillStart(): void
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
        $this->assertStringContainsString('fa fa-clock-o text-warning', $icon, 'The icon link does not have the right css classes.');

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+300 minutes');
        
        $sStart = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->startdate))));

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Start: %s'), $sStart), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('fa fa-clock-o text-warning', $newIcon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey has an expire date in the past and no start date.
     */
    public function testSurveyIconExpiredNoStartdate(): void
    {

        $survey = new \Survey();
        $survey->active = 'Y';

        $threeDaysAgo = date_create()->sub(self::$intervals['threeDays'])->format('Y-m-d H:i:s');

        $survey->expires = $threeDaysAgo;

        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));
        $icon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Expired: %s'), $sExpires), $icon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('fa fa fa-step-forward text-warning', $icon, 'The icon link does not have the right css classes.');

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+360 minutes');
        
        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Expired: %s'), $sExpires), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('fa fa fa-step-forward text-warning', $newIcon, 'The icon link does not have the right css classes.');
    }

    /**
     * The survey has an expire date in the past and a start date in the past.
     */
    public function testSurveyIconExpired(): void
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
        $this->assertStringContainsString('fa fa fa-step-forward text-warning', $icon, 'The icon link does not have the right css classes.');

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+60 minutes');
        
        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));

        $newIcon = $survey->getRunning();

        $this->assertStringContainsString(sprintf(gT('Expired: %s'), $sExpires), $newIcon, 'The icon link does not have the right text.');
        $this->assertStringContainsString('fa fa fa-step-forward text-warning', $newIcon, 'The icon link does not have the right css classes.');
    }
}
