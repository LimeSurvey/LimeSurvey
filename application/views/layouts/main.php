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
            $cs->registerCssFile(App()->theme->baseUrl . '/css/style.css');
            $cs->registerScriptFile(App()->params['bower-asset'] . '/jquery-ui/jquery-ui.min.js');
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
            <?php $this->widget('TbAlert'); ?>
            <div id="content">
            <?php echo $content; ?>
            </div>
            <div id="ajaxprogress" title="Ajax request in progress" style="text-align: center;">
                <img src="<?php echo Yii::app()->getConfig('adminstyleurl');?>/images/ajax-loader.gif"/>
            </div>
            <?php $this->renderPartial('/global/footer'); ?>
        </div>
        
    </body>

</html>