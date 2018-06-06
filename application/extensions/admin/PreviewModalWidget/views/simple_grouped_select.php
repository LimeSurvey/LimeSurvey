<?php
/**
 * This view defines a simple select instead of a modal box
 */
?>
<select id="<?=$this->widgetsJsName?>" name="<?=$this->widgetsJsName?>" class="form-control">
    <?php 
    foreach ($this->groupStructureArray as $sGroupTitle => $aGroupArray) {  
        echo sprintf("<optgroup label='%s'>", $aGroupArray[$this->groupTitleKey]);
        foreach ($aGroupArray[$this->groupItemsKey] as $sItemKey => $aItemContent) { 
            $selected = $this->value == $sItemKey ? 'selected' : '';
            if(YII_DEBUG) {
                echo sprintf("<option value='%s' %s>%s (%s)</option>", $sItemKey, $selected, $aItemContent['description'], $sItemKey);
            } else {
                echo sprintf("<option value='%s' %s>%s</option>", $sItemKey, $selected, $aItemContent['description']);
            }
        } 
        echo "</optgroup>"; 
    } 
    ?>
</select> 