<?php
/**
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
    <th class="answertext">
        <input type="hidden" name="java<?php echo $myfname;?>" id="java<?php echo $myfname;?>" value="<?php echo $value;?>" />
        <?php if($error): ?>
            <div class="alert alert-danger" role="alert">
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
    <td data-title='<?php eT("Increase"); ?>' class="answer_cell_I answer-item radio-item">
        <label for="answer<?php echo $myfname;?>-I">
            <input
                class="radio"
                type="radio"
                name="<?php echo $myfname;?>"
                id="answer<?php echo $myfname;?>-I"
                value="I"
                <?php echo $Ichecked;?>
                onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
                />
            </label>
    </td>

    <!-- Same -->
    <td data-title='<?php eT("Same");?>' class="answer_cell_S answer-item radio-item">
        <label for="answer<?php echo $myfname; ?>-S">
            <input
                class="radio"
                type="radio"
                name="<?php echo $myfname; ?>"
                id="answer<?php echo $myfname;?>-S"
                value="S"
                <?php echo $Schecked?>
                onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
            />
        </label>
    </td>

    <!-- Decrease -->
    <td data-title='<?php eT("Decrease"); ?>' class="answer_cell_D answer-item radio-item">
        <label for="answer<?php echo $myfname;?>-D">
            <input
                class="radio"
                type="radio"
                name="<?php echo $myfname;?>"
                id="answer<?php echo $myfname;?>-D"
                value="D"
                <?php echo $Dchecked?>
                onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
            />
        </label>
    </td>

    <!-- No Answer -->
    <?php if($no_answer):?>
        <td data-title='<?php eT("No answer");?>' class="answer-item radio-item noanswer-item">
            <label for="answer<?php echo $myfname;?>-">
                <input
                    class="radio"
                    type="radio"
                    name="<?php echo $myfname;?>"
                    id="answer<?php echo $myfname;?>-"
                    value=""
                    <?php echo $NAchecked?>
                    onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
                />
            </label>
        </td>
    <?php endif;?>
</tr>
<!-- end of answer_row -->
