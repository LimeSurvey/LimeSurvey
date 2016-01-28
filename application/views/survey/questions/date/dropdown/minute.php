<?php
/**
 * Minute dropdown Html
 * @var $minuteId = $ia[1];
 * @var $currentminute
 * @var $dropdown_dates_minute_step     $aQuestionAttributes['dropdown_dates_minute_step']
 * @var $datepart
 */
?>

<label for="minute<?php echo $minuteId; ?>" class="hide">
    <?php eT('Minute'); ?>
</label>
<select id="minute<?php echo $minuteId; ?>" name="minute<?php echo $minuteId; ?>" class="minute">
    <option value="">
        <?php eT('Minute'); ?>
    </option>

    <?php for($i=0; $i<60; $i+=$dropdown_dates_minute_step):?>
        <option value="<?php echo $i; ?>" <?php if ($i === (int)$currentminute && is_numeric($currentminute)):?>SELECTED<?php endif;?> >
            <?php if ($datepart=='i'):?>
                <?php echo sprintf('%02d', $i);?>
            <?php else:?>
                <?php echo $i;?>
            <?php endif;?>
        </option>
    <?php endfor;?>
</select>
