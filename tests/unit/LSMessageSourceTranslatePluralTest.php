<?php

namespace ls\tests;

use LSGettextMoFile;
use LSMessageSource;

/**
 * Tests the translation of gettext plural entries (msgid / msgid_plural)
 */
class LSMessageSourceTranslatePluralTest extends TestBaseClass
{
    /** @var string Directory holding the temporary pl/pl.mo catalog */
    private static string $basePath;

    /**
     * Writes a Polish catalog with one plural entry
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$basePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lsplural' . uniqid();
        mkdir(self::$basePath . DIRECTORY_SEPARATOR . 'pl', 0777, true);
        (new LSGettextMoFile())->save(
            self::$basePath . DIRECTORY_SEPARATOR . 'pl' . DIRECTORY_SEPARATOR . 'pl.mo',
            [
                '' => "Content-Type: text/plain; charset=UTF-8\n"
                    . "Plural-Forms: nplurals=3; plural=(n == 1) ? 0 : ((n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 12 || n % 100 > 14)) ? 1 : 2);\n",
                "%d file\0%d files" => "%d plik\0%d pliki\0%d plików",
                "%d answer\0%d answers" => "%d odpowiedź\0%d odpowiedzi",
            ]
        );
    }

    /**
     * Removes the temporary catalog
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        unlink(self::$basePath . DIRECTORY_SEPARATOR . 'pl' . DIRECTORY_SEPARATOR . 'pl.mo');
        rmdir(self::$basePath . DIRECTORY_SEPARATOR . 'pl');
        rmdir(self::$basePath);
        parent::tearDownAfterClass();
    }

    /**
     * Returns a message source reading the temporary catalog
     *
     * @return LSMessageSource
     */
    private function getMessageSource(): LSMessageSource
    {
        $messageSource = new LSMessageSource();
        $messageSource->basePath = self::$basePath;
        return $messageSource;
    }

    /**
     * @return void
     */
    public function testPicksFormByPluralRule(): void
    {
        $messageSource = $this->getMessageSource();
        $expected = [1 => '%d plik', 2 => '%d pliki', 4 => '%d pliki', 5 => '%d plików', 12 => '%d plików', 22 => '%d pliki', 0 => '%d plików'];
        foreach ($expected as $count => $form) {
            $this->assertSame($form, $messageSource->translatePlural('%d file', '%d files', $count, 'pl'), "Wrong form for n = $count");
        }
    }

    /**
     * A translation with fewer forms than the language needs uses its last form
     *
     * @return void
     */
    public function testMissingFormUsesLastForm(): void
    {
        $this->assertSame('%d odpowiedzi', $this->getMessageSource()->translatePlural('%d answer', '%d answers', 5, 'pl'));
    }

    /**
     * @return void
     */
    public function testUntranslatedFallsBackToEnglish(): void
    {
        $messageSource = $this->getMessageSource();
        $this->assertSame('%d day', $messageSource->translatePlural('%d day', '%d days', 1, 'pl'));
        $this->assertSame('%d days', $messageSource->translatePlural('%d day', '%d days', 0, 'pl'));
        $this->assertSame('%d days', $messageSource->translatePlural('%d day', '%d days', 5, 'pl'));
    }

    /**
     * The source language is not looked up at all
     *
     * @return void
     */
    public function testSourceLanguage(): void
    {
        $messageSource = $this->getMessageSource();
        $this->assertSame('%d file', $messageSource->translatePlural('%d file', '%d files', 1, 'en'));
        $this->assertSame('%d files', $messageSource->translatePlural('%d file', '%d files', 2, 'en'));
    }

    /**
     * gTPlural() escapes the chosen form like gT()
     *
     * @return void
     */
    public function testGTPluralEscapes(): void
    {
        $this->assertSame('one &quot;item&quot;', gTPlural('one "item"', '%d "items"', 1, 'html', 'en'));
        $this->assertSame('%d "items"', gTPlural('one "item"', '%d "items"', 3, 'unescaped', 'en'));
    }
}
