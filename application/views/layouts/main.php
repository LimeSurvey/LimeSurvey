<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <?php 
            App()->getClientScript()->registerPackage('jqueryui');
            App()->getClientScript()->registerPackage('qTip2');
            App()->getClientScript()->registerPackage('jquery-superfish');
            App()->getClientScript()->registerPackage('jquery-cookie');
            App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "admin_core.js");
            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "jquery-ui/jquery-ui.css" );
            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "printablestyle.css", 'print');
            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('styleurl') . "adminstyle.css" );
            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "adminstyle.css" );
            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'jquery.multiselect.css');
            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'jquery.multiselect.filter.css');
            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') .  "displayParticipants.css");

        ?>
        <link rel="shortcut icon" href="<?php echo App()->baseUrl; ?>styles/favicon.ico" type="image/x-icon" />
        <link rel="icon" href="<?php echo App()->baseUrl; ?>styles/favicon.ico" type="image/x-icon" />
        <?php $this->widget('ext.LimeScript.LimeScript'); ?>
        <?php $this->widget('ext.LimeDebug.LimeDebug'); ?>
        <title>Limesurvey Administration</title>
    </head>
    <body>
        <div class="wrapper clearfix">
            <?php $this->widget('ext.FlashMessage.FlashMessage'); ?>
            <?php echo CHtml::tag('div', array('class' => 'maintitle titlebar'), App()->getConfig('sitename')); ?>
            <?php $this->widget('ext.Menu.MenuWidget', $this->navData); ?>
            <div id="content">
            <?php echo $content; ?>
            </div>
            <div id="ajaxprogress" title="Ajax request in progress" style="text-align: center">
                <img src="<?php echo Yii::app()->getConfig('adminstyleurl');?>/images/ajax-loader.gif"/>
            </div>
        </div>
        <?php $this->widget('ext.AdminFooter.AdminFooter'); ?>
    </body>

</html>
