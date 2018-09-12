<?php
/**
 * Cell for Grand Total
 *
 * @var $empty
 */
?>

<!-- td_grand_total -->
<?php if($empty):?>
    <td>&nbsp;</td>
<?php else:?>
    <td class="total grand information-item">
        <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled form-control" />
    </td>
<?php endif;?>
<!-- end of td_grand_total -->
