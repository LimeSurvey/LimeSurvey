<?php
/**
 * Cell when checkbox layout is used in advanced question attribute
 *
 * @var $dataTitle
 * @var $ld
 * @var $answertypeclass
 * @var $value $myvalue
 * @var $myfname2
 * @var $setmyvalue
 * @var $checkconditionFunction
 * @var $extraclass
 */
?>

<!-- answer_td_checkboxes -->
<td class="answer_cell_<?php echo $ld; ?> question-item answer-item <?php echo $answertypeclass;?>-item checkbox-item">
    <input
        type="hidden"
        name="java<?php echo $myfname2;?>"
        id="java<?php echo $myfname2;?>"
        value="<?php echo $value; ?>"
    />
    <input
        type="hidden"
        name="<?php echo $myfname2; ?>"
        id="answer<?php echo $myfname2;?>"
        class="hidden"
        value="<?php echo $value; ?>"
    />
    <input
        type="checkbox"
        class="<?php echo $extraclass;?>"
        name="<?php echo $myfname2;?>"
        id="cbox_<?php echo $myfname2;?>"
        value="1"
        <?php echo $setmyvalue; ?>
        />
        <label for="cbox_<?php echo $myfname2;?>" class="ls-label-xs-visibility">
            <?php echo $dataTitle;?>
        </label>
    </td>
<!-- end of answer_td_checkboxes -->
