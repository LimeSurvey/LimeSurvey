<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>MakeTime for Arabic/Islamic Higri Calendar</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/style.css" media="all" />
</head>

<body>

<div class="Paragraph">
<h2>Arabic/Hijri Maketime:</h2>
<p align="justified">Arabic and Islamic customization of PHP mktime function. It can convert Hijri date 
into UNIX timestamp format.</p>

<b><i>UNIX timestamp</i></b>
<p align="justified">Development of the Unix operating system began at Bell Laboratories in 1969 by Dennis 
Ritchie and Ken Thompson, with the first PDP-11 version becoming operational in February 1971. Unix wisely 
adopted the convention that all internal dates and times (for example, the time of creation and last modification 
of files) were kept in Universal Time, and converted to local time based on a per-user time zone specification. 
This far-sighted choice has made it vastly easier to integrate Unix systems into far-flung networks without 
a chaos of conflicting time settings.</p>

<p align="justified">The machines on which Unix was developed and initially deployed could not support 
arithmetic on integers longer than 32 bits without costly multiple-precision computation in software. The 
internal representation of time was therefore chosen to be the number of seconds elapsed since 00:00 Universal 
time on January 1, 1970 in the Gregorian calendar (Julian day 2440587.5), with time stored as a 32 bit signed 
integer (long in the original C implementation).</p>

<p align="justified">The influence of Unix time representation has spread well beyond Unix since most C and 
C++ libraries on other systems provide Unix-compatible time and date functions. The major drawback of Unix 
time representation is that, if kept as a 32 bit signed quantity, on January 19, 2038 it will go negative, 
resulting in chaos in programs unprepared for this. Modern Unix and C implementations define the result of 
the time() function as type time_t, which leaves the door open for remediation (by changing the definition 
to a 64 bit integer, for example) before the clock ticks the dreaded doomsday second.</p>
</div><br />

<div class="Paragraph">
<h2 dir="ltr" id="example-1">
<a href="#example-1" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 1:</h2>

<?php

error_reporting(E_STRICT);

date_default_timezone_set('UTC');

/*
  // Autoload files using Composer autoload
  require_once __DIR__ . '/../vendor/autoload.php';
*/

require '../src/Arabic.php';
$Arabic = new \ArPHP\I18N\Arabic();

$correction = $Arabic->mktimeCorrection(9, 1429);
$time = $Arabic->mktime(0, 0, 0, 9, 1, 1429, $correction);
echo "Calculated first day of Ramadan 1429 unix timestamp is: $time<br>";

$Gregorian = date('l F j, Y', $time);
echo "Which is $Gregorian in Gregorian calendar<br>";

$days = $Arabic->hijriMonthDays(9, 1429);
echo "That Ramadan has $days days in total";

?>
</div><br />

<div class="Paragraph">
<h2 dir="ltr">Example Code 1:</h2>
<?php
$code = <<< END
<?php
    date_default_timezone_set('UTC');

	\$Arabic = new \\ArPHP\\I18N\\Arabic();

    \$correction = \$Arabic->mktimeCorrection(9, 1429);
    \$time = \$Arabic->mktime(0, 0, 0, 9, 1, 1429, \$correction);    
    echo "Calculated first day of Ramadan 1429 unix timestamp is: \$time<br>";
    
    \$Gregorian = date('l F j, Y', \$time);
    echo "Which is \$Gregorian in Gregorian calendar";

    \$days = \$Arabic->hijriMonthDays(9, 1429);
    echo "That Ramadan has \$days days in total";
END;

highlight_string($code);
?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_mktimeCorrection" target="_blank">mktimeCorrection</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_mktime" target="_blank">mktime</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_hijriMonthDays" target="_blank">hijriMonthDays</a>
</i>
</div>
<footer><i><a href="https://github.com/khaled-alshamaa/ar-php">Ar-PHP</a>, an open-source library for website developers to process Arabic content</i></footer>
</body>
</html>
