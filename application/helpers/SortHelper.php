<?php

/**
* This file is part of LimeSurvey
* Offer some helper for sorting
* @since 6.3.7
*/

namespace LimeSurvey\Helpers;

use Yii;

/**
 * Language-aware string sorting
 *
 * PHP's native sort functions compare strings byte by byte, so text with accented or other
 * non-ASCII characters is ordered incorrectly (e.g. "États-Unis" sorts after "Zambie").
 * This helper was introduced to fix that for alphabetically ordered answer options and
 * subquestions (issue #19206, used by QuestionOrderingService).
 *
 * When the php-intl extension is available, sorting is done by \Collator using the locale
 * (CLDR code) of the given LimeSurvey language, which gives correct, language-specific
 * alphabetical order. php-intl is not a hard requirement of LimeSurvey, so without it the
 * helper falls back to PHP's native sort functions (case-insensitive for strings) and logs
 * a warning; accented characters are then not ordered correctly.
 *
 * Usage: SortHelper::getInstance($language)->asort($array, SortHelper::SORT_STRING);
 * The instance is a singleton that is recreated whenever a different language is requested.
 */
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

    /**
     * Get the sort helper for a language, (re)creating it when the language changes
     *
     * @param string $language LimeSurvey language code used to pick the collation locale
     * @return self
     */
    public static function getInstance($language)
    {
        if (empty(self::$instance) || $language !== self::$language) {
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
     * Sort array maintaining index association in reverse order
     * @param string[] $array to sort
     * @param integer $flags in self::SORT_REGULAR (default or invalid), self::SORT_NUMERIC, self::SORT_STRING
     * @return boolean, see Collator::asort
     * @see arsort and Collator::asort
     */
    public function arsort(array &$array, int $flags = self::SORT_REGULAR): bool
    {
        if (is_null(self::$collator)) {
            return arsort($array, self::getFlag($flags));
        }

        // Use our own asort method and then reverse the array
        $result = $this->asort($array, $flags);
        if ($result) {
            $array = array_reverse($array, true); // true preserves keys
        }
        return $result;
    }

    /**
     * Returns the sort flag for the sort function in use (Collator or PHP fallback)
     *
     * String sorting in the PHP fallback is case-insensitive.
     *
     * @param integer $type One of self::SORT_REGULAR, self::SORT_NUMERIC, self::SORT_STRING
     * @return integer Collator::SORT_* flag when Collator is available, else PHP SORT_* flag(s)
     */
    private static function getFlag($type)
    {
        if (is_null(self::$collator)) {
            switch ($type) {
                case self::SORT_STRING:
                    return SORT_STRING | SORT_FLAG_CASE;
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
