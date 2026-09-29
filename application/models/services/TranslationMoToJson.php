<?php

namespace LimeSurvey\Models\Services;

use CGettextMessageSource;
use LSGettextMoFile;
use Yii;

/**
 * Class for converting gettext MO files into JSON format.
 */
class TranslationMoToJson
{
    /** @var string the language abbreviation (e.g. 'de') */
    private string $language;

    /**
     * Initializes the translation service with the given language and
     * creates a message source for the given language.
     *
     * @param string $language the language abbreviation (e.g. 'de').
     */
    public function __construct($language)
    {
        $this->language = $language;
    }

    /**
     * Returns the translations from MO file, as array or in JSON format.
     *
     * @param bool $translateToJson
     *
     * containing the translations ("source_msg" => "translated_msg") or in case of error an array with an error message.
     *
     * @return array|string The translated messages in JSON format or an array with an error message.
     *                      ["source_msg" => "translated_msg"]
     *                      ["error" => "error message..."]
     */
    public function translateMoToJson(bool $translateToJson = false)
    {
        $pathApplication = Yii::app()->getConfig('rootdir');
        $pathToLanguageFiles = $pathApplication . DIRECTORY_SEPARATOR . "locale"
            . DIRECTORY_SEPARATOR . $this->language . DIRECTORY_SEPARATOR
            . $this->language . '.mo';
        if (!file_exists($pathToLanguageFiles)) {
            return [
                'error' => 'Translation file not found.',
                'path' => $pathToLanguageFiles
            ];
        }

        $file = new LSGettextMoFile(false);
        try {
            $messagesGettext = $file->load($pathToLanguageFiles, '');
            foreach ($messagesGettext as $original => $translation) {
                // if original contains ":" in the end, make an extra entry without the ":"
                if (str_ends_with($original, ':')) {
                    $strippedOriginal = rtrim($original, ':');
//                    trimming translation both for LTR and RTL
                    $strippedTranslation = trim($translation, ':');
                    $messagesGettext[$strippedOriginal] = $strippedTranslation;
                }
            }
        } catch (\CException $e) {
            return [
                'error' => 'Error loading translation file.',
                'exception' => $e->getMessage()
            ];
        }

        if ($translateToJson) {
            return json_encode($messagesGettext);
        }

        return $messagesGettext;
    }
}
