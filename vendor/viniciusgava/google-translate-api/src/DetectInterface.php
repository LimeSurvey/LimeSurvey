<?php

namespace GoogleTranslate;

/**
 * Google Translate API PHP Client
 *
 * @link https://github.com/viniciusgava/google-translate-php-client
 * @license http://www.gnu.org/copyleft/gpl.html
 * @author Vinicius Gava (gava.vinicius@gmail.com)
 */
interface DetectInterface
{
    /**
     * Detected the language of a text
     *
     * If pass a string as text, it will return one array item like example bellow.
     * If pass a array as text, it will return various array items like example bellow(collection).
     *
     * Return example:
     * [
     *      (string) 'language' => (string) Supported language code, generally consisting of its ISO 639-1 identifier,
     *      (string) 'name' => (string) Human readable name of the language localized to the target language
     * ]
     *
     * @param string|array $text String or multiple strings(array) to be detected
     * @return array array structure return above
     *
     * @throws Exception\InvalidTextException
     * @throws Exception\DetectErrorException
     */
    public function detect($text): array;
}
