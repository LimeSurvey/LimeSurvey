<?php
/**
 * Header of the application
 * Called from render_wrapped_template
 */
?>
<!DOCTYPE html>
<html lang="<?php echo $adminlang; ?>"<?php echo $languageRTL;?> >
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <!-- Assets load -->
    <?php 
        // jQuery plugins
        App()->getClientScript()->registerPackage('jqueryui');
        App()->getClientScript()->registerPackage('jquery-cookie');
        App()->getClientScript()->registerPackage('qTip2');
    
        // Bootstrap
        App()->bootstrap->register();   
        App()->getClientScript()->registerPackage('lime-bootstrap');

        // Right to Left
        if (getLanguageRTL($_SESSION['adminlang']))
            App()->getClientScript()->registerPackage('adminstyle-rtl');
        
        // Printable
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
	
<?php $this->widget('ext.FlashMessage.FlashMessage'); ?>

<script>
var frameSrc = "/login";
</script>

<?php if(isset($formatdata)):?>
    <script type='text/javascript'>
        var userdateformat='<?php echo $formatdata['jsdate']; ?>';
        var userlanguage='<?php echo $adminlang; ?>';
    </script>
<?php endif; ?>