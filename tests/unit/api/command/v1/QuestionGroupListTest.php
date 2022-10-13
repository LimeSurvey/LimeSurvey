<?php

namespace ls\tests\unit\api\command\v1;

use Eloquent\Phony\Phpunit\Phony;
use Survey;
use QuestionGroup;
use Permission;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\QuestionGroupList;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\ApiSession;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;

/**
 * @testdox API command v1 QuestionGroupList
 */
class QuestionGroupListTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * @testdox Returns invalid session response (error unauthorised) if session key is not valid.
     */
    public function testQuestionGroupListInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'surveyID' => 'surveyID',
            'language' => 'language'
        ));
        $command = new QuestionGroupList;
        $response = $command->run($request);

        $this->assertResponseInvalidSession($response);
    }

    /**
     * @testdox Returns error not-found if survey id is not valid.
     */
    public function testQuestionGroupListAddInvalidSurveyId()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'surveyID' => 'surveyID',
            'language' => 'language'
        ));

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $command = new QuestionGroupList;
        $command->setApiSession($mockApiSession);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorNotFound
        );
    }

    /**
     * @testdox Returns invalid session response (error unauthorised) users does not have permission.
     */
    public function testQuestionGroupListAddNoPermission()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'surveyID' => 'surveyID',
            'language' => 'language'
        ));

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $mockSurveyModelHandle = Phony::mock(Survey::class);
        $mockSurveyModelHandle->hasSurveyPermission
            ->returns(true);
        $mockSurveyModel = $mockSurveyModelHandle->get();

        $mockModelPermissionHandle = Phony::mock(Permission::class);
        $mockModelPermissionHandle->hasSurveyPermission
            ->returns(false);
        $mockModelPermission = $mockModelPermissionHandle->get();

        $command = new QuestionGroupList;
        $command->setApiSession($mockApiSession);
        $command->setSurveyModel($mockSurveyModel);
        $command->setPermissionModel($mockModelPermission);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorUnauthorised
        );
    }

    /**
     * @testdox Returns success with status if there are no groups for survey id.
     */
    public function testQuestionGroupListNoGroupsForSurveyId()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'surveyID' => 'surveyID',
            'language' => 'language'
        ));

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $mockSurveyModelHandle = Phony::mock(Survey::class);
        $mockSurveyModelHandle->hasSurveyPermission
            ->returns(true);
        $mockSurveyModel = $mockSurveyModelHandle->get();

        $mockModelPermissionHandle = Phony::mock(Permission::class);
        $mockModelPermissionHandle->hasSurveyPermission
            ->returns(true);
        $mockModelPermission = $mockModelPermissionHandle->get();

        $command = new QuestionGroupList;
        $command->setApiSession($mockApiSession);
        $command->setSurveyModel($mockSurveyModel);
        $command->setPermissionModel($mockModelPermission);
        $command->setQuestionGroupModelCollectionWithLn10sBySid([]);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusSuccess
        );
    }

    /**
     * @testdox Returns success with data.
     */
    public function testQuestionGroupListSuccessWithData()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'surveyID' => 'surveyID',
            'language' => 'en'
        ));

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $mockSurveyModelHandle = Phony::mock(Survey::class);
        $mockSurveyModelHandle->hasSurveyPermission
            ->returns(true);
        $mockSurveyModel = $mockSurveyModelHandle->get();

        $mockModelPermissionHandle = Phony::mock(Permission::class);
        $mockModelPermissionHandle->hasSurveyPermission
            ->returns(true);
        $mockModelPermission = $mockModelPermissionHandle->get();

        $questionGroup = new QuestionGroup;
        $questionGroup->setAttributes(array(
            'id' => '1',
            'gid' => '1',
            'sid' => '712896',
            'group_order' => '1',
            'randomization_group' => '',
            'grelevance' => '1',
            'language' => 'en'
        ), false);
        $questionGroup->questiongroupl10ns = array(
            'en' => array(
                'group_name' => 'Question group 1',
                'description' => ''
            )
        );

        $command = new QuestionGroupList;
        $command->setApiSession($mockApiSession);
        $command->setSurveyModel($mockSurveyModel);
        $command->setPermissionModel($mockModelPermission);
        $command->setQuestionGroupModelCollectionWithLn10sBySid([
            $questionGroup
        ]);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusSuccess
        );

        $responseData = $response->getData();


        $this->assertEquals(array(
            'id' => '1',
            'gid' => '1',
            'sid' => '712896',
            'group_order' => '1',
            'randomization_group' => '',
            'grelevance' => '1',
            'group_name' => 'Question group 1',
            'description' => '',
            'language' => 'en'
        ), $responseData[0]);
    }
}
