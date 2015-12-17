<!DOCTYPE html>
<html lang="<?php echo $adminlang; ?>"<?php echo $languageRTL;?>>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <?php 
        App()->getClientScript()->registerPackage('jqueryui');

        App()->getClientScript()->registerPackage('qTip2');
        App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/' . "jquery-ui/jquery-ui.css" );
        App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/' . "superfish.css" );
        App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles-public/' . 'jquery.multiselect.css');
        App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles-public/' . 'jquery.multiselect.filter.css');
        App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/' .  "displayParticipants.css");
        App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/' . "adminstyle.css" );
        App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/' . "adminstyle.css" );
        if (\ls\helpers\SurveyTranslator::getLanguageRTL($_SESSION['adminlang']))
        {        
            App()->getClientScript()->registerCssFile(App()->getConfig('adminstyleurl') . "adminstyle-rtl.css" );
        }
        App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/' . "printablestyle.css", 'print');
    ?>
    <?php echo $datepickerlang;?>
    <title><?php echo $sitename;?></title>
    <link rel="shortcut icon" href="<?php echo $baseurl;?>images/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="<?php echo $baseurl;?>images/favicon.ico" type="image/x-icon" />
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
    <?php $this->widget('TbAlert'); ?>
    <div class='maintitle'><?php echo $sitename; ?></div>
