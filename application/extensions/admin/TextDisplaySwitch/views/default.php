
<div class="selector__toggle_<?=$this->widgetsJsName?>">
    <?=viewHelper::flatEllipsizeText($this->textToDisplay,true,$this->abbreviationSize,$this->abbreviationSign)?>
</div>
<div class="selector__toggle_<?=$this->widgetsJsName?> d-none">
    <?=$this->textToDisplay?>
</div>

<div class="selector__toggle_full_text float-end" data-bs-toggle="buttons">
    <label class="btn btn-outline-secondary btn-xs" data-target=".selector__toggle_<?=$this->widgetsJsName?>" data-set-state="full">
        <span><?=gT("Show more")?></span>
        <!-- <span class="d-none"><?=gT("Show less")?></span> -->
    </label>
</div>
