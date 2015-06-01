<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link rel="shortcut icon" href="<?php echo App()->theme->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <link rel="icon" href="<?php echo App()->theme->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <?php
			$this->widget('ext.LimeScript.LimeScript');
			$this->widget('ext.LimeDebug.LimeDebug');
			App()->bootstrap->register();
            $cs = App()->clientScript;
            $cs->registerCssFile(App()->theme->baseUrl . '/css/style.css');

            $cs->registerCssFile(App()->params['bower-asset'] . '/select2-bootstrap-css/select2-bootstrap.min.css');
            $cs->registerScriptFile('/scripts/ajax.js');

        $cs->registerScriptFile(App()->params['bower-asset'] . '/remarkable-bootstrap-notify/bootstrap-notify.min.js');
        $cs->registerScriptFile(App()->params['bower-asset'] . '/jquery-ui/jquery-ui.min.js');
        $cs->registerScriptFile(App()->params['bower-asset'] . '/tinymce/tinymce.min.js');
        $cs->registerScriptFile('/scripts/htmleditor.js');
        $cs->registerScript('links', "$('body').on('click', '.disabled a', function (e) { e.preventDefault(); });");
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
                        $this->renderPartial('/global/surveyNavigator', ['survey' => $this->models['survey']]);
                    ?>
                </div>
                <div id="content" class="col-lg-10 col-md-9 col-sm-12">
                <?php echo $content; ?>
                </div>
            </div>
            <div id="ajaxprogress" title="Ajax request in progress" style="text-align: center;">
                <img src="<?php echo Yii::app()->getConfig('adminstyleurl');?>/images/ajax-loader.gif"/>
            </div>
            <?php $this->renderPartial('/global/footer'); ?>
        </div>
        
    </body>

</html>