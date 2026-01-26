<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveysGroupCreator;
use PHPUnit\Framework\TestCase;
use LSHttpRequest;
use LSWebUser;
use SurveysGroups;
use SurveysGroupsettings;

class SurveysGroupCreatorTest extends TestCase
{
    public static function setupBeforeClass(): void
    {
        \Yii::import('application.helpers.common_helper', true);
        \Yii::import('application.helpers.globalsettings_helper', true);
    }

    /**
     * Fail when POST lacks mandatory fields.
     */
    public function testBasicFailure()
    {
        // No need to mock LSWebUser.
        $user = new LSWebUser();
        $user->id = 1;

        // Mock getPost for the request.
        $request = $this
            ->getMockBuilder(LSHttpRequest::class)
            ->setMethods(['getPost'])
            ->getMock();
        $request->method('getPost')->willReturn([
            'name' => 'moo'
        ]);

        $surveysGroups = $this->getSurveysGroupsMock();
        $surveysGroupsettings = $this->getSurveysGroupssettingsMock();

        $service = new SurveysGroupCreator(
            $request,
            $user,
            $surveysGroups,
            $surveysGroupsettings
        );
        $this->assertFalse($service->save());
        // Exactly two errors when title and sort order are null.
        $this->assertCount(2, $surveysGroups->errors);
    }

    /**
     * @return SurveysGroups mock object
     */
    private function getSurveysGroupsMock()
    {
        $surveysGroups = $this
            ->getMockBuilder(SurveysGroups::class)
            ->setMethods(['save', 'attributes'])
            ->getMock();
        $surveysGroups->method('save')->will(
            $this->returnCallback(
                function () use ($surveysGroups) {
                    return $surveysGroups->validate();
                }
            )
        );

        $surveysGroups->method('attributes')->willReturn([
            'gsid',
            'name',
            'title',
            'description',
            'sortorder',
            'owner_id',
            'parent_id',
            'alwaysavailable',
            'created',
            'modified',
            'created_by',
            'parentgroup',
            'hasSurveys'
        ]);

        return $surveysGroups;
    }

    /**
     * @return SurveysGroupsettings mock object
     */
    private function getSurveysGroupssettingsMock()
    {
        $surveysGroupsettings = $this
            ->getMockBuilder(SurveysGroupsettings::class)
            ->setMethods(['save', 'attributes'])
            ->getMock();
        $surveysGroupsettings->method('save')->will(
            $this->returnCallback(
                function () use ($surveysGroupsettings) {
                    return $surveysGroupsettings->validate();
                }
            )
        );

        $surveysGroupsettings->method('attributes')->willReturn([
            'gsid',
            'owner_id',
            'admin',
            'expires',
            'startdate',
            'adminemail',
            'anonymized',
            'format',
            'savetimings',
            'template',
            'datestamp',
            'usecookie',
            'allowregister',
            'allowsave',
            'autonumber_start',
            'autoredirect',
            'allowprev',
            'printanswers',
            'ipaddr',
            'refurl',
            'savequotaexit',
            'datecreated',
            'showsurveypolicynotice',
            'publicstatistics',
            'publicgraphs',
            'listpublic',
            'htmlemail',
            'sendconfirmation',
            'tokenanswerspersistence',
            'assessments',
            'usecaptcha',
            'bounce_email',
            'attributedescriptions',
            'emailresponseto',
            'emailnotificationto',
            'tokenlength',
            'showxquestions',
            'showgroupinfo',
            'shownoanswer',
            'showqnumcode',
            'showwelcome',
            'showprogress',
            'questionindex',
            'navigationdelay',
            'nokeyboard',
            'alloweditaftercompletion',
            'ipanonymize'
        ]);

        return $surveysGroupsettings;
    }
}
