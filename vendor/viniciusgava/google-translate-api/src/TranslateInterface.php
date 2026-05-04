<?php

namespace GoogleTranslate;

/**
 * Google Translate API PHP Client
 *
 * @link https://github.com/viniciusgava/google-translate-php-client
 * @license http://www.gnu.org/copyleft/gpl.html
 * @author Vinicius Gava (gava.vinicius@gmail.com)
 */
interface TranslateInterface
{
    /**
     * Translate a text or multiple texts
     *
     * If pass a string as text, it will return a string.
     * If pass a array as text, it will return a array.
     * If you do not fill the source language param, but you pass a variable, you can capture the detected
     * string language. The returned value variable type follow the same rule of text.
     *
     * @param string|array $text String or multiple strings(array) to be translated
     * @param string $targetLanguage Target language. ie: pt, en, es
     * @param null|string|array $sourceLanguage Source language. If not passed, google will try figure out it.
     * @return string|array Return retails above
     *
     * @throws Exception\InvalidTextException
     * @throws Exception\InvalidTargetLanguageException
     * @throws Exception\InvalidSourceLanguageException
     * @throws Exception\TranslateErrorException
     */
    public function translate($text, string $targetLanguage, &$sourceLanguage = null);
}
