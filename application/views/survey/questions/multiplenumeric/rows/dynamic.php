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
<?php if($sumRemainingEqn || $sumEqn):?>
  <div class="col-sm-push-<?php echo $sLabelWidth; ?> col-sm-<?php echo $sInputContainerWidth; ?>">
    <div class="ls-group-remaining-total">
      <table>    
        <?php if($sumRemainingEqn):?>
          <tr class="form-group ls-group-remaining">
            <td class="control-label">
              <?php eT('Remaining: ');?>
            </td>
            
            <td class="ls-input-group">
              <?php if ($prefix != ''): ?>
                <div class="ls-input-group-extra prefix-text prefix text-right">
                  <?php echo $prefix; ?>
                </div>
              <?php endif; ?>
              <div id="remainingvalue_<?php echo $id; ?>" class="form-control-static numeric dynamic-remaining" data-number="1"><!-- alternative class : form-control : display like an input:text -->
                      {<?php echo $sumRemainingEqn;?>}
              </div>
              <?php if ($suffix != ''): ?>
                <div class="ls-input-group-extra suffix-text suffix text-left">
                  <?php echo $suffix; ?>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endif; ?>

        <?php if($sumEqn):?>
          <tr class="form-group ls-group-total">
            <td class="control-label">
              <?php eT('Total: ');?>
            </td>
            <td class="ls-input-group">
              <?php if ($prefix != ''): ?>
                <div class="ls-input-group-extra prefix-text prefix text-right">
                  <?php echo $prefix; ?>
                </div>
              <?php endif; ?>
              <div id="totalvalue_<?php echo $id; ?>" class="form-control-static numeric dynamic-total" data-number="1">
              {<?php echo $sumEqn; ?>}
              </div>
              <?php if ($suffix != ''): ?>
                <div class="ls-input-group-extra suffix-text suffix text-left">
                  <?php echo $suffix; ?>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endif; ?>
      </table>
   </div>
<?php endif; ?>