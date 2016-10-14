<?php
/**
 * Multiple numerci : dynamic row (remaining + total)
 * @var $id
 * @var $sumRemainingEqn
 * @var $sumEqn
 * @var $sLabelWidth
 * @var $sInputContainerWidth
 * @var $prefix
 * @var $suffix
 */
?>
<?php if($sumRemainingEqn):?>
    <li class="form-group">
        <div class="control-label col-xs-12 col-sm-<?php echo $sLabelWidth; ?>">
            <?php eT('Remaining: ');?>
        </div>
        <div class="ls-input-group col-xs-12 col-sm-<?php echo $sInputContainerWidth; ?>">
            <?php echo $prefix; ?>
            <div id="remainingvalue_<?php echo $id; ?>" class="form-control-static numeric dynamic-remaining"><!-- alteranative class : form-control : display like an input:text -->
                {<?php echo $sumRemainingEqn;?>}
            </div>
            <?php echo $suffix; ?>
        </div>
    </li>
<?php endif; ?>

<?php if($sumEqn):?>
    <li class="form-group">
        <div class="control-label col-xs-12 col-sm-<?php echo $sLabelWidth; ?>">
            <?php eT('Total: ');?>
        </div>
       <div class="ls-input-group col-xs-12 col-sm-<?php echo $sInputContainerWidth; ?>">
            <?php echo $prefix; ?>
            <div id="totalvalue_<?php echo $id; ?>" class="form-control-static numeric dynamic-total">
                {<?php echo $sumEqn; ?>}
            </div>
            <?php echo $suffix; ?>
        </div>
    </li>
<?php endif; ?>
