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
        <input type="text" <?php echo ($inputsize ? 'size="'.$inputsize.'"': '') ; ?> value="" disabled="disabled" class="disabled form-control" data-number='1' />
    </td>
<?php endif;?>
<!-- end of td_grand_total -->
