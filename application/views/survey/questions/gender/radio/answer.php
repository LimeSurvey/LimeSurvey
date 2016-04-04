<?php
/**
 * Gender question, radio item Html
 *
 * @var $name
 * @var $checkconditionFunction
 * @var $fChecked
 * @var $mChecked
 * @var $naChecked
 * @var $value
 */
?>

<!--Gender question, radio display -->

<!-- answer -->
<div class="answers-list radio-list">

    <!-- Female -->
    <div class="col-xs-4 answer-item radio-item">
        <input
            class="radio"
            type="radio"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>F"
            value="F"
            <?php echo $fChecked; ?>
            onclick="<?php echo $checkconditionFunction; ?>"
        />

        <label for="answer<?php echo $name;?>F" class="answertext">
            <?php eT('Female');?>
        </label>
    </div>

    <!-- Male -->
    <div class="col-xs-4 answer-item radio-item">
        <input
            class="radio"
            type="radio"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>M"
            value="M"
            <?php echo $mChecked;?>
            onclick="<?php echo $checkconditionFunction; ?>"
        />

        <label for="answer<?php echo $name;?>M" class="answertext">
            <?php eT('Male');?>
        </label>
    </div>

    <!-- No answer -->
    <?php if($noAnswer):?>
        <div class="col-xs-4 answer-item radio-item noanswer-item">
            <input
                class="radio"
                type="radio"
                name="<?php echo $name;?>"
                id="answer<?php echo $name;?>"
                value=""
                <?php echo $naChecked;?>
                onclick="<?php echo $checkconditionFunction; ?>"
            />

            <label for="answer<?php echo $name;?>" class="answertext">
                <?php eT('No answer'); ?>
            </label>
        </div>
    <?php endif;?>

    <!-- Value -->
    <input
        type="hidden"
        name="java<?php echo $name;?>"
        id="java<?php echo $name; ?>"
        value="<?php echo $value;?>"
    />
</div>
<!-- end of answer -->
