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
<tr id="javatbd<?php echo $myfname;?>" class="well answers-list radio-list array<?php echo $zebra; ?>"  <?php echo $sDisplayStyle; ?>>
    <!-- Answer text /  Errors -->
    <th class="answertext">
        <input type="hidden" name="java<?php echo $myfname;?>" id="java<?php echo $myfname;?>" value="<?php echo $value; ?>" />
        <?php if($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $answertext;?>
            </div>
        <?php else: ?>
            <?php echo $answertext;?>
        <?php endif;?>
    </th>

    <!-- Yes -->
    <td data-title='<?php eT("Yes"); ?>' class="answer_cell_Y answer-item radio-item text-center radio">
        <input
            class="radio"
            type="radio"
            name="<?php echo $myfname;?>"
            id="answer<?php echo $myfname;?>-Y"
            value="Y"
            <?php echo $Ychecked;?>
            onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
            aria-labelledby="label-answer<?php echo $myfname;?>-Y"
            />
        <label for="answer<?php echo $myfname;?>-Y"></label>
        <!--
             The label text is provided inside a div,
             To respect the global HTML flow of other question types
        -->
        <div class="hide label-text" id="label-answer<?php echo $myfname;?>-Y">
            <?php eT("Yes"); ?>
        </div>
    </td>

    <!-- Uncertain -->
    <td data-title='<?php eT("Uncertain");?>' class="answer_cell_U answer-item radio-item text-center radio">
        <input
            class="radio"
            type="radio"
            name="<?php echo $myfname; ?>"
            id="answer<?php echo $myfname;?>-U"
            value="U"
            <?php echo $Uchecked?>
            onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
            aria-labelledby="label-answer<?php echo $myfname;?>-U"
        />
        <label for="answer<?php echo $myfname; ?>-U"></label>
        <!--
             The label text is provided inside a div,
             To respect the global HTML flow of other question types
        -->
        <div class="hide label-text" id="label-answer<?php echo $myfname;?>-U">
            <?php eT("Uncertain");?>
        </div>
    </td>

    <!-- No -->
    <td data-title='<?php eT("No"); ?>' class="answer_cell_N answer-item radio-item text-center radio">
        <input
            class="radio"
            type="radio"
            name="<?php echo $myfname;?>"
            id="answer<?php echo $myfname;?>-N"
            value="N"
            <?php echo $Nchecked?>
            onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
            aria-labelledby="label-answer<?php echo $myfname;?>-N"
        />
        <label for="answer<?php echo $myfname;?>-N"></label>
        <!--
             The label text is provided inside a div,
             To respect the global HTML flow of other question types
        -->
        <div class="hide label-text" id="label-answer<?php echo $myfname;?>-N">
            <?php eT("No"); ?>
        </div>
    </td>

    <!-- No Answer -->
    <?php if($no_answer):?>
        <td data-title='<?php eT("No answer");?>' class="answer-item radio-item noanswer-item text-center radio">
            <input
                class="radio"
                type="radio"
                name="<?php echo $myfname;?>"
                id="answer<?php echo $myfname;?>-"
                value=""
                <?php echo $NAchecked?>
                onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
            />
            <label for="answer<?php echo $myfname;?>-"></label>
            <!--
                 The label text is provided inside a div,
                 To respect the global HTML flow of other question types
            -->
            <div class="hide label-text" id="label-answer<?php echo $myfname;?>-">
                <?php eT("No answer");?>
            </div>
        </td>
    <?php endif;?>
</tr>
<!-- end of answer_row -->
