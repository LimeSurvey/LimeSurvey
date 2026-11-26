<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use Yii;

/**
 * Tests that buildXMLFromQuery() closes the table element for every row count,
 * also when the row count is a multiple of its chunk size of 1000 rows (see #20876).
 */
class BuildXMLFromQueryChunkTest extends TestBaseClass
{
    /** @var string Attribute name marking the rows of this test */
    private const ATTRIBUTE = 'test20876chunk';

    /**
     * Loads the export helper.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Yii::import('application.helpers.export_helper', true);
    }

    /**
     * Removes the rows of this test.
     */
    protected function tearDown(): void
    {
        Yii::app()->db->createCommand()->delete(
            '{{question_attributes}}',
            'attribute = :attribute',
            [':attribute' => self::ATTRIBUTE]
        );
        parent::tearDown();
    }

    /**
     * Row counts below, at and above the chunk size.
     *
     * @return array<string, int[]>
     */
    public static function rowCountProvider(): array
    {
        return [
            'no rows' => [0],
            'one row less than a chunk' => [999],
            'exactly one chunk' => [1000],
            'one row more than a chunk' => [1001],
            'exactly two chunks' => [2000],
        ];
    }

    /**
     * The element written after the table must be a sibling of the table element, not a child of its rows.
     *
     * @dataProvider rowCountProvider
     * @param int $rowCount
     */
    public function testTableElementIsClosed(int $rowCount): void
    {
        $this->insertRows($rowCount);

        $xmlWriter = new \XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startDocument('1.0', 'UTF-8');
        $xmlWriter->startElement('document');
        $query = "SELECT qaid, qid, attribute, value FROM {{question_attributes}}"
            . " WHERE attribute = '" . self::ATTRIBUTE . "' ORDER BY qaid";
        buildXMLFromQuery($xmlWriter, $query);
        $xmlWriter->writeElement('nexttable', 'next');
        $xmlWriter->endElement();
        $xmlWriter->endDocument();

        $xml = simplexml_load_string($xmlWriter->outputMemory());
        $this->assertNotFalse($xml, 'The export is not valid XML.');
        $this->assertCount(1, $xml->nexttable, 'The element after the table is not on the top level.');
        if ($rowCount === 0) {
            $this->assertCount(0, $xml->question_attributes, 'An empty table must not be exported.');
            return;
        }
        $this->assertCount($rowCount, $xml->question_attributes->rows->row);
        $this->assertCount(
            $rowCount,
            $xml->question_attributes->rows->children(),
            'The rows element contains other elements than rows.'
        );
    }

    /**
     * Inserts the given number of question attribute rows in batches.
     *
     * @param int $rowCount
     * @return void
     */
    private function insertRows(int $rowCount): void
    {
        $rows = [];
        for ($i = 1; $i <= $rowCount; $i++) {
            $rows[] = ['qid' => 0, 'attribute' => self::ATTRIBUTE, 'value' => (string) $i];
        }
        $commandBuilder = Yii::app()->db->getCommandBuilder();
        foreach (array_chunk($rows, 500) as $batch) {
            $commandBuilder->createMultipleInsertCommand('{{question_attributes}}', $batch)->execute();
        }
    }
}
