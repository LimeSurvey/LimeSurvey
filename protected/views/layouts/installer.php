<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8"/>
	<meta name="author" content="" />

    <link rel="shortcut icon" href="<?php echo Yii::app()->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
    <?php
        echo \CHtml::linkTag("icon", "image/x-icon", App()->publicUrl . "/images/favicon.ico");
        App()->getClientScript()->registerPackage('jqueryui');
        App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/installer.css');

        App()->bootstrap->register();
    ?>
	<title><?=gT("LimeSurvey installer"); ?></title>
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
                <?= \CHtml::image(App()->publicUrl . '/images/poweredby.png', gT("Powered by LimeSurvey")); ?>
            </div>
        </div>
    </div>

</body>
</html>