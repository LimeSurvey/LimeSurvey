<?php
/**
 * Header of the application
 * Called from renderWrappedTemplate
 */
?>
<!DOCTYPE html>
<html lang="<?php echo str_replace(['-informal','-easy'], ['',''], htmlspecialchars((string) $adminlang)); ?>"<?php echo $languageRTL;?> >
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- Assets load -->

    <!--
        Notice to developers :


        If you turn debug mode on, the asset manager will be off.
        If you turn degug mode off, the asset manager will be on.

        Without the asset manager, the CSS/JS files are published from their real url (eg: http://yourlimesurvey.url/scripts/admin/admin_core.js)
        So, once a browser loaded once those files, it cache it, and don't load it anymore.
        Then, if you change some css/js files, final user must clean its browser cache to get the new version of the cache.
        This was the old LS behaviour, if debug mode is on, you'll have this very behaviour

        The asset manager resolve the browser cache problem. It copy the css/js files to a tmp directory before publishing it:
        http://yourlimesurvey.url/tmp/assets/e929b9d4/admin_core.js

        For admin GUI, the asset manager works on a base of a "file by file" : each single css/js file is published as a single asset.
        So if you touch any css/js file published via the asset (updating its date of modification), the asset manager will AUTOMATICALLY create a new tmp directory:
        http://yourlimesurvey.url/tmp/assets/eb139b88/admin_core.js

        Then, the browser will automatically reload the file, and the final user don't need to refresh its cache.
        You should never have to delete the tmp/assets directory. You can do it to free some space on your server, but that all.

        notice: the css/js files from third party extension use the package system. It means that the asset manager will publish them on the base of the directory logic.
        So, if you update any css/js file from a third party extension, make sure that the modification date of the root directory is updated.
    -->
    <?php if(!YII_DEBUG ||  Yii::app()->getConfig('use_asset_manager')): ?>
        <!-- Debug mode is off, so the asset manager will be used-->
    <?php else: ?>
        <!-- Debug mode is on, so the asset manager will not be used -->
    <?php endif; ?>

    <?php echo $datepickerlang;?>
    <title><?php echo $sitename;?></title>
    <link rel="shortcut icon" href="<?php echo Yii::app()->getConfig('styleurl');?>favicon.ico" type="image/x-icon" />
    <link rel="icon" href="<?php echo Yii::app()->getConfig('styleurl');?>favicon.ico" type="image/x-icon" />
    <?php echo $firebug ?>
    <?php $this->widget('ext.LimeScript.LimeScript'); ?>
    <?php //$this->widget('ext.LimeDebug.LimeDebug'); ?>
</head>
<body>
    <div id="beginScripts">
        <###begin###>
    </div>
<!-- Loading wrapper -->
<div id='ls-loading'>
    <span id='ls-loading-spinner' class='ri-loader-2-fill remix-spin remix-4x'></span>
    <span class='visually-hidden'><?php eT('Loading...'); ?></span>
</div>

<?php $this->widget('ext.FlashMessage.FlashMessage'); ?>

<?php App()->getClientScript()->registerScript("HeaderVariables",
'var frameSrc = "/login";
'.(isset($formatdata) ? 
    ' var userdateformat="'.$formatdata['jsdate'].'";'
   .'var userlanguage="'.$adminlang.'";'
   : '' ), LSYii_ClientScript::POS_HEAD); ?>
