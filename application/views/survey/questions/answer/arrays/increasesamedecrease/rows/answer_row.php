<?php
/**
 * Array, Increase Same Decrease
 *
 * @var $myfname
 * @var $sDisplayStyle
 * @var $answertext
 * @var $Ichecked
 * @var $Schecked
 * @var $Dchecked

 * @var $Uchecked
 * @var $Nchecked
 * @var $NAchecked
 * @var $value
 * @var $checkconditionFunction
 * @var $no_answer
 * @var $error
 */
?>

<!-- answer_row -->
<tr id="javatbd<?php echo $myfname;?>" class="row-inc-same-dec answers-list radio-list <?php echo ($odd) ? "ls-odd" : "ls-even"; ?> <?php echo ($error) ? " ls-error-mandatory has-error" : ""; ?>" role="radiogroup"  aria-labelledby="answertext<?php echo $myfname;?>">
    <!-- Answer text /  Errors -->
    <th id="answertext<?php echo $myfname;?>" class="answertext control-label<?php echo ($answerwidth==0)? " sr-only":""; ?>">
        <?php echo $answertext;?>
        <?php
        /* Value for expression manager javascript (use id) ; no need to submit */
        echo \CHtml::hiddenField("java{$myfname}",$value,array(
            'id' => "java{$myfname}",
            'disabled' => true,
        ));
        ?>
    </th>

    <!-- Increase -->
    <td class="answer_cell_I answer-item radio-item">
        <input
            type="radio"
            name="<?php echo $myfname;?>"
            id="answer<?php echo $myfname;?>-I"
            value="I"
            <?php echo $Ichecked;?>
            />
        <label for="answer<?php echo $myfname;?>-I" class="ls-label-xs-visibility">
            <?php eT("Increase"); ?>
        </label>
    </td>

    <!-- Same -->
    <td class="answer_cell_S answer-item radio-item">
        <input
            type="radio"
            name="<?php echo $myfname; ?>"
            id="answer<?php echo $myfname;?>-S"
            value="S"
            <?php echo $Schecked?>
        />
        <label for="answer<?php echo $myfname; ?>-S" class="ls-label-xs-visibility">
            <?php eT("Same");?>
        </label>
    </td>

    <!-- Decrease -->
    <td class="answer_cell_D answer-item radio-item">
        <input
            type="radio"
            name="<?php echo $myfname;?>"
            id="answer<?php echo $myfname;?>-D"
            value="D"
            <?php echo $Dchecked?>
        />
        <label for="answer<?php echo $myfname;?>-D" class="ls-label-xs-visibility">
            <?php eT("Decrease"); ?>
        </label>
    </td>

    <!-- No Answer -->
    <?php if($no_answer):?>
        <td class="answer_cell_ answer-item noanswer-item radio-item">
            <input
                type="radio"
                name="<?php echo $myfname;?>"
                id="answer<?php echo $myfname;?>-"
                value=""
                <?php echo $NAchecked?>
            />
            <label for="answer<?php echo $myfname;?>-" class="ls-label-xs-visibility">
                <?php eT("No answer");?>
            </label>
        </td>
    <?php endif;?>
</tr>
<!-- end of answer_row -->
