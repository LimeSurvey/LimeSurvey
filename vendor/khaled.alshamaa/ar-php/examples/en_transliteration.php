<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>Arabic-English Transliteration</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/style.css" media="all" />
</head>

<body>

<div class="Paragraph">
<h2>English-Arabic Transliteration:</h2>
<p align="justified">Transliterate English words into Arabic by render them in the orthography of the Arabic language and vise versa.</p>

<p align="justified">Out of vocabulary (OOV) words are a common source of errors in cross language information retrieval. Bilingual dictionaries 
are often limited in their coverage of named entities, numbers, technical terms and acronyms. There is a need to generate 
translations for these "on-the-fly" or at query time. A significant proportion of OOV words are named entities and technical 
terms. Typical analyses finds around 50% of OOV words to be named entities. Yet these can be the most important words in the queries. 
Cross language retrieval performance (average precision) reduced more than 50% when named entities in the queries were not translated.
When the query language and the document language share the same alphabet it may be sufficient to use the OOV word as its 
own translation. However, when the two languages have different alphabets, the query term must somehow be rendered in the 
orthography of the other language. The process of converting a word from one orthography into another is called transliteration.</p>

<p align="justified">Foreign words often occur in Arabic text as transliteration. This is the case for many categories of foreign words, not just 
proper names but also technical terms such as caviar, telephone and internet.</p>
</div><br />

<div class="Paragraph">
<h2 dir="ltr" id="example-1">
<a href="#example-1" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 1:</h2>
<?php

error_reporting(E_STRICT);

/*
  // Autoload files using Composer autoload
  require_once __DIR__ . '/../vendor/autoload.php';
*/

require '../src/Arabic.php';
$Arabic = new \ArPHP\I18N\Arabic();

$ar_terms = array('خالِد الشَمعَة', 'جُبران خَليل جُبران', 'كاظِم الساهِر',
            'ماجِدَة الرُومِي', 'نِزار قَبَّانِي', 'سُوق الحَمِيدِيَّة؟', 'مَغارَة جَعِيتَا', 
            'غُوطَة دِمَشق', 'حَلَب الشَهبَاء', 'جَزيرَة أَرواد', 'بِلاد الرافِدَين',
            'أهرامات الجِيزَة', 'دِرْع', 'عِيد', 'عُود', 'رِدْء', 'إِيدَاء', 'هِبَة الله', 'قاضٍ');

echo <<< END
<center>
  <table border="0" cellspacing="2" cellpadding="5" width="500">
    <tr>
      <td bgcolor="#27509D" align="center" width="150">
        <b>
          <font color="#ffffff" face="Tahoma">
            English<br />(auto generated)
          </font>
        </b>
      </td>
      <td bgcolor="#27509D" align="center" width="150">
        <b>
          <font color="#ffffff">
            Arabic<br />(sample input)
          </font>
        </b>
      </td>
    </tr>
END;

foreach ($ar_terms as $term) {
    echo '<tr><td bgcolor="#f5f5f5" align="left"><font face="Tahoma">';
    echo $Arabic->ar2en($term);
    echo '</font></td>';
    echo '<td bgcolor="#f5f5f5" align="right">'.$term.'</td></tr>';
}

echo '</table></center>';
?>
</div><br />
<div class="Paragraph">
<h2 dir="ltr">Example Code 1:</h2>
<?php
$code = <<< ENDALL
<?php
	\$Arabic = new \\ArPHP\\I18N\\Arabic();

    \$ar_terms = array('خالِد الشَمعَة', 'جُبران خَليل جُبران', 'كاظِم الساهِر',
            'ماجِدَة الرُومِي', 'نِزار قَبَّانِي', 'سُوق الحَمِيدِيَّة؟', 'مَغارَة جَعِيتَا', 
            'غُوطَة دِمَشق', 'حَلَب الشَهبَاء', 'جَزيرَة أَرواد', 'بِلاد الرافِدَين',
            'أهرامات الجِيزَة', 'دِرْع', 'عِيد', 'عُود', 'رِدْء', 'إِيدَاء', 'هِبَة الله', 'قاضٍ');
    echo <<< END
<center>
  <table border="0" cellspacing="2" cellpadding="5" width="500">
    <tr>
      <td bgcolor="#27509D" align="center" width="150">
        <b>
          <font color="#ffffff" face="Tahoma">
            English<br />(auto generated)
          </font>
        </b>
      </td>
      <td bgcolor="#27509D" align="center" width="150">
        <b>
          <font color="#ffffff">
            Arabic<br />(sample input)
          </font>
        </b>
      </td>
    </tr>
END;

    foreach (\$ar_terms as \$term) {
        echo '<tr><td bgcolor="#f5f5f5" align="left"><font face="Tahoma">';
        echo \$Arabic->ar2en(\$term);
        echo '</font></td>';
        echo '<td bgcolor="#f5f5f5" align="right">'.\$term.'</td></tr>';
    }

    echo '</table></center>';
ENDALL;

highlight_string($code);
?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_ar2en" target="_blank">ar2en</a>
</i>
</div>
<footer><i><a href="https://github.com/khaled-alshamaa/ar-php">Ar-PHP</a>, an open-source library for website developers to process Arabic content</i></footer>
</body>
</html>
