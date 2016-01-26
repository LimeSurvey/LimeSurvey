<?php
/**
 * Multiple short texts question, item input text Html
 * @var $extraclass
 * @var $sDisplayStyle
 * @var $kpclass
 * @var $alert
 * @var $theanswer
 * @var $labelname                  'answer'.$myfname
 * @var $prefixclass
 * @var $sliders
 * @var $sliderleft
 * @var $sliderright
 * @var $prefix
 * @var $tiwidth
 * @var $myfname
 * @var $dispVal
 * @var $maxlength
 * @var $suffix
 * @var $checkconditionFunction     $checkconditionFunction $checkconditionFunction.'(this.value, this.name, this.type);"
 */
?>
<!-- question attribute "display_rows" is set -> we need a textarea to be able to show several rows -->
<li class="form-group question-item answer-item text-item numeric-item <?php echo $extraclass;?>" <?php echo $sDisplayStyle;?>>
    <!-- Alert for mandatory -->
    <?php if ($alert):?>
        <div class="alert alert-danger errormandatory" role="alert">'.
            <?php echo $theanswer; ?>
        </div>
    <?php endif;?>

    <!-- Label -->
    <label for="<?php echo $labelname;?>" class="<?php echo $prefixclass;?>-label numeric-label col-xs-12">
        <?php echo $theanswer; ?>
    </label>

    <!-- Slider left -->
    <?php if($sliders): ?>
        <div class="slider_lefttext">
            <?php echo $sliderleft;?>
        </div>
    <?php endif;?>

    <!-- Input -->
    <div class="input">

        <!-- Prefix -->
        <?php if($prefix!=''):?>
            <div class='col-xs-2 text-right prefix-container'>
                <?php echo $prefix;?>
            </div>
        <?php endif;?>

        <!-- Input -->
        <div class='col-xs-8'>
            <input
                class="form-control text <?php echo $kpclass; ?>"
                type="text"
                size="<?php echo $tiwidth; ?>"
                name="<?php echo $myfname; ?>"
                id="answer<?php echo $myfname; ?>"
                title="<?php eT('Only numbers may be entered in this field.');?>"
                value="<?php echo $dispVal; ?>"
                onkeyup="<?php echo $checkconditionFunction;?>"
                <?php echo $maxlength;?>
            />
        </div>

        <!-- Suffix -->
        <?php if($suffix!=''):?>
            <div class='col-xs-2 text-right prefix-container'>
                <?php echo $suffix;?>
            </div>
        <?php endif;?>
    </div>

    <!-- Slider right -->
    <?php if($sliders): ?>
        <div class="slider_lefttext">
            <?php echo $sliderright;?>
        </div>
    <?php endif;?>
</li>
