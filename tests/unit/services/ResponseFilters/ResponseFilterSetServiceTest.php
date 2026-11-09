<?php

namespace ls\tests\unit\services\ResponseFilters;

use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSet;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSetService;
use Permission;
use PHPUnit\Framework\TestCase;
use Survey;

class ResponseFilterSetServiceTest extends TestCase
{
    private const FIELD_MAP = [
        '111X1X10' => ['fieldname' => '111X1X10', 'qid' => 10, 'type' => 'S'],
    ];

    private function survey(): Survey
    {
        $survey = $this->getMockBuilder(Survey::class)
            ->onlyMethods(['getHasTokensTable'])
            ->getMock();
        $survey->method('getHasTokensTable')->willReturn(false);
        $survey->sid = 111;

        return $survey;
    }

    /** @param string[] $granted The survey permissions the user has */
    private function service(array $granted): ResponseFilterSetService
    {
        $permission = $this->createMock(Permission::class);
        $permission->method('hasSurveyPermission')->willReturnCallback(
            static fn(int $surveyId, string $name): bool => in_array($name, $granted, true)
        );

        return new ResponseFilterSetService($permission);
    }

    private function resolve(array $entries, array $granted): array
    {
        return $this->service($granted)->resolve(
            ResponseFilterSet::fromRequestValue($entries),
            $this->survey(),
            static fn(): array => self::FIELD_MAP
        );
    }

    /**
     * @testdox No filter resolves to nothing
     */
    public function testNoFilterResolvesToNothing(): void
    {
        $this->assertSame([], $this->resolve([], []));
    }

    /**
     * @testdox A question filter is resolved against the field map
     */
    public function testQuestionFilterIsResolved(): void
    {
        $resolved = $this->resolve([['source' => 'question', 'qid' => 10, 'text' => 'refund']], []);

        $this->assertCount(1, $resolved);
        $this->assertSame(['111X1X10'], $resolved[0]->getConditions()[0]->getKeys());
    }

    /**
     * @testdox A participant filter needs the tokens permission
     */
    public function testParticipantFilterNeedsTokensPermission(): void
    {
        $this->expectException(PermissionDeniedException::class);

        $this->resolve(
            [['source' => 'participant', 'attribute' => '{TOKEN:EMAIL}', 'value' => 'example.org']],
            ['responses']
        );
    }

    /**
     * @testdox The field map is only built when there is a filter to resolve
     */
    public function testFieldMapIsOnlyBuiltWhenNeeded(): void
    {
        $built = 0;
        $fieldMap = static function () use (&$built): array {
            $built++;
            return self::FIELD_MAP;
        };

        $this->service([])->resolve(ResponseFilterSet::fromRequestValue([]), $this->survey(), $fieldMap);
        $this->assertSame(0, $built);

        try {
            $this->service([])->resolve(
                ResponseFilterSet::fromRequestValue(
                    [['source' => 'participant', 'attribute' => '{TOKEN:EMAIL}', 'value' => 'example.org']]
                ),
                $this->survey(),
                $fieldMap
            );
        } catch (PermissionDeniedException $exception) {
            // Expected; the map still shouldn't have been built.
        }
        $this->assertSame(0, $built);

        $this->service([])->resolve(
            ResponseFilterSet::fromRequestValue([['source' => 'question', 'qid' => 10, 'text' => 'refund']]),
            $this->survey(),
            $fieldMap
        );
        $this->assertSame(1, $built);
    }
}
