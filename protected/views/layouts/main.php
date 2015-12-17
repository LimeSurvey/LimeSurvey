<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link rel="shortcut icon" href="<?php echo App()->theme->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <link rel="icon" href="<?php echo App()->theme->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <?php
			App()->bootstrap->register();
            $cs = App()->clientScript;
            App()->getComponent('yiiwheels')->registerAssetJs('bootstrap-bootbox.min.js');
            $cs->registerPackage('jqueryui');
            $cs->registerPackage('bootstrap-notify');
            $cs->registerScriptFile(App()->getBaseUrl() . Yii::getPathOfAlias('public') . '/scripts/unobtrusive.js');
            $cs->registerCssFile(App()->theme->baseUrl . '/css/style.css');

            // Disable disabled links.
            $cs->registerScript('links', "$('body').on('click', '.disabled a', function (e) { console.log('noo'); e.preventDefault(); });");

        ?>
        <title>Limesurvey Administration</title>
    </head>
    <body class="layout-main">
        <?php
            if (!App()->user->isGuest) {
                $this->renderPartial('/global/menus');
            }
		?>
        <div class="container-fluid">
            <?php $this->widget(TbAlert::class); ?>
            <div id="content">
            <?php echo $content; ?>
            </div>
            <div id="ajaxprogress" title="Ajax request in progress" style="text-align: center;">
                <?=CHtml::image(App()->theme->baseUrl . '/images/ajax-loader.gif'); ?>
            </div>
            <?php $this->renderPartial('/global/footer'); ?>
        </div>
        
    </body>

</html>