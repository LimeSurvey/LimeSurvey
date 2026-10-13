<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyStatus;
use LimeSurvey\Models\Services\SurveyAggregateService;
use LimeSurvey\DI;
use LimeSurvey\ObjectPatch\{
    Op\OpStandard,
    OpHandler\OpHandlerException
};
use ls\tests\TestBaseClass;
use Mockery;

/**
 * @testdox OpHandlerSurveyStatus
 */
class OpHandlerSurveyStatusTest extends TestBaseClass
{
    public function tearDown(): void
    {
        DI::getContainer()->set(
            SurveyAggregateService::class,
            \DI\autowire(SurveyAggregateService::class)
        );
        parent::tearDown();
    }

    /**
     * @testdox activation blocked by a plugin throws with the plugin message
     */
    public function testActivationBlockedByPluginThrowsPluginMessage()
    {
        $this->mockService(
            'activate',
            ['error' => 'plugin', 'message' => 'Can not activate without expiration date.']
        );

        $this->expectException(OpHandlerException::class);
        $this->expectExceptionMessage('Can not activate without expiration date.');
        $this->handle(['activate' => true]);
    }

    /**
     * @testdox failed activation without message throws with error code
     */
    public function testActivationErrorThrowsWithErrorCode()
    {
        $this->mockService('activate', ['error' => 'surveytablecreation']);

        $this->expectException(OpHandlerException::class);
        $this->expectExceptionMessage('(surveytablecreation)');
        $this->handle(['activate' => true]);
    }

    /**
     * @testdox deactivation blocked by a plugin throws with the plugin message
     */
    public function testDeactivationBlockedByPluginThrowsPluginMessage()
    {
        $this->mockService(
            'deactivate',
            ['beforeDeactivate' => ['success' => false, 'message' => 'Not today.']]
        );

        $this->expectException(OpHandlerException::class);
        $this->expectExceptionMessage('Not today.');
        $this->handle(['deactivate' => true]);
    }

    /**
     * @testdox successful activation does not throw
     */
    public function testActivationSuccessDoesNotThrow()
    {
        $this->mockService('activate', ['status' => 'OK']);

        $this->assertIsArray($this->handle(['activate' => true]));
    }

    private function mockService(string $method, array $result): void
    {
        $service = Mockery::mock(SurveyAggregateService::class);
        $service->shouldReceive($method)->once()->andReturn($result);
        DI::getContainer()->set(SurveyAggregateService::class, $service);
    }

    private function handle(array $props)
    {
        $op = OpStandard::factory('surveyStatus', 'update', 123456, $props, ['id' => 123456]);
        $opHandler = DI::getContainer()->get(OpHandlerSurveyStatus::class);
        $this->assertTrue($opHandler->canHandle($op));
        return $opHandler->handle($op);
    }
}
