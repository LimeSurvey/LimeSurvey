<?php
class Translator
{
    //An associative array:  key = language code, value = translation library
    private $translations = array();

    //The following array stores field names that require pulling a value from the
    //internationalization layer. <fieldname> => <internationalization key>
    private $headerTranslationKeys = array(
    'id' => 'id',
    'lastname' => 'Last name',
    'firstname' => 'First name',
    'email' => 'Email address',
    'token' => 'Token',
    'datestamp' => 'Date last action',
    'startdate' => 'Date started',
    'submitdate' => 'Completed',
    'ipaddr' => 'IP address',
    'refurl' => 'Referring URL',
    'lastpage' => 'Last page',
    'startlanguage' => 'Start language'
    );

    public function translate($key, $sLanguageCode)
    {
        return $this->getTranslationLibrary($sLanguageCode)->gT($key);
    }

    /**
    * Accepts a fieldName from a survey fieldMap and returns the translated value
    * for the fieldName in the survey's base language (if one exists).
    * If no translation exists for the provided column/fieldName then
    * false is returned.
    *
    * To add any columns/fieldNames to be processed by this function, simply add the
    * column/fieldName to the $headerTranslationKeys associative array.
    *
    * This provides a mechanism for determining of a column in a survey's data table
    * needs to be translated through the translation mechanism, or if its an actual
    * survey data column.
    *
    * @param string $column
    * @param string $sLanguageCode
    * @return string
    */
    public function translateHeading($column, $sLanguageCode)
    {
        $key = $this->getHeaderTranslationKey($column);
        //echo "Column: $column, Key: $key".PHP_EOL;
        if ($key)
        {
            return $this->translate($key, $sLanguageCode);
        }
        else
        {
            return false;
        }
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