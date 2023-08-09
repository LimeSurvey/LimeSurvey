<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>Convert keyboard language programmatically (English - Arabic)</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/style.css" media="all" />
</head>

<body>

<div class="Paragraph">
<h2>Arabic Keyboard Swapping Language:</h2>
<p align="justified">Convert keyboard language between English/French and Arabic programmatically. This function can 
be helpful in dual language forms when users miss change keyboard language while they are entering data.</p>

<p align="justified">If you wrote an Arabic sentence while your keyboard stays in English mode by mistake, you will 
get a non-sense English text on your screen. In that case you can use this method to make a kind of magic conversion 
to swap that odd text by original Arabic sentence you meant when you type on your keyboard.</p>

<p align="justified">Please note that magic conversion in the opposite direction (if you type English sentences while 
your keyboard stays in Arabic mode) is also available, but it is not reliable as much as previous case because in Arabic 
keyboard we have some keys provide a short-cut to type two chars in one click (these keys include: b, B, G and T).</p>

<p align="justified">Well, we try to come over this issue by suppose that user used optimum way by using short-cut 
keys when available instead of assemble chars using stand alone keys, but if (s)he does not then you may have some 
typo chars in converted text.</p>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-1-a">
<a href="#example-1-a" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 1 (a):</h2>
<?php
error_reporting(E_STRICT);

/*
  // Autoload files using Composer autoload
  require_once __DIR__ . '/../vendor/autoload.php';
*/

require '../src/Arabic.php';
$Arabic = new \ArPHP\I18N\Arabic();

$str = "Hpf lk hgkhs hglj'vtdkK Hpf hg`dk dldg,k f;gdjil Ygn
,p]hkdm hgHl,v tb drt,k ljv]]dk fdk krdqdk>";
echo "<u><i>Before - English Keyboard:</i></u><br />$str<br /><br />";

$text = $Arabic->swapEa($str);
echo "<u><i>After:</i></u><br />$text<br /><br />";

?>
</div><br />
<div class="Paragraph">
<h2>Example Code 1 (a):</h2>
<?php
$code = <<< END
<?php
    \$Arabic = new \\ArPHP\\I18N\\Arabic();

    \$str = "Hpf lk hgkhs hglj'vtdkK Hpf hg`dk dldg,k f;gdjil Ygn
    ,p]hkdm hgHl,v tb drt,k ljv]]dk fdk krdqdk>";
    echo "<u><i>Before - English Keyboard:</i></u><br />\$str<br /><br />";
    
    \$text = \$Arabic->swapEa(\$str);
    echo "<u><i>After:</i></u><br />\$text<br /><br />";
?>
END;

highlight_string($code);

?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_swapEa" target="_blank">swapEa</a>
</i>
</div>
<br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-1-b">
<a href="#example-1-b" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 1 (b):</h2>
<?php
$str = 'Hpf lk hgkhs hgljùvtdkK Hpf hg²dk dldg;k fmgdjil Ygn 
;p$hkd, hgHl;v tb drt;k ljv$$dk fdk krdadk/';
echo "<u><i>Before - French Keyboard:</i></u><br />$str<br /><br />";

$text = $Arabic->swapFa($str);
echo "<u><i>After:</i></u><br />$text<br /><br /><b>جبران خليل جبران</b>";

?>
</div><br />
<div class="Paragraph">
<h2>Example Code 1 (b):</h2>
<?php
$code = <<< END
<?php
    \$Arabic = new \\ArPHP\\I18N\\Arabic();

    \$str = 'Hpf lk hgkhs hgljùvtdkK Hpf hg²dk dldg;k fmgdjil Ygn 
    ;p\$hkd, hgHl;v tb drt;k ljv\$\$dk fdk krdadk/';
    echo "<u><i>Before - French Keyboard:</i></u><br />\$str<br /><br />";

    \$text = \$Arabic->swapFa(\$str);
    echo "<u><i>After:</i></u><br />\$text<br /><br /><b>جبران خليل جبران</b>";
?>
END;

highlight_string($code);

?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_swapFa" target="_blank">swapFa</a>
</i>
</div>
<br />

<div class="Paragraph">
<h2 dir="ltr" id="example-2">
<a href="#example-2" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 2:</h2>
<?php
    $str = "ِىغ هىفثممهلثىف بخخم ؤشى ةشنث فاهىلس لاهللثق ةخقث ؤخةحمثء شىي ةخقث رهخمثىفز ÷ف فشنثس ش فخعؤا خب لثىهعس شىي ش مخف خب ؤخعقشلث فخ ةخرث هى فاث خححخسهفث يهقثؤفهخىز";
    echo "<u><i>Before:</i></u><br />$str<br /><br />";
    
    $text = $Arabic->swapAe($str);
    echo "<u><i>After:</i></u><br />$text<br /><br /><b>Albert Einstein</b>";
?>

</div><br />
<div class="Paragraph">
<h2>Example Code 2:</h2>
<?php
$code = <<< END
<?php
    \$Arabic = new \\ArPHP\\I18N\\Arabic();
    
    \$str = "ِىغ هىفثممهلثىف بخخم ؤشى ةشنث فاهىلس لاهللثق ةخقث ؤخةحمثء شىي ةخقث رهخمثىفز ÷ف فشنثس ش فخعؤا خب لثىهعس شىي ش مخف خب ؤخعقشلث فخ ةخرث هى فاث خححخسهفث يهقثؤفهخىز";
    
    echo "<u><i>Before:</i></u><br />\$str<br /><br />";
    
    \$text = \$Arabic->swapAe(\$str);
    echo "<u><i>After:</i></u><br />\$text<br /><br /><b>Albert Einstein</b>";
?>
END;

highlight_string($code);

?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_swapAe" target="_blank">swapAe</a>
</i>
</div>
<br />

<div class="Paragraph">
<h2 dir="ltr" id="example-3">
<a href="#example-3" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 3:</h2>
<?php
    $examples = array("ff'z g;k fefhj", "FF'Z G;K FEFHJ", 'ٍمخصمغ لاعف سعقثمغ', 'sLOWLY BUT SURELY');

    foreach ($examples as $example) {
        $fix = $Arabic->fixKeyboardLang($example);

        echo '<font color="red">' . $example . '</font> => ';
        echo '<font color="blue">' . $fix . '</font><br />';
    }
?>

</div><br />
<div class="Paragraph">
<h2>Example Code 3:</h2>
<?php
$code = <<< END
<?php
    \$Arabic = new \\ArPHP\\I18N\\Arabic();
    
    \$examples = array("ff'z g;k fefhj", "FF'Z G;K FEFHJ", 'ٍمخصمغ لاعف سعقثمغ', 'sLOWLY BUT SURELY');

    foreach (\$examples as \$example) {
        \$fix = \$Arabic->fixKeyboardLang(\$example);

        echo '<font color="red">' . \$example . '</font> => ';
        echo '<font color="blue">' . \$fix . '</font><br />';
    }
?>
END;

highlight_string($code);
?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_fixKeyboardLang" target="_blank">fixKeyboardLang</a>
</i>
</div>
<footer><i><a href="https://github.com/khaled-alshamaa/ar-php">Ar-PHP</a>, an open-source library for website developers to process Arabic content</i></footer>
</body>
</html>
