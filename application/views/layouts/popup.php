<!DOCTYPE html>
<html lang="<?php echo App()->language; ?>"<?php if(getLanguageRTL(Yii::app()->language)) {echo 'dir="rtl"';}?> >
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <?php
            App()->bootstrap->register();
            App()->getClientScript()->registerPackage('jqueryui');
        ?>
        <link rel="shortcut icon" href="<?php echo Yii::app()->getConfig('publicstyleurl'); ?>favicon.ico" type="image/x-icon" />
        <link rel="icon" href="<?php echo Yii::app()->getConfig('publicstyleurl'); ?>favicon.ico" type="image/x-icon" />
        <?php $this->widget('ext.LimeScript.LimeScript'); ?>
        <?php //$this->widget('ext.LimeDebug.LimeDebug'); ?>
        <title><?php echo $this->pageTitle; ?></title>
    </head>
    <body>
        <div class="wrapper clearfix">
            <div id="content">
                <?php echo $content; ?>
            </div>
        </div>
    </body>

</html>
