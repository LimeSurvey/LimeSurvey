<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <?php
			$this->widget('ext.LimeScript.LimeScript');
			App()->bootstrap->register();
            var_dump(App()->theme); die();
		?>
        <link rel="shortcut icon" href="<?php echo App()->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <link rel="icon" href="<?php echo App()->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <title>Limesurvey Administration</title>
    </head>
    <body class="layout-minimal">
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
