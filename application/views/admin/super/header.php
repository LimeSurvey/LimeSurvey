<?php
/**
 * Header of the application
 * Called from render_wrapped_template
 */
?>
<!DOCTYPE html>
<html lang="<?php echo str_replace('-informal','',$adminlang); ?>"<?php echo $languageRTL;?> >
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- Assets load -->
    <?php
        // jQuery plugins
        App()->getClientScript()->registerPackage('jqueryui');
        App()->getClientScript()->registerPackage('jquery-cookie');
        App()->getClientScript()->registerPackage('fontawesome');
        // Bootstrap
        App()->bootstrap->register();

        // We want the asset manager to reload the files if they are changed.
        // Using registerPackage only publish the whole directory, and never update it (unless tmp/assets/ directories are deleted).  Command was :   App()->getClientScript()->registerPackage($sAdminthemePackageName);
        // The way to grant the possibility for asset manager to re-publish those files when they are changed is to publish them one by one.
        // In debug mode, we don't use assets.

        if(!YII_DEBUG)
        {
            foreach ($aPackageStyles as $cssfile)
            {
                App()->getClientScript()->registerCssFile( App()->getAssetManager()->publish( dirname(Yii::app()->request->scriptFile).'/styles/'.$sAdmintheme.'/css/' . $cssfile) );
            }

            foreach ($aPackageScripts as $jsfile)
            {
                App()->getClientScript()->registerScriptFile( App()->getAssetManager()->publish( dirname(Yii::app()->request->scriptFile).'/styles/'.$sAdmintheme.'/scripts/' . $jsfile) );
            }
        }
        else
        {
            foreach ($aPackageStyles as $cssfile)
            {
                App()->getClientScript()->registerCssFile( Yii::app()->getBaseUrl(true).'/styles/'.$sAdmintheme.'/css/' . $cssfile );
            }

            foreach ($aPackageScripts as $jsfile)
            {
                App()->getClientScript()->registerScriptFile( Yii::app()->getBaseUrl(true).'/styles/'.$sAdmintheme.'/scripts/' . $jsfile );
            }
        }

        // Right to Left
        if (getLanguageRTL($_SESSION['adminlang']))
        {
            App()->getClientScript()->registerCssFile( App()->getAssetManager()->publish( dirname(Yii::app()->request->scriptFile).'/styles/'.$sAdmintheme.'/css/adminstyle-rtl.css') );
        }


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

<script type='text/javascript'>
var frameSrc = "/login";
    <?php if(isset($formatdata)):?>
    var userdateformat='<?php echo $formatdata['jsdate']; ?>';
    var userlanguage='<?php echo $adminlang; ?>';
    <?php endif; ?>
</script>
