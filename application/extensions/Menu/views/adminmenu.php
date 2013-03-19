<?php 
    /* @var $this MenuWidget */
    
    App()->getClientScript()->registerCssFile(App()->getAssetManager()->publish(Yii::getPathOfAlias('ext.Menu.assets'). '/nav.css'));
    App()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(Yii::getPathOfAlias('ext.Menu.assets'). '/nav.js'));
?>
<nav class="menubar">
    <?php 
        if (isset($menu['title']))
        {
            echo '<div class="menubar-title ui-widget-header">';
            echo $menu['title'];
            echo '</div>';
        }
        if (isset($menu['items']))
        {
            echo $this->renderMenu($menu);
        }
    ?>
</nav>
<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
