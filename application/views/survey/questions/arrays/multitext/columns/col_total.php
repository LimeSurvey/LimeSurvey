<?php
/**
 * Optional column for total, grand total, etc
 * Define the width
 *
 * @var $empty
 */
?>
<!-- col_total -->
<?php if($empty):?>
    <td>&nbsp;</td>
<?php else:?>
    <td class="total information-item">
        <?php if($label):?>
            <label class="hidden-md hidden-lg"><?php gT("Total");?></label>
        <?php endif;?>
        <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled form-control" />
    </td>
<?php endif;?>
<!-- end of col_total -->
