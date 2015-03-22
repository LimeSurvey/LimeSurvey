<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <?php
			App()->bootstrap->register();
            App()->clientScript->registerCssFile(App()->theme->baseUrl . '/css/style.css');
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
        <div style="position: absolute; top: 5px; right: 5px;">
            <?php echo TbHtml::link('Login', ['users/login']); ?> 
        </div>
        <div class="alerts">
            <?php $this->widget('TbAlert'); ?>
        </div>
        <div class="container">
            
            <div id="content">
            <?php echo $content; ?>
            </div>
        </div>
    </body>

</html>
