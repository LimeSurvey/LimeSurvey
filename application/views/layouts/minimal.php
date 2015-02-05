<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <?php
			$this->widget('ext.LimeScript.LimeScript');
			App()->bootstrap->register();
		?>
        <link rel="shortcut icon" href="<?php echo App()->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <link rel="icon" href="<?php echo App()->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <title>Limesurvey Administration</title>
    </head>
    <body>
        <?php
            if (!App()->user->isGuest) {
                echo CHtml::openTag('nav');
                $this->widget('ext.yii-barmenu.BarMenu', array(
                    'items' => require __DIR__ . '/../menu.php',
                    'iconUrl' => App()->getConfig('adminimageurl')
                ));
                echo CHtml::closeTag('nav');
            }
		?>
        <div class="container">
            <?php $this->widget('ext.FlashMessage.FlashMessage'); ?>
            <div id="content">
            <?php echo $content; ?>
            </div>
        </div>
    </body>

</html>
