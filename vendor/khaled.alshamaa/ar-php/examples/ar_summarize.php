<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>Arabic Auto Summarize Class</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/style.css" media="all" />

<style type="text/css">
.summary { background-color:#eeee80; }
</style>

</head>

<body>

<div class="Paragraph">
<h2>Arabic Auto Summarize:</h2>
<p align="justified">Determines key points by analyzing Arabic document and assigning a score to each sentence. 
Sentences that contain words used frequently in the document are given a higher score. You can then choose a 
percentage of the highest-scoring sentences to display in the summary. It works best on well-structured 
documents such as reports, articles, and scientific papers.</p>

<p align="justified">It cuts wordy copy to the bone by counting words and ranking sentences. First, it 
identifies the most common words in the document and assigns a "score" to each word, the more frequently 
a word is used, the higher the score. Then, it "averages" each sentence by adding the scores of its words 
and dividing the sum by the number of words in the sentence, the higher the average, the higher the rank 
of the sentence. It can summarize texts to specific number of sentences or percentage of the original copy.</p>

<p align="justified">We use statistical approach, with some attention apparently paid to:
<ul>
<li>Location: leading sentences of paragraph, title, introduction, and conclusion.</li>
<li>Fixed phrases: in-text summaries.</li>
<li>Frequencies of words, phrases, proper names.</li>
<li>Contextual material: query, title, headline, initial paragraph.</li>
</ul>
</p>

<p align="justified">The motivation for this class is the range of applications for key phrases:
<ul>
<li>Mini-summary: Automatic key phrase extraction can provide a quick mini-summary for a long document. 
For example, it could be a feature in a web sites; just click the summarize button when browsing a long web page.</li>
<li>Highlights: It can highlight key phrases in a long document, to facilitate skimming the document.</li>
<li>Author Assistance: Automatic key phrase extraction can help an author or editor who wants to supply a list 
of key phrases for a document. For example, the administrator of a web site might want to have a key phrase list 
at the top of each web page. The automatically extracted phrases can be a starting point for further manual 
refinement by the author or editor.</li>
<li>Text Compression: On a device with limited display capacity or limited bandwidth, key phrases can 
be a substitute for the full text. For example, a web page could be reduced for display on a Twitter post.</li>
</ul>
</p>
</div><br />

<?php
$title    = 'أضخم تجربة علمية لدراسة بنية المادة المعتمة بمصادم الهدرونات الكبير';
$contents = <<<END
قال علماء في مركز أبحاث الفيزياء التابع للمنظمة الأوروبية للابحاث النووية يوم الجمعة
أنهم حققوا تصادما بين جسيمات بكثافة قياسية في إنجاز مهم في برنامجهم لكشف أسرار الكون. 
وجاء التطور في الساعات الأولى بعد تغذية مصادم الهدرونات الكبير بحزمة أشعة بها 
جسيمات أكثر بحوالي ستة في المئة لكل وحدة بالمقارنة مع المستوى القياسي السابق 
الذي سجله مصادم تيفاترون التابع لمختبر فرميلاب الأمريكي العام الماضي. 
وكل تصادم في النفق الدائري لمصادم الهدرونات البالغ طوله 27 كيلومترا تحت الأرض 
بسرعة أقل من سرعة الضوء يحدث محاكاة للانفجار العظيم الذي يفسر به علماء نشوء الكون 
قبل 13.7 مليار سنة. وكلما زادت "كثافة الحزمة" أو ارتفع عدد الجسيمات فيها زاد 
عدد التصادمات التي تحدث وزادت أيضا المادة التي يكون على العلماء تحليلها. 
ويجري فعليا انتاج ملايين كثيرة من هذه "الانفجارات العظيمة المصغرة" يوميا. 
وقال رولف هوير المدير العام للمنظمة الاوروبية للأبحاث النووية ومقرها على الحدود 
الفرنسية السويسرية قرب جنيف أن "كثافة الحزمة هي الأساس لنجاح مصادم الهدرونات الكبير 
ولذا فهذه خطوة مهمة جدا"، وأضاف "الكثافة الأعلى تعني مزيدا من البيانات، ومزيد 
من البيانات يعني إمكانية أكبر للكشف." وقال سيرجيو برتولوتشي مدير الأبحاث في المنظمة 
"يوجد إحساس ملموس بأننا على أعتاب كشف جديد". وفي حين زاد الفيزيائيون والمهندسون 
في المنظمة كثافة حزم الأشعة على مدى الأسبوع المنصرم قال جيمس جيليه المتحدث باسم المنظمة 
أنهم جمعوا معلومات تزيد على ما جمعوه على مدى تسعة أشهر من عمل مصادم الهدرونات في 2010. 
وتخزن تلك المعلومات على آلاف من أقراص الكمبيوتر. ويمثل المصادم البالغة تكلفته 
عشرة مليارات دولار أكبر تجربة علمية منفردة في العالم وقد بدأ تشغيله في نهاية 
مارس آذار 2010. وبعد الإغلاق الدائم لمصادم تيفاترون في الخريف القادم سيصبح 
مصادم الهدرونات المصادم الكبير الوحيد الموجود في العالم. ومن بين أهداف 
مصادم الهدرونات الكبير معرفة ما إذا كان الجسيم البسيط المعروف بإسم جسيم هيجز 
أو بوزون هيجز موجود فعليا. ويحمل الجسيم إسم العالم البريطاني بيتر هيجز 
الذي كان أول من افترض وجوده كعامل أعطي الكتلة للجسيمات بعد الإنفجار العظيم. 
ومن خلال متابعة التصادمات على أجهزة الكمبيوتر في المنظمة الأوروبية للأبحاث النووية 
وفي معامل في أنحاء العالم مرتبطة بها يأمل العلماء أيضا أن يجدوا دليلا قويا على 
وجود المادة المعتمة التي يعتقد أنها تشكل حوالي ربع الكون المعروف وربما الطاقة المعتمة 
التي يعتقد أنها تمثل حوالي 70 في المئة من الكون. ويقول علماء الفلك أن تجارب 
المنظمة الأوروبية للأبحاث النووية قد تلقي الضوء أيضا على نظريات جديدة بازغة 
تشير إلى أن الكون المعروف هو مجرد جزء من نظام لأكوان كثيرة غير مرئية لبعضها البعض 
ولا توجد وسائل للتواصل بينها. ويأملون أيضا أن يقدم مصادم الهدرونات الكبير 
الذي سيبقى يعمل على مدى عقد بعد توقف فني لمدة عام في 2013 بعض الدعم 
لدلائل يتعقبها باحثون آخرون على أن الكون المعروف سبقه كون آخر قبل الانفجار العظيم. 
وبعد التوقف عام 2013 يهدف علماء المنظمة الأوروبية للأبحاث النووية إلى زيادة 
الطاقة الكلية لكل تصادم بين الجسيمات من الحد الاقصى الحالي البالغ 7 تيرا الكترون فولت 
إلى 14 تيرا الكترون فولت. وسيزيد ذلك أيضا من فرصة التوصل لاكتشافات جديدة فيما تصفه 
المنظمة بأنه "الفيزياء الجديدة" بما يدفع المعرفة لتجاوز ما يسمى النموذج المعياري 
المعتمد على نظريات العالم البرت اينشتاين في اوائل القرن العشرين.
END;

$contents = str_replace("\n", ' ', $contents);
?>

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

    $rate        = 25;
    $summary     = $Arabic->arSummary($contents, '', $rate, 1, 1);
    $highlighted = $Arabic->arSummary($contents, '', $rate, 1, 2);
    $keywords    = $Arabic->arSummaryKeywords($contents, 5);

    echo "<h3>$title:</h3>";
    echo "<h4>الكلمات المفتاحية</h4>$keywords";
    echo "<h4>الملخص</h4>$summary";
    echo "<h4>النص الكامل</h4>$highlighted";
?>
</div><br />
<div class="Paragraph">
<h2>Example Code 1:</h2>
<?php
$code = <<< ENDALL
<?php
    \$Arabic = new \\ArPHP\\I18N\\Arabic();
    
    \$rate        = 25;
    \$summary     = \$Arabic->arSummary(\$contents, '', \$rate, 1, 1);
    \$highlighted = \$Arabic->arSummary(\$contents, '', \$rate, 1, 2);
    \$keywords    = \$Arabic->arSummaryKeywords(\$contents, 5);

    echo "<h3>\$title:</h3>";
    echo "<h4>الكلمات المفتاحية</h4>\$keywords";
    echo "<h4>الملخص</h4>\$summary";
    echo "<h4>النص الكامل</h4>\$highlighted";
ENDALL;

highlight_string($code);
?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_arSummary" target="_blank">arSummary</a>
</i>
</div><br />

<div class="Paragraph" dir="rtl">
<h2 dir="ltr" id="example-2">
<a href="#example-2" class="anchor"><img src="./images/link_icon.png" width="16" border="0"></a>Example Output 2:</h2>
<?php
    $summary     = $Arabic->arSummary($contents, 'هيجنز', $rate, 1, 1);
    $highlighted = $Arabic->arSummary($contents, 'هيجنز', $rate, 1, 2);
    
    echo "<h4>الملخص لو كنت تبحث عن كلمة هيجنز</h4>$summary";
    echo "<h4>النص الكامل</h4>$highlighted";
?>
</div><br />
<div class="Paragraph">
<h2>Example Code 2:</h2>
<?php
$code = <<< ENDALL
<?php
    \$Arabic = new \\ArPHP\\I18N\\Arabic();
    
    \$rate = 25;
    \$summary = \$Arabic->arSummary(\$contents, 'هيجنز', \$rate, 1, 1);
    \$highlighted = \$Arabic->arSummary(\$contents, 'هيجنز', \$rate, 1, 2);

    echo "<h4>الملخص لو كنت تبحث عن كلمة هيجنز</h4>\$summary";
    echo "<h4>النص الكامل</h4>\$highlighted";
ENDALL;

highlight_string($code);
?>
<hr/><i>Related Documentation: 
<a href="https://khaled-alshamaa.github.io/ar-php/classes/ArPHP-I18N-Arabic.html#method_arSummary" target="_blank">arSummary</a>
</i>
</div>

<footer><i><a href="https://github.com/khaled-alshamaa/ar-php">Ar-PHP</a>, an open-source library for website developers to process Arabic content</i></footer>
</body>
</html>
