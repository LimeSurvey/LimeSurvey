<?php
// Ukrainian
return array(
    'prefixAgo' => NULL,
    'prefixFromNow' => "через",
    'suffixAgo' => "тому",
    'suffixFromNow' => NULL,
    'seconds' => "менше хвилини",
    'minute' => "хвилина",
    'minutes' => array("%d хвилина", "%d хвилини", "%d хвилин"),
    'hour' => "година",
    'hours' => array("%d година", "%d години", "%d годин"),
    'day' => "день",
    'days' => array("%d день", "%d дні", "%d днів"),
    'month' => "місяць",
    'months' => array("%d місяць", "%d місяці", "%d місяців"),
    'year' => "рік",
    'years' => array("%d рік", "%d роки", "%d років"),
    'wordSeparator' => ' ',
    'numbers' => array (),
    'rules' =>
        function($n) {
            $n10 = $n % 10;
            if ( ($n10 == 1) && ( ($n == 1) || ($n > 20) ) ) {
                return 0;
            } else if ( ($n10 > 1) && ($n10 < 5) && ( ($n > 20) || ($n < 10) ) ) {
                return 1;
            }
            return 2;
        },
);