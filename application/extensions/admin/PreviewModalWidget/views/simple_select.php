<?php
/**
 * This view defines a simple select instead of a modal box
 */

//disable dropdown select when survey is active
$disable = '';
if ($this->survey_active) {
    $disable = 'disabled';
}

?>
<select id="<?=$this->widgetsJsName?>" name="<?=$this->widgetsJsName?>" class="form-select" <?= $disable?> >
    <?php 
    foreach ($this->itemArray as $sItemKey => $aItemContent) { 
        $selected = $this->value == $sItemKey ? 'selected' : '';
        if(YII_DEBUG) {
            echo sprintf("<option value='%s' %s>%s (%s)</option>", $sItemKey, $selected, $aItemContent['title'], $sItemKey);
        } else {
            echo sprintf("<option value='%s' %s>%s</option>", $sItemKey, $selected, $aItemContent['title']);
        }
    } 
    ?>
</select> 
