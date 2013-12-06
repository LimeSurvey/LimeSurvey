<?php
class Translator
{
    //An associative array:  key = language code, value = translation library
    private $translations = array();

    public function translate($key, $sLanguageCode)
    {
        return $this->getTranslationLibrary($sLanguageCode)->gT($key);
    }

    protected function getTranslationLibrary($sLanguageCode)
    {
        $library = null;
        if (!array_key_exists($sLanguageCode, $this->translations))
        {
            $library = new limesurvey_lang($sLanguageCode);
            $this->translations[$sLanguageCode] = $library;
        }
        else
        {
            $library = $this->translations[$sLanguageCode];
        }
        return $library;
    }

    /**
    * Finds the header translation key for the column passed in.  If no key is
    * found then false is returned.
    *
    * @param string $key
    * @return string (or false if no match is found)
    */
    public function getHeaderTranslationKey($column)
    {
        if (isset($this->headerTranslationKeys[$column]))
        {
            return $this->headerTranslationKeys[$column];
        }
        else
        {
            return false;
        }
    }
}