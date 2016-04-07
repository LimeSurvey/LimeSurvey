<?php
/**
 * Hour dropdown Html
 * @var $hourId
 * @var $currenthour
 * @var $datepart
 */
?>
<!-- hour -->
<div class="col-sm-2 col-xs-12">
    <label for="hour<?php echo $hourId; ?>" class="hide">
        <?php eT('Hour'); ?>
    </label>

    <select id="hour<?php echo $hourId; ?>" name="hour<?php echo $hourId; ?>" class="hour form-control">
        <option value=""><?php eT('Hour'); ?></option>
        <?php for ($i=0; $i<24; $i++): ?>
            <option value="<?php echo $i; ?>" <?php if ($i === (int)$currenthour && is_numeric($currenthour)):?>SELECTED<?php endif;?>>
                <?php if ($datepart=='H'):?>
                    <?php echo sprintf('%02d', $i); ?>
                <?php else:?>
                    <?php echo $i;?>
                <?php endif;?>
            </option>
        <?php endfor;?>
    </select>
</div>
<!-- end of hour -->
