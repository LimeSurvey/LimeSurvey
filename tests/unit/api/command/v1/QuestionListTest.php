<?php

namespace ls\tests\unit\api\command\v1;

use Permission;
use Survey;
use QuestionGroup;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\QuestionList;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\Auth\AuthSession;
use Mockery;

/**
 * @testdox API command v1 QuestionList.
 */
class QuestionListTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * @testdox Returns invalid session response (error unauthorised) if session key is not valid.
     */
    public function testQuestionListInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'surveyID' => 'surveyID',
            'groupID' => 'groupID',
            'language' => 'language'
        ));
        $response = (new QuestionList())->run($request);

        $this->assertResponseInvalidSession($response);

        $this->assertResponseDataStatus(
            $response,
            AuthSession::ERROR_INVALID_SESSION_KEY
        );
    }

    /**
     * @testdox Returns error not-found if survey id is not valid.
     */
    public function testQuestionListInvalidSurveyId()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'surveyID' => 'surveyID',
            'groupID' => 'groupID',
            'language' => 'language'
        ));

        $mockAuthSession = Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $command = new QuestionList();
        $command->setAuthSession($mockAuthSession);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorNotFound()
        );

        $this->assertResponseDataStatus(
            $response,
            'Error: Invalid survey ID'
        );
    }

    /**
     * @testdox Returns invalid session response (error unauthorised) user does not have permission.
     */
    public function testQuestionListNoPermission()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'surveyID' => 'mock',
            'groupID' => 'groupID',
            'language' => 'language'
        ));

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockSurveyModel= Mockery::mock(Survey::class);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'survey', 'read', null)
            ->andReturns(false);

        $command = new QuestionList();
        $command->setAuthSession($mockAuthSession);
        $command->setPermissionModel($mockModelPermission);
        $command->setSurveyModel($mockSurveyModel);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorUnauthorised()
        );

        $this->assertResponseDataStatus(
            $response,
            'No permission'
        );
    }


    /**
     * @testdox Returns error bad request if language is not valid.
     */
    public function testQuestionListInvalidLanguage()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'surveyID' => 'mock',
            'groupID' => 'groupID',
            'language' => 'invalid-language'
        ));

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockSurveyModel= Mockery::mock(Survey::class);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'survey', 'read', null)
            ->andReturns(true);

        $command = new QuestionList();
        $command->setAuthSession($mockAuthSession);
        $command->setPermissionModel($mockModelPermission);
        $command->setSurveyModel($mockSurveyModel);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorBadRequest()
        );

        $this->assertResponseDataStatus(
            $response,
            'Error: Invalid language'
        );
    }

    /**
     * @testdox Returns error bad request if group is not valid.
     */
    public function testQuestionListInvalidGroup()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'surveyID' => 'mock',
            'groupID' => 'invalid-group-id',
            'language' => 'en'
        ));

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockSurveyModel = new Survey();
        $mockSurveyModel->setAttributes(
            array('allLanguages' => array('en')),
            false
        );

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'survey', 'read', null)
            ->andReturns(true);

        $command = new QuestionList();
        $command->setAuthSession($mockAuthSession);
        $command->setPermissionModel($mockModelPermission);
        $command->setSurveyModel($mockSurveyModel);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorNotFound()
        );

        $this->assertResponseDataStatus(
            $response,
            'Error: group not found'
        );
    }

    /**
     * @testdox Returns error success if no questions found.
     */
    public function testQuestionListNoQuestionsFound()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'surveyID' => 'mock',
            'groupID' => 'invalid-group-id',
            'language' => 'en'
        ));

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockSurveyModel = new Survey();
        $mockSurveyModel->setAttributes(
            array('allLanguages' => array('en')),
            false
        );

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'survey', 'read', null)
            ->andReturns(true);

        $command = new QuestionList();
        $command->setAuthSession($mockAuthSession);
        $command->setPermissionModel($mockModelPermission);
        $command->setSurveyModel($mockSurveyModel);
        $command->setQuestionGroupModel(new QuestionGroup());

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusSuccess()
        );

        $this->assertResponseDataStatus(
            $response,
            'No questions found'
        );
    }
}
