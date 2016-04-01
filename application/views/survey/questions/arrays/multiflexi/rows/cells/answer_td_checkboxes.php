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
<td data-title="<?php echo $dataTitle;?>" class="answer-cell-6 answer_cell_00<?php echo $ld; ?> question-item answer-item <?php echo $answertypeclass;?>-item">

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
        value="<?php echo $value; ?>"
    />

    <input
        type="checkbox"
        class="checkbox <?php echo $extraclass;?>"
        name="cbox_<?php echo $myfname2;?>"
        id="cbox_<?php echo $myfname2;?>"
        <?php echo $setmyvalue; ?>
        onclick="
            cancelBubbleThis(event);
            aelt=document.getElementById('answer<?php echo $myfname2;?>');
            jelt=document.getElementById('java<?php echo $myfname2;?>');
            if(this.checked)
            {
                aelt.value=1;
                jelt.value=1;
                <?php echo $checkconditionFunction; ?>(1,'<?php echo $myfname2;?>',aelt.type);
            else
            {
                aelt.value='';
                jelt.value='';
                <?php echo $checkconditionFunction; ?>('','<?php echo $myfname2;?>',aelt.type);
            }
            return true;"
        onchange="checkconditions(this.value, this.name, this.type)"
        />

        <label class="hide read" for="cbox_<?php echo $myfname2;?>">
            <?php echo $dataTitle;?>
        </label>
    </td>
<!-- end of answer_td_checkboxes -->
