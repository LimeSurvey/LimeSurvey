<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>Spell numbers in the Arabic idiom</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/style.css" media="all" />
</head>

<body>

<div class="Paragraph">
<h2>Spell Numbers in the Arabic Idiom:</h2>
<p align="justified">Spell numbers in the Arabic idiom. This function is very useful for e-Commerce applications 
in Arabic for example. It accepts almost any numeric value and convert it into an equivalent string of words in 
written Arabic language and take care of feminine and Arabic grammar rules.</p>

<p align="justified">If you ever have to create an Arabic PHP application built around invoicing or accounting, 
you might find this method useful. Its sole reason for existence is to help you translate integers into their 
spoken-word equivalents in Arabic language.How is this useful? Well, consider the typical invoice: In addition to 
a description of the work done, the date, and the hourly or project cost, it always includes a total cost at the 
end, the amount that the customer is expected to pay. To avoid any misinterpretation of the total amount, many 
organizations (mine included) put the amount in both words and figures; for example, $1,200 becomes "one thousand 
and two hundred dollars." You probably do the same thing every time you write a check.</p>

<p align="justified">Now take this scenario to a Web-based invoicing system. The actual data used to generate the 
invoice will be stored in a database as integers, both to save space and to simplify calculations. So when a printable 
invoice is generated, your Web application will need to convert those integers into words, this is more clarity 
and more personality.</p>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-1">
<a href="#example-1" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 1: المعدود مذكر مرفوع</h2>
<?php

error_reporting(E_STRICT);

/*
  // Autoload files using Composer autoload
  require_once __DIR__ . '/../vendor/autoload.php';
*/

require '../src/Arabic.php';
$Arabic = new \ArPHP\I18N\Arabic();

$Arabic->setNumberFeminine(1);
$Arabic->setNumberFormat(1);
           
$integer = 141592653589;

$text = $Arabic->int2str($integer);

echo "<center>$integer<br />$text</center>";
?>

</div><br />
<div class="Paragraph">
<h2>Example Code 1:</h2>
<?php
$code = <<< END
<?php
	\$Arabic = new \\ArPHP\\I18N\\Arabic();

    \$Arabic->setNumberFeminine(1);
    \$Arabic->setNumberFormat(1);

    \$integer = 141592653589;

    \$text = \$Arabic->int2str(\$integer);

    echo "<center>\$integer<br />\$text</center>";
END;

highlight_string($code);

?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setNumberFeminine" target="_blank">setNumberFeminine</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setNumberFormat" target="_blank">setNumberFormat</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_int2str" target="_blank">int2str</a>
</i>
</div>
<br />
<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-2">
<a href="#example-2" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 2: المعدود مؤنث منصوب أو مجرور</h2>
<?php
    $Arabic->setNumberFeminine(2);
    $Arabic->setNumberFormat(2);

    $integer = 141592653589;

    $text = $Arabic->int2str($integer);

    echo "<center>$integer<br />$text</center>";
?>

</div><br />
<div class="Paragraph">
<h2>Example Code 2:</h2>
<?php
$code = <<< END
<?php
	\$Arabic = new \\ArPHP\\I18N\\Arabic();

    \$Arabic->setNumberFeminine(2);
    \$Arabic->setNumberFormat(2);

    \$integer = 141592653589;
    
    \$text = \$Arabic->int2str(\$integer);
    
    echo "<center>\$integer<br />\$text</center>";
END;

highlight_string($code);

?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setNumberFeminine" target="_blank">setNumberFeminine</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setNumberFormat" target="_blank">setNumberFormat</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_int2str" target="_blank">int2str</a>
</i>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-3">
<a href="#example-3" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 3: المعدود مؤنث منصوب أو مجرور وهو سالب بفاصلة عشرية</h2>
<?php
    $Arabic->setNumberFeminine(2);
    $Arabic->setNumberFormat(2);
    
    $integer = '-2749.317';
    
    $text = $Arabic->int2str($integer);
    
    echo "<p dir=ltr align=center>$integer<br />$text</p>";
?>

</div><br />
<div class="Paragraph">
<h2>Example Code 3:</h2>
<?php
$code = <<< END
<?php
	\$Arabic = new \\ArPHP\\I18N\\Arabic();
    
    \$Arabic->setNumberFeminine(2);
    \$Arabic->setNumberFormat(2);
    
    \$integer = '-2749.317';
    
    \$text = \$Arabic->int2str(\$integer);
    
    echo "<p dir=ltr align=center>\$integer<br />\$text</p>";
END;

highlight_string($code);

?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setNumberFeminine" target="_blank">setNumberFeminine</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setNumberFormat" target="_blank">setNumberFormat</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_int2str" target="_blank">int2str</a>
</i>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-4">
<a href="#example-4" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 4: العملات العربية</h2>
<?php
    $Arabic->setNumberFeminine(1);
    $Arabic->setNumberFormat(1);

    $number = 7.25;
    $text   = $Arabic->money2str($number, 'KWD', 'ar');
    
    echo "<p align=center>$number<br />$text</p>";
?>

</div><br />
<div class="Paragraph">
<h2>Example Code 4:</h2>
<?php
$code = <<< END
<?php
	\$Arabic = new \\ArPHP\\I18N\\Arabic();

    \$Arabic->setNumberFeminine(1);
    \$Arabic->setNumberFormat(1);
    
    \$number = 7.25;
    \$text   = \$Arabic->money2str(\$number, 'KWD', 'ar');
    
    echo "<p align=center>\$number<br />\$text</p>";
END;

highlight_string($code);

?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setNumberFeminine" target="_blank">setNumberFeminine</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setNumberFormat" target="_blank">setNumberFormat</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_money2str" target="_blank">money2str</a>
</i>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-5">
<a href="#example-5" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>
Example Output 5: صيغ الجمع
</h2>
<?php
    $number = 9;
    $text   = $Arabic->arPlural('تعليق', $number);
    $text   = str_replace('%d', $number, $text);
    
    echo "<p align=center>$text</p>";
    
    $number = 16;
    $text   = $Arabic->arPlural('صندوق', $number, 'صندوقان', 'صناديق', 'صندوقا');
    $text   = str_replace('%d', $number, $text);

    echo "<p align=center>$text</p>";
?>

</div><br />
<div class="Paragraph">
<h2>Example Code 5:</h2>
<?php
$code = <<< END
<?php
	\$Arabic = new \\ArPHP\\I18N\\Arabic();

    \$number = 9;
    \$text   = \$Arabic->arPlural('تعليق', \$number);
    \$text   = str_replace('%d', \$number, \$text);
    
    echo "<p align=center>\$text</p>";
    
    \$number = 16;
    \$text   = \$Arabic->arPlural('صندوق', \$number, 'صندوقان', 'صناديق', 'صندوقا');
    \$text   = str_replace('%d', \$number, \$text);

    echo "<p align=center>\$text</p>";
END;

highlight_string($code);

?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_arPlural" target="_blank">arPlural</a>
</i>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-6">
<a href="#example-6" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 6: الأرقام الهندية</h2>
<?php
    $text1 = '1975/8/2 9:43 صباحا';
    $text2 = $Arabic->int2indic($text1);
    
    echo "<p align=center>$text1<br />$text2</p>";
?>

</div><br />
<div class="Paragraph">
<h2>Example Code 6:</h2>
<?php
$code = <<< END
<?php
	\$Arabic = new \\ArPHP\\I18N\\Arabic();
    
    \$text1 = '1975/8/2 9:43 صباحا';
    \$text2 = \$Arabic->int2indic(\$text1);
    
    echo "<p align=center>\$text1<br />\$text2</p>";
END;

highlight_string($code);

?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_int2indic" target="_blank">int2indic</a>
</i>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-7">
<a href="#example-7" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 7: ترتيب لمعدود مؤنث منصوب أو مجرور</h2>
<?php
    $Arabic->setNumberFeminine(2);
    $Arabic->setNumberFormat(2);
    $Arabic->setNumberOrder(2);
    
    $integer = '17';
    
    $text = $Arabic->int2str($integer);
    
    echo "<p align=center>$integer<br />$text</p>";
?>
</div><br />
<div class="Paragraph">
<h2>Example Code 7:</h2>
<?php
$code = <<< END
<?php
	\$Arabic = new \\ArPHP\\I18N\\Arabic();
    
    \$Arabic->setNumberFeminine(2);
    \$Arabic->setNumberFormat(2);
    \$Arabic->setNumberOrder(2);
    
    \$integer = '17';
    
    \$text = \$Arabic->int2str(\$integer);
    
    echo "<p align=center>\$integer<br />\$text</p>";
END;

highlight_string($code);

?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setNumberFeminine" target="_blank">setNumberFeminine</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setNumberFormat" target="_blank">setNumberFormat</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setNumberOrder" target="_blank">setNumberOrder</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_int2str" target="_blank">int2str</a>
</i>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-8">
<a href="#example-8" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 8: تحويل الرقم المكتوب إلى عدد صحيح من جديد</h2>
<?php
    $string  = 'مليار ومئتين وخمسة وستين مليون وثلاثمئة وثمانية وخمسين ألف وتسعمئة وتسعة وسبعين';

    $integer = $Arabic->str2int($string);
    
    echo "<p align=center>$string<br />$integer</p>";
?>

</div><br />
<div class="Paragraph">
<h2>Example Code 8:</h2>
<?php
$code = <<< END
<?php
	\$Arabic = new \\ArPHP\\I18N\\Arabic();
    
    \$string  = 'مليار ومئتين وخمسة وستين مليون وثلاثمئة وثمانية وخمسين ألف وتسعمئة وتسعة وسبعين';

    \$integer = \$Arabic->str2int(\$string);
    
    echo "<p align=center>\$string<br />\$integer</p>";
END;

highlight_string($code);
?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_str2int" target="_blank">str2int</a>
</i>
</div>
<footer><i><a href="https://github.com/khaled-alshamaa/ar-php">Ar-PHP</a>, an open-source library for website developers to process Arabic content</i></footer>
</body>
</html>
