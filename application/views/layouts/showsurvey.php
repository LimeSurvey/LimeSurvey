<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <?php
			App()->bootstrap->register();
        $cs = App()->clientScript;
        $cs->registerCssFile(App()->theme->baseUrl . '/css/style.css');
        $cs->registerCoreScript('ExpressionManager');
        $cs->registerScriptFile(App()->createUrl('surveys/script', ['id' => App()->surveySessionManager->current->surveyId]));
		?>
        <link rel="shortcut icon" href="<?php echo App()->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <link rel="icon" href="<?php echo App()->baseUrl; ?>images/favicon.ico" type="image/x-icon" />

        <title>Limesurvey Administration</title>
    </head>
    <?php
        $bodyClasses = "layout-minimal";
        if (App()->maintenanceMode) {
            $bodyClasses .= " maintenance";
        }
    ?>
    <body class="<?=$bodyClasses; ?>">
        <div class="alerts">
            <?php $this->widget(TbAlert::class); ?>
        </div>
        <div class="container">
            
            <div id="content">
            <?php echo $content; ?>
            </div>
        </div>
    </body>

</html>
