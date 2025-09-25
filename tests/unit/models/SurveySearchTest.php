<?php

namespace ls\tests;

class SurveySearchTest extends TestBaseClass
{
    /**
     * Testing that survey in subgroup SG04
     * can be found when searching in group SG01.
     */
    public function testSurveyFoundOnGroupOne()
    {
        $testData = $this->createGroups();

        $s = new \Survey('search');
        $s->gsid = $testData['sgids'][0];

        $dataProvider = $s->search();
        $data = $dataProvider->getData();        

        $this->assertNotEmpty($data, 'The survey search results were unexpectedly empty');
        $this->assertEquals($testData['sid'], $data[0]->sid, 'The survey found was not the expected one');

        // Preserve test data
        return $testData;
    }

    /**
     * Testing that survey in subgroup SG04
     * can be found when searching in subgroup SG02.
     *
     * @depends testSurveyFoundOnGroupOne
     */
    public function testSurveyFoundOnGroupTwo($testData)
    {
        $s = new \Survey('search');
        $s->gsid = $testData['sgids'][1];

        $dataProvider = $s->search();
        $data = $dataProvider->getData();

        $this->assertNotEmpty($data, 'The survey search results were unexpectedly empty');
        $this->assertEquals($testData['sid'], $data[0]->sid, 'The survey found was not the expected one');

        // Preserve test data
        return $testData;
    }

    /**
     * Testing that survey in subgroup SG04
     * can be found when searching in subgroup SG03.
     *
     * @depends testSurveyFoundOnGroupTwo
     */
    public function testSurveyFoundOnGroupThree($testData)
    {
        $s = new \Survey('search');
        $s->gsid = $testData['sgids'][2];

        $dataProvider = $s->search();
        $data = $dataProvider->getData();

        $this->assertNotEmpty($data, 'The survey search results were unexpectedly empty');
        $this->assertEquals($testData['sid'], $data[0]->sid, 'The survey found was not the expected one');

        // Preserve test data
        return $testData;
    }

    /**
     * Testing that survey in subgroup SG04
     * can be found when searching in that group.
     *
     * @depends testSurveyFoundOnGroupThree
     */
    public function testSurveyFoundOnGroupFour($testData)
    {
        $s = new \Survey('search');
        $s->gsid = $testData['sgids'][3];

        $dataProvider = $s->search();
        $data = $dataProvider->getData();

        $this->assertNotEmpty($data, 'The survey search results were unexpectedly empty');
        $this->assertEquals($testData['sid'], $data[0]->sid, 'The survey found was not the expected one');

        // Preserve test data
        return $testData;
    }

    /**
     * Creating a new survey in subgroup SG03.
     * Testing that two surveys can be found when
     * searching in subgroup SG03.
     *
     * @depends testSurveyFoundOnGroupFour
     */
    public function testTwoSurveysFoundOnGroupThree($testData)
    {
        if (getenv('LOCAL_TEST')) {
            $this->markTestSkipped();
        }
        // Create a new survey in SG03.
        $surveyData = array(
            'gsid' => $testData['sgids'][2],
        );

        $survey = \Survey::model()->insertNewSurvey($surveyData);

        $s = new \Survey('search');
        $s->gsid = $testData['sgids'][2];

        $dataProvider = $s->search();
        $data = $dataProvider->getData();

        $sids = array($testData['sid'], $survey->sid);

        $this->assertNotEmpty($data, 'The survey search results were unexpectedly empty');
        $this->assertCount(2, $data, 'Two surveys should have been found.');

        $this->assertThat($sids, $this->contains($data[0]->sid), 'One of the expected surveys was not found.');
        $this->assertThat($sids, $this->contains($data[1]->sid), 'One of the expected surveys was not found.');

        // Preserve test data
        $testData['sidTwo'] = $survey->sid;
        return $testData;
    }

    /**
     * Creating a new survey in subgroup SG02.
     * Testing that three surveys can be found when
     * searching in group SG01.
     *
     * @depends testTwoSurveysFoundOnGroupThree
     */
    public function testThreeSurveysFoundOnGroupOne($testData)
    {
        // Create a new survey in SG02.
        $surveyData = array(
            'gsid' => $testData['sgids'][1],
        );

        $survey = \Survey::model()->insertNewSurvey($surveyData);

        $s = new \Survey('search');
        $s->gsid = $testData['sgids'][0];

        $dataProvider = $s->search();
        $data = $dataProvider->getData();

        $sids = array($testData['sid'], $testData['sidTwo'], $survey->sid);

        $this->assertNotEmpty($data, 'The survey search results were unexpectedly empty');
        $this->assertCount(3, $data, 'Three surveys should have been found.');

        $this->assertThat($sids, $this->contains($data[0]->sid), 'One of the expected surveys was not found.');
        $this->assertThat($sids, $this->contains($data[1]->sid), 'One of the expected surveys was not found.');
        $this->assertThat($sids, $this->contains($data[2]->sid), 'One of the expected surveys was not found.');

        // Preserve test data
        $testData['sidThree'] = $survey->sid;
        return $testData;
    }

    /**
     * Testing that all surveys can be found when
     * searching for any group (without a specific gsid)
     *
     * @depends testThreeSurveysFoundOnGroupOne
     */
    public function testAllSurveysFound($testData)
    {
        $s = new \Survey('search');
        $s->active = null; // Somehow this gets defaulted to N, so setting it to null.

        $dataProvider = $s->search();
        $dataProvider->pagination = false;
        $data = $dataProvider->getData();
        $totalItemCount = $dataProvider->getTotalItemCount();

        $sids = array($testData['sid'], $testData['sidTwo'], $testData['sidThree']);
        $surveyCount = (int)\Survey::model()->count();

        $this->assertNotEmpty($data, 'The survey search results were unexpectedly empty');
        $this->assertCount($surveyCount, $data, 'The number of surveys in the resultset found does not match the number of surveys in the database.');
        $this->assertEquals($surveyCount, $totalItemCount, 'The number of surveys found does not match the number of surveys in the database.');

        $this->assertThat($sids, $this->contains($data[0]->sid), 'One of the expected surveys was not found.');
        $this->assertThat($sids, $this->contains($data[1]->sid), 'One of the expected surveys was not found.');
        $this->assertThat($sids, $this->contains($data[2]->sid), 'One of the expected surveys was not found.');
    }

    private function createGroups()
    {
        $testData = array(
            'sgids' => array(),
            'sid' => 0,
        );

        // No need to mock LSWebUser.
        $user = new   \LSWebUser();
        $user->id = 1;

        $groups = array(
            array(
                'name' => 'SG01',
                'sortorder' => 1,
                'title' => 'Survey group one'
            ),
            array(
                'name' => 'SG02',
                'sortorder' => 2,
                'title' => 'Survey group two'
            ),
            array(
                'name' => 'SG03',
                'sortorder' => 3,
                'title' => 'Survey group three'
            ),
            array(
                'name' => 'SG04',
                'sortorder' => 4,
                'title' => 'Survey group four'
            ),
        );

        foreach ($groups as $group) {
            $model = new \SurveysGroups();
            $model->owner_id = $user->id;

            if (! empty($testData['sgids'])) {
                $model->parent_id = end($testData['sgids']);
                reset($testData['sgids']);
            }

            // Mock getPost for the request.
            $request = $this
                        ->getMockBuilder(\LSHttpRequest::class)
                        ->setMethods(['getPost'])
                        ->getMock();

            $request->method('getPost')->willReturn($group);

            $service = new \LimeSurvey\Models\Services\SurveysGroupCreator(
                $request,
                $user,
                $model,
                new \SurveysGroupsettings()
            );

            $service->save();

            $testData['sgids'][] = $model->gsid;
        }

        // This survey belongs to the last group.
        $surveyData = array(
            'gsid' => end($testData['sgids']),
        );

        reset($testData['sgids']);

        $survey = \Survey::model()->insertNewSurvey($surveyData);
        $testData['sid'] = $survey->sid;

        return $testData;
    }

    public static function tearDownAfterClass(): void
    {
        // Get superadmin permission.
        \Yii::app()->session['loginID'] = 1;

        // Get survey groups
        $surveyGroup = new \SurveysGroups();
        $surveyGroup->name = 'SG04';

        $groupDataProvider = $surveyGroup->search();
        $SG04 = $groupDataProvider->getData()[0];

        $parents = $SG04->getAllParents();

        // Get surveys
        $s = new \Survey('search');
        $s->gsid = $parents->gsid;

        $surveysDataProvider = $s->search();
        $surveys = $surveysDataProvider->getData();

        // Delete surveys
        foreach ($surveys as $survey) {
            \Survey::model()->deleteSurvey($survey->sid);
        }

        // Delete groups and group settings
        foreach ($parents as $parent) {
            $settingsModel = new \SurveysGroupsettings();
            $settings = $settingsModel->findByAttributes(array('gsid' => $parent->gsid));
            $settings->delete();
            $parent->delete();
        }

        $SG04->delete();
    }
}
