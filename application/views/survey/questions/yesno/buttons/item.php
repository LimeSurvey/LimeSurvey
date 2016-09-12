<?php
/**
 * Yes / No Question, buttons item Html
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
<div class='col-xs-12 col-sm-6'>  <!-- Full width on Phone; otherwise half width -->
    <ul class="list-unstyled list-inline btn-group btn-group-justified answers-list button-list yesno-button" data-toggle="buttons">
        <!-- Yes -->
        <li id="javatbd<?php echo $name;?>Y" class="button-item btn btn-primary <?php if($yChecked){ echo "active";}?>">
            <input
                class="radio"
                type="radio"
                name="<?php echo $name;?>"
                id="answer<?php echo $name;?>Y"
                value="Y"
                <?php echo $yChecked; ?>
                onclick="<?php echo $checkconditionFunction; ?>"
            />
            <label for="answer<?php echo $name;?>Y">
                <span class="fa fa-check" aria-hidden="true"></span> <?php eT('Yes');?>
            </label>
        </li>
        <!-- No -->
        <li id="javatbd<?php echo $name;?>N" class="button-item btn btn-primary <?php if($nChecked){ echo "active";}?>">
            <input
                class="radio"
                type="radio"
                name="<?php echo $name;?>"
                id="answer<?php echo $name;?>N"
                value="N"
                <?php echo $nChecked; ?>
                onclick="<?php echo $checkconditionFunction;?>"
            />
            <label for="answer<?php echo $name;?>Y">
                <span class="fa fa-ban" aria-hidden="true"></span> <?php eT('No');?>
            </label>
        </li>

        <!-- No answer -->
        <?php if($noAnswer):?>
            <li id="javatbd<?php echo $name;?>" class="btn btn-primary <?php if($naChecked){ echo "active";}?>">
                <input
                    class="radio"
                    type="radio"
                    name="<?php echo $name;?>"
                    id="answer<?php echo $name;?>"
                    value=""
                    <?php echo $naChecked; ?>
                    onclick="<?php echo $checkconditionFunction;?>"
                />
                <label for="answer<?php echo $name;?>Y">
                    <span class="fa fa-circle-thin" aria-hidden="true"></span> <?php eT('No answer');?>
                </label>
            </li>
        <?php endif;?>
    </ul>
</div>
<!-- Value for expression manager (use id) -->
<input
    type="hidden"
    name="java<?php echo $name;?>"
    id="java<?php echo $name;?>"
    value="<?php echo $value;?>"
/>

