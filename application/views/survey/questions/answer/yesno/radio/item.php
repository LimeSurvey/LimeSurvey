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

<ul class="<?php echo $coreClass;?> list-unstyled form-inline" role="radiogroup" aria-labelledby="ls-question-text-<?php echo $basename; ?>">
    <!-- Yes -->
    <li id="javatbd<?php echo $name;?>Y"  class="form-group answer-item radio-item">
        <input
            type="radio"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>Y"
            value="Y"
            <?php echo $yChecked; ?>
        />
        <label for="answer<?php echo $name;?>Y" class="control-label answer-text">
            <?php eT('Yes');?>
        </label>
    </li>

    <!-- No -->
    <li id="javatbd<?php echo $name;?>N"  class="form-group answer-item radio-item">
        <input
            type="radio"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>N"
            value="N"
            <?php echo $nChecked; ?>
        />
        <label for="answer<?php echo $name;?>N" class="control-label answer-text" >
            <?php eT('No');?>
        </label>
    </li>

    <!-- No answer -->
    <?php if($noAnswer):?>
        <li id="javatbd<?php echo $name;?>"  class="form-group answer-item radio-item noanswer-item">
            <input
                type="radio"
                name="<?php echo $name;?>"
                id="answer<?php echo $name;?>"
                value=""
                <?php echo $naChecked; ?>
            />
            <label for="answer<?php echo $name;?>" class="control-label answer-text">
                <?php eT('No answer');?>
            </label>
        </li>
    <?php endif;?>
</ul>
<?php
/* Value for expression manager javascript (use id) ; no need to submit */
echo \CHtml::hiddenField("java{$name}",$value,array(
    'id' => "java{$name}",
    'disabled' => true,
));
?>
