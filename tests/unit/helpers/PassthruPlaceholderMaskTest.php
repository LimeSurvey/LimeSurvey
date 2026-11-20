<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * {PASSTHRU:...} placeholders in end URLs must not be reported as expression syntax errors
 * by the survey logic file and the quota validation.
 * @see https://bugs.limesurvey.org/view.php?id=20802
 */
class PassthruPlaceholderMaskTest extends TestBaseClass
{
    /**
     * A PASSTHRU placeholder is no syntax error and is shown unchanged.
     */
    public function testPassthruPlaceholderIsNoSyntaxError()
    {
        $url = 'https://example.org/return?subjectid={PASSTHRU:subjectid}&lang={PASSTHRU:lang}';
        [$prettyPrint, $hasErrors] = $this->prettyPrintUrl($url);

        $this->assertFalse($hasErrors, 'PASSTHRU placeholders must not be reported as syntax errors');
        $this->assertStringContainsString('subjectid={PASSTHRU:subjectid}', $prettyPrint);
        $this->assertStringContainsString('lang={PASSTHRU:lang}', $prettyPrint);
        $this->assertStringNotContainsString('LSPASSTHRU', $prettyPrint, 'No marker may be left in the output');
    }

    /**
     * Real expression errors in the URL are still reported.
     */
    public function testExpressionErrorsAreStillReported()
    {
        [, $hasErrors] = $this->prettyPrintUrl('https://example.org/?id={PASSTHRU:id}&x={if(}');

        $this->assertTrue($hasErrors, 'An invalid expression next to a PASSTHRU placeholder must still be reported');
    }

    /**
     * Without a PASSTHRU placeholder the URL is left unchanged.
     */
    public function testUrlWithoutPlaceholderIsUnchanged()
    {
        $url = 'https://example.org/?id={TOKEN:FIRSTNAME}';
        [$maskedUrl, $markers] = \LimeExpressionManager::maskPassthruPlaceholders($url);

        $this->assertSame($url, $maskedUrl);
        $this->assertSame([], $markers);
    }

    /**
     * HTML special characters in a placeholder are escaped in the output.
     */
    public function testPlaceholderIsHtmlEscaped()
    {
        [, $markers] = \LimeExpressionManager::maskPassthruPlaceholders('https://example.org/?a={PASSTHRU:a"b&c}');

        $this->assertSame(['{PASSTHRU:a&quot;b&amp;c}'], array_values($markers));
    }

    /**
     * Masks and pretty-prints a URL the way the survey logic file does.
     * @param string $url The end URL
     * @return array{0: string, 1: bool} The pretty-printed HTML, and whether there were errors
     */
    private function prettyPrintUrl($url)
    {
        [$maskedUrl, $markers] = \LimeExpressionManager::maskPassthruPlaceholders($url);
        $em = new \ExpressionManager();
        $em->sProcessStringContainingExpressions('End - ' . $maskedUrl, 0, 1, 1, -1, -1, true);
        $prettyPrint = strtr($em->GetLastPrettyPrintExpression(), $markers);
        return [$prettyPrint, $em->HasErrors()];
    }
}
