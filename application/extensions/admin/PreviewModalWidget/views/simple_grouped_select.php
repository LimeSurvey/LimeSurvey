<?php
/**
 * This view defines a simple select instead of a modal box
 */


$disable = '';
if ($this->survey_active) {
    $disable = 'disabled';
}

?>
<select id="<?=$this->widgetsJsName?>" name="<?=$this->widgetsJsName?>" class="form-select" <?=$disable?>>
    <?php 
    foreach ($this->groupStructureArray as $sGroupTitle => $aGroupArray) {  
        echo sprintf("<optgroup label='%s'>", $aGroupArray[$this->groupTitleKey]);
        foreach ($aGroupArray[$this->groupItemsKey] as $aItemContent) {
            $selected = $this->value == $aItemContent['type'] && $this->theme == $aItemContent['name'] ? 'selected' : '';
            if(YII_DEBUG) {
                echo sprintf("<option value='%s' data-theme='%s' %s>%s (%s)</option>", $aItemContent['type'], $aItemContent['name'], $selected, $aItemContent['title'], $aItemContent['type']);
            } else {
                echo sprintf("<option value='%s' data-theme='%s' %s>%s</option>", $aItemContent['type'], $aItemContent['name'], $selected, $aItemContent['title']);
            }
        } 
        echo "</optgroup>";
    } 
    ?>
</select> 
