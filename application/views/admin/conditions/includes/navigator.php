<div class="row ms-auto">
    <label for='questionNav' class="text-nowrap col-sm-7 col-form-label col-form-label-sm"><?php eT("Move to question:");?></label>
    <div class="col-5">
    <select id='questionNav' class="form-select">

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
    <a href="#" id="selector__hiddenNavigation" class="d-none pjax">hidden</a>
    </div>
</div>

<?php
App()->getClientScript()->registerScript('conditionmovetoquestion', 
"$('#questionNav').off('.conditionmovetoquestion').on('change.conditionmovetoquestion', function(e){
    $(document).trigger('pjax:load', {url : $(this).val()});
});", LSYii_ClientScript::POS_POSTSCRIPT);
?>

