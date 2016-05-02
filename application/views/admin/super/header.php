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

    <!--
        Notice to developpers :


        If you turn debgug mode on, the asset manager will be off.
        If you turn degbug mode off, the asset manager will be on.

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

    <?php if(!YII_DEBUG): ?>
        <!-- Debug mode is off, so the asset manager will be used-->
    <?php else: ?>
        <!-- Debug mode is on, so the asset manager will not be used -->
    <?php endif; ?>
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
