<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="author" content=""/>

    <link rel="shortcut icon" href="<?php echo Yii::app()->getConfig('styleurl'); ?>favicon.ico" type="image/x-icon"/>
    <link rel="icon" href="<?php echo Yii::app()->getConfig('styleurl'); ?>favicon.ico" type="image/x-icon"/>
    <?php
    $script = "$(function() {
        $('.on').animate({
                    color: '#0B55C4'
                }, 1000 );

        $('.demo').find('a:first').button().end().
            find('a:eq(1)').button().end().
            find('a:eq(2)').button();
        });";
    App()->getClientScript()->registerScript('installer', $script);
    ?>
    <link rel="icon" href="<?php echo Yii::app()->baseUrl; ?>/images/favicon.ico"/>
    <title><?php eT("GititSurvey installer"); ?></title>
</head>

<body>
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="pagetitle"><?php eT("GititSurvey installer"); ?></h1>
        </div>
    </div>
    <?php echo $content; ?>

    <div class="row m-3 mt-5">
        <div class="col-12" style="text-align: center;">
            <img src="<?php echo Yii::app()->baseUrl; ?>/installer/images/poweredby.png" alt="Powered by LimeSurvey"/>
        </div>
    </div>
</div>

</body>
</html>
