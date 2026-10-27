<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

/**
 * Calculates the code of a newly added answer option or subquestion from the existing codes.
 * Existing codes are never changed: only the code of the new row is derived.
 */
class NextChildCode
{
    /**
     * Calculate the next code based on previous codes.
     *
     * @param array $stringCodes Existing codes of the same scale
     * @param string|null $firstCode Code to use when there are no previous codes (built from the user's prefix setting)
     * @return array [string $code, int $position]
     */
    public static function calculate(array $stringCodes, ?string $firstCode = null): array
    {
        if (empty($stringCodes)) {
            return [$firstCode ?? '1', 0];
        }
        // We get the numerical part of each code and we store them in Arrays
        // One array is to store the pure numerical values (so we can search in it for the greates value, and increment it)
        // Another array is to store the string values (so we keep all the prefixed "0")
        $numCodes = array();
        foreach ($stringCodes as $key => $stringCode) {
            // This will loop into the code, from the last character to the first letter
            $numericSuffix = '';
            $n = 1;
            $numeric = true;
            while ($numeric === true && $n <= strlen((string) $stringCode)) {
                $currentCharacter = (string) substr((string) $stringCode, -$n, 1); // get the current character

                if (ctype_digit($currentCharacter)) {
                    // check if it's numerical
                    $numericSuffix = $currentCharacter . $numericSuffix; // store it in a string
                    $n = $n + 1;
                } else {
                    $numeric = false; // At first non numeric character found, the loop is stopped
                }
            }
            $numCodesWithZero[$key] = (string) $numericSuffix; // In string type, we can have   : "0001"
            $numCodes[$key]         = (int) $numericSuffix; // In int type, we can only have : "1"
        }

        // Let's get the greatest code
        $greatestNumCode          = max($numCodes); // greatest code
        $key                      = array_keys($numCodes, max($numCodes)); // its key (same key in all tables)
        $greatesNumCodeWithZeros  = (isset($numCodesWithZero)) ? $numCodesWithZero[$key[0]] : ''; // its value with prefixed 0 (like : 001)
        $stringCodeOfGreatestCode = $stringCodes[$key[0]]; // its original submitted  string (like: SQ001)

        // We get the string part of it: it's the original string code, without the greates code with its 0 :
        // like  substr ("SQ001", (strlen(SQ001)) - strlen(001) ) ==> "SQ"
        $stringPartOfNewCode    = (string) substr((string) $stringCodeOfGreatestCode, 0, (strlen((string) $stringCodeOfGreatestCode) - strlen($greatesNumCodeWithZeros)));

        // We increment by one the greatest code
        $numericalPartOfNewCode = $greatestNumCode + 1;

        // We get the list of 0 : (using $numericalPartOfNewCode will remove the excedent 0 ; SQ009 will be followed by SQ010 )
        // When the new number is as long as or longer than the old one (999 => 1000), no zero is kept:
        // like in SQ99 => SQ100, not SQ991000
        $zeroCount  = max(0, strlen($greatesNumCodeWithZeros) - strlen((string) $numericalPartOfNewCode));
        $listOfZero = (string) substr($greatesNumCodeWithZeros, 0, $zeroCount);

        // We finally build the new code
        return [$stringPartOfNewCode . $listOfZero . $numericalPartOfNewCode, $numericalPartOfNewCode];
    }
}
