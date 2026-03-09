<?php

namespace ls\tests\unit\services;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;
use LimeSurvey\Models\Services\ExportSurveyResultsService;
use LimeSurvey\Models\Services\Export\ExportAnswerFormatter;
use LimeSurvey\Models\Services\Export\CsvExportWriter;
use LimeSurvey\Models\Services\Export\HtmlExportWriter;
use ls\tests\TestBaseClass;
use Mockery;
use RuntimeException;

/**
 * Unit tests for ExportSurveyResultsService
 *
 * @group services
 */
class ExportSurveyResultsServiceTest extends TestBaseClass
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test that unsupported export type throws InvalidArgumentException
     */
    public function testExportResponsesThrowsExceptionForUnsupportedType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported export type: random');

        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $answerMock = Mockery::mock(\Answer::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();

        $answerFormatterMock = new ExportAnswerFormatter();

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock
        );

        $service->exportResponses(123456, 'random');
    }

    /**
     * Test that non-existent survey throws RuntimeException
     */
    public function testExportResponsesThrowsExceptionForNonExistentSurvey()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Survey not found: 999999');

        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $surveyMock->shouldReceive('findByPk')
            ->with(999999)
            ->andReturn(null);

        $answerMock = Mockery::mock(\Answer::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();

        $answerFormatterMock = new ExportAnswerFormatter();

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock
        );

        $service->exportResponses(999999, 'csv');
    }

    /**
     * Test that CSV export returns expected structure
     */
    public function testCsvExportReturnsCorrectStructure()
    {
        $foundSurvey = Mockery::mock(\Survey::class)->makePartial();
        $foundSurvey->shouldReceive('__get')
            ->with('language')
            ->andReturn('en');
        $foundSurvey->shouldReceive('__get')
            ->with('sid')
            ->andReturn(123456);

        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $surveyMock->shouldReceive('findByPk')
            ->with(123456)
            ->andReturn($foundSurvey);

        $answerMock = Mockery::mock(\Answer::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();

        $answerFormatterMock = new ExportAnswerFormatter();

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('exportResponsesInChunks')
            ->once()
            ->andReturn([
                'content' => 'csv,content',
                'filePath' => null,
                'filename' => 'test.csv',
                'mimeType' => 'text/csv',
                'extension' => 'csv',
                'size' => 11,
                'responseCount' => 0
            ]);

        $result = $service->exportResponses(123456, 'csv');

        $this->assertIsArray($result);
        $this->assertEquals('csv', $result['extension']);
        $this->assertEquals('text/csv', $result['mimeType']);
    }

    /**
     * Test that XLSX export throws unsupported type exception
     */
    public function testXlsxExportThrowsUnsupportedException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported export type: xlsx');

        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $answerMock = Mockery::mock(\Answer::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();

        $answerFormatterMock = new ExportAnswerFormatter();

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock
        );

        $service->exportResponses(123456, 'xlsx');
    }

    /**
     * Test that XLS export throws unsupported type exception
     */
    public function testXlsExportThrowsUnsupportedException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported export type: xls');

        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $answerMock = Mockery::mock(\Answer::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();

        $answerFormatterMock = new ExportAnswerFormatter();

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock
        );

        $service->exportResponses(123456, 'xls');
    }

    /**
     * Test that HTML export returns correct structure
     */
    public function testHtmlExportReturnsCorrectStructure()
    {
        $foundSurvey = Mockery::mock(\Survey::class)->makePartial();
        $foundSurvey->shouldReceive('__get')
            ->with('language')
            ->andReturn('en');
        $foundSurvey->shouldReceive('__get')
            ->with('sid')
            ->andReturn(123456);

        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $surveyMock->shouldReceive('findByPk')
            ->andReturn($foundSurvey);

        $answerMock = Mockery::mock(\Answer::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();

        $answerFormatterMock = new ExportAnswerFormatter();

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('exportResponsesInChunks')
            ->once()
            ->andReturn([
                'content' => '<html></html>',
                'filePath' => null,
                'filename' => 'test.html',
                'mimeType' => 'text/html',
                'extension' => 'html',
                'size' => 100,
                'responseCount' => 0
            ]);

        $result = $service->exportResponses(123456, 'html');

        $this->assertEquals('html', $result['extension']);
        $this->assertEquals('text/html', $result['mimeType']);
    }

    /**
     * Test that service can be instantiated with valid dependencies
     */
    public function testServiceCanBeInstantiated()
    {
        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $answerMock = Mockery::mock(\Answer::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();

        $answerFormatterMock = new ExportAnswerFormatter();

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock
        );

        $this->assertInstanceOf(ExportSurveyResultsService::class, $service);
    }

    /**
     * Test that default language is used when not provided
     */
    public function testDefaultLanguageIsUsedWhenNotProvided()
    {
        $foundSurvey = Mockery::mock(\Survey::class)->makePartial();
        $foundSurvey->language = 'de';
        $foundSurvey->sid = 123456;

        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $surveyMock->shouldReceive('findByPk')
            ->andReturn($foundSurvey);

        $answerMock = Mockery::mock(\Answer::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();

        $answerFormatterMock = new ExportAnswerFormatter();

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('exportResponsesInChunks')
            ->once()
            ->withArgs(function ($surveyId, $exportType, $metadata, $chunkSize) {
                return $metadata['language'] === 'de';
            })
            ->andReturn([
                'content' => '',
                'filePath' => null,
                'filename' => 'test.csv',
                'mimeType' => 'text/csv',
                'extension' => 'csv',
                'size' => 0,
                'responseCount' => 0
            ]);

        $result = $service->exportResponses(123456, 'csv');

        $this->assertIsArray($result);
    }

    /**
     * Test that provided language overrides survey default
     */
    public function testProvidedLanguageOverridesSurveyDefault()
    {
        $foundSurvey = Mockery::mock(\Survey::class)->makePartial();
        $foundSurvey->shouldReceive('__get')
            ->with('language')
            ->andReturn('de');
        $foundSurvey->shouldReceive('__get')
            ->with('sid')
            ->andReturn(123456);

        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $surveyMock->shouldReceive('findByPk')
            ->andReturn($foundSurvey);

        $answerMock = Mockery::mock(\Answer::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();

        $answerFormatterMock = new ExportAnswerFormatter();

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('exportResponsesInChunks')
            ->once()
            ->withArgs(function ($surveyId, $exportType, $metadata, $chunkSize) {
                return $metadata['language'] === 'fr';
            })
            ->andReturn([
                'content' => '',
                'filePath' => null,
                'filename' => 'test.csv',
                'mimeType' => 'text/csv',
                'extension' => 'csv',
                'size' => 0,
                'responseCount' => 0
            ]);

        $result = $service->exportResponses(123456, 'csv', 'fr');

        $this->assertIsArray($result);
    }

    /**
     * Test that metadata is correctly passed to exportResponsesInChunks
     */
    public function testMetadataIsCorrectlyPassedToExport()
    {
        $foundSurvey = Mockery::mock(\Survey::class)->makePartial();
        $foundSurvey->language = 'en';
        $foundSurvey->sid = 123456;

        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $surveyMock->shouldReceive('findByPk')
            ->andReturn($foundSurvey);

        $answerMock = Mockery::mock(\Answer::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();

        $answerFormatterMock = new ExportAnswerFormatter();

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('exportResponsesInChunks')
            ->once()
            ->withArgs(function ($surveyId, $exportType, $metadata, $chunkSize) {
                return $surveyId === 123456
                    && $exportType === 'csv'
                    && $metadata['surveyId'] === 123456
                    && $metadata['language'] === 'en'
                    && $metadata['exportType'] === 'csv'
                    && $metadata['outputMode'] === 'file';
            })
            ->andReturn([
                'content' => null,
                'filePath' => '/tmp/test.csv',
                'filename' => 'test.csv',
                'mimeType' => 'text/csv',
                'extension' => 'csv',
                'size' => 0,
                'responseCount' => 0
            ]);

        $result = $service->exportResponses(123456, 'csv', null, 'file');

        $this->assertIsArray($result);
    }

    /**
     * Test that chunk size parameter is passed correctly
     */
    public function testChunkSizeParameterIsUsed()
    {
        $foundSurvey = Mockery::mock(\Survey::class)->makePartial();
        $foundSurvey->shouldReceive('__get')
            ->with('language')
            ->andReturn('en');
        $foundSurvey->shouldReceive('__get')
            ->with('sid')
            ->andReturn(123456);

        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $surveyMock->shouldReceive('findByPk')
            ->andReturn($foundSurvey);

        $answerMock = Mockery::mock(\Answer::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();

        $answerFormatterMock = new ExportAnswerFormatter();

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('exportResponsesInChunks')
            ->once()
            ->withArgs(function ($surveyId, $exportType, $metadata, $chunkSize) {
                return $chunkSize === 50;
            })
            ->andReturn([
                'content' => '',
                'filePath' => null,
                'filename' => 'test.csv',
                'mimeType' => 'text/csv',
                'extension' => 'csv',
                'size' => 0,
                'responseCount' => 0
            ]);

        $result = $service->exportResponses(123456, 'csv', null, 'memory', 50);

        $this->assertIsArray($result);
    }

    /**
     * Test various invalid export types
     *
     * @dataProvider invalidExportTypesProvider
     */
    public function testInvalidExportTypesThrowException(string $invalidType)
    {
        $this->expectException(InvalidArgumentException::class);

        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $answerMock = Mockery::mock(\Answer::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();

        $answerFormatterMock = new ExportAnswerFormatter();

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock
        );

        $service->exportResponses(123456, $invalidType);
    }

    /**
     * Data provider for invalid export types
     */
    public static function invalidExportTypesProvider(): array
    {
        return [
            'json' => ['json'],
            'xml' => ['xml'],
            'txt' => ['txt'],
            'doc' => ['doc'],
            'empty' => [''],
            'uppercase csv' => ['CSV'],
            'mixed case' => ['Csv'],
        ];
    }

    /**
     * Test that export result contains expected keys
     */
    public function testExportResultContainsExpectedKeys()
    {
        $foundSurvey = Mockery::mock(\Survey::class)->makePartial();
        $foundSurvey->shouldReceive('__get')
            ->with('language')
            ->andReturn('en');
        $foundSurvey->shouldReceive('__get')
            ->with('sid')
            ->andReturn(123456);

        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $surveyMock->shouldReceive('findByPk')
            ->andReturn($foundSurvey);

        $answerMock = Mockery::mock(\Answer::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();

        $answerFormatterMock = new ExportAnswerFormatter();

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('exportResponsesInChunks')
            ->once()
            ->andReturn([
                'content' => 'csv-content',
                'filePath' => null,
                'filename' => 'test.csv',
                'mimeType' => 'text/csv',
                'extension' => 'csv',
                'size' => 11,
                'responseCount' => 2
            ]);

        $result = $service->exportResponses(123456, 'csv');

        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('mimeType', $result);
        $this->assertArrayHasKey('extension', $result);
        $this->assertArrayHasKey('size', $result);
        $this->assertArrayHasKey('responseCount', $result);
        $this->assertEquals(2, $result['responseCount']);
    }
}
