<?php

namespace GoogleTranslate;

/**
 * Google Translate API PHP Client
 *
 * @link https://github.com/viniciusgava/google-translate-php-client
 * @license http://www.gnu.org/copyleft/gpl.html
 * @author Vinicius Gava (gava.vinicius@gmail.com)
 */
interface LanguagesInterface
{
    /**
     * List language supports
     *
     * Return example:
     * [
     *      [
     *              (string) 'language' => (string) Supported language code, generally consisting of its ISO 639-1 id,
     *              (string) 'name' => (string) Human readable name of the language localized to the target language
     *      ]
     *      (...)
     * ]
     *
     * @param string|null $targetLanguage Target language. ie: pt, en, es
     * @return array array structure return above
     *
     * @throws Exception\InvalidTargetLanguageException
     * @throws Exception\TranslateErrorException
     */
    public function languages(string $targetLanguage = null);
}
