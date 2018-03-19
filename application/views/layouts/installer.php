<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="author" content="" />

    <link rel="shortcut icon" href="<?php echo Yii::app()->getConfig('styleurl'); ?>favicon.ico" type="image/x-icon" />
    <link rel="icon" href="<?php echo Yii::app()->getConfig('styleurl');?>favicon.ico" type="image/x-icon" />

        <?php
        App()->getClientScript()->registerPackage('bootstrap');
        App()->getClientScript()->registerPackage('bootstrap-switch');
        App()->getClientScript()->registerPackage('jquery');
        App()->getClientScript()->registerPackage('jqueryui');
        App()->getClientScript()->registerPackage('adminbasics');
        App()->getClientScript()->registerPackage('fontawesome');
        App()->getClientScript()->registerPackage('font-roboto');

        App()->getClientScript()->registerCssFile( Yii::app()->getConfig('styleurl') . 'Sea_Green/css/lime-admin-colors.css');
        App()->getClientScript()->registerCssFile( Yii::app()->getConfig('styleurl') . 'Sea_Green/css/lime-admin-common.css');

        App()->getClientScript()->registerCssFile(App()->baseUrl . '/installer/css/main.css');
        App()->getClientScript()->registerCssFile(App()->baseUrl . '/installer/css/fonts.css');

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
    <link rel="icon" href="<?php echo Yii::app()->baseUrl; ?>/images/favicon.ico" />
    <title><?php eT("LimeSurvey installer"); ?></title>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pagetitle"><?php eT("LimeSurvey installer"); ?></h1>
            </div>
        </div>
        <?php echo $content; ?>

        <div class="row" style="margin-top: 30px;">
            <div class="col-xs-12" style="text-align: center;">
                <img src="<?php echo Yii::app()->baseUrl; ?>/installer/images/poweredby.png" alt="Powered by LimeSurvey"/>
            </div>
        </div>
    </div>

</body>
</html>
