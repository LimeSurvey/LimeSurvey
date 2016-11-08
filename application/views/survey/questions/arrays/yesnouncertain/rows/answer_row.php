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
<tr id="javatbd<?php echo $myfname;?>" class="answers-list radio-list <?php echo ($odd) ? "ls-odd" : "ls-even"; ?> <?php echo ($error) ? " has-error" : ""; ?>"  <?php echo $sDisplayStyle; ?> role="radiogroup" aria-labelledby="answertext<?php echo $myfname;?>">
    <!-- Answer text /  Errors -->
    <th class="answertext control-label" id="answertext<?php echo $myfname;?>">
         <?php echo $answertext;?>
        <input type="hidden" name="java<?php echo $myfname;?>" id="java<?php echo $myfname;?>" value="<?php echo $value; ?>" />
    </th>

    <!-- Yes -->
    <td class="answer_cell_Y answer-item radio-item">
        <input
            type="radio"
            name="<?php echo $myfname;?>"
            id="answer<?php echo $myfname;?>-Y"
            value="Y"
            <?php echo $Ychecked;?>
            onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
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
            onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
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
            onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
        />
        <label for="answer<?php echo $myfname;?>-N" class="ls-label-xs-visibility">
            <?php eT("No"); ?>
        </label>
    </td>

    <!-- No Answer -->
    <?php if($no_answer):?>
        <td class="answer_cell_ answer-item radio-item noanswer-item">
            <input
                type="radio"
                name="<?php echo $myfname;?>"
                id="answer<?php echo $myfname;?>-"
                value=""
                <?php echo $NAchecked?>
                onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
            />
            <label for="answer<?php echo $myfname;?>-" class="ls-label-xs-visibility">
                <?php eT("No answer");?>
            </label>
        </td>
    <?php endif;?>
</tr>
<!-- end of answer_row -->
