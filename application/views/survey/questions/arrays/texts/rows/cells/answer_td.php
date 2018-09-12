<?php
/**
 * Answer cell
 *
 * @var $ld
 * @var $myfname2
 * @var $labelText $labelans[$thiskey]
 * @var $kpclass
 * @var $maxlength
 * @var $inputwidth
 * @var $value
 */
?>

<!-- answer_td -->
<td class="answer-cell-4 answer_cell_<?php echo $ld;?> answer-item text-item">
    <label class="visible-xs-block read" for="answer<?php echo $myfname2; ?>">
        <?php echo $labelText;?>
    </label>

    <input
        type="hidden"
        name="java<?php echo $myfname2;?>"
        id="java<?php echo $myfname2; ?>"
    />

    <input
        type="text"
        name="<?php echo $myfname2; ?>"
        id="answer<?php echo $myfname2; ?>"
        class="form-control <?php echo $kpclass; ?>"
        <?php echo $maxlength; ?>
        size="<?php echo $inputwidth; ?>"
        value="<?php echo $value;?>"
    />
</td>
<!-- end of answer_td -->
