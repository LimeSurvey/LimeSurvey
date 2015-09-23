<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <?php 
        ?>
        <link rel="shortcut icon" href="<?php echo App()->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <link rel="icon" href="<?php echo App()->baseUrl; ?>images/favicon.ico" type="image/x-icon" />
        <?php
			App()->bootstrap->register();
		?>
        <title>Limesurvey User Dashboard</title>
    </head>
    <body class="layout-main">
        <?php
            if (true || !App()->user->isGuest) {
                $items = require __DIR__ . '/../menu.php';
                $this->widget('TbNavbar', [
                    'brandUrl' => ['moduleplugin'],
                    'brandLabel' => 'LS User Dashboard',
                    'display' => null,
//                    'fluid' => true,
                    'items' => [
                    [
                        'class' => 'TbNav',
                        'items' => $items[0]
                    ], 
                    [
                        'class' => 'TbNav',
                        'htmlOptions' => [
                            'class' => 'navbar-right'
                        ],
                        'items' => $items[1]
                    ]]
                ]);
            }
		?>
        <div class="container">
            <div id="content">
            <?php echo $content; ?>
            </div>
        </div>
    </body>

</html>
