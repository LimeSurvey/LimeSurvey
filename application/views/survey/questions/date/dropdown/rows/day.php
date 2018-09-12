<?php
/**
 * Day dropdown Html
 * @var $dayId
 * @var $currentdate
 */
?>

<!-- day -->
<div class="col-sm-2 col-xs-12">
    <label for="day<?php echo $dayId;?>" class="hide">
        <?php eT('Day'); ?>
    </label>
    <select id="day<?php echo $dayId;?>" name="day<?php echo $dayId;?>" class="form-control day">
        <option value="">
            <?php eT('Day'); ?>
        </option>

        <?php for ($i=1; $i<=31; $i++): ?>
            <option value="<?php echo sprintf('%02d', $i); ?>" <?php if($i == $currentdate):?>SELECTED<?php endif; ?> >
                <?php echo sprintf('%02d', $i); ?>
            </option>
        <?php endfor; ?>
    </select>
</div>
<!-- end of day -->
