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
    <li class="form-group ls-group-remaining ls-group-dynamic">
        <div class="control-label col-xs-12 col-sm-<?php echo $sLabelWidth; ?>">
            <?php eT('Remaining: ');?>
        </div>
        <div class="ls-input-group col-xs-12 col-sm-<?php echo $sInputContainerWidth; ?>">
            <?php if ($prefix != ''): ?>
                <div class="ls-input-group-extra prefix-text prefix">
                    <?php echo $prefix; ?>
                </div>
            <?php endif; ?>
            <div id="remainingvalue_<?php echo $id; ?>" class="form-control-static numeric dynamic-remaining" data-number="1"><!-- alternative class : form-control : display like an input:text -->
                {<?php echo $sumRemainingEqn;?>}
            </div>
            <?php if ($suffix != ''): ?>
                <div class="ls-input-group-extra suffix-text suffix">
                    <?php echo $suffix; ?>
                </div>
            <?php endif; ?>
        </div>
    </li>
<?php endif; ?>

<?php if($sumEqn):?>
    <li class="form-group ls-group-total ls-group-dynamic">
        <div class="control-label col-xs-12 col-sm-<?php echo $sLabelWidth; ?>">
            <?php eT('Total: ');?>
        </div>
       <div class="ls-input-group col-xs-12 col-sm-<?php echo $sInputContainerWidth; ?>">
            <?php if ($prefix != ''): ?>
                <div class="ls-input-group-extra prefix-text prefix">
                    <?php echo $prefix; ?>
                </div>
            <?php endif; ?>
            <div id="totalvalue_<?php echo $id; ?>" class="form-control-static numeric dynamic-total" data-number="1">
                {<?php echo $sumEqn; ?>}
            </div>
            <?php if ($suffix != ''): ?>
                <div class="ls-input-group-extra suffix-text suffix">
                    <?php echo $suffix; ?>
                </div>
            <?php endif; ?>
        </div>
    </li>
<?php endif; ?>
