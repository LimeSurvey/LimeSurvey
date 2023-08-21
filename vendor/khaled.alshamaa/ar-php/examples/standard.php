<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>Arabic Text ArStandard</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/style.css" media="all" />
</head>

<body>

<div class="Paragraph">
<h2>Arabic Text Standardize:</h2>
<p align="justified">Standardize Arabic text just like rules followed in magazines and newspapers like 
spaces before and after punctuations, brackets and units etc ...</p>
</div><br />

<div class="Paragraph" dir="rtl">
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

    $content = <<<END
هذا نص عربي ، و فيه علامات ترقيم بحاجة إلى ضبط و معايرة !و كذلك نصوص( بين 
أقواس )أو حتى مؤطرة"بإشارات إقتباس "أو- علامات إعتراض -الخ......
<br>
لذا ستكون هذه المكتبة أداة و وسيلة لمعالجة مثل هكذا حالات، بما فيها الواحدات 1 
Kg أو مثلا MB 16 وسواها حتى النسب المؤية مثل 20% أو %50 وهكذا ...
END;

    $str = $Arabic->standard($content);

    echo '<b>Origenal:</b>';
    echo '<p dir="rtl" align="justify">';
    echo $content . '</p>';
    
    echo '<b>Standard:</b>';
    echo '<p dir="rtl" align="justify">';
    echo $str . '</p>';
?>

</div><br />
<div class="Paragraph">
<h2>Example Code 1:</h2>
<?php
$code = <<< ENDALL
<?php
	\$Arabic = new \\ArPHP\\I18N\\Arabic();
    
    \$content = <<<END
هذا نص عربي ، و فيه علامات ترقيم بحاجة إلى ضبط و معايرة !و كذلك نصوص( بين 
أقواس )أو حتى مؤطرة"بإشارات إقتباس "أو- علامات إعتراض -الخ......
<br>
لذا ستكون هذه المكتبة أداة و وسيلة لمعالجة مثل هكذا حالات، بما فيها الواحدات 1 
Kg أو مثلا MB 16 وسواها حتى النسب المؤية مثل 20% أو %50 وهكذا ...
END;

    \$str = \$Arabic->standard(\$content);
    
    echo '<b>Origenal:</b>';
    echo '<p dir="rtl" align="justify">';
    echo \$content . '</p>';
    
    echo '<b>Standard:</b>';
    echo '<p dir="rtl" align="justify">';
    echo \$str . '</p>';
ENDALL;

highlight_string($code);
?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_standard" target="_blank">standard</a>
</i>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-2">
<a href="#example-2" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 2:</h2>
<?php
    $content = <<<END
إذا رُمتَ أنْ تَحيا سَليماً مِن الأذى
...
وَ دينُكَ مَوفورٌ وعِرْضُكَ صَيِّنُ
<br />
لِســـــــانَكَ لا تَذكُرْ بِهِ عَورَةَ امرئٍ
...
فَكُلُّكَ عَوراتٌ وللنّاسِ ألسُنُ
END;
    
    echo '<b>Origenal</b>';
    echo '<p dir="rtl" align="justify">';
    echo $content . '</p>';

    $str1 = $Arabic->stripHarakat($content);
    
    echo '<hr /><b>Strip All Harakat</b>';
    echo '<p dir="rtl" align="justify">';
    echo $str1 . '</p>';

    $str2 = $Arabic->stripHarakat($content, FALSE, FALSE, FALSE, FALSE);
    
    echo '<hr /><b>Strip Harakat but Tatweel, Tanwen, Shadda, and Last Harakat</b>';
    echo '<p dir="rtl" align="justify">';
    echo $str2 . '</p>';

    $str2 = $Arabic->stripHarakat($content, FALSE, TRUE, FALSE, TRUE, FALSE);
    
    echo '<hr /><b>Strip Last Harakat Only (including Tanwen)</b>';
    echo '<p dir="rtl" align="justify">';
    echo $str2 . '</p>';
?>

</div><br />
<div class="Paragraph">
<h2>Example Code 2:</h2>
<?php
$code = <<< ENDALL
<?php
	\$Arabic = new \\ArPHP\\I18N\\Arabic();
    
    \$content = <<<END
إذا رُمتَ أنْ تَحيا سَليماً مِن الأذى
...
وَ دينُكَ مَوفورٌ وعِرْضُكَ صَيِّنُ
<br />
لِســـــــانَكَ لا تَذكُرْ بِهِ عَورَةَ امرئٍ
...
فَكُلُّكَ عَوراتٌ وللنّاسِ ألسُنُ
END;
    
    echo '<b>Origenal</b>';
    echo '<p dir="rtl" align="justify">';
    echo \$content . '</p>';

    \$str1 = \$Arabic->stripHarakat(\$content);
    
    echo '<hr /><b>Strip All Harakat</b>';
    echo '<p dir="rtl" align="justify">';
    echo \$str1 . '</p>';

    \$str2 = \$Arabic->stripHarakat(\$content, FALSE, FALSE, FALSE, FALSE);
    
    echo '<hr /><b>Strip Harakat but Tatweel, Tanwen, Shadda, and Last Harakat</b>';
    echo '<p dir="rtl" align="justify">';
    echo \$str2 . '</p>';

    \$str2 = \$Arabic->stripHarakat(\$content, FALSE, TRUE, FALSE, TRUE, FALSE);
    
    echo '<hr /><b>Strip Last Harakat Only (including Tanwen)</b>';
    echo '<p dir="rtl" align="justify">';
    echo \$str2 . '</p>';
ENDALL;

highlight_string($code);
?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_stripHarakat" target="_blank">stripHarakat</a>
</i>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-3">
<a href="#example-3" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 3:</h2>
<?php
    $content = <<<END
هل تعلم أن النقاط تم إختراعها للعجم وليس للعرب، 
حتى أن العرب قديما كانوا لا يستخدمون النقاط 
وأنت كذلك يمكنك أن تقرأ مقاطع كاملة بدون نقاط كما كان يفعل الأسلاف،
وكانوا يفهمون الكلمات من سياق الجملة 
وأبسط مثال على ذلك أنك تقرأ هذا المقطع من دون مشاكل.
END;

    echo '<b>Origenal</b>';
    echo '<p dir="rtl" align="justify">';
    echo $content . '</p>';

    echo '<hr /><b>String With No Dots Nor Hamza</b>';
    echo '<p dir="rtl" align="justify">';
    echo $Arabic->noDots($content) . '</p>';
?>
</div><br />
<div class="Paragraph">
<h2>Example Code 3:</h2>
<?php
$code = <<< ENDALL
<?php
	\$Arabic = new \\ArPHP\\I18N\\Arabic();
    
    \$content = <<<END
هل تعلم أن النقاط تم إختراعها للعجم وليس للعرب، 
حتى أن العرب قديما كانوا لا يستخدمون النقاط 
وأنت كذلك يمكنك أن تقرأ مقاطع كاملة بدون نقاط كما كان يفعل الأسلاف،
وكانوا يفهمون الكلمات من سياق الجملة 
وأبسط مثال على ذلك أنك تقرأ هذا المقطع من دون مشاكل.
END;

    echo '<b>Origenal</b>';
    echo '<p dir="rtl" align="justify">';
    echo \$content . '</p>';

    echo '<hr /><b>String With No Dots Nor Hamza</b>';
    echo '<p dir="rtl" align="justify">';
    echo \$Arabic->noDots(\$content) . '</p>';
ENDALL;

highlight_string($code);
?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_noDots" target="_blank">noDots</a>
</i>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-4">
<a href="#example-4" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 4:</h2>
<?php
    $text = 'آسِفـــةٌ لا تَنَبُّؤْ 456';

    $Arabic->setNorm('stripTatweel', true)
           ->setNorm('stripTanween', true)
           ->setNorm('stripShadda', true)
           ->setNorm('stripLastHarakat', true)
           ->setNorm('stripWordHarakat', true)
           ->setNorm('normaliseLamAlef', true)
           ->setNorm('normaliseAlef', true)
           ->setNorm('normaliseHamza', true)
           ->setNorm('normaliseTaa', true);
    
    # you can also use all form like the following example
    # $Arabic->setNorm('all', true)->setNorm('normaliseHamza', false)->setNorm('normaliseTaa', false);

    echo '<b>Origenal Text</b>';
    echo '<p dir="rtl" align="justify">';
    echo $text . '</p>';

    echo '<hr /><b>Normalized Text</b>';
    echo '<p dir="rtl" align="justify">';
    echo $Arabic->arNormalizeText($text) . '<br/>';
    echo $Arabic->arNormalizeText($text, 'Hindu') . '</p>';
?>
</div><br />
<div class="Paragraph">
<h2>Example Code 4:</h2>
<?php
$code = <<< ENDALL
<?php
	\$Arabic = new \\ArPHP\\I18N\\Arabic();
    
    \$text = 'آسِفـــةٌ لا تَنَبُّؤْ 456';

    \$Arabic->setNorm('stripTatweel', true)
           ->setNorm('stripTanween', true)
           ->setNorm('stripShadda', true)
           ->setNorm('stripLastHarakat', true)
           ->setNorm('stripWordHarakat', true)
           ->setNorm('normaliseLamAlef', true)
           ->setNorm('normaliseAlef', true)
           ->setNorm('normaliseHamza', true)
           ->setNorm('normaliseTaa', true);

    # you can also use all form like the following example
    # \$Arabic->setNorm('all', true)->setNorm('normaliseHamza', false)->setNorm('normaliseTaa', false);

    echo '<b>Origenal Text</b>';
    echo '<p dir="rtl" align="justify">';
    echo \$text . '</p>';

    echo '<hr /><b>Normalized Text</b>';
    echo '<p dir="rtl" align="justify">';
    echo \$Arabic->arNormalizeText(\$text) . '<br/>';
    echo \$Arabic->arNormalizeText(\$text, 'Hindu') . '</p>';    
ENDALL;

highlight_string($code);
?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setNorm" target="_blank">setNorm</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_arNormalizeText" target="_blank">arNormalizeText</a>
</i>
</div>

<footer><i><a href="https://github.com/khaled-alshamaa/ar-php">Ar-PHP</a>, an open-source library for website developers to process Arabic content</i></footer>
</body>
</html>
