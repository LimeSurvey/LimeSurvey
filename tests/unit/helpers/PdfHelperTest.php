<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Tests for the pdfHelper methods preparing the print answers HTML for TCPDF.
 */
class PdfHelperTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.helpers.pdfHelper', true);
    }

    /**
     * A question with the answers next to it becomes a non-breakable table, with nested rows converted too.
     */
    public function testQuestionRowBecomesNonBreakableTable()
    {
        $html = '<div class="row question-container-printanswers">'
            . '<div class="col-md-4"><b>Question</b></div>'
            . '<div class="col-lg-8"><div class="row">'
            . '<div class="col-lg-6"><b> Subquestion </b></div>'
            . '<div class="col-lg-6 text-end">Answer</div>'
            . '</div></div>'
            . '</div>';
        $dom = $this->loadResult(\pdfHelper::convertGridToTables($html));

        $this->assertSame(0, $dom->getElementsByTagName('div')->length, 'All grid divs should have been replaced.');
        $questionTable = $dom->getElementsByTagName('table')->item(0);
        $this->assertSame('true', $questionTable->getAttribute('nobr'), 'A question must not be split across pages.');

        $questionCell = $this->getCellByText($dom, 'Question');
        $this->assertSame('33.33%', $questionCell->getAttribute('width'));
        $subquestionCell = $this->getCellByText($dom, 'Subquestion');
        $this->assertSame('50%', $subquestionCell->getAttribute('width'));
        $this->assertSame('Subquestion', $subquestionCell->textContent, 'White space around the cell content should be removed.');
        $this->assertSame('right', $this->getCellByText($dom, 'Answer')->getAttribute('align'));
    }

    /**
     * Full width columns wrap to a new line: The question is shown above its answers.
     */
    public function testWrappedColumnsAreStacked()
    {
        $html = '<div class="row question-container-printanswers">'
            . '<div class="col-12"><b>Question</b></div>'
            . '<div class="col-12">Answer</div>'
            . '</div>';
        $dom = $this->loadResult(\pdfHelper::convertGridToTables($html));

        $questionTable = $dom->getElementsByTagName('table')->item(0);
        $this->assertSame('true', $questionTable->getAttribute('nobr'));
        $rows = $questionTable->getElementsByTagName('tr');
        // A spacer row, then one row for the question and one for the answer
        $this->assertSame(3, $rows->length);
        $this->assertSame('Question', $rows->item(1)->textContent);
        $this->assertSame('Answer', $rows->item(2)->textContent);
    }

    /**
     * A row with only full width columns (that is not a question) is replaced by its columns,
     * so it can still be split across pages.
     */
    public function testFullWidthRowIsUnwrapped()
    {
        $html = '<div class="row"><div class="container-fluid col-11 col-md-10 offset-1 offset-lg-2">'
            . '<p>First</p><p>Second</p>'
            . '</div></div>';
        $dom = $this->loadResult(\pdfHelper::convertGridToTables($html));

        $this->assertSame(0, $dom->getElementsByTagName('table')->length);
        $this->assertSame(1, $dom->getElementsByTagName('div')->length);
        $this->assertSame('', $dom->getElementsByTagName('div')->item(0)->getAttribute('class'));
        $this->assertSame(2, $dom->getElementsByTagName('p')->length);
    }

    /**
     * Rows containing something else than grid columns are left unchanged, as well as the text encoding.
     */
    public function testNonGridContentIsKept()
    {
        $html = '<div class="row"><span>Grüße</span><div class="col-6">A</div></div>';
        $result = \pdfHelper::convertGridToTables($html);
        $dom = $this->loadResult($result);

        $this->assertSame(0, $dom->getElementsByTagName('table')->length);
        $this->assertSame(2, $dom->getElementsByTagName('div')->length);
        $this->assertStringContainsString('Grüße', html_entity_decode($result, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Transparent backgrounds are removed, since TCPDF draws them in gray. Other backgrounds are kept.
     */
    public function testTransparentBackgroundsAreRemoved()
    {
        $html = '<style>table { background-color: transparent; } .a { background: TRANSPARENT !important } '
            . '.b { background-color: #f5f5f5; }</style><td style="background-color:transparent;color:red">';
        $result = \pdfHelper::removeTransparentBackgrounds($html);

        $this->assertStringNotContainsStringIgnoringCase('transparent', $result);
        $this->assertStringContainsString('background-color: #f5f5f5;', $result);
        $this->assertStringContainsString('style="color:red"', $result);
    }

    /**
     * Images of the LimeSurvey directory are embedded, whatever the base URL of the installation.
     */
    public function testLocalImagesAreEmbedded()
    {
        $publicDir = \Yii::app()->getConfig('publicdir');
        $html = '<img src="/limesurvey/assets/images/decor-1.png" alt="Under base URL">'
            . '<img src="/assets/images/decor-1.png" alt="From server root">'
            . '<img src="https://example.org/limesurvey/assets/images/decor-1.png" alt="Absolute URL">';
        $dom = $this->loadResult(\pdfHelper::embedLocalImages($html, $publicDir, '/limesurvey', 'https://example.org'));

        $images = $dom->getElementsByTagName('img');
        $this->assertSame(3, $images->length);
        foreach ($images as $image) {
            $this->assertStringStartsWith('data:image/png;base64,', $image->getAttribute('src'), $image->getAttribute('alt'));
        }
    }

    /**
     * SVG images are embedded as a TCPDF data stream, since TCPDF doesn't read SVG data URIs.
     */
    public function testLocalSvgImagesAreEmbedded()
    {
        $publicDir = \Yii::app()->getConfig('publicdir');
        $tempDir = \Yii::app()->getConfig('tempdir');
        $svgFile = $tempDir . '/pdfhelper-test-' . uniqid() . '.svg';
        file_put_contents($svgFile, '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><rect width="10" height="10"/></svg>');
        $src = '/' . ltrim(substr(realpath($svgFile), strlen(realpath($publicDir))), '/');
        try {
            $dom = $this->loadResult(\pdfHelper::embedLocalImages('<img src="' . $src . '">', $publicDir, '', ''));
        } finally {
            unlink($svgFile);
        }

        $imageSrc = $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
        $this->assertStringStartsWith('@', $imageSrc);
        $this->assertStringContainsString('<svg', base64_decode(substr($imageSrc, 1)));
    }

    /**
     * Images that can't be loaded are replaced by their alternative text, and other files are never embedded.
     */
    public function testMissingOrForbiddenImagesAreRemoved()
    {
        $publicDir = \Yii::app()->getConfig('publicdir');
        $html = '<p>'
            . '<img src="/upload/does-not-exist.png" alt="Missing">'
            . '<img src="/application/config/config-defaults.php" alt="Not an image">'
            . '<img src="/../../../../etc/hostname" alt="Outside">'
            . '<img src="https://other.example.com/image.png" alt="Remote">'
            . '</p>';
        $dom = $this->loadResult(\pdfHelper::embedLocalImages($html, $publicDir, '', 'https://example.org'));

        $images = $dom->getElementsByTagName('img');
        $this->assertSame(1, $images->length, 'Only the image on another server should be kept.');
        $this->assertSame('https://other.example.com/image.png', $images->item(0)->getAttribute('src'));
        $this->assertSame('MissingNot an imageOutside', $dom->getElementsByTagName('p')->item(0)->textContent);
    }

    /**
     * Load the converted HTML into a DOM document.
     *
     * @param string $html
     * @return \DOMDocument
     */
    private function loadResult($html)
    {
        $dom = new \DOMDocument();
        $previousUseErrors = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($previousUseErrors);
        return $dom;
    }

    /**
     * Get the innermost table cell containing exactly a text.
     *
     * @param \DOMDocument $dom
     * @param string $text
     * @return \DOMElement
     */
    private function getCellByText(\DOMDocument $dom, $text)
    {
        foreach ($dom->getElementsByTagName('td') as $cell) {
            if (trim($cell->textContent) === $text && $cell->getElementsByTagName('td')->length === 0) {
                return $cell;
            }
        }
        $this->fail("No cell with the text $text");
    }
}
