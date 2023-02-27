<?php

namespace ls\tests;

class SurveyTest extends BaseModelTestCase
{
    protected $modelClassName = \Survey::class;

    public static function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();

        \SettingGlobal::setSetting('timeadjust', '+0 hours');
    }

    public function testInactiveSurve(): void
    {
        $survey = new \Survey();
        $survey->active = 'N';

        $icon = $survey->getRunning();

        $this->assertSame(
            '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $survey->sid) . '" class="survey-state" data-toggle="tooltip" title="' . gT('Inactive') . '"><span class="fa fa-stop text-warning"></span><span class="sr-only">' . gT('Inactive') . '"</span></a>',
            $icon,
            'The correct icon for an inactive survey was not returned.'
        );
    }

    public function testActiveSurveyNoExpirationDate(): void
    {
        $survey = new \Survey();
        $survey->active = 'Y';
        $survey->datecreated = '2023-01-20 18:56:37';
        $survey->startdate = '2023-02-15 18:56:37';

        $icon = $survey->getRunning();

        $this->assertSame(
            '<a href="' . App()->createUrl('/surveyAdministration/view/surveyid/' . $this->sid) . '" class="survey-state" data-toggle="tooltip" title="' . gT('End: Never') . '"><span class="fa  fa-play text-success"></span><span class="sr-only">SS' . gT('End: Never') . '</span></a>',
            $icon,
            'The correct icon for an active survey with no expiration date was not returned.'
        );
    }
}
