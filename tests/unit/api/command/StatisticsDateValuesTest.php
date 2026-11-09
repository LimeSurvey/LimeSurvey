<?php

namespace ls\tests\unit\api\command;

use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Libraries\Api\Command\V1\StatisticsDateValues;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSetService;
use LimeSurvey\Models\Services\SurveyStatistics\DateTimeValues;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use Permission;
use PHPUnit\Framework\TestCase;
use Survey;

class StatisticsDateValuesTest extends TestCase
{
    private const SURVEY_ID = 123;
    private const QID = 5;

    private array $originalGet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalGet = $_GET;
    }

    protected function tearDown(): void
    {
        $_GET = $this->originalGet;
        parent::tearDown();
    }

    /**
     * @param string[] $granted The survey permissions the user has
     */
    private function makeCommand(array $granted, DateTimeValues $values): StatisticsDateValues
    {
        $permission = $this->createMock(Permission::class);
        $permission->method('hasSurveyPermission')->willReturnCallback(
            static fn(int $surveyId, string $name): bool => in_array($name, $granted, true)
        );

        $survey = new Survey();
        $survey->sid = self::SURVEY_ID;
        $survey->language = 'en';

        return new class (
            $permission,
            new ResponseFactory(),
            new StatisticsResponseFilters(),
            $values,
            new ResponseFilterSetService($permission),
            $survey
        ) extends StatisticsDateValues {
            private Survey $survey;

            public function __construct($permission, $factory, $filters, $values, $filterSetService, Survey $survey)
            {
                parent::__construct($permission, $factory, $filters, $values, $filterSetService);
                $this->survey = $survey;
            }

            protected function findSurvey(int $surveyId): ?Survey
            {
                return $this->survey;
            }
        };
    }

    private function runCommand(array $query, array $granted, DateTimeValues $values)
    {
        $_GET = $query;

        return $this->makeCommand($granted, $values)->run(new Request(['_id' => self::SURVEY_ID]));
    }

    /**
     * @testdox Without the statistics permission nothing is read
     */
    public function testStatisticsPermissionIsRequired()
    {
        $values = $this->createMock(DateTimeValues::class);
        $values->expects($this->never())->method('get');

        $response = $this->runCommand(['qid' => self::QID], ['responses'], $values);

        $this->assertInstanceOf(StatusErrorUnauthorised::class, $response->getStatus());
    }

    /**
     * @testdox A missing question ID is a bad request
     */
    public function testQuestionIdIsRequired()
    {
        $values = $this->createMock(DateTimeValues::class);
        $values->expects($this->never())->method('get');

        $response = $this->runCommand([], ['statistics'], $values);

        $this->assertInstanceOf(StatusErrorBadRequest::class, $response->getStatus());
    }

    /**
     * @testdox Decrypting needs the responses permission on top of statistics
     */
    public function testDecryptNeedsResponsesPermission()
    {
        $values = $this->createMock(DateTimeValues::class);
        $values->expects($this->never())->method('get');

        $response = $this->runCommand(['qid' => self::QID, 'decrypt' => '1'], ['statistics'], $values);

        $this->assertInstanceOf(StatusErrorUnauthorised::class, $response->getStatus());
    }

    /**
     * @testdox Decrypting is passed on with both permissions
     */
    public function testDecryptIsPassedOnWithBothPermissions()
    {
        $result = ['hasTime' => true, 'total' => 0, 'days' => []];
        $values = $this->createMock(DateTimeValues::class);
        $values->expects($this->once())->method('get')
            ->with(self::SURVEY_ID, self::QID, null, [], true)
            ->willReturn($result);

        $response = $this->runCommand(
            ['qid' => self::QID, 'decrypt' => '1'],
            ['statistics', 'responses'],
            $values
        );

        $this->assertInstanceOf(StatusSuccess::class, $response->getStatus());
        $this->assertSame($result, $response->getData());
    }

    /**
     * @testdox Plain values need neither decrypt nor the responses permission
     */
    public function testPlainValuesOnlyNeedStatisticsPermission()
    {
        $values = $this->createMock(DateTimeValues::class);
        $values->expects($this->once())->method('get')
            ->with(self::SURVEY_ID, self::QID, null, [], false)
            ->willReturn([]);

        $response = $this->runCommand(['qid' => self::QID], ['statistics'], $values);

        $this->assertInstanceOf(StatusSuccess::class, $response->getStatus());
    }

    /**
     * @testdox A participant filter needs the tokens permission, as on the statistics endpoint
     */
    public function testParticipantFilterNeedsTokensPermission()
    {
        $values = $this->createMock(DateTimeValues::class);
        $values->expects($this->never())->method('get');
        $_GET = ['qid' => self::QID];

        $response = $this->makeCommand(['statistics'], $values)->run(new Request([
            '_id' => self::SURVEY_ID,
            'filterSet' => [[
                'join' => 'and',
                'source' => 'participant',
                'attribute' => '{TOKEN:EMAIL}',
                'value' => 'example.org',
            ]],
        ]));

        $this->assertInstanceOf(StatusErrorUnauthorised::class, $response->getStatus());
    }
}
