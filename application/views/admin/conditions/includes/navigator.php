<div class="form-group pull-right">
    <label for='questionNav'><?php eT("Move to question:");?></label>
    <select id='questionNav' class="form-control"  onchange="window.open(this.options[this.selectedIndex].value,'_top')">

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
</div>
