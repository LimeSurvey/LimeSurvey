<?php
/**
 * Yes / No Question, buttons item Html
 *
 * @var $name                           $ia[1]
 * @var $yChecked
 * @var $nChecked
 * @var $naChecked
 * @var $noAnswer
 * @var $value                          $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$name]
 */
?>
<ul class="<?php echo $coreClass;?> list-unstyled form-inline btn-group btn-group-justified" data-toggle="buttons" role="radiogroup" aria-labelledby="ls-question-text-<?php echo $basename; ?>">
    <!-- Yes -->
    <li id="javatbd<?php echo $name;?>Y" class="button-item form-group btn btn-primary <?php if($yChecked){ echo "active";}?>">
        <input
            type="radio"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>Y"
            value="Y"
            <?php echo $yChecked; ?>
        />
        <label for="answer<?php echo $name;?>Y">
            <span class="fa fa-check ls-icon" aria-hidden="true"></span> <?php eT('Yes');?>
        </label>
    </li>
    <!-- No -->
    <li id="javatbd<?php echo $name;?>N" class="button-item form-group btn btn-primary <?php if($nChecked){ echo "active";}?>">
        <input
            type="radio"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>N"
            value="N"
            <?php echo $nChecked; ?>
        />
        <label for="answer<?php echo $name;?>Y">
            <span class="fa fa-ban ls-icon" aria-hidden="true"></span> <?php eT('No');?>
        </label>
    </li>

    <!-- No answer -->
    <?php if($noAnswer):?>
        <li id="javatbd<?php echo $name;?>" class="button-item form-group btn btn-primary <?php if($naChecked){ echo "active";}?>">
            <input
                type="radio"
                name="<?php echo $name;?>"
                id="answer<?php echo $name;?>"
                value=""
                <?php echo $naChecked; ?>
            />
            <label for="answer<?php echo $name;?>Y">
                <span class="fa fa-circle-thin ls-icon" aria-hidden="true"></span> <?php eT('No answer');?>
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
