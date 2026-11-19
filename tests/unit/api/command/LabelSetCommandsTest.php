<?php

namespace ls\tests\unit\api\command;

use LabelSet;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorForbidden;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\V1\LabelSetCreate;
use LimeSurvey\Api\Command\V1\LabelSetDetail;
use LimeSurvey\Api\Command\V1\LabelSetList;
use LimeSurvey\Api\Command\V1\LabelSetOverwrite;
use LimeSurvey\Api\Command\V1\LabelSetSave;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputUserPermissions;
use LimeSurvey\Api\Command\V1\UserPermission;
use LimeSurvey\Models\Services\Exception\BadRequestException;
use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\Exception\PersistErrorException;
use LimeSurvey\Models\Services\LabelSetService;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use Mockery;
use Mockery\MockInterface;
use Permission;

/**
 * Tests for the label set REST commands of the new question editor.
 *
 * The service is mocked: Its logic is covered by LabelSetServiceTest. These tests check
 * how the commands read the request and map the service results to responses.
 */
class LabelSetCommandsTest extends TestBaseClass
{
    use AssertResponse;

    /** @var array Labels as sent by the editor */
    private const LABELS = [
        ['code' => 'A1', 'assessmentValue' => 0, 'l10ns' => ['en' => 'Yes']],
    ];

    /**
     * Verify the call count expectations of the mocks.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * A mocked label set service.
     *
     * @return LabelSetService&MockInterface
     */
    private function mockService(): LabelSetService
    {
        return Mockery::mock(LabelSetService::class);
    }

    /**
     * An unsaved label set model with the given id, as returned by create() and overwrite().
     *
     * @param int $lid Label set id
     * @return LabelSet
     */
    private function makeLabelSet(int $lid): LabelSet
    {
        $labelSet = new LabelSet();
        $labelSet->lid = $lid;
        return $labelSet;
    }

    /**
     * Assert that the response is an error with the given status and error code.
     *
     * @param Response $response The command response
     * @param \LimeSurvey\Api\Command\Response\Status $status Expected status
     * @param string $code Expected error code
     * @return void
     */
    private function assertError(Response $response, $status, string $code): void
    {
        $this->assertResponseStatus($response, $status);
        $this->assertResponseDataStatus($response, $code);
    }

    /**
     * The comma separated "languages" query param is split, and "updatable" is read as a flag.
     *
     * @return void
     */
    public function testListPassesTheFilters(): void
    {
        $labelSets = [['lid' => 1, 'name' => 'Yes/No']];
        $service = $this->mockService();
        $service->shouldReceive('getLabelSets')->once()->with(['en', 'de'], true)->andReturn($labelSets);

        $response = (new LabelSetList(new ResponseFactory(), $service))
            ->run(new Request(['languages' => 'en,de', 'updatable' => '1']));

        $this->assertResponseStatus($response, new StatusSuccess());
        $this->assertSame(['labelSets' => $labelSets], $response->getData());
    }

    /**
     * Without query params every label set is listed.
     *
     * @return void
     */
    public function testListWithoutFilters(): void
    {
        $service = $this->mockService();
        $service->shouldReceive('getLabelSets')->once()->with([], false)->andReturn([]);

        $response = (new LabelSetList(new ResponseFactory(), $service))->run(new Request([]));

        $this->assertResponseStatus($response, new StatusSuccess());
        $this->assertSame(['labelSets' => []], $response->getData());
    }

    /**
     * Only "1"/"true" switch the updatable filter on, and an array of languages is passed through.
     *
     * @return void
     */
    public function testListUpdatableFlagAndLanguageArray(): void
    {
        $service = $this->mockService();
        $service->shouldReceive('getLabelSets')->once()->with(['en'], false)->andReturn([]);

        $response = (new LabelSetList(new ResponseFactory(), $service))
            ->run(new Request(['languages' => ['en'], 'updatable' => '0']));

        $this->assertResponseStatus($response, new StatusSuccess());
    }

    /**
     * The label set of the route id is returned.
     *
     * @return void
     */
    public function testDetailReturnsTheLabelSet(): void
    {
        $labelSet = ['lid' => 5, 'name' => 'Yes/No', 'languages' => ['en'], 'labels' => []];
        $service = $this->mockService();
        $service->shouldReceive('getLabelSet')->once()->with(5)->andReturn($labelSet);

        $response = (new LabelSetDetail(new ResponseFactory(), $service))->run(new Request(['_id' => '5']));

        $this->assertResponseStatus($response, new StatusSuccess());
        $this->assertSame(['labelSet' => $labelSet], $response->getData());
    }

    /**
     * An unknown label set gives a 404.
     *
     * @return void
     */
    public function testDetailOfUnknownLabelSet(): void
    {
        $service = $this->mockService();
        $service->shouldReceive('getLabelSet')->once()->with(999)->andThrow(new NotFoundException('Label set not found'));

        $response = (new LabelSetDetail(new ResponseFactory(), $service))->run(new Request(['_id' => 999]));

        $this->assertError($response, new StatusErrorNotFound(), LabelSetDetail::ERROR_LABEL_SET_NOT_FOUND);
    }

    /**
     * A created label set is returned as it was saved.
     *
     * @return void
     */
    public function testCreateReturnsTheSavedLabelSet(): void
    {
        $saved = ['lid' => 42, 'name' => 'Yes/No', 'languages' => ['en'], 'labels' => self::LABELS];
        $service = $this->mockService();
        $service->shouldReceive('create')->once()->with('Yes/No', ['en'], self::LABELS)
            ->andReturn($this->makeLabelSet(42));
        $service->shouldReceive('getLabelSet')->once()->with(42)->andReturn($saved);

        $response = (new LabelSetCreate(new ResponseFactory(), $service))->run(new Request([
            'name' => 'Yes/No',
            'languages' => ['en'],
            'labels' => self::LABELS,
        ]));

        $this->assertResponseStatus($response, new StatusSuccess());
        $this->assertSame(['labelSet' => $saved], $response->getData());
    }

    /**
     * Invalid request bodies are rejected before the service is called.
     *
     * @return array<string, array{0: array}>
     */
    public function invalidCreateRequestProvider(): array
    {
        return [
            'name is not a string' => [['name' => ['x'], 'languages' => ['en'], 'labels' => self::LABELS]],
            'languages is not a list' => [['name' => 'x', 'languages' => 'en', 'labels' => self::LABELS]],
            'labels is not a list' => [['name' => 'x', 'languages' => ['en'], 'labels' => 'A1']],
            'id in the body' => [[
                '_content' => ['_id' => 1],
                'name' => 'x',
                'languages' => ['en'],
                'labels' => self::LABELS,
            ]],
        ];
    }

    /**
     * Invalid request bodies give a 400 without saving anything.
     *
     * @dataProvider invalidCreateRequestProvider
     * @param array $data Request data
     * @return void
     */
    public function testCreateRejectsInvalidRequests(array $data): void
    {
        $service = $this->mockService();
        $service->shouldNotReceive('create');

        $response = (new LabelSetCreate(new ResponseFactory(), $service))->run(new Request($data));

        $this->assertError($response, new StatusErrorBadRequest(), LabelSetSave::ERROR_LABEL_SET_INVALID);
    }

    /**
     * Service exceptions and the responses they must give.
     *
     * @return array<string, array{0: \Exception, 1: \LimeSurvey\Api\Command\Response\Status, 2: ?string}>
     */
    public function saveExceptionProvider(): array
    {
        return [
            'no permission' => [new PermissionDeniedException('Access denied'), new StatusErrorForbidden(), null],
            'invalid data' => [
                new BadRequestException('Invalid code'),
                new StatusErrorBadRequest(),
                LabelSetSave::ERROR_LABEL_SET_INVALID,
            ],
            'save failed' => [
                new PersistErrorException('Could not save'),
                new StatusErrorBadRequest(),
                LabelSetSave::ERROR_LABEL_SET_NOT_SAVED,
            ],
        ];
    }

    /**
     * Exceptions of create() are turned into error responses.
     *
     * @dataProvider saveExceptionProvider
     * @param \Exception $exception Exception thrown by the service
     * @param \LimeSurvey\Api\Command\Response\Status $status Expected response status
     * @param string|null $code Expected error code, null when the response has none
     * @return void
     */
    public function testCreateMapsServiceExceptions(\Exception $exception, $status, ?string $code): void
    {
        $service = $this->mockService();
        $service->shouldReceive('create')->once()->andThrow($exception);
        $service->shouldNotReceive('getLabelSet');

        $response = (new LabelSetCreate(new ResponseFactory(), $service))->run(new Request([
            'name' => 'Yes/No',
            'languages' => ['en'],
            'labels' => self::LABELS,
        ]));

        $this->assertResponseStatus($response, $status);
        if ($code !== null) {
            $this->assertResponseDataStatus($response, $code);
        }
    }

    /**
     * The label set of the route id is overwritten and returned.
     *
     * @return void
     */
    public function testOverwriteUsesTheRouteId(): void
    {
        $saved = ['lid' => 7, 'name' => 'Yes/No', 'languages' => ['en'], 'labels' => self::LABELS];
        $service = $this->mockService();
        $service->shouldReceive('overwrite')->once()->with(7, ['en'], self::LABELS)
            ->andReturn($this->makeLabelSet(7));
        $service->shouldReceive('getLabelSet')->once()->with(7)->andReturn($saved);

        $response = (new LabelSetOverwrite(new ResponseFactory(), $service))->run(new Request([
            '_id' => '7',
            'languages' => ['en'],
            'labels' => self::LABELS,
        ]));

        $this->assertResponseStatus($response, new StatusSuccess());
        $this->assertSame(['labelSet' => $saved], $response->getData());
    }

    /**
     * An id in the body is rejected, as the REST layer would let it replace the route id.
     *
     * @return void
     */
    public function testOverwriteRejectsIdInBody(): void
    {
        $service = $this->mockService();
        $service->shouldNotReceive('overwrite');

        $response = (new LabelSetOverwrite(new ResponseFactory(), $service))->run(new Request([
            '_id' => 8,
            '_content' => ['_id' => 8],
            'languages' => ['en'],
            'labels' => self::LABELS,
        ]));

        $this->assertError($response, new StatusErrorBadRequest(), LabelSetSave::ERROR_LABEL_SET_INVALID);
    }

    /**
     * Overwriting an unknown label set gives a 404.
     *
     * @return void
     */
    public function testOverwriteOfUnknownLabelSet(): void
    {
        $service = $this->mockService();
        $service->shouldReceive('overwrite')->once()->andThrow(new NotFoundException('Label set not found'));

        $response = (new LabelSetOverwrite(new ResponseFactory(), $service))->run(new Request([
            '_id' => 999,
            'languages' => ['en'],
            'labels' => self::LABELS,
        ]));

        $this->assertError($response, new StatusErrorNotFound(), LabelSetSave::ERROR_LABEL_SET_NOT_FOUND);
    }

    /**
     * Exceptions of overwrite() are turned into error responses.
     *
     * @dataProvider saveExceptionProvider
     * @param \Exception $exception Exception thrown by the service
     * @param \LimeSurvey\Api\Command\Response\Status $status Expected response status
     * @param string|null $code Expected error code, null when the response has none
     * @return void
     */
    public function testOverwriteMapsServiceExceptions(\Exception $exception, $status, ?string $code): void
    {
        $service = $this->mockService();
        $service->shouldReceive('overwrite')->once()->andThrow($exception);
        $service->shouldNotReceive('getLabelSet');

        $response = (new LabelSetOverwrite(new ResponseFactory(), $service))->run(new Request([
            '_id' => 7,
            'languages' => ['en'],
            'labels' => self::LABELS,
        ]));

        $this->assertResponseStatus($response, $status);
        if ($code !== null) {
            $this->assertResponseDataStatus($response, $code);
        }
    }

    /**
     * Label set permission combinations of the user permission endpoint.
     *
     * @return array<string, array{0: bool, 1: bool}>
     */
    public function labelSetPermissionProvider(): array
    {
        return [
            'create and update' => [true, true],
            'create only' => [true, false],
            'update only' => [false, true],
            'neither' => [false, false],
        ];
    }

    /**
     * The effective permissions of a survey include the label set rights the editor needs.
     *
     * @dataProvider labelSetPermissionProvider
     * @param bool $mayCreate Whether the user has the global label set create right
     * @param bool $mayUpdate Whether the user may overwrite at least one label set
     * @return void
     */
    public function testUserPermissionIncludesLabelSetRights(bool $mayCreate, bool $mayUpdate): void
    {
        $permission = Mockery::mock(Permission::class)->makePartial();
        $permission->shouldReceive('hasSurveyPermission')->andReturn(true);
        $permission->shouldReceive('hasGlobalPermission')->with('labelsets', 'create')->andReturn($mayCreate);
        $service = $this->mockService();
        $service->shouldReceive('hasUpdatableLabelSet')->once()->andReturn($mayUpdate);

        $command = new UserPermission(
            $permission,
            new TransformerOutputUserPermissions(),
            new ResponseFactory(),
            $service
        );
        $response = $command->run(new Request(['surveyId' => 123]));

        $this->assertResponseStatus($response, new StatusSuccess());
        $effective = $response->getData()['permissions']['effective'];
        $this->assertSame($mayCreate, $effective['labelSetsCreate']);
        $this->assertSame($mayUpdate, $effective['labelSetsUpdate']);
    }

    /**
     * Without a survey id no effective permissions are computed, so the label sets aren't checked.
     *
     * @return void
     */
    public function testUserPermissionWithoutSurveySkipsLabelSets(): void
    {
        $permission = Mockery::mock(Permission::class)->makePartial();
        $service = $this->mockService();
        $service->shouldNotReceive('hasUpdatableLabelSet');

        $command = new UserPermission(
            $permission,
            new TransformerOutputUserPermissions(),
            new ResponseFactory(),
            $service
        );
        $response = $command->run(new Request([]));

        $this->assertResponseStatus($response, new StatusSuccess());
        $this->assertArrayNotHasKey('effective', $response->getData()['permissions']);
    }
}
