
<div class="selector__toggle_<?=$this->widgetsJsName?>">
    <?=viewHelper::flatEllipsizeText($this->textToDisplay,true,$this->abbreviationSize,$this->abbreviationSign)?>
</div>
<div class="selector__toggle_<?=$this->widgetsJsName?> hidden">
    <?=$this->textToDisplay?>
</div>

<div class="selector__toggle_full_text float-end" data-toggle="buttons">
    <label class="btn btn-outline-secondary btn-xs" data-target=".selector__toggle_<?=$this->widgetsJsName?>" data-set-state="full">
        <input type="checkbox" > <span><?=gT("Show more")?></span>
        <!-- <span class="hidden"><?=gT("Show less")?></span> -->
    </label>
</div>
