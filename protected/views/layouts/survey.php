<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link rel="shortcut icon" href="<?php echo App()->theme->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <link rel="icon" href="<?php echo App()->theme->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <?php
            App()->bootstrap->register();

            $this->widget(LimeScript::class);
            $cs = App()->clientScript;
            /** @var CAssetManager $am */
            $am = App()->assetManager;
            $public = App()->publicUrl;
            $cs->registerCssFile(App()->theme->baseUrl . '/css/style.css');

            $cs->registerCssFile($am->publish(Yii::getPathOfAlias('bower.select2-bootstrap-css') . '/select2-bootstrap.min.css'));
            $cs->registerScriptFile("$public/scripts/ajax.js");
            $cs->registerScriptFile("$public/scripts/unobtrusive.js");

            $cs->registerScriptFile($am->publish(Yii::getPathOfAlias('bower.remarkable-bootstrap-notify') . '/bootstrap-notify.min.js'));
            $cs->registerPackage('jqueryui');
            App()->getComponent('yiiwheels')->registerAssetJs('bootstrap-bootbox.min.js');
            $cs->registerScriptFile($am->publish(Yii::getPathOfAlias('bower.tinymce')) . '/tinymce.js');
            $cs->registerScriptFile("$public/scripts/htmleditor.js");

            // Disable disabled links.
            $cs->registerScript('links', "$('body').on('click', '.disabled a', function (e) { console.log('noo'); e.preventDefault(); });");

        ?>
        <title>Limesurvey Administration</title>
    </head>
    <body class="layout-main">
        <?php
        /** @var \ls\controllers\Controller $this */
        if (!App()->user->isGuest) {
            $this->renderPartial('/global/menus');
        }
        ?>
        <div class="container-fluid">
            <div class="row">
                <?php $this->widget(TbAlert::class); ?>
                <div id="survey-navigator" class="col-lg-2 col-md-3 col-sm-12">
                    <?php
                        $this->renderPartial('/global/surveyNavigator', ['survey' => $this->menus['survey']]);
                    ?>
                </div>
                <div id="content" class="col-lg-10 col-md-9 col-sm-12">
                <?php echo $content; ?>
                </div>
            </div>
            <div id="ajaxprogress" title="Ajax request in progress" style="text-align: center;">
                <?=CHtml::image(App()->theme->baseUrl . '/images/ajax-loader.gif'); ?>
            </div>
            <?php $this->renderPartial('/global/footer'); ?>
        </div>
        
    </body>

</html>
