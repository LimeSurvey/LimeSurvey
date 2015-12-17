<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <?php 
            App()->getClientScript()->registerPackage('jqueryui');
            App()->getClientScript()->registerPackage('qTip2');
             // Needed by admin_core : to be fixed ?
            App()->getClientScript()->registerScriptFile(App()->publicUrl . '/scripts/admin/' . "admin_core.js");
            App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/' . "jquery-ui/jquery-ui.css" );
            App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/' . "printablestyle.css", 'print');
            App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/' . "adminstyle.css" );
            App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/' . "adminstyle.css" );

        ?>
        <link rel="shortcut icon" href="<?php echo App()->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <link rel="icon" href="<?php echo App()->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <?php $this->widget('ext.LimeScript.LimeScript'); ?>
        <title><?php echo $this->pageTitle; ?></title>
    </head>
    <body>
        <div class="wrapper clearfix">
            <?php $this->widget('TbAlert'); ?>
            <div id="content">
            <?php echo $content; ?>
            </div>
            <div id="ajaxprogress" title="Ajax request in progress" style="text-align: center">
                <img src="<?php echo App()->publicUrl . '/styles/gringegreen/';?>/images/ajax-loader.gif"/>
            </div>
        </div>
    </body>

</html>
