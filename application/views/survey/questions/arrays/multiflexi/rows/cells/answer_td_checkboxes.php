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
<td class="answer-cell-6 answer_cell_<?php echo $ld; ?> question-item answer-item <?php echo $answertypeclass;?>-item checkbox-item checkbox text-center">

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
            }
            else
            {
                aelt.value='';
                jelt.value='';
                <?php echo $checkconditionFunction; ?>('','<?php echo $myfname2;?>',aelt.type);
            }
            return true;"
        onchange="checkconditions(this.value, this.name, this.type)"
        aria-labelledby="label-cbox_<?php echo $myfname2;?>"
        />

        <label for="cbox_<?php echo $myfname2;?>"></label>

        <!--
             The label text is provided inside a div,
             so final user can add paragraph, div, or whatever he wants in the subquestion text
             This field is related to the input thanks to attribute aria-labelledby
        -->
        <div class="visible-xs-block label-text" id="label-cbox_<?php echo $myfname2;?>">
            <?php echo $dataTitle;?>
        </div>

    </td>
<!-- end of answer_td_checkboxes -->
