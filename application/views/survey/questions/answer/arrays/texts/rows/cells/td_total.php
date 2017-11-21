<?php
/**
 * Cell for total
 * @var $empty
 */
?>
<!-- td_total -->
<td class="total information-item">
    <?php if($empty):?>
        &nbsp;
    <?php else:?>
        <label class="sr-only"><?php eT("Total");?></label>
        <input title="[[ROW_NAME]] total" <?php echo ($inputsize ? 'size="'.$inputsize.'"': '') ; ?> value="" type="text" disabled="disabled" class="disabled form-control"  data-number='1' />
    <?php endif;?>
</td>
<!-- end of td_total -->
