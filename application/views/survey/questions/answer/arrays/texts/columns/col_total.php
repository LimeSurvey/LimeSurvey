<?php
/**
 * Optional td item for total, grand total, etc
 * Define the width
 *
 * @var $empty
 */
?>
<!-- col_total -->
<td class="total information-item">
    <?php if($empty):?>
        &nbsp;
    <?php else:?>
        <label class="sr-only"><?php eT("Total");?></label>
        <input title="[[ROW_NAME]] total" size="<?php echo $inputsize; ?>" value="" type="text" disabled="disabled" class="disabled form-control"  data-number='1' />
    <?php endif;?>
</td>

