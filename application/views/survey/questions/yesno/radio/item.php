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
    <li class="answer-item radio-item">
        <input
            class="radio"
            type="radio"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>Y"
            value="Y"
            <?php echo $yChecked; ?>
            onclick="<?php echo $checkconditionFunction; ?>"
        />

        <label for="answer<?php echo $name;?>Y" class="answertext">
            <?php eT('Yes');?>
        </label>
    </li>

    <!-- No -->
    <li class="answer-item radio-item">
        <input
            class="radio"
            type="radio"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>N"
            value="N"
            <?php echo $nChecked; ?>
            onclick="<?php echo $checkconditionFunction;?>"
        />

        <label for="answer<?php echo $name;?>N" class="answertext" >
            <?php eT('No');?>
        </label>
    </li>

    <!-- No answer -->
    <?php if($noAnswer):?>
        <li class="answer-item radio-item noanswer-item">
            <input
                class="radio"
                type="radio"
                name="<?php echo $name;?>"
                id="answer<?php echo $name;?>"
                value=""
                <?php echo $naChecked; ?>
                onclick="<?php echo $checkconditionFunction;?>"
            />

            <label for="answer<?php echo $name;?>" class="answertext">
                <?php eT('No answer');?>
            </label>
        </li>
    <?php endif;?>

    <input
        type="hidden"
        name="java<?php echo $name;?>"
        id="java<?php echo $name;?>"
        value="<?php echo $value;?>"
    />
</ul>
