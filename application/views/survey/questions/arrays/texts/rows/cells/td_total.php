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
        <label class="hidden-md hidden-lg"><?php eT("Total");?></label>
        <input name="[[ROW_NAME]]_total" title="[[ROW_NAME]] total" size="[[INPUT_WIDTH]]" value="" type="text" disabled="disabled" class="disabled form-control" />
    <?php endif;?>
</td>
<!-- end of td_total -->
