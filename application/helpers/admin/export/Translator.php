<?php
class Translator
{
    //An associative array:  key = language code, value = translation library
    private $translations = array();

    public function translate($key, $sLanguageCode)
    {
        return gT($key, 'html', $sLanguageCode);
    }

    /**
     * Finds the header translation key for the column passed in.  If no key is
     * found then false is returned.
     *
     * @return string (or false if no match is found)
     */
    public function getHeaderTranslationKey($column)
    {
        if (isset($this->headerTranslationKeys[$column])) {
            return $this->headerTranslationKeys[$column];
        } else {
            return false;
        }
    }
}