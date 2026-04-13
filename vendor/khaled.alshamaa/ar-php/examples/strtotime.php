<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>Parse about any Arabic textual datetime description into a Unix timestamp</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/style.css" media="all" />
</head>

<body>

<div class="Paragraph">
<h2>Arabic StrToTime:</h2>
<p align="justified">Parse about any Arabic textual datetime description into a Unix timestamp.</p>

<p align="justified">The function expects to be given a string containing an Arabic date format 
and will try to parse that format into a Unix timestamp (the number of seconds since January 
1 1970 00:00:00 GMT), relative to the timestamp given in now, or the current time if none is supplied.</p>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-1">
<a href="#example-1" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 1:</h2>
<?php

error_reporting(E_STRICT);

date_default_timezone_set('UTC');
$time = time();

echo date('l dS F Y', $time);
echo '<br /><br />';

/*
  // Autoload files using Composer autoload
  require_once __DIR__ . '/../vendor/autoload.php';
*/

require '../src/Arabic.php';
$Arabic = new \ArPHP\I18N\Arabic();

$str  = 'الخميس القادم';
$int  = $Arabic->strtotime($str, $time);
$date = date('l dS F Y', $int);
echo "$str - $int - $date<br /><br />";

$str  = 'الأحد الماضي';
$int  = $Arabic->strtotime($str, $time);
$date = date('l dS F Y', $int);
echo "$str - $int - $date<br /><br />";

$str  = 'بعد أسبوع و ثلاثة أيام';
$int  = $Arabic->strtotime($str, $time);
$date = date('l dS F Y', $int);
echo "$str - $int - $date<br /><br />";

$str  = 'منذ تسعة أيام';
$int  = $Arabic->strtotime($str, $time);
$date = date('l dS F Y', $int);
echo "$str - $int - $date<br /><br />";

$str  = 'قبل إسبوعين';
$int  = $Arabic->strtotime($str, $time);
$date = date('l dS F Y', $int);
echo "$str - $int - $date<br /><br />";

$str  = '2 آب 1975';
$int  = $Arabic->strtotime($str, $time);
$date = date('l dS F Y', $int);
echo "$str - $int - $date<br /><br />";

$str  = '1 رمضان 1429';
$int  = $Arabic->strtotime($str, $time);
$date = date('l dS F Y', $int);
echo "$str - $int - $date<br /><br />";
?>
</div><br />
<div class="Paragraph">
<h2 dir="ltr">Example Code 1:</h2>
<?php
$code = <<< END
<?php
    date_default_timezone_set('UTC');
    \$time = time();

    echo date('l dS F Y', \$time);
    echo '<br /><br />';

	\$Arabic = new \\ArPHP\\I18N\\Arabic();

    \$str  = 'الخميس القادم';
    \$int  = \$Arabic->strtotime(\$str, \$time);
    \$date = date('l dS F Y', \$int);
    echo "\$str - \$int - \$date<br /><br />";
    
    \$str  = 'الأحد الماضي';
    \$int  = \$Arabic->strtotime(\$str, \$time);
    \$date = date('l dS F Y', \$int);
    echo "\$str - \$int - \$date<br /><br />";
    
    \$str  = 'بعد أسبوع و ثلاثة أيام';
    \$int  = \$Arabic->strtotime(\$str, \$time);
    \$date = date('l dS F Y', \$int);
    echo "\$str - \$int - \$date<br /><br />";
    
    \$str  = 'منذ تسعة أيام';
    \$int  = \$Arabic->strtotime(\$str, \$time);
    \$date = date('l dS F Y', \$int);
    echo "\$str - \$int - \$date<br /><br />";
    
    \$str  = 'قبل إسبوعين';
    \$int  = \$Arabic->strtotime(\$str, \$time);
    \$date = date('l dS F Y', \$int);
    echo "\$str - \$int - \$date<br /><br />";
    
    \$str  = '2 آب 1975';
    \$int  = \$Arabic->strtotime(\$str, \$time);
    \$date = date('l dS F Y', \$int);
    echo "\$str - \$int - \$date<br /><br />";

    \$str  = '1 رمضان 1429';
    \$int  = \$Arabic->strtotime(\$str, \$time);
    \$date = date('l dS F Y', \$int);
    echo "\$str - \$int - \$date<br /><br />";
END;

highlight_string($code);
?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_strtotime" target="_blank">strtotime</a>
</i>
</div><br />



<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-2">
<a href="#example-2" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 2:</h2>
<?php

$time  = time();
$other = $time - 1.618 * 3600 * 24 * 365;

$str = $Arabic->diffForHumans($time, $other);
echo "$str<br /><br />";

$str = $Arabic->diffForHumans($time, $other, 3);
echo "$str<br /><br />";

$str = $Arabic->diffForHumans($time, $other, 3, false);
echo "$str<br /><br />";

$str = $Arabic->diffForHumans($other, $time, 7);
echo "$str<br /><br />";
?>
</div><br />
<div class="Paragraph">
<h2 dir="ltr">Example Code 2:</h2>
<?php
$code = <<< END
<?php
    date_default_timezone_set('UTC');
    
    \$time  = time();
    \$other = \$time - 1.618 * 3600 * 24 * 365;

	\$Arabic = new \\ArPHP\\I18N\\Arabic();

    \$str = \$Arabic->diffForHumans(\$time, \$other);
    echo "\$str<br /><br />";

    \$str = \$Arabic->diffForHumans(\$time, \$other, 3);
    echo "\$str<br /><br />";

    \$str = \$Arabic->diffForHumans(\$time, \$other, 3, false);
    echo "\$str<br /><br />";

    \$str = \$Arabic->diffForHumans(\$other, \$time, 7);
    echo "\$str<br /><br />";
END;

highlight_string($code);
?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_diffForHumans" target="_blank">diffForHumans</a>
</i>
</div>

<footer><i><a href="https://github.com/khaled-alshamaa/ar-php">Ar-PHP</a>, an open-source library for website developers to process Arabic content</i></footer>
</body>
</html>
