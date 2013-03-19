<?php 
    /* @var $this MenuWidget */
    
    App()->getClientScript()->registerCssFile(App()->getAssetManager()->publish(Yii::getPathOfAlias('ext.Menu.assets'). '/nav.css'));
    App()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(Yii::getPathOfAlias('ext.Menu.assets'). '/nav.js'));
?>
<div class="menubar">
    <?php 
        if (isset($menu['title']))
        {
            echo '<div class="menubar-title ui-widget-header">';
            echo $menu['title'];
            echo '</div>';
        }
        if (isset($menu['items']))
        {
            echo '<nav class="menubar">';
            echo $this->renderMenu($menu);
            echo '</nav>';
        }
    ?>
</div>
<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
