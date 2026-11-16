<?php

namespace ls\tests;

use LS_Twig_Debug_Extension;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * Tests for the Twig dump() function provided by LS_Twig_Debug_Extension.
 */
class LSTwigDebugExtensionTest extends TestBaseClass
{
    /**
     * Renders a template string with the debug extension loaded
     * @param string $source the twig source
     * @param array $context the template context
     * @param bool $debug whether Twig debug mode is enabled
     * @return string the rendered output
     */
    private function render($source, array $context = [], $debug = true)
    {
        $twig = new Environment(new ArrayLoader(['test.twig' => $source]), ['debug' => $debug]);
        $twig->addExtension(new LS_Twig_Debug_Extension());
        return $twig->render('test.twig', $context);
    }

    /**
     * Dump output starts with the twig file and line of the dump() call, not a PHP vendor file.
     */
    public function testDumpShowsTwigFileAndLine()
    {
        $output = $this->render("first line\n\n{{ dump('value') }}");

        $this->assertStringContainsString('<pre class="ls-twig-dump">test.twig:3:', $output);
        $this->assertStringNotContainsString('DebugExtension.php', $output);
    }

    /**
     * Dumped values are HTML-encoded.
     */
    public function testDumpIsHtmlEncoded()
    {
        $output = $this->render("{{ dump(foo) }}", ['foo' => 'a<b>&c']);

        $this->assertStringContainsString('a&lt;b&gt;&amp;c', $output);
        $this->assertStringNotContainsString('<b>', $output);
    }

    /**
     * Without arguments the whole context is dumped.
     */
    public function testDumpWithoutArgumentsDumpsContext()
    {
        $output = $this->render("{{ dump() }}", ['firstVariable' => 1, 'secondVariable' => 'two']);

        $this->assertStringContainsString('firstVariable', $output);
        $this->assertStringContainsString('secondVariable', $output);
    }

    /**
     * Invalid UTF-8 in a dumped value does not empty the output.
     */
    public function testDumpWithInvalidUtf8()
    {
        $output = $this->render("{{ dump(foo) }}", ['foo' => "valid \xC3\x28 invalid"]);

        $this->assertStringContainsString('test.twig:1:', $output);
        $this->assertStringContainsString('valid', $output);
    }

    /**
     * Nothing is output when Twig debug mode is disabled.
     */
    public function testDumpOutputsNothingWithoutDebug()
    {
        $this->assertSame('', $this->render("{{ dump('value') }}", [], false));
    }
}
