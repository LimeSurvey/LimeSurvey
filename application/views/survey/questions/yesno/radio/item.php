<?php
/**
 * Yes / No Question, radio item Html
 *
 * @var $name                           $ia[1]
 * @var $yChecked
 * @var $nChecked
 * @var $naChecked
 * @var $noAnswer
 * @var $checkconditionFunction         $checkconditionFunction(this.value, this.name, this.type)
 * @var $value                          $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$name]
 */
?>

<ul class="list-unstyled answers-list radio-list">

    <!-- Yes -->
    <li class="answer-item radio-item radio">
        <input
            class="radio"
            type="radio"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>Y"
            value="Y"
            <?php echo $yChecked; ?>
            onclick="<?php echo $checkconditionFunction; ?>"
            aria-labelledby="label-answer<?php echo $name;?>Y"
        />

        <label for="answer<?php echo $name;?>Y" class="answertext"></label>
        <!--
             The label text is provided inside a div,
             To respect the global HTML flow of other question types
        -->
        <div class="label-text label-clickable" id="label-answer<?php echo $name;?>Y">
            <?php eT('Yes');?>
        </div>
    </li>

    <!-- No -->
    <li class="answer-item radio-item  radio">
        <input
            class="radio"
            type="radio"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>N"
            value="N"
            <?php echo $nChecked; ?>
            onclick="<?php echo $checkconditionFunction;?>"
            aria-labelledby="label-answer<?php echo $name;?>N"
        />

        <label for="answer<?php echo $name;?>N" class="answertext" ></label>
        <!--
             The label text is provided inside a div,
             To respect the global HTML flow of other question types
        -->
        <div class="label-text label-clickable" id="label-answer<?php echo $name;?>N">
            <?php eT('No');?>
        </div>
    </li>

    <!-- No answer -->
    <?php if($noAnswer):?>
        <li class="answer-item radio-item noanswer-item  radio">
            <input
                class="radio"
                type="radio"
                name="<?php echo $name;?>"
                id="answer<?php echo $name;?>"
                value=""
                <?php echo $naChecked; ?>
                onclick="<?php echo $checkconditionFunction;?>"
                aria-labelledby="label-answer<?php echo $name;?>"
            />

            <label for="answer<?php echo $name;?>" class="answertext"></label>
            <!--
                 The label text is provided inside a div,
                 To respect the global HTML flow of other question types
            -->
            <div class="label-text label-clickable" id="label-answer<?php echo $name;?>">
                <?php eT('No answer');?>
            </div>
        </li>
    <?php endif;?>
</ul>

<input
    type="hidden"
    name="java<?php echo $name;?>"
    id="java<?php echo $name;?>"
    value="<?php echo $value;?>"
/>
