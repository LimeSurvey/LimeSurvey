<?php
    error_reporting(E_STRICT);
    $time_start = microtime(true);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>Arabic Query Class</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/style.css" media="all" />
</head>

<body>

<div class="Paragraph">
<h2>Arabic SQL Query:</h2>
<p align="justified">Build WHERE condition for SQL statement using MySQL REGEXP and Arabic lexical rules.</p>

<p align="justified">With the exception of the Qur'an and pedagogical texts, Arabic is generally written without vowels or 
other graphic symbols that indicate how a word is pronounced. The reader is expected to fill these in from context. Some of 
the graphic symbols include sukuun, which is placed over a consonant to indicate that it is not followed by a vowel; shadda, 
written over a consonant to indicate it is doubled; and hamza, the sign of the glottal stop, which can be written above or 
below (alif) at the beginning of a word, or on (alif), (waaw), (yaa'), or by itself on the line elsewhere. Also, common 
spelling differences regularly appear, including the use of (haa') for (taa' marbuuta) and (alif maqsuura) for (yaa'). These 
features of written Arabic, which are also seen in Hebrew as well as other languages written with Arabic script (such as 
Farsi, Pashto, and Urdu), make analyzing and searching texts quite challenging. In addition, Arabic morphology and grammar 
are quite rich and present some unique issues for information retrieval applications.</p>

<p align="justified">There are essentially three ways to search an Arabic text with Arabic queries: literal, stem-based or root-based.</p>

<p align="justified">A literal search, the simplest search and retrieval method, matches documents based on the search terms 
exactly as the user entered them. The advantage of this technique is that the documents returned will without a doubt contain 
the exact term for which the user is looking. But this advantage is also the biggest disadvantage: many, if not most, of the 
documents containing the terms in different forms will be missed. Given the many ambiguities of written Arabic, the success 
rate of this method is quite low. For example, if the user searches for (kitaab, book), he or she will not find documents 
that only contain (al-kitaabu, the book).</p>

<p align="justified">Stem-based searching, a more complicated method, requires some normalization of the original texts and 
the queries. This is done by removing the vowel signs, unifying the hamza forms and removing or standardizing the other signs. 
Additionally, grammatical affixes and other constructions which attach directly to words, such as conjunctions, prepositions, 
and the definite article, should be identified and removed. Finally, regular and irregular plural forms need to be identified 
and reduced to their singular forms. Performing this type of stemming leads to more successful searches, but can be problematic 
due to over-generation or incorrect generation of stems.</p>

<p align="justified">A third method for searching Arabic texts is to index and search for the root forms of each word. Since 
most verbs and nouns in Arabic are derived from triliteral (or, rarely, quadriliteral) roots, identifying the underlying root 
of each word theoretically retrieves most of the documents containing a given search term regardless of form. However, there 
are some significant challenges with this approach. Determining the root for a given word is extremely difficult, since it 
requires a detailed morphological, syntactic and semantic analysis of the text to fully disambiguate the root forms. The issue 
is complicated further by the fact that not all words are derived from roots. For example, loan words (words borrowed from 
another language) are not based on root forms, although there are even exceptions to this rule. For example, some loans that 
have a structure similar to triliteral roots, such as the English word film, are handled grammatically as if they were root-based, 
adding to the complexity of this type of search. Finally, the root can serve as the foundation for a wide variety of words with 
related meanings. The root (k-t-b) is used for many words related to writing, including (kataba, to write), (kitaab, book), 
(maktab, office), and (kaatib, author). But the same root is also used for regiment/battalion, (katiiba). As a result, searching 
based on root forms results in very high recall, but precision is usually quite low.</p>

<p align="justified">While search and retrieval of Arabic text will never be an easy task, relying on linguistic analysis tools 
and methods can help make the process more successful. Ultimately, the search method you choose should depend on how critical it 
is to retrieve every conceivable instance of a word or phrase and the resources you have to process search returns in order to 
determine their true relevance.</p>

<p align="justified"><i>Reference: Volume 13 Issue 7 of MultiLingual Computing & Technology published by MultiLingual Computing, 
Inc., 319 North First Ave., Sandpoint, Idaho, USA, 208-263-8178, Fax: 208-263-6310.</i></p>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-1">
<a href="#example-1" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 1:</h2>
<?php
    /*
      // Autoload files using Composer autoload
      require_once __DIR__ . '/../vendor/autoload.php';
    */

    require '../src/Arabic.php';
    $Arabic = new \ArPHP\I18N\Arabic();
        
    echo 'نتائج البحث عن <b>فلسطينيون</b>:<br />';
    echo $Arabic->arQueryAllForms('فلسطينيون');
    
    $keyword = 'فلسطينيون';
    $keyword = str_replace('\"', '"', $keyword);

    $Arabic->setQueryStrFields('field');
    $Arabic->setQueryMode(0); // 0 for any word, 1 for all words

    $strCondition = $Arabic->arQueryWhereCondition($keyword);
    $strOrderBy   = $Arabic->arQueryOrderBy($keyword);

    $StrSQL = "SELECT `field` FROM `table` WHERE $strCondition ORDER BY $strOrderBy";
    
    echo '<hr />صيغة استعلام قاعدة البيانات <span dir="ltr">(SQL Query Statement)</span><br />';
    echo '<pre dir="ltr" style="background-color: #e0e0e0; padding: 5px">' . $StrSQL . '</pre>';
?>
</div><br />
<div class="Paragraph">
<h2 dir="ltr">Example Code 1:</h2>
<?php
$code = <<< END
<?php
    \$Arabic = new \\ArPHP\\I18N\\Arabic();
        
    echo 'نتائج البحث عن <b>فلسطينيون</b>:<br />';
    echo \$Arabic->arQueryAllForms('فلسطينيون');
    
    \$keyword = 'فلسطينيون';
    \$keyword = str_replace('\\"', '"', \$keyword);

    \$Arabic->setQueryStrFields('field');
    \$Arabic->setQueryMode(0);  // 0 for any word, 1 for all words

    \$strCondition = \$Arabic->arQueryWhereCondition(\$keyword);
    \$strOrderBy   = \$Arabic->arQueryOrderBy(\$keyword);

    \$StrSQL = "SELECT `field` FROM `table` WHERE \$strCondition ORDER BY \$strOrderBy";
    
    echo '<hr />صيغة استعلام قاعدة البيانات <span dir="ltr">(SQL Query Statement)</span><br />';
    echo '<pre dir="ltr" style="background-color: #e0e0e0; padding: 5px">' . \$StrSQL . '</pre>';
END;

highlight_string($code);

?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_arQueryAllForms" target="_blank">arQueryAllForms</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setQueryStrFields" target="_blank">setQueryStrFields</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_setQueryMode" target="_blank">setQueryMode</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_arQueryWhereCondition" target="_blank">arQueryWhereCondition</a>,
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_arQueryOrderBy" target="_blank">arQueryOrderBy</a>
</i>
</div>
<footer><i><a href="https://github.com/khaled-alshamaa/ar-php">Ar-PHP</a>, an open-source library for website developers to process Arabic content</i></footer>
</body>
</html>
