<?php

namespace LimeSurvey\Models\Services;

use CGettextMessageSource;
use LSGettextMoFile;

/**
 * Class for converting gettext MO files into JSON format.
 */
class TranslationMoToJson
{
    /** @var string the language abbreviation (e.g. 'de') */
    private string $language;

    /**
     * @var \LSMessageSource the message source for the given language.
     */
    private  $msgSource;

    /**
     * Initializes the translation service with the given language and
     * creates a message source for the given language.
     *
     * @param string $language the language abbreviation (e.g. 'de').
     */
    public function __construct($language){
        $this->language = $language;
        $this->msgSource = new \LSMessageSource();
    }

    /**
     * Transforms the given MO file into a JSON representation of the translations.
     *
     * @param $poFile
     * @return false|int|string
     */
    public function translateMoToJson($poFile){
        $translations = $this->msgSource->loadMessages('', $this->language);

        return json_encode($translations);
    }

}
