<?php
/**
 * Minute dropdown Html
 * @var $minuteId
 * @var $currentminute
 * @var $dropdown_dates_minute_step
 * @var $datepart
 */
?>

<!-- minute -->
<div class="col-sm-2 col-xs-12">
    <label for="minute<?php echo $minuteId; ?>" class="hide">
        <?php eT('Minute'); ?>
    </label>
    <select id="minute<?php echo $minuteId; ?>" name="minute<?php echo $minuteId; ?>" class="minute form-control">
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
</div>
<!-- end of minute -->
