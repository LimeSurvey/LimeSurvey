<?php

/**
* This file is part of LimeSurvey
* Offer some helper for sorting
* @since 6.3.7
*/

namespace LimeSurvey\Helpers;

use Yii;

// Can not use Collator class, must work without

class SortHelper
{
    /** @var integer flag for regular sort */
    const SORT_REGULAR = 0;
    /** @var integer flag for numeric sort */
    const SORT_NUMERIC = 1;
    /** @var integer flag for string sort */
    const SORT_STRING = 2;

    /**
     * @var self Singleton
     */
    private static $instance = null;

    /**
     * @var string current language
     */
    private static $language = null;
    /**
     * @var \Collator if possible
     */
    private static $collator = null;

    /**
     * Set the language when construct
     */
    private function __construct($language)
    {
        self::$language = $language;
        if (class_exists('Collator')) {
            $locale = $language;
            $languageData = getLanguageData();
            if (isset($languageData[$language]['cldr'])) {
                $locale = $languageData[$language]['cldr'];
            }
            self::$collator = new \Collator($locale);
        } else {
            Yii::log(
                "For better alphabetical ordering : you need php-intl.",
                'warning',
                'application.helpers.sorthelper.construct'
            );
        }
    }

    public static function getInstance($language)
    {
        if (empty(self::$instance) || $language === self::$language) {
            self::$instance = new self($language);
        }

        return self::$instance;
    }

    /**
     * Sort array maintaining index association
     * @see asort and Collator::asort
     * @param string[] $array to sort
     * @param integer $flags in self::SORT_REGULAR (default or invalid), self::SORT_NUMERIC, self::SORT_STRING
     * @return boolean, see Collator::asort
     */
    public function asort(array &$array, int $flags = self::SORT_REGULAR): bool
    {
        if (is_null(self::$collator)) {
            return asort($array, self::getFlag($flags));
        }
        return self::$collator->asort($array, self::getFlag($flags));
    }

    /**
     * Return flag tupe depend on functoion sed
     * @param integer
     * @return integer
     */
    private static function getFlag($type)
    {
        if (is_null(self::$collator)) {
            switch ($type) {
                case self::SORT_STRING:
                    return SORT_STRING;
                case self::SORT_NUMERIC:
                    return SORT_NUMERIC;
                case self::SORT_REGULAR:
                default:
                    return SORT_REGULAR;
            }
        }
        switch ($type) {
            case self::SORT_STRING:
                return \Collator::SORT_STRING;
            case self::SORT_NUMERIC:
                return \Collator::SORT_NUMERIC;
            case self::SORT_REGULAR:
            default:
                return \Collator::SORT_REGULAR;
        }
    }
}
