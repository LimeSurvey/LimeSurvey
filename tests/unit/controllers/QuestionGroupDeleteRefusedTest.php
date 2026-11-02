<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;
use QuestionGroup;
use QuestionGroupsAdministrationController;
use Survey;

/**
 * Deleting a question group that the service refuses to delete (active survey or
 * missing permission) must show an error message instead of a server error (#19880).
 */
class QuestionGroupDeleteRefusedTest extends TestBaseClass
{
    /** @var QuestionGroupsAdministrationController */
    private $controller;

    /** @var \User[] */
    private $testUsers = [];

    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::importSurvey(self::$surveysFolder . '/limesurvey_survey_143933.lss');
    }

    /**
     * Creates the controller with rendering stubbed out.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        \Yii::import('application.controllers.QuestionGroupsAdministrationController', true);
        // Rendering needs a full HTTP request context, which unit tests don't have
        $this->controller = new class ('questionGroupsAdministration') extends QuestionGroupsAdministrationController {
            /** @var array[] Data passed to each renderPartial() call */
            public $renderedPartials = [];

            public function renderPartial($view, $data = null, $return = false, $processOutput = false)
            {
                $this->renderedPartials[] = $data;
                return '';
            }
        };
        \Yii::app()->setController($this->controller);
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    /**
     * Restores the survey state, the request and the logged in user.
     * @return void
     */
    public function tearDown(): void
    {
        Survey::model()->updateByPk(self::$surveyId, ['active' => 'N']);
        Survey::model()->resetCache();
        $_POST = [];
        unset($_SERVER['REQUEST_METHOD']);
        \Yii::app()->user->setId(1);
        \Yii::app()->session['loginID'] = 1;
        foreach ($this->testUsers as $user) {
            \Permission::model()->deleteAllByAttributes(['uid' => $user->uid]);
            $user->delete();
        }
        $this->testUsers = [];
        $this->controller = null;
        parent::tearDown();
    }

    /**
     * Deleting a group of an active survey is refused with a message about the active survey.
     * @return void
     */
    public function testDeleteGroupOfActiveSurveyIsRefused()
    {
        Survey::model()->updateByPk(self::$surveyId, ['active' => 'Y']);
        Survey::model()->resetCache();
        $gid = $this->getFirstGroupId();

        $response = $this->deleteGroup($gid);

        $this->assertFalse($response['success']);
        $this->assertSame(0, $response['deletedGroups']);
        $this->assertSame(gT("It is not possible to add/delete groups if the survey is active."), $response['message']);
        $this->assertNotNull(QuestionGroup::model()->findByPk($gid));
    }

    /**
     * Deleting a group without the survey content delete permission is refused with "Access denied!".
     * @return void
     */
    public function testDeleteGroupWithoutPermissionIsRefused()
    {
        $userName = 'group_deleter_' . uniqid();
        $user = self::createUserWithPermissions([
            'users_name' => $userName,
            'full_name' => $userName,
            'email' => $userName . '@example.org',
            'lang' => 'auto',
            'password' => 'testpassword123',
        ], []);
        $this->testUsers[] = $user;
        \Yii::app()->user->setId($user->uid);
        \Yii::app()->session['loginID'] = $user->uid;
        $gid = $this->getFirstGroupId();

        $response = $this->deleteGroup($gid);

        $this->assertFalse($response['success']);
        $this->assertSame(0, $response['deletedGroups']);
        $this->assertSame(gT("Access denied!"), $response['message']);
        $this->assertNotNull(QuestionGroup::model()->findByPk($gid));
    }

    /**
     * Posts the delete request for the given group and returns the JSON response data.
     * @param int $gid
     * @return array
     */
    private function deleteGroup(int $gid): array
    {
        $_POST = ['gid' => $gid, 'surveyid' => self::$surveyId];
        $this->controller->actionDelete(true);
        $this->assertNotEmpty($this->controller->renderedPartials);
        return end($this->controller->renderedPartials)['data'];
    }

    /**
     * @return int
     */
    private function getFirstGroupId(): int
    {
        $group = QuestionGroup::model()->findByAttributes(['sid' => self::$surveyId]);
        $this->assertNotNull($group);
        return (int) $group->gid;
    }
}
