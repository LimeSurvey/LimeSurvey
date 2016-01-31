<?php
/**
 * Multiple short texts question, item input text Html
 * @var $alert;
 * @var $maxlength;
 * @var $tiwidth
 * @var $extraclass
 * @var $sDisplayStyle
 * @var $prefix
 * @var $myfname
 * @var $labelText                  $ansrow['question']
 * @var $prefix
 * @var $kpclass
 * @var $rows                       $drows.' '.$maxlength
 * @var $checkconditionFunction     $checkconditionFunction.'(this.value, this.name, this.type)'
 * @var $dispVal
 * @var $suffix
 */
?>
<!-- question attribute "display_rows" is set -> we need a textarea to be able to show several rows -->
<li class="question-item answer-item text-item <?php echo $extraclass;?>" <?php echo $sDisplayStyle;?>>
    <?php if($alert):?>
        <div class="alert alert-danger errormandatory"  role="alert">
            <?php echo $labelText;?>
        </div> <!-- alert -->

    <?php endif;?>
    <div  class="form-group-row row">
        <label class='control-label col-xs-12' for="answer<?php echo $myfname; ?>">
            <?php echo $labelText;?>
        </label>
        <div class="col-xs-12 input">
            <?php echo $prefix;?>
            <input
                class="text form-control <?php echo $kpclass;?>"
                type="text"
                size="<?php echo $tiwidth;?>"
                name="<?php echo $myfname;?>"
                id="answer<?php echo $myfname; ?>"
                value="<?php echo $dispVal;?>"
                onkeyup="<?php echo $checkconditionFunction; ?>"
                <?php echo $maxlength; ?>
            />
            <?php echo $suffix;?>
        </div>  <!-- xs-12 -->
    </div> <!-- form group -->
</li>
