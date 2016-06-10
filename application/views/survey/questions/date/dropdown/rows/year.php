<?php
/**
 * Year dropdown Html
 * @var $yearId
 * @var $currentyear
 * @var $yearmax
 * @var $reverse
 * @var $yearmin
 * @var $step
 */
?>

<!-- year -->
<div class="col-sm-2 col-xs-12">
    <label for="year<?php echo $yearId; ?>" class="hide">
        <?php eT('Year'); ?>
    </label>

    <select id="year<?php echo $yearId; ?>" name="year<?php echo $yearId; ?>" class="year form-control">
        <option value="">
            <?php eT('Year'); ?>
        </option>
        <?php for ($i=$yearmax; ($reverse? $i<=$yearmin: $i>=$yearmin); $i+=$step): ?>
            <option value="<?php echo $i; ?>" <?php if ($i == $currentyear):?>SELECTED<?php endif;?> >
                <?php echo $i; ?>
            </option>
        <?php endfor; ?>
    </select>
</div>
<!-- end of year -->
