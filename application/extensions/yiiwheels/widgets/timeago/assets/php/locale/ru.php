<?php
// Russian
return array(
    'prefixAgo' => NULL,
    'prefixFromNow' => "через",
    'suffixAgo' => "назад",
    'suffixFromNow' => NULL,
    'seconds' => "меньше минуты",
    'minute' => "минуту",
    'minutes' => array("%d минута", "%d минуты", "%d минут"),
    'hour' => "час",
    'hours' => array("%d час", "%d часа", "%d часов"),
    'day' => "день",
    'days' => array("%d день", "%d дня", "%d дней"),
    'month' => "месяц",
    'months' => array("%d месяц", "%d месяца", "%d месяцев"),
    'year' => "год",
    'years' => array("%d год", "%d года", "%d лет"),
    'wordSeparator' => ' ',
    'numbers' => array(),
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