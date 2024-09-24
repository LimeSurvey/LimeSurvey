<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>Arabic Sentiment Analysis</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/style.css" media="all" />
</head>

<body>

<div class="Paragraph">
<h2>Arabic Sentiment Analysis:</h2>

<p>Sentiment analysis is one of the most useful Natural Language Processing (NLP) functionalities 
that can determine the tone (positive, negative) of the text (e.g., product reviews, comments, etc.).</p>

<p>This Machine Learning (ML) model was built using a dataset published on 
<a href="https://www.kaggle.com/abedkhooli/arabic-100k-reviews" target="_blank">Kaggle</a> 
and combines 100k Arabic reviews from hotels, books, movies, products, and a few airlines. 
Text (reviews) were cleaned by removing Arabic diacritics and non-Arabic characters. 
Predictions are calculated using the log-odds statistics, and method accuracy exceeds 75% 
which is not a bad performance for a model sized 28.2 KB.</p>

<i>It has been tested also with the <a href="" target="_blank">HARD: Hotel Arabic-Reviews Dataset</a>, 
and it was able to achieve 82% on the balanced reviews dataset (in total 105,698 reviews).</i>

<p>For simplicity, we assumed that all the words in the first language spoken by the Semitic peoples consisted 
of bi-radicals (i.e., two sounds/letters). Therefore, we can handle the majority of Arabic word roots as being 
expanded by the addition of a third letter, with the resulting meaning having a semantic relation to the original bi-radical
(Ref: <a href="https://repositories.lib.utexas.edu/handle/2152/13246" target="_blank">The biradical origin of semitic roots</a>).</p>
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

$reviews = array('الخدمة كانت بطيئة',
                 'الإطلالة رائعة والطعام لذيذ',
                 'التبريد لا يعمل والواي فاي ضعيفة',
                 'النظافة مميزة وموظفي الاستقبال متعاونين',
                 'جاءت القطعة مكسورة والعلبة مفتوحة',
                 'المنتج مطابق للمواصفات والتسليم سريع');

echo <<< END
<center>
  <table border="0" cellspacing="2" cellpadding="5" width="60%">
    <tr>
      <td bgcolor="#27509D" align="center" width="50%">
        <b><font color="#ffffff">Arabic Review (sample input)</font></b>
      </td>
      <td bgcolor="#27509D" align="center" width="25%">
        <b><font color="#ffffff">Sentiment (auto generated)</font></b>
      </td>
      <td bgcolor="#27509D" align="center" width="25%">
        <b><font color="#ffffff">Probability (auto generated)</font></b>
      </td>
    </tr>
END;

foreach ($reviews as $review) {
    $analysis = $Arabic->arSentiment($review);
    
    if ($analysis['isPositive']) {
        $sentiment = 'Positive';
        $bgcolor   = '#E0F0FF';
    } else {
        $sentiment = 'Negative';
        $bgcolor   = '#FFF0FF';
    }
    
    $probability = sprintf('%0.1f', round(100 * $analysis['probability'], 1));
    
    echo '<tr><td bgcolor="'.$bgcolor.'" align="right">';
    echo '<font face="Tahoma">'.$review.'</font></td>';
    echo '<td bgcolor="'.$bgcolor.'" align="center">'.$sentiment.'</td>';
    echo '<td bgcolor="'.$bgcolor.'" align="center">'.$probability.'%</td></tr>';
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

    \$reviews = array('الخدمة كانت بطيئة',
                     'الإطلالة رائعة والطعام لذيذ',
                     'التبريد لا يعمل والواي فاي ضعيفة',
                     'النظافة مميزة وموظفي الاستقبال متعاونين',
                     'جاءت القطعة مكسورة والعلبة مفتوحة',
                     'المنتج مطابق للمواصفات والتسليم سريع');

    echo <<< END
<center>
  <table border="0" cellspacing="2" cellpadding="5" width="60%">
    <tr>
      <td bgcolor="#27509D" align="center" width="50%">
        <b><font color="#ffffff">Arabic Review (sample input)</font></b>
      </td>
      <td bgcolor="#27509D" align="center" width="25%">
        <b><font color="#ffffff">Sentiment (auto generated)</font></b>
      </td>
      <td bgcolor="#27509D" align="center" width="25%">
        <b><font color="#ffffff">Probability (auto generated)</font></b>
      </td>
    </tr>
END;

    foreach (\$reviews as \$review) {
        \$analysis = \$Arabic->arSentiment(\$review);
        
        if (\$analysis['isPositive']) {
            \$sentiment = 'Positive';
            \$bgcolor   = '#E0F0FF';
        } else {
            \$sentiment = 'Negative';
            \$bgcolor   = '#FFF0FF';
        }

        \$probability = sprintf('%0.1f', round(100 * \$analysis['probability'], 1));

        echo '<tr><td bgcolor="'.\$bgcolor.'" align="right">';
        echo '<font face="Tahoma">'.\$review.'</font></td>';
        echo '<td bgcolor="'.\$bgcolor.'" align="center">'.\$sentiment.'</td>';
        echo '<td bgcolor="'.\$bgcolor.'" align="center">'.\$probability.'%</td></tr>';
    }

    echo '</table></center>';
ENDALL;

highlight_string($code);
?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_arSentiment" target="_blank">arSentiment</a>
</i>
</div>
<footer><i><a href="https://github.com/khaled-alshamaa/ar-php">Ar-PHP</a>, an open-source library for website developers to process Arabic content</i></footer>
</body>
</html>
