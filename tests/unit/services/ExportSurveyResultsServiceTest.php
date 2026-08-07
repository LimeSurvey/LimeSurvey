<?php

namespace ls\tests\unit\services;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;
use LimeSurvey\Models\Services\ExportSurveyResultsService;
use LimeSurvey\Models\Services\Export\ExportAnswerFormatter;
use LimeSurvey\Models\Services\Export\CsvExportWriter;
use LimeSurvey\Models\Services\Export\HtmlExportWriter;
use LimeSurvey\Models\Services\SurveyAnswerCache;
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
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
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

        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
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

        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
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
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
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
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
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

        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
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
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
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

        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('exportResponsesInChunks')
            ->once()
            ->withArgs(function ($surveyId, $exportType, $metadata) {
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

        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('exportResponsesInChunks')
            ->once()
            ->withArgs(function ($surveyId, $exportType, $metadata) {
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

        $service->setLanguage('fr');
        $result = $service->exportResponses(123456, 'csv');

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

        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('exportResponsesInChunks')
            ->once()
            ->withArgs(function ($surveyId, $exportType, $metadata) {
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

        $service->setOutputMode('file');
        $result = $service->exportResponses(123456, 'csv');

        $this->assertIsArray($result);
    }

    /**
     * Test that chunk size setter/getter works correctly
     */
    public function testChunkSizeSetterGetter()
    {
        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
        );

        $this->assertEquals(500, $service->getChunkSize());
        $result = $service->setChunkSize(50);
        $this->assertSame($service, $result);
        $this->assertEquals(50, $service->getChunkSize());
    }

    /**
     * Test that invalid chunk size throws exception
     */
    public function testInvalidChunkSizeThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);

        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
        );

        $service->setChunkSize(-1);
    }

    /**
     * Test that answer format setter/getter works correctly
     */
    public function testAnswerFormatSetterGetter()
    {
        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
        );

        $this->assertEquals('long', $service->getAnswerFormat());
        $result = $service->setAnswerFormat('short');
        $this->assertSame($service, $result);
        $this->assertEquals('short', $service->getAnswerFormat());
    }

    /**
     * Test that setters return $this for fluent chaining
     */
    public function testSettersReturnSelfForChaining()
    {
        $surveyMock = Mockery::mock(\Survey::class)->makePartial();
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
        );

        $result = $service
            ->setLanguage('de')
            ->setOutputMode('file')
            ->setChunkSize(100)
            ->setAnswerFormat('short');

        $this->assertSame($service, $result);
        $this->assertEquals('de', $service->getLanguage());
        $this->assertEquals('file', $service->getOutputMode());
        $this->assertEquals(100, $service->getChunkSize());
        $this->assertEquals('short', $service->getAnswerFormat());
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
        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
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

        $filterPatcherMock = Mockery::mock(FilterPatcher::class)->makePartial();
        $transformerMock = Mockery::mock(TransformerOutputSurveyResponses::class)->makePartial();
        $answerCache = new SurveyAnswerCache();
        $answerFormatterMock = new ExportAnswerFormatter($answerCache);

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $filterPatcherMock,
            $transformerMock,
            $answerFormatterMock,
            $answerCache
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
