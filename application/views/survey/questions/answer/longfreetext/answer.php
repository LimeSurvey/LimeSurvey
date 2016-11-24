<?php
/**
 * Lon free text question, item Html
 *
 * @var $extraclass
 * @var $name
 * @var $drows
 * @var $tiwidth
 * @var $maxlength
 * @var $checkconditionFunction
 * @var $dispVal
 */
?>
<!-- Long Free Text -->

<!-- answer -->
<?php if($withColumn): ?>
<div class='<?php echo $coreClass; ?> row'>
    <div class="<?php echo $extraclass; ?>">
<?php else: ?>
<div class='<?php echo $coreClass; ?> <?php echo $extraclass; ?>'>
<?php endif; ?>
    <textarea
        class="form-control <?php echo $kpclass; ?>"
        name="<?php echo $name; ?>"
        id="answer<?php echo $name; ?>"
        rows="<?php echo $drows; ?>"
        <?php echo ($inputsize ? 'cols="'.$inputsize.'"': '') ; ?>
        <?php echo ($maxlength ? 'maxlength='.$maxlength: ''); ?>
        aria-labelledby="ls-question-text-<?php echo $basename; ?>"
    ><?php echo $dispVal;?></textarea>
<?php if($withColumn): ?>
    </div>
</div>
<?php else: ?>
</div>
<?php endif; ?>

<!-- end of answer -->
