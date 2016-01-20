<!DOCTYPE html>
<html lang="<?php echo $adminlang; ?>"<?php echo $languageRTL;?>>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <?php
        App()->getClientScript()->registerPackage('jqueryui');
        App()->getClientScript()->registerPackage('jquery-cookie');
        App()->getClientScript()->registerPackage('jquery-superfish');
        App()->getClientScript()->registerPackage('qTip2');
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "jquery-ui/jquery-ui.css" );
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "superfish.css" );

        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'jquery.multiselect.css');
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'jquery.multiselect.filter.css');
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') .  "displayParticipants.css");
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('styleurl') . "adminstyle.css" ); // Global style used by all template

        /**
         * Load adminstyle
         * If the template set in the database doesn't exist, it loads greengreen adminstyle (from LSYii_Controller::__construct, calling $this->loadHelper('globalsettings'); wich launch injectglobalsettings)
         * If debug mode is off, it uses asset Manager to do it, else, it just register the css file
         */
        if(!YII_DEBUG)
        {
         App()->getClientScript()->registerCssFile( App()->getAssetManager()->publish( dirname(Yii::app()->request->scriptFile).'/styles/'.Yii::app()->getConfig('admintheme').'/' . "adminstyle.css") );
        }
        else
        {
         App()->getClientScript()->registerCssFile(Yii::app()->getConfig('styleurl').Yii::app()->getConfig('admintheme')."/adminstyle.css" );
        }

        if (getLanguageRTL($_SESSION['adminlang']))
        {
            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "adminstyle-rtl.css" );
        }
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "printablestyle.css", 'print');
    ?>

    <?php echo $datepickerlang;?>
    <title><?php echo $sitename;?></title>
    <link rel="shortcut icon" href="<?php echo $baseurl;?>styles/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="<?php echo $baseurl;?>styles/favicon.ico" type="image/x-icon" />
    <?php echo $firebug ?>
    <?php $this->widget('ext.LimeScript.LimeScript'); ?>
    <?php $this->widget('ext.LimeDebug.LimeDebug'); ?>
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
