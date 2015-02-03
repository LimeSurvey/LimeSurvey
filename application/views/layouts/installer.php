<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8"/>
	<meta name="author" content="" />

    <link rel="shortcut icon" href="<?php echo Yii::app()->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="<?php echo $this->createUrl('/');?>images/favicon.ico" type="image/x-icon" />

        <?php
        App()->getClientScript()->registerPackage('jqueryui');
        App()->getClientScript()->registerCssFile(App()->baseUrl . '/installer/css/main.css');
        //App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . 'grid.css', 'all');

        //App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . 'adminstyle.css', 'all');

        $script = "$(function() {
        $('.demo').find('a:first').button().end().
            find('a:eq(1)').button().end().
            find('a:eq(2)').button();
        });";
        App()->bootstrap->register();
        App()->getClientScript()->registerScript('installer', $script);
    ?>
    <link rel="icon" href="<?php echo Yii::app()->baseUrl; ?>/images/favicon.ico" />
	<title><?php eT("LimeSurvey installer"); ?></title>
</head>

<body id="installer">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1><?php echo gT("LimeSurvey installer") . ' - ' . $this->stepTitle; ?></h1>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4" style="min-height: 250px;">
                <?php $this->renderPartial('/installer/sidebar_view'); ?>
            </div>
            <div class="col-md-8" style="min-height: 250px;">
                <?php echo $content; ?>
            </div>
        </div>
        <div class="row" style="margin-top: 30px;">
            <div class="col-md-12" style="text-align: center;">
                <img src="<?php echo Yii::app()->baseUrl; ?>/installer/images/poweredby.png" alt="Powered by LimeSurvey"/>
            </div>
        </div>
    </div>

</body>
</html>