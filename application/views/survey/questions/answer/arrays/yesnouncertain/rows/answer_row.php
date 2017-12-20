<?php
/**
 * Generate a row for the table
 *
 * @var $myfname
 * @var $sDisplayStyle
 * @var $answertext
 * @var $Ychecked
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
<tr id="javatbd<?php echo $myfname;?>" class="answers-list radio-list <?php echo ($odd) ? "ls-odd" : "ls-even"; ?> <?php echo ($error) ? " has-error" : ""; ?>" role="radiogroup" aria-labelledby="answertext<?php echo $myfname;?>">
    <!-- Answer text /  Errors -->
    <th class="answertext control-label<?php if($error){ echo " error-mandatory";} ?><?php echo ($answerwidth==0)? " sr-only":""; ?>" id="answertext<?php echo $myfname;?>">
        <?php echo $answertext;?>
        <?php
        /* Value for expression manager javascript (use id) ; no need to submit */
        echo \CHtml::hiddenField("java{$myfname}",$value,array(
            'id' => "java{$myfname}",
            'disabled' => true,
        ));
        ?>
    </th>

    <!-- Yes -->
    <td class="answer_cell_Y answer-item radio-item">
        <input
            type="radio"
            name="<?php echo $myfname;?>"
            id="answer<?php echo $myfname;?>-Y"
            value="Y"
            <?php echo $Ychecked;?>
            />
        <label for="answer<?php echo $myfname;?>-Y" class="ls-label-xs-visibility">
            <?php eT("Yes"); ?>
        </label>
    </td>

    <!-- Uncertain -->
    <td class="answer_cell_U answer-item radio-item">
        <input
            type="radio"
            name="<?php echo $myfname; ?>"
            id="answer<?php echo $myfname;?>-U"
            value="U"
            <?php echo $Uchecked?>
        />
        <label for="answer<?php echo $myfname; ?>-U" class="ls-label-xs-visibility">
            <?php eT("Uncertain");?>
        </label>
    </td>

    <!-- No -->
    <td class="answer_cell_N answer-item radio-item">
        <input
            type="radio"
            name="<?php echo $myfname;?>"
            id="answer<?php echo $myfname;?>-N"
            value="N"
            <?php echo $Nchecked?>
        />
        <label for="answer<?php echo $myfname;?>-N" class="ls-label-xs-visibility">
            <?php eT("No"); ?>
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
