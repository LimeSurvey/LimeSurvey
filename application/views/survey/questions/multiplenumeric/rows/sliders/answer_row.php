<?php
/**
 * Multiple short texts question, item input text Html
 * @var $tip
 * @var $alert
 * @var $maxlength
 * @var $tiwidth
 * @var $extraclass
 * @var $sDisplayStyle
 * @var $prefix
 * @var $myfname
 * @var $labelText
 * @var $prefix
 * @var $kpclass
 * @var $rows
 * @var $checkconditionFunction
 * @var $dispVal
 * @var $suffix
 * @var $sUnformatedValue
 * @var $slider_min
 * @var $slider_max
 * @var $slider_step
 * @var $slider_default
 * @var $slider_middlestart
 * @var $slider_orientation
 * @var $slider_handle
 * @var $slider_reset
 * @var $sSeparator
 * @var $slider_debug
 */

//the complicated default slider setting will be simplified header_remove
//Three cases:
//  1: posted value safed
//  2: default value set 
//  3: slider starts in middle position

$sliderStart = 'null';
if($dispVal) //posted value => higest priority
{
    $sliderStart = $dispVal;
} 
else if($slider_default) //
{
    $sliderStart = $slider_default;
}
else if($slider_middlestart==1) //
{
    $sliderStart = intval(($slider_max + $slider_min)/2);
}

?>

<div  id='javatbd<?php echo $myfname; ?>' class="question-item answer-item numeric-item  text-item <?php echo $extraclass;?> col-sm-12" <?php echo $sDisplayStyle;?>>

    <div class="form-group row">
        <?php if($alert):?>
            <div class="label label-danger errormandatory"  role="alert">
                <?php echo $labelText;?>
            </div> <!-- alert -->
        <?php else:?>
            <label class='control-label col-xs-12 numeric-label' for="answer<?php echo $myfname; ?>">
                <?php echo $labelText;?>
            </label>
        <?php endif;?>
        <div>

        <div class='slider-container row'>
            <div class='col-xs-12 col-sm-<?php echo $tiwidth; ?>'>
                        <?php if (!empty($sliderright) || !empty($sliderleft)): ?>
                            <span class='pull-left col-xs-12 col-sm-3 slider-left-span'><?php echo $sliderleft;?></span>
                        <?php endif; ?>

                        <!-- Different col size depending on right|left -->
                        <!-- TODO: Move PHP to qanda -->
                        <?php if (empty($sliderleft) && empty($sliderright)): ?>
                            <div class='col-xs-12 col-sm-12'>
                        <?php else: ?>
                            <div class='col-xs-12 col-sm-6'>
                        <?php endif; ?>

                            <input
                                class="text form-control pull-left <?php echo $kpclass;?>"
                                type="text"
                                name="slider_<?php echo $myfname;?>"
                                id="slider_answer<?php echo $myfname; ?>"
                                value="<?php echo $sliderStart; ?>"
                                <?php echo $maxlength; ?>
                                data-slider-value="<?php echo $sliderStart; ?>"
                                data-slider-min='<?php echo $slider_min;?>'
                                data-slider-max='<?php echo $slider_max;?>'
                                data-slider-step='<?php echo $slider_step;?>'
                                data-slider-orientation='<?php echo $slider_orientation;?>'
                                data-slider-handle='<?php echo $slider_handle;?>'
                                data-slider-tooltip='always'
                                data-slider-reset='<?php echo $slider_reset; ?>'
                                data-slider-prefix='<?php echo $prefix; ?>'
                                data-slider-suffix='<?php echo $suffix; ?>'
                                data-separator='<?php echo $sSeparator;?>'
                            />

                            <?php if($slider_showminmax): ?>
                                <div class='pull-<?php if (getLanguageRTL(App()->language)): echo 'right'; else: echo 'left'; endif; ?> '>
                                    <span class='help-block'><?php echo $slider_min; ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if($slider_showminmax): ?>
                                <div class='pull-<?php if (getLanguageRTL(App()->language)): echo 'left'; else: echo 'right'; endif; ?> '>
                                    <span class='help-block'><?php echo $slider_max; ?></span>
                                </div>
                            <?php endif; ?>

                        </div>
                        <?php if (!empty($sliderright) || !empty($sliderleft)): ?>
                            <span class='pull-right col-xs-12 col-sm-3 slider-right-span'><?php echo $sliderright;?></span>
                        <?php endif; ?>

                    </div>
                    <?php if ($slider_reset): ?>
                        <div class='col-xs-2'>
                            <div id="answer<?php echo $myfname; ?>_resetslider" class='pull-left btn btn-default'>
                                <span class='fa fa-times slider-reset' aria-hidden='true'></span>&nbsp;<?php eT("Reset"); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>


        </div>
        <input type="hidden" name="<?php echo $myfname;?>" onchange="<?php echo $checkconditionFunction; ?>" id="answer<?php echo $myfname; ?>"  value="<?php echo ($dispVal ? $dispVal : null);?>" />
    </div> <!-- form group -->
</div>

<?php if($sliders): ?>
    <div>
    <style scoped>
    /**
    * Slider custom handle
    */
    .slider-handle.custom
    {
        background: transparent none; /* You can customize the handle and set a background image */
    }
    .slider-handle.custom::before
    {
        line-height: 20px;
        font-size: 20px;
        font-family: FontAwesome;
        content: '\<?php echo $slider_custom_handle;?>';  /*unicode character ;*/
    }
    </style>
    </div>
    <script type='text/javascript'>
            // Most of this javascript is here to handle the fact that bootstrapSlider need numerical value in the input
            // It can't accept "NULL" nor anyother thousand separator than "." (else it become a string)
            // See : https://github.com/LimeSurvey/LimeSurvey/blob/master/scripts/bootstrap-slider.js#l1453-l1461
            // If the bootstrapSlider were updated, most of this javascript would not be necessary.
            $(document).ready(function(){
                // Set of the needed informations for the slider
                var myfname = '<?php echo $myfname; ?>';
                var $inputEl = $('#slider_answer' + myfname);
                var $resultEl = $('#answer' + myfname);
                var $prefix = $inputEl.data('slider-prefix');
                var $suffix = $inputEl.data('slider-suffix');
                var $separator = $inputEl.data('separator');
                var regExpTest = new RegExp(/^-?[0-9]+(.|,)?[0-9]*$/);
                // We start the slider, and provide it the formated value with prefix and suffix for its tooltip
                // Use closure for namespace, so we can use theSlider variable for all sliders.
                (function () {
                    var theSlider = $inputEl.bootstrapSlider({
                        value : <?php echo $sliderStart; ?>,
                        formatter: function (value) {
                            var displayValue = "";
                            if(regExpTest.test(value.toString())){
                                displayValue = value.toString().replace(/\./,$separator);
                            }
                            return $prefix + displayValue + $suffix;
                        },
                    });

                    // When user change the value of the slider :
                    // we need to show the tooltip (if it was hidden)
                    // and to update the value of the input element with correct format
                    theSlider.on('slideStart', function(){
                        $('#javatbd' + myfname).find('div.tooltip').show(); // Show the tooltip
                        value = $inputEl.val(); // We get the current value of the bootstrapSlider
                        //console.log('value', value);
                        displayValue = value.toString().replace('.',$separator); // We format it with the right separator
                        $resultEl.val(displayValue).trigger('change'); // We parse it to the element
                    });

                    theSlider.on('slideStop', function() {
                        value = $inputEl.val(); // We get the current value of the bootstrapSlider
                        //console.log('value', value);
                        displayValue = value.toString().replace('.',$separator); // We format it with the right separator
                        $inputEl.trigger('onkeyup');
                        $resultEl.val(displayValue).trigger('change');
                        //LEMrel<?php echo $qid; ?>() // We call the EM
                        //console.log('$resultEl: ', $resultEl.val());
                    });

                    // Click the reset button
                    $('#answer' + '<?php echo $myfname; ?>' + '_resetslider').on('click', function() {
                        $('#javatbd' + myfname).find('div.tooltip').hide();

                        // Position slider button at beginning
                        theSlider.bootstrapSlider('setValue', null);

                        // Set value to null
                        $inputEl.attr('value', '').trigger("keyup");
                        $resultEl.val('').trigger('change');
                        //LEMrel<?php echo $qid; ?>() // We call the EM
                    });

                    // On form submission, if user action is still on,
                    // we must force the value of the input to ''
                    // and force the thousand separator (this bug still affect 2.06)
                    $("form").submit(function (e) {
                        $('#javatbd<?php echo $myfname; ?> slider').hide(),
                        $inputEl.bootstrapSlider('destroy');
                        return true;
                    });
                    $("#vmsg_<?php echo $qid;?>_default").text('<?php eT('Please click and drag the slider handles to enter your answer.');?>');
                })();
            });
    </script>
<?php endif; ?>
<!-- end of answer_row -->
