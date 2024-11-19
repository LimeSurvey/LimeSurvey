<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>Arabic/Islamic Date and Calendar</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/style.css" media="all" />
</head>

<body>

<div class="Paragraph">
<h2>Arabic/Hijri Date:</h2>
<p align="justified">Arabic and Islamic customization of PHP date function. It can convert UNIX timestamp into 
string in Arabic as well as convert it into Hijri calendar.</p>

<b><i>The Islamic Calendar</i></b>
<p align="justified">The Islamic calendar is purely lunar and consists of twelve alternating months of 30 and 
29 days, with the final 29 day month extended to 30 days during leap years. Leap years follow a 30 year cycle 
and occur in years 1, 5, 7, 10, 13, 16, 18, 21, 24, 26, and 29. The calendar begins on Friday, July 16th, 622 C.E. 
in the Julian calendar, Julian day 1948439.5, the day of Muhammad's separate from Mecca to Medina, the first day 
of the first month of year 1 A.H. "Anno Hegira".</p>

<p align="justified">Each cycle of 30 years thus contains 19 normal years of 354 days and 11 leap years of 355, 
so the average length of a year is therefore ((19 x 354) + (11 x 355)) / 30 = 354.365... days, with a mean length 
of month of 1/12 this figure, or 29.53055... days, which closely approximates the mean synodic month (time from 
new Moon to next new Moon) of 29.530588 days, with the calendar only slipping one day with respect to the Moon 
every 2525 years. Since the calendar is fixed to the Moon, not the solar year, the months shift with respect to 
the seasons, with each month beginning about 11 days earlier in each successive solar year.</p>

<p align="justified">The convert presented here is the most commonly used civil calendar in the Islamic world; 
for religious purposes months are defined to start with the first observation of the crescent of the new Moon.</p>

<b><i>The Julian Calendar</i></b>
<p align="justified">The Julian calendar was proclaimed by Julius Casar in 46 B.C. and underwent several 
modifications before reaching its final form in 8 C.E. The Julian calendar differs from the Gregorian only 
in the determination of leap years, lacking the correction for years divisible by 100 and 400 in the Gregorian 
calendar. In the Julian calendar, any positive year is a leap year if divisible by 4. (Negative years are leap 
years if when divided by 4 a remainder of 3 results.) Days are considered to begin at midnight.</p>

<p align="justified">In the Julian calendar the average year has a length of 365.25 days. compared to the actual 
solar tropical year of 365.24219878 days. The calendar thus accumulates one day of error with respect to the solar 
year every 128 years. Being a purely solar calendar, no attempt is made to synchronise the start of months to the 
phases of the Moon.</p>

<b><i>The Gregorian Calendar</i></b>
<p align="justified">The Gregorian calendar was proclaimed by Pope Gregory XIII and took effect in most Catholic 
states in 1582, in which October 4, 1582 of the Julian calendar was followed by October 15 in the new calendar, 
correcting for the accumulated discrepancy between the Julian calendar and the equinox as of that date. When 
comparing historical dates, it's important to note that the Gregorian calendar, used universally today in Western 
countries and in international commerce, was adopted at different times by different countries. Britain and her 
colonies (including what is now the United States), did not switch to the Gregorian calendar until 1752, when 
Wednesday 2nd September in the Julian calendar dawned as Thursday the 14th in the Gregorian.</p>

<p align="justified">The Gregorian calendar is a minor correction to the Julian. In the Julian calendar every 
fourth year is a leap year in which February has 29, not 28 days, but in the Gregorian, years divisible by 100 
are not leap years unless they are also divisible by 400. How prescient was Pope Gregory! Whatever the problems 
of Y2K, they won't include sloppy programming which assumes every year divisible by 4 is a leap year since 2000, 
unlike the previous and subsequent years divisible by 100, is a leap year. As in the Julian calendar, days are 
considered to begin at midnight.</p>

<p align="justified">The average length of a year in the Gregorian calendar is 365.2425 days compared to the 
actual solar tropical year (time from equinox to equinox) of 365.24219878 days, so the calendar accumulates one 
day of error with respect to the solar year about every 3300 years. As a purely solar calendar, no attempt is 
made to synchronise the start of months to the phases of the Moon.</p>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-1">
<a href="#example-1" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 1:</h2>
<?php

error_reporting(E_STRICT);

date_default_timezone_set('GMT');
$time = time();

echo date('l j F Y h:i:s A', $time);
echo '<br /><br />';

/*
  // Autoload files using Composer autoload
  require_once __DIR__ . '/../vendor/autoload.php';
*/

require '../src/Arabic.php';
$Arabic = new \ArPHP\I18N\Arabic();

$correction = $Arabic->dateCorrection($time);
echo $Arabic->date('l j F Y h:i:s A', $time, $correction);

$day = $Arabic->date('j', $time, $correction);
echo ' [<a href="moon.php?day='.$day.'" target=_blank>القمر الليلة</a>]';
echo '<br /><br />';

$Arabic->setDateMode(8);
echo $Arabic->date('l j F Y h:i:s A', $time, $correction);
echo '<br /><br />';

$Arabic->setDateMode(2);
echo $Arabic->date('l j F Y h:i:s A', $time);
echo '<br /><br />';

$Arabic->setDateMode(3);
echo $Arabic->date('l j F Y h:i:s A', $time);
echo '<br /><br />';

$Arabic->setDateMode(4);
echo $Arabic->date('l j F Y h:i:s A', $time);
echo '<br /><br />';

$Arabic->setDateMode(5);
echo $Arabic->date('l j F Y h:i:s A', $time);
echo '<br /><br />';

$Arabic->setDateMode(6);
echo $Arabic->date('l j F Y h:i:s A', $time);
echo '<br /><br />';

$Arabic->setDateMode(7);
echo $Arabic->date('l j F Y h:i:s A', $time);

?>
</div><br />
<div class="Paragraph">
<h2>Example Code 1:</h2>
<?php
$code = <<< END
<?php
    date_default_timezone_set('GMT');
    \$time = time();

    echo date('l j F Y h:i:s A', \$time);
    echo '<br /><br />';

	\$Arabic = new \\ArPHP\\I18N\\Arabic();

    \$correction = \$Arabic->dateCorrection (\$time);
    echo \$Arabic->date('l j F Y h:i:s A', \$time, \$correction);
	echo '<br /><br />';

	\$Arabic->setDateMode(8);
	echo \$Arabic->date('l j F Y h:i:s A', \$time, \$correction);
	echo '<br /><br />';

    \$Arabic->setDateMode(2);
    echo \$Arabic->date('l j F Y h:i:s A', \$time);
    echo '<br /><br />';
    
    \$Arabic->setDateMode(3);
    echo \$Arabic->date('l j F Y h:i:s A', \$time);
    echo '<br /><br />';

    \$Arabic->setDateMode(4);
    echo \$Arabic->date('l j F Y h:i:s A', \$time);
    echo '<br /><br />';

    \$Arabic->setDateMode(5);
    echo \$Arabic->date('l j F Y h:i:s A', \$time);
    echo '<br /><br />';

    \$Arabic->setDateMode(6);
    echo \$Arabic->date('l j F Y h:i:s A', \$time);
    echo '<br /><br />';

    \$Arabic->setDateMode(7);
    echo \$Arabic->date('l j F Y h:i:s A', \$time);
END;

highlight_string($code);
?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_dateCorrection" target="_blank">dateCorrection</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setDateMode" target="_blank">setDateMode</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_date" target="_blank">date</a>
</i>
</div>
<footer><i><a href="https://github.com/khaled-alshamaa/ar-php">Ar-PHP</a>, an open-source library for website developers to process Arabic content</i></footer>
</body>
</html>
