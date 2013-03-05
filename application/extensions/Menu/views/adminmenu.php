<?php 
    /* @var $this MenuWidget */
    
    App()->getClientScript()->registerCssFile(App()->getAssetManager()->publish(Yii::getPathOfAlias('ext.Menu.assets'). '/nav.css'));
    App()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(Yii::getPathOfAlias('ext.Menu.assets'). '/nav.js'));
    
    echo CHtml::tag('div', array(
        'class' => 'maintitle titlebar',
        'id' => 'title-' . $menu['role']
    ), $menu['title']);
?>
<nav class="menubar">
    <?php 
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
