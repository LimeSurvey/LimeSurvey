<?php

namespace ls\tests\unit\api\command;

use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LimeSurvey\Api\Command\V1\QuestionTypeList;
use LimeSurvey\Models\Services\ReactQuestionTypeConfig;
use ls\tests\TestBaseClass;
use QuestionTheme;

/**
 * Tests the question-types REST command: Installed question themes merged with react/questiontypes.xml.
 */
class QuestionTypeListTest extends TestBaseClass
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
     * Runs the command and returns the response data.
     *
     * @param ReactQuestionTypeConfig|null $config Reader of questiontypes.xml, defaults to the shipped file
     * @return array<string, mixed>
     */
    private function runCommand(?ReactQuestionTypeConfig $config = null): array
    {
        $command = new QuestionTypeList(new ResponseFactory(), $config ?? new ReactQuestionTypeConfig());
        $data = $command->run(new Request([]))->getData();
        $this->assertTrue($data['success']);
        return $data;
    }

    public function testEveryInstalledThemeIsReturnedWithItsType(): void
    {
        $questionTypes = $this->runCommand()['questionTypes'];

        /** @var QuestionTheme[] $questionThemes */
        $questionThemes = QuestionTheme::model()->findAll();
        $this->assertNotEmpty($questionThemes);
        foreach ($questionThemes as $questionTheme) {
            $this->assertArrayHasKey($questionTheme->name, $questionTypes);
            $questionType = $questionTypes[$questionTheme->name];
            $this->assertSame($questionTheme->question_type, $questionType['type']);
            $this->assertSame($questionTheme->visible === 'Y', $questionType['visible']);
            $this->assertFalse($questionType['editorOnly']);
        }
    }

    public function testEditorTitleWinsOverThemeTitle(): void
    {
        $questionTypes = $this->runCommand()['questionTypes'];

        $this->assertSame('Short text', $questionTypes['shortfreetext']['title']);
        $this->assertSame('List (Radio)', $questionTypes['listradio']['title'], 'Without an editor title, the theme title is used');
        $this->assertSame('Huge free text', $questionTypes['hugefreetext']['title']);
    }

    public function testListedThemesGetCategoryAndOrder(): void
    {
        $data = $this->runCommand();
        $questionTypes = $data['questionTypes'];

        $this->assertSame('Ranking & rating', $data['categories'][2]['title'], 'Titles are not HTML-escaped');
        $this->assertSame('text', $questionTypes['longfreetext']['category']);
        $this->assertSame(1, $questionTypes['longfreetext']['sortOrder']);
        $this->assertFalse($questionTypes['longfreetext']['devOnly']);
        $this->assertTrue($questionTypes['ranking']['devOnly']);
        $this->assertNull($questionTypes['hugefreetext']['category'], 'Themes not listed are not offered in the selector');
        $this->assertNull($questionTypes['hugefreetext']['sortOrder']);
    }

    public function testEditorOnlyTypeIsAdded(): void
    {
        $rating = $this->runCommand()['questionTypes']['rating'];

        $this->assertSame('RT', $rating['type']);
        $this->assertSame('Rating', $rating['title']);
        $this->assertTrue($rating['editorOnly']);
        $this->assertTrue($rating['devOnly']);
    }

    public function testEveryListedThemeIsInstalledOrEditorOnly(): void
    {
        $installed = array_map(function (QuestionTheme $questionTheme) {
            return $questionTheme->name;
        }, QuestionTheme::model()->findAll());

        foreach ((new ReactQuestionTypeConfig())->getThemes() as $name => $theme) {
            $this->assertTrue(
                in_array($name, $installed, true) || $theme['type'] !== '',
                sprintf('%s in questiontypes.xml is not an installed question theme and has no type', $name)
            );
        }
    }

    public function testListedThemeWithoutInstallationOrTypeIsSkipped(): void
    {
        $this->directory = sys_get_temp_dir() . '/react-question-types-' . uniqid();
        mkdir($this->directory);
        file_put_contents(
            $this->directory . '/' . ReactQuestionTypeConfig::FILE,
            '<questiontypes><categories><category><name>first</name><themes>'
            . '<theme><name>not_installed_theme</name></theme>'
            . '<theme><name>listradio</name><title>Custom title</title></theme>'
            . '</themes></category></categories></questiontypes>'
        );

        $questionTypes = $this->runCommand(new ReactQuestionTypeConfig($this->directory))['questionTypes'];

        $this->assertArrayNotHasKey('not_installed_theme', $questionTypes);
        $this->assertSame('Custom title', $questionTypes['listradio']['title']);
        $this->assertSame('first', $questionTypes['listradio']['category']);
    }
}
