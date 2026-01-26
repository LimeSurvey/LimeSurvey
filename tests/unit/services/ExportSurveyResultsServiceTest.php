<?php

namespace ls\tests\unit\services;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;
use LimeSurvey\Models\Services\ExportSurveyResultsService;
use LimeSurvey\Models\Services\Export\CsvExportWriter;
use LimeSurvey\Models\Services\Export\XlsxExportWriter;
use LimeSurvey\Models\Services\Export\XlsExportWriter;
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

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock
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

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock
        );

        $service->exportResponses(999999, 'csv');
    }

    /**
     * Test that CSV export uses CsvExportWriter and returns expected structure
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

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('fetchSurveyResponsesInChunks')
            ->andReturn(['responses' => [], 'surveyQuestions' => []]);

        $writerMock = Mockery::mock(CsvExportWriter::class);
        $writerMock->shouldReceive('export')
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

        $service->shouldReceive('getExportWriter')
            ->with('csv')
            ->andReturn($writerMock);

        $result = $service->exportResponses(123456, 'csv');

        $this->assertIsArray($result);
        $this->assertEquals('csv', $result['extension']);
        $this->assertEquals('text/csv', $result['mimeType']);
    }

    /**
     * Test that XLSX export uses XlsxExportWriter
     */
    public function testXlsxExportUsesCorrectWriter()
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

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('fetchSurveyResponsesInChunks')
            ->andReturn(['responses' => [], 'surveyQuestions' => []]);

        $writerMock = Mockery::mock(XlsxExportWriter::class);
        $writerMock->shouldReceive('export')
            ->once()
            ->andReturn([
                'content' => 'xlsx-content',
                'filePath' => null,
                'filename' => 'test.xlsx',
                'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'extension' => 'xlsx',
                'size' => 100,
                'responseCount' => 0
            ]);

        $service->shouldReceive('getExportWriter')
            ->with('xlsx')
            ->andReturn($writerMock);

        $result = $service->exportResponses(123456, 'xlsx');

        $this->assertEquals('xlsx', $result['extension']);
    }

    /**
     * Test that XLS export uses XlsExportWriter
     */
    public function testXlsExportUsesCorrectWriter()
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

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('fetchSurveyResponsesInChunks')
            ->andReturn(['responses' => [], 'surveyQuestions' => []]);

        $writerMock = Mockery::mock(XlsExportWriter::class);
        $writerMock->shouldReceive('export')
            ->once()
            ->andReturn([
                'content' => 'xls-content',
                'filePath' => null,
                'filename' => 'test.xls',
                'mimeType' => 'application/vnd.ms-excel',
                'extension' => 'xls',
                'size' => 100,
                'responseCount' => 0
            ]);

        $service->shouldReceive('getExportWriter')
            ->with('xls')
            ->andReturn($writerMock);

        $result = $service->exportResponses(123456, 'xls');

        $this->assertEquals('xls', $result['extension']);
    }

    /**
     * Test that HTML export uses HtmlExportWriter
     */
    public function testHtmlExportUsesCorrectWriter()
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

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('fetchSurveyResponsesInChunks')
            ->andReturn(['responses' => [], 'surveyQuestions' => []]);

        $writerMock = Mockery::mock(HtmlExportWriter::class);
        $writerMock->shouldReceive('export')
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

        $service->shouldReceive('getExportWriter')
            ->with('html')
            ->andReturn($writerMock);

        $result = $service->exportResponses(123456, 'html');

        $this->assertEquals('html', $result['extension']);
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

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock
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

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('fetchSurveyResponsesInChunks')
            ->andReturn(['responses' => [], 'surveyQuestions' => []]);

        $writerMock = Mockery::mock(CsvExportWriter::class);
        $writerMock->shouldReceive('export')
            ->once()
            ->withArgs(function ($responses, $questions, $metadata) {
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

        $service->shouldReceive('getExportWriter')
            ->andReturn($writerMock);

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

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('fetchSurveyResponsesInChunks')
            ->andReturn(['responses' => [], 'surveyQuestions' => []]);

        $writerMock = Mockery::mock(CsvExportWriter::class);
        $writerMock->shouldReceive('export')
            ->once()
            ->withArgs(function ($responses, $questions, $metadata) {
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

        $service->shouldReceive('getExportWriter')
            ->andReturn($writerMock);

        $result = $service->exportResponses(123456, 'csv', 'fr');

        $this->assertIsArray($result);
    }

    /**
     * Test that metadata is correctly passed to the writer
     */
    public function testMetadataIsCorrectlyPassedToWriter()
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

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('fetchSurveyResponsesInChunks')
            ->andReturn(['responses' => [], 'surveyQuestions' => []]);

        $writerMock = Mockery::mock(CsvExportWriter::class);
        $writerMock->shouldReceive('export')
            ->once()
            ->withArgs(function ($responses, $questions, $metadata) {
                return $metadata['surveyId'] === 123456
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

        $service->shouldReceive('getExportWriter')
            ->andReturn($writerMock);

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

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        // Verify the chunk size is passed to fetchSurveyResponsesInChunks
        $service->shouldReceive('fetchSurveyResponsesInChunks')
            ->once()
            ->with(123456, 50)
            ->andReturn(['responses' => [], 'surveyQuestions' => []]);

        $writerMock = Mockery::mock(CsvExportWriter::class);
        $writerMock->shouldReceive('export')
            ->andReturn([
                'content' => '',
                'filePath' => null,
                'filename' => 'test.csv',
                'mimeType' => 'text/csv',
                'extension' => 'csv',
                'size' => 0,
                'responseCount' => 0
            ]);

        $service->shouldReceive('getExportWriter')
            ->andReturn($writerMock);

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

        $service = new ExportSurveyResultsService(
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock
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
     * Test that responses and questions are passed to writer
     */
    public function testResponsesAndQuestionsArePassedToWriter()
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

        $service = Mockery::mock(ExportSurveyResultsService::class, [
            $surveyMock,
            $answerMock,
            $filterPatcherMock,
            $transformerMock
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $mockResponses = [
            ['id' => 1, 'answers' => ['Q1' => ['value' => 'answer1']]],
            ['id' => 2, 'answers' => ['Q1' => ['value' => 'answer2']]]
        ];
        $mockQuestions = [
            'Q1' => ['qid' => 1, 'gid' => 1]
        ];

        $service->shouldReceive('fetchSurveyResponsesInChunks')
            ->andReturn([
                'responses' => $mockResponses,
                'surveyQuestions' => $mockQuestions
            ]);

        $writerMock = Mockery::mock(CsvExportWriter::class);
        $writerMock->shouldReceive('export')
            ->once()
            ->withArgs(function ($responses, $questions, $metadata) use ($mockResponses, $mockQuestions) {
                return $responses === $mockResponses
                    && $questions === $mockQuestions;
            })
            ->andReturn([
                'content' => 'csv-content',
                'filePath' => null,
                'filename' => 'test.csv',
                'mimeType' => 'text/csv',
                'extension' => 'csv',
                'size' => 11,
                'responseCount' => 2
            ]);

        $service->shouldReceive('getExportWriter')
            ->andReturn($writerMock);

        $result = $service->exportResponses(123456, 'csv');

        $this->assertEquals(2, $result['responseCount']);
    }
}
