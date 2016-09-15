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
<tr id="javatbd<?php echo $myfname;?>" class="row-inc-same-dec well answers-list radio-list array<?php echo $zebra; ?>"  <?php echo $sDisplayStyle; ?>>
    <!-- Answer text /  Errors -->
    <th class="answertext  text-center">
        <input type="hidden" name="java<?php echo $myfname;?>" id="java<?php echo $myfname;?>" value="<?php echo $value;?>" />
        <?php if($error): ?>
            <div class="label label-danger" role="alert">
                <?php echo $answertext;?>
            </div>
        <?php else: ?>
            <?php echo $answertext;?>
        <?php endif;?>

        <input
            type="hidden"
            name="thjava<?php echo $myfname;?>"
            id="thjava<?php echo $myfname;?>"
            value="<?php echo $value;?>"
        />

    </th>

    <!-- Increase -->
    <td class="answer_cell_I answer-item radio-item radio text-center">
        <input
            class="radio"
            type="radio"
            name="<?php echo $myfname;?>"
            id="answer<?php echo $myfname;?>-I"
            value="I"
            <?php echo $Ichecked;?>
            onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
            aria-labelledby="label-answer<?php echo $myfname;?>-I"
            />
        <label for="answer<?php echo $myfname;?>-I"></label>
        <!--
             The label text is provided inside a div,
             To respect the global HTML flow of other question types
        -->
        <div class="visible-xs-block label-text" id="label-answer<?php echo $myfname;?>-I">
            <?php eT("Increase"); ?>
        </div>
    </td>

    <!-- Same -->
    <td class="answer_cell_S answer-item radio-item  radio text-center">
        <input
            class="radio"
            type="radio"
            name="<?php echo $myfname; ?>"
            id="answer<?php echo $myfname;?>-S"
            value="S"
            <?php echo $Schecked?>
            onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
            aria-labelledby="label-answer<?php echo $myfname; ?>-S"
        />
        <label for="answer<?php echo $myfname; ?>-S"></label>
        <!--
             The label text is provided inside a div,
             To respect the global HTML flow of other question types
        -->
        <div class="visible-xs-block label-text" id="label-answer<?php echo $myfname; ?>-S">
            <?php eT("Same");?>
        </div>
    </td>

    <!-- Decrease -->
    <td class="answer_cell_D answer-item radio-item radio  text-center">
        <input
            class="radio"
            type="radio"
            name="<?php echo $myfname;?>"
            id="answer<?php echo $myfname;?>-D"
            value="D"
            <?php echo $Dchecked?>
            onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
            aria-labelledby="label-answer<?php echo $myfname;?>-D"
        />
        <label for="answer<?php echo $myfname;?>-D"></label>
        <!--
             The label text is provided inside a div,
             To respect the global HTML flow of other question types
        -->
        <div class="visible-xs-block label-text" id="label-answer<?php echo $myfname;?>-D">
            <?php eT("Decrease"); ?>
        </div>
    </td>

    <!-- No Answer -->
    <?php if($no_answer):?>
        <td class="answer-item radio-item noanswer-item radio text-center">
            <input
                class="radio"
                type="radio"
                name="<?php echo $myfname;?>"
                id="answer<?php echo $myfname;?>-"
                value=""
                <?php echo $NAchecked?>
                onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
                aria-labelledby="answer<?php echo $myfname;?>-"
            />
            <label for="answer<?php echo $myfname;?>-"></label>
            <!--
                 The label text is provided inside a div,
                 To respect the global HTML flow of other question types
            -->
            <div class="visible-xs-block label-text" id="labelanswer<?php echo $myfname;?>-">
                <?php eT("No answer");?>
            </div>
        </td>
    <?php endif;?>
</tr>
<!-- end of answer_row -->
