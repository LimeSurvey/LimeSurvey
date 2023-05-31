<?php

namespace ls\tests;

class SurveySearchTest extends TestBaseClass
{
    public function testSurveyFoundOnGroupOne()
    {
        $testData = $this->createGroups();

        $s = new \Survey('search');
        $s->gsid = $testData['sgids'][0];

        $dataProvider = $s->search();
        $data = $dataProvider->getData();

        $this->assertNotEmpty($data, 'The survey was not found');
        $this->assertEquals($testData['sid'], $data[0]->sid, 'The survey id is incorrect.');

        // Preserve test data
        return $testData;
    }

    /**
     * @depends testSurveyFoundOnGroupOne
     */
    public function testSurveyFoundOnGroupTwo($testData)
    {
        $s = new \Survey('search');
        $s->gsid = $testData['sgids'][1];

        $dataProvider = $s->search();
        $data = $dataProvider->getData();

        $this->assertNotEmpty($data, 'The survey was not found');
        $this->assertEquals($testData['sid'], $data[0]->sid, 'The survey id is incorrect.');

        // Preserve test data
        return $testData;
    }

    /**
     * @depends testSurveyFoundOnGroupTwo
     */
    public function testSurveyFoundOnGroupThree($testData)
    {
        $s = new \Survey('search');
        $s->gsid = $testData['sgids'][2];

        $dataProvider = $s->search();
        $data = $dataProvider->getData();

        $this->assertNotEmpty($data, 'The survey was not found');
        $this->assertEquals($testData['sid'], $data[0]->sid, 'The survey id is incorrect.');

        // Preserve test data
        return $testData;
    }

    /**
     * @depends testSurveyFoundOnGroupThree
     */
    public function testSurveyFoundOnGroupFour($testData)
    {
        $s = new \Survey('search');
        $s->gsid = $testData['sgids'][3];

        $dataProvider = $s->search();
        $data = $dataProvider->getData();

        $this->assertNotEmpty($data, 'The survey was not found');
        $this->assertEquals($testData['sid'], $data[0]->sid, 'The survey id is incorrect.');
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
}
