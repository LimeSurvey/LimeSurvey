<div class="form-inline">
    <div class="form-group pull-right ls-flex-row">
        <label for='questionNav' class="text-nowrap"> <?php eT("Move to question:");?> </label>
        <select id='questionNav' class="form-control" onchange="$(document).trigger('pjax:load', {url : $(this).val()});">

            <optgroup class='activesurveyselect' label='<?php eT("Before"); ?>' >
                <?php foreach ($theserows as $row): ?>
                    <option value='<?php echo $row['value']; ?>'>
                        <?php echo $row['text']; ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>

            <optgroup class='activesurveyselect' label='<?php eT("Current"); ?>' >
                <option value='<?php echo $currentValue; ?>' selected='selected'>
                    <?php echo $currentText; ?>
                </option>
            </optgroup>

            <optgroup class='activesurveyselect' label='<?php eT("After"); ?>' >
                <?php foreach ($postrows as $row): ?>
                    <option value='<?php echo $row['value']; ?>'>
                        <?php echo $row['text']; ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>

        </select>
        <a href="#" id="selector__hiddenNavigation" class="hidden pjax">hidden</a>
    </div>
</div>
<?php
/*App()->getClientScript()->registerScript('conditionmovetoquestion', 
"$('#questionNav').off('.conditionmovetoquestion').on('change.conditionmovetoquestion', function(e){
    $(document).trigger('pjax:load', {url : $(this).val()});
});", LSYii_ClientScript::POS_POSTSCRIPT);*/
?>

