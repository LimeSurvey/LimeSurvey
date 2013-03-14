<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <?php 
            /* @var $cs CClientScript */
            $cs=Yii::app()->getClientScript();
            $cs->registerCoreScript('jquery');
            $cs->registerScriptFile(Yii::app()->getConfig('third_party') . 'jqueryui/js/jquery-ui-1.10.0.custom.js');
            $cs->registerScriptFile(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.ui.touch-punch.min.js');
            $cs->registerScriptFile(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.qtip.js');
            $cs->registerScriptFile(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.notify.js');
            $cs->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'admin_core.js');
            
            
            $cs->registerCssFile(Yii::app()->getConfig('third_party'). "jqueryui/css/smoothness/jquery-ui-1.10.0.custom.css");
            
            $cs->registerCssFile(Yii::app()->getConfig('adminstyleurl'). "/jquery-ui/jquery-ui.css");
            $cs->registerCssFile(Yii::app()->getConfig('adminstyleurl'). "printablestyle.css", "print");
            $cs->registerCssFile(Yii::app()->getConfig('styleurl'). "adminstyle.css");
            $cs->registerCssFile(Yii::app()->getConfig('adminstyleurl'). "adminstyle.css");
            $cs->registerCssFile(Yii::app()->getConfig('third_party'). "jqueryui/css/smoothness/jquery-ui-1.10.0.custom.css");
            /*
             * if ($bIsRTL){?>
            <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getConfig('adminstyleurl');?>adminstyle-rtl.css" /><?php
            }*/
        
        ?>
        
        
        
        <link rel="shortcut icon" href="<?php echo App()->baseUrl; ?>styles/favicon.ico" type="image/x-icon" />
        <link rel="icon" href="<?php echo App()->baseUrl; ?>styles/favicon.ico" type="image/x-icon" />
        <?php $this->widget('ext.LimeScript.LimeScript'); ?>
        <title>Limesurvey Administration</title>
        
    </head>
    <body>
        <div class="wrapper">
            <?php $this->widget('ext.FlashMessage.FlashMessage'); ?>
            <?php $this->widget('ext.Menu.MenuWidget', $this->navData); ?>
            <div id="content">
            <?php echo $content; ?>
            </div>
            <div id="ajaxprogress" title="Ajax request in progress" style="text-align: center">
                <img src="<?php echo Yii::app()->getConfig('adminstyleurl');?>/images/ajax-loader.gif"/>
            </div>
        </div>
    </body>

</html>
