<!DOCTYPE html>
<html lang="<?php echo $adminlang; ?>"<?php echo $languageRTL;?>>
<head>
    <?php 
        echo $meta;
        
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts') . "jquery/jquery.ui.touch-punch.min.js");
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts') . "jquery/jquery.notify.js");
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.qtip.js');
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "admin_core.js");
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "jquery-ui/jquery-ui.css" );
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "printablestyle.css", 'print');
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "adminstyle.css" );
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('styleurl') . "adminstyle.css" );
        
        if ($bIsRTL)
        {
            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('styleurl') . "adminstyle-rtl.css" );
        }

        foreach ($css_admin_includes as $cssinclude)
        {
            App()->getClientScript()->registerCssFile($cssinclude);
        }
   
    ?>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    
    <?php echo $datepickerlang;?>
    <title><?php echo $sitename;?></title>
    <?php

        
            ?>
    <link rel="shortcut icon" href="<?php echo $baseurl;?>styles/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="<?php echo $baseurl;?>styles/favicon.ico" type="image/x-icon" />
    <?php echo $firebug ?>
    <?php $this->widget('ext.LimeScript.LimeScript'); ?>
</head>
<body>
<?php if(isset($formatdata)) { ?>
    <script type='text/javascript'>
        var userdateformat='<?php echo $formatdata['jsdate']; ?>';
        var userlanguage='<?php echo $adminlang; ?>';
    </script>
    <?php } ?>
<div class='wrapper'>
    <?php $this->widget('ext.FlashMessage.FlashMessage'); ?>
    <div class='maintitle'><?php echo $sitename; ?></div>
