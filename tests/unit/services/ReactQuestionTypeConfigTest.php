<?php

namespace ls\tests;

use LimeSurvey\Models\Services\ReactQuestionTypeConfig;

/**
 * Tests the reader of the editor question type list (react/questiontypes.xml).
 */
class ReactQuestionTypeConfigTest extends TestBaseClass
{
    /** @var string|null Temporary directory with a questiontypes.xml */
    private $directory;

    /**
     * Removes the temporary directory of the test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        if ($this->directory !== null) {
            @unlink($this->directory . '/' . ReactQuestionTypeConfig::FILE);
            @rmdir($this->directory);
            $this->directory = null;
        }
        parent::tearDown();
    }

    /**
     * Writes a questiontypes.xml into a new temporary directory and returns a reader for it.
     *
     * @param string|null $xml File content, null for no file
     * @return ReactQuestionTypeConfig
     */
    private function makeConfig(?string $xml): ReactQuestionTypeConfig
    {
        $this->directory = sys_get_temp_dir() . '/react-question-types-' . uniqid();
        mkdir($this->directory);
        if ($xml !== null) {
            file_put_contents($this->directory . '/' . ReactQuestionTypeConfig::FILE, $xml);
        }
        return new ReactQuestionTypeConfig($this->directory);
    }

    public function testCategoriesAndThemesAreReadInOrder(): void
    {
        $config = $this->makeConfig(
            '<questiontypes><categories>'
                . '<category><name>first</name><title>First &amp; more</title><icon>FirstIcon</icon><themes>'
                . '<theme><name>listradio</name></theme>'
                . '<theme><name>bootstrap_buttons</name><title>Buttons</title><devOnly>1</devOnly></theme>'
                . '</themes></category>'
                . '<category><name>second</name><title>Second</title><icon>SecondIcon</icon><themes>'
                . '<theme><name>rating</name><type>RT</type><devOnly>0</devOnly></theme>'
                . '</themes></category>'
                . '</categories></questiontypes>'
        );

        $this->assertSame(
            [
                ['name' => 'first', 'title' => 'First & more', 'icon' => 'FirstIcon'],
                ['name' => 'second', 'title' => 'Second', 'icon' => 'SecondIcon'],
            ],
            $config->getCategories()
        );
        $this->assertSame(
            [
                'listradio' => ['category' => 'first', 'sortOrder' => 0, 'title' => '', 'type' => '', 'devOnly' => false],
                'bootstrap_buttons' => ['category' => 'first', 'sortOrder' => 1, 'title' => 'Buttons', 'type' => '', 'devOnly' => true],
                'rating' => ['category' => 'second', 'sortOrder' => 0, 'title' => '', 'type' => 'RT', 'devOnly' => false],
            ],
            $config->getThemes()
        );
    }

    public function testFirstEntryOfADuplicateThemeWins(): void
    {
        $config = $this->makeConfig(
            '<questiontypes><categories>'
                . '<category><name>first</name><themes><theme><name>listradio</name></theme></themes></category>'
                . '<category><name>second</name><themes>'
                . '<theme><name>listradio</name></theme><theme><name>yesno</name></theme>'
                . '</themes></category>'
                . '</categories></questiontypes>'
        );

        $themes = $config->getThemes();
        $this->assertSame('first', $themes['listradio']['category']);
        $this->assertSame(0, $themes['yesno']['sortOrder']);
    }

    public function testMissingFileGivesEmptyLists(): void
    {
        $config = $this->makeConfig(null);

        $this->assertSame([], $config->getCategories());
        $this->assertSame([], $config->getThemes());
    }

    public function testMalformedFileGivesEmptyLists(): void
    {
        $config = $this->makeConfig('<questiontypes><categories><category>');

        $this->assertSame([], $config->getCategories());
        $this->assertSame([], $config->getThemes());
    }

    public function testShippedFileListsTheSelectorCategories(): void
    {
        $config = new ReactQuestionTypeConfig();

        $this->assertSame(
            ['singleChoice', 'multipleChoice', 'rankingRating', 'datesData', 'text', 'number', 'array'],
            array_column($config->getCategories(), 'name')
        );
        $this->assertSame('Short text', $config->getThemes()['shortfreetext']['title']);
    }
}
