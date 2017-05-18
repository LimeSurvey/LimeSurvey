<?php
/**
 * Gender question, button item Html
 *
 * @var $name
 * @var $checkconditionFunction
 * @var $fChecked
 * @var $mChecked
 * @var $naChecked
 * @var $value
 */
?>

<!--Gender question, buttons display -->
<!-- answer -->
<ul class="<?php echo $coreClass;?> list-unstyled form-inline btn-group btn-group-justified" data-toggle="buttons" role="radiogroup" aria-labelledby="ls-question-text-<?php echo $basename; ?>">
    <!-- Female -->
    <li id="javatbd<?php echo $name;?>F" class="button-item btn btn-primary <?php if($fChecked!=''){echo 'active';}?>">
        <input
            class="radio"
            type="radio"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>F"
            value="F"
            <?php echo $fChecked; ?>
        />
        <label for="answer<?php echo $name;?>F">
            <span class="fa fa-venus ls-icon" aria-hidden="true"></span> <?php eT('Female');?>
        </label>
    </li>

    <!-- Male -->
    <li id="javatbd<?php echo $name;?>M" class="button-item btn btn-primary  <?php if($mChecked!=''){echo 'active';}?> ">
        <input
            class="radio"
            type="radio"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>M"
            value="M"
            <?php echo $mChecked;?>
        />
        <label for="answer<?php echo $name;?>M">
            <span class="fa fa-mars ls-icon" aria-hidden="true"></span> <?php eT('Male');?>
        </label>
    </li>

<!-- No answer -->
    <?php if($noAnswer):?>
    <li id="javatbd<?php echo $name;?>" class="button-item btn btn-primary  <?php if($naChecked!=''){echo 'active';}?>">
        <input
            class="radio"
            type="radio"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>"
            value=""
            <?php echo $naChecked;?>
        />
        <label for="answer<?php echo $name;?>">
            <span class="fa fa-genderless ls-icon" aria-hidden="true"></span> <?php eT('No answer'); ?>
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
<!-- end of answer -->
