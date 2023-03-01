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
     * The survey is not active.
     */
    public function testInactiveSurvey(): void
    {
        $survey = new \Survey();
        $survey->active = 'N';

        $icon = $survey->getRunning();

        $this->assertSame(
            '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $survey->sid) . '" class="survey-state" data-toggle="tooltip" title="' . gT('Inactive') . '"><span class="fa fa-stop text-warning"></span><span class="sr-only">' . gT('Inactive') . '</span></a>',
            $icon,
            'The correct icon for an inactive survey was not returned.'
        );
    }

    /**
     * The survey is active but it has no start or expire dates set.
     */
    public function testActiveSurveyNoDates(): void
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $icon = $survey->getRunning();

        $this->assertSame(
            '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $survey->sid) . '" class="survey-state" data-toggle="tooltip" title="' . gT('Active') . '"><span class="fa fa-play text-success"></span><span class="sr-only">' . gT('Active') . '</span></a>',
            $icon,
            'The correct icon for an active survey with no dates was not returned.'
        );
    }

    /**
     * The survey is active, it has a start date in the past but no expire date.
     */
    public function testActiveSurveyNoExpireDate(): void
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $threeDaysAgo = date_create()->sub(self::$intervals['threeDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $threeDaysAgo;

        $expectedIcon = '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $survey->sid) . '" class="survey-state" data-toggle="tooltip" title="' . gT('End: Never') . '"><span class="fa fa-play text-success"></span><span class="sr-only">' . gT('End: Never') . '</span></a>';
        $icon = $survey->getRunning();

        $this->assertSame(
            $expectedIcon,
            $icon,
            'The correct icon for an active survey with a start date set, but no expire date was not returned.'
        );

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+420 minutes');

        $newIcon = $survey->getRunning();

        $this->assertSame(
            $expectedIcon,
            $newIcon,
            'The correct icon for an active survey with a start date set, but no expire date was not returned. (Time adjust test).'
        );
    }

    /**
     * The survey is active, it has an expire date in the future but no start date.
     */
    public function testActiveSurveyNoStartDate(): void
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $inFourDays = date_create()->add(self::$intervals['fourDays'])->format('Y-m-d H:i:s');

        $survey->expires = $inFourDays;

        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));

        $expectedIcon = '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $survey->sid) . '" class="survey-state" data-toggle="tooltip" title="' . sprintf(gT('End: %s'), $sExpires) . '"><span class="fa fa-play text-success"></span><span class="sr-only">' . sprintf(gT('End: %s'), $sExpires) . '</span></a>';
        $icon = $survey->getRunning();

        $this->assertSame(
            $expectedIcon,
            $icon,
            'The correct icon for an active survey with an expire date in the future but no start date was not returned.'
        );

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+120 minutes');

        $newIcon = $survey->getRunning();

        $this->assertSame(
            $expectedIcon,
            $newIcon,
            'The correct icon for an active survey with an expire date in the future but no start date was not returned. (Time adjust test).'
        );
    }

    /**
     * The survey is active, it has an expire date in the future and a start date in the past.
     */
    public function testActiveSurveyExpireDateInTheFutureStartDateInThePast(): void
    {

        $survey = new \Survey();
        $survey->active = 'Y';

        $oneDayAgo = date_create()->sub(self::$intervals['oneDay'])->format('Y-m-d H:i:s');
        $inFiveDays = date_create()->add(self::$intervals['fiveDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $oneDayAgo;
        $survey->expires = $inFiveDays;

        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));

        $expectedIcon = '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $survey->sid) . '" class="survey-state" data-toggle="tooltip" title="' . sprintf(gT('End: %s'), $sExpires) . '"><span class="fa fa-play text-success"></span><span class="sr-only">' . sprintf(gT('End: %s'), $sExpires) . '</span></a>';
        $icon = $survey->getRunning();

        $this->assertSame(
            $expectedIcon,
            $icon,
            'The correct icon for an active survey with an expire date in the future and a start date in the past was not returned.'
        );

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+180 minutes');

        $newIcon = $survey->getRunning();

        $this->assertSame(
            $expectedIcon,
            $newIcon,
            'The correct icon for an active survey with an expire date in the future and a start date in the past was not returned. (Time adjust test).'
        );
    }

    /**
     * The survey has a start date in the future and no expire date.
     */
    public function testSurveyWillStartNoExpireDate(): void
    {

        $survey = new \Survey();
        $survey->active = 'Y';

        $inSixDays = date_create()->add(self::$intervals['sixDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $inSixDays;

        $sStart = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->startdate))));
        $expectedIcon = '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $survey->sid) . '" class="survey-state" data-toggle="tooltip" title="' . sprintf(gT('Start: %s'), $sStart) . '"><span class="fa fa-clock-o text-warning"></span><span class="sr-only">' . sprintf(gT('Start: %s'), $sStart) . '</span></a>';
        $icon = $survey->getRunning();

        $this->assertSame(
            $expectedIcon,
            $icon,
            'The correct icon for a survey with a start date in the future and no expire date was not returned.'
        );

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+240 minutes');

        $newIcon = $survey->getRunning();

        $this->assertSame(
            $expectedIcon,
            $newIcon,
            'The correct icon for a survey with a start date in the future and no expire date was not returned. (Time adjust test).'
        );
    }

    /**
     * The survey has a start date in the future and an expire date in the future.
     */
    public function testSurveyWillStart(): void
    {

        $survey = new \Survey();
        $survey->active = 'Y';

        $inFourDays = date_create()->add(self::$intervals['fourDays'])->format('Y-m-d H:i:s');
        $inSevenDays = date_create()->add(self::$intervals['sevenDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $inFourDays;
        $survey->expires = $inSevenDays;

        $sStart = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->startdate))));
        $expectedIcon = '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $survey->sid) . '" class="survey-state" data-toggle="tooltip" title="' . sprintf(gT('Start: %s'), $sStart) . '"><span class="fa fa-clock-o text-warning"></span><span class="sr-only">' . sprintf(gT('Start: %s'), $sStart) . '</span></a>';
        $icon = $survey->getRunning();

        $this->assertSame(
            $expectedIcon,
            $icon,
            'The correct icon for a survey with a start date in the future and no expire date was not returned.'
        );

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+300 minutes');

        $newIcon = $survey->getRunning();

        $this->assertSame(
            $expectedIcon,
            $newIcon,
            'The correct icon for a survey with a start date in the future and no expire date was not returned. (Time adjust test).'
        );
    }

    /**
     * The survey has an expire date in the past and no start date.
     */
    public function testSurveyExpiredNoStartdate(): void
    {

        $survey = new \Survey();
        $survey->active = 'Y';

        $threeDaysAgo = date_create()->sub(self::$intervals['threeDays'])->format('Y-m-d H:i:s');

        $survey->expires = $threeDaysAgo;

        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));
        $expectedIcon = '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $survey->sid) . '" class="survey-state" data-toggle="tooltip" title="' . sprintf(gT('Expired: %s'), $sExpires) . '"><span class="fa fa fa-step-forward text-warning"></span><span class="sr-only">' . sprintf(gT('Expired: %s'), $sExpires) . '</span></a>';
        $icon = $survey->getRunning();

        $this->assertSame(
            $expectedIcon,
            $icon,
            'The correct icon for an expired survey with a start date in the past and no start date was not returned.'
        );

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+360 minutes');

        $newIcon = $survey->getRunning();

        $this->assertSame(
            $expectedIcon,
            $newIcon,
            'The correct icon for an expired survey with a start date in the past and no start date was not returned. (Time adjust test).'
        );
    }

    /**
     * The survey has an expire date in the past and a start date in the past.
     */
    public function testSurveyExpired(): void
    {
        $survey = new \Survey();
        $survey->active = 'Y';

        $fiveDaysAgo = date_create()->sub(self::$intervals['fiveDays'])->format('Y-m-d H:i:s');
        $sevenDaysAgo = date_create()->sub(self::$intervals['sevenDays'])->format('Y-m-d H:i:s');

        $survey->startdate = $sevenDaysAgo;
        $survey->expires = $fiveDaysAgo;

        $sExpires = convertToGlobalSettingFormat(date("Y-m-d H:i:s", strtotime(\Yii::app()->getConfig('timeadjust'), strtotime($survey->expires))));
        $expectedIcon = '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $survey->sid) . '" class="survey-state" data-toggle="tooltip" title="' . sprintf(gT('Expired: %s'), $sExpires) . '"><span class="fa fa fa-step-forward text-warning"></span><span class="sr-only">' . sprintf(gT('Expired: %s'), $sExpires) . '</span></a>';
        $icon = $survey->getRunning();

        $this->assertSame(
            $expectedIcon,
            $icon,
            'The correct icon for an expired survey with a start date in the past and a start date in the past was not returned.'
        );

        //Test with time adjust.
        \SettingGlobal::setSetting('timeadjust', '+60 minutes');

        $newIcon = $survey->getRunning();

        $this->assertSame(
            $expectedIcon,
            $newIcon,
            'The correct icon for an expired survey with a start date in the past and a start date in the past was not returned. (Time adjust test).'
        );
    }
}
