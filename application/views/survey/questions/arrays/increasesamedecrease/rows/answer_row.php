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
<tr id="javatbd<?php echo $myfname;?>" class="row-inc-same-dec well answers-list radio-list array<?php echo $zebra; ?><?php if($error){ echo " bg-warning";} ?>"  <?php echo $sDisplayStyle; ?>  role="radiogroup"  aria-labelledby="answertext<?php echo $myfname;?>">
    <!-- Answer text /  Errors -->
    <th class="answertext text-center<?php if($error):?> text-danger<?php endif;?>">
        <div id="answertext<?php echo $myfname;?>"><?php echo $answertext;?></div>
        <input type="hidden" name="java<?php echo $myfname;?>" id="java<?php echo $myfname;?>" value="<?php echo $value;?>" />
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
            />
        <label for="answer<?php echo $myfname;?>-I" class="text-hide-md text-hide-lg">
            <?php eT("Increase"); ?>
        </label>
    </td>

    <!-- Same -->
    <td class="answer_cell_S answer-item radio-item radio text-center">
        <input
            class="radio"
            type="radio"
            name="<?php echo $myfname; ?>"
            id="answer<?php echo $myfname;?>-S"
            value="S"
            <?php echo $Schecked?>
            onclick="<?php echo $checkconditionFunction;?>(this.value, this.name, this.type)"
        />
        <label for="answer<?php echo $myfname; ?>-S" class="text-hide-md text-hide-lg">
            <?php eT("Same");?>
        </label>
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
        />
        <label for="answer<?php echo $myfname;?>-D" class="text-hide-md text-hide-lg">
            <?php eT("Decrease"); ?>
        </label>
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
            />
            <label for="answer<?php echo $myfname;?>-" class="text-hide-md text-hide-lg">
                <?php eT("No answer");?>
            </label>
        </td>
    <?php endif;?>
</tr>
<!-- end of answer_row -->
