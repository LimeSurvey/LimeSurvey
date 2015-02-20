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
            App()->clientScript->registerCssFile(App()->theme->baseUrl . '/css/style.css');
		?>
        <title>Limesurvey Administration</title>
    </head>
    <body class="layout-main">
        <?php
            if (!App()->user->isGuest) {
                $items = require __DIR__ . '/../menu.php';
                $this->widget('TbNavbar', [
                    'brandUrl' => ['surveys/index'],
                    'display' => null,
                    'fluid' => true,
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
                if (isset($this->survey)) {
                    $items = require __DIR__ . '/../surveyMenu.php';
                    $this->widget('TbNavbar', [
                    'brandLabel' => false,
                    'display' => null,
                    'fluid' => true,
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
                if (isset($this->group)) {
                    $items = require __DIR__ . '/../groupMenu.php';
                    $this->widget('TbNavbar', [
                    'brandLabel' => false,
                    'display' => null,
                    'fluid' => true,
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
                if (isset($this->question)) {
                    $items = require __DIR__ . '/../questionMenu.php';
                    $this->widget('TbNavbar', [
                    'brandLabel' => false,
                    'display' => null,
                    'fluid' => true,
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
            <?php $this->renderPartial('/footer'); ?>
        </div>
        
    </body>

</html>