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
 * @var $slider_orientation
 * @var $slider_handle
 * @var $slider_reset
 * @var $sSeparator
 */
?>
<!-- question attribute "display_rows" is set -> we need a textarea to be able to show several rows -->
<div  id='javatbd<?php echo $myfname; ?>' class="question-item answer-item numeric-item  text-item <?php echo $extraclass;?>" <?php echo $sDisplayStyle;?>>
    <?php if($alert):?>
        <div class="alert alert-danger errormandatory"  role="alert">
            <?php echo $labelText;?>
        </div> <!-- alert -->
    <?php endif;?>
    <div class="form-group row">
        <label class='control-label col-xs-12 numeric-label' for="answer<?php echo $myfname; ?>">
            <?php echo $labelText;?>
        </label>
        <div class="col-xs-12 col-sm-4">
            <?php echo $sliderleft;?>
            <?php if(!$sliders): ?>
                <input
                    class="text form-control numeric <?php echo $kpclass;?>"
                    type="text"
                    size="<?php echo $tiwidth;?>"
                    name="<?php echo $myfname;?>"
                    id="answer<?php echo $myfname; ?>"
                    value="<?php echo $dispVal;?>"
                    onkeyup="<?php echo $checkconditionFunction; ?>"
                    title="<?php eT('Only numbers may be entered in this field.'); ?>";
                    <?php echo $maxlength; ?>
                />
            <?php else:?>
                <input
                    class="text form-control <?php echo $kpclass;?>"
                    type="text"
                    size="<?php echo $tiwidth;?>"
                    name="<?php echo $myfname;?>"
                    id="answer<?php echo $myfname; ?>"
                    value="<?php echo $dispVal;?>"
                    onkeyup="<?php echo $checkconditionFunction; ?>"
                    <?php echo $maxlength; ?>
                    data-slider-value="<?php echo $sUnformatedValue;?>"
                    data-slider-min='<?php echo $slider_min;?>'
                    data-slider-max='<?php echo $slider_max;?>'
                    data-slider-step='<?php echo $slider_step;?>'
                    data-slider-value='<?php echo $slider_default;?>'
                    data-slider-orientation='<?php echo $slider_orientation;?>'
                    data-slider-handle='<?php echo $slider_handle;?>'
                    data-slider-tooltip='always'
                    data-slider-reset='<?php echo $slider_reset; ?>'
                    data-slider-prefix='<?php echo $prefix; ?>'
                    data-slider-suffix='<?php echo $suffix; ?>'
                    data-separator='<?php echo $sSeparator;?>'
                />
            <?php endif;?>
            <?php echo $sliderright;?>
        </div>  <!-- xs-12 -->
        <div class='col-xs-12 col-sm-8'>
            <?php if ($slider_reset): ?>
                <span id="answer<?php echo $myfname; ?>_resetslider" class='btn btn-default fa fa-times slider-reset'>&nbsp;<?php eT("Reset"); ?></span>
            <?php endif; ?>
        </div>
        <input type="hidden" name="slider_user_no_action_<?php echo $myfname; ?>" id="slider_user_no_action_<?php echo $myfname; ?>" value="<?php echo $slider_user_no_action?>" />
    </div> <!-- form group -->
</div>

<?php if($sliders): ?>
    <div>
    <style scoped>
    /**
    * Slider custom handle
    */
    .slider-handle.custom {
    background: transparent none;
    /* You can customize the handle and set a background image */
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
        <!--
            // Most of this javascript is here to handle the fact that bootstrapSlider need numerical value in the input
            // It can't accept "NULL" nor anyother thousand separator than "." (else it become a string)
            // See : https://github.com/LimeSurvey/LimeSurvey/blob/master/scripts/bootstrap-slider.js#l1453-l1461
            // If the bootstrapSlider were updated, most of this javascript would not be necessary.
            $(document).ready(function(){
                // Set of the needed informations for the slider
                var myfname = '<?php echo $myfname; ?>';
                var $inputEl = $('#answer' + myfname);
                var $sliderNoActionEl = $('#slider_user_no_action_' + myfname);
                var $prefix = $inputEl.data('slider-prefix');
                var $suffix = $inputEl.data('slider-suffix');
                var $separator = $inputEl.data('separator');

                // We start the slider, and provide it the formated value with prefix and suffix for its tooltip
                var mySlider_<?php echo $myfname; ?> = $inputEl.bootstrapSlider({
                    formatter: function (value) {
                        displayValue = value.toString().replace('.',$separator);
                        return $prefix + displayValue + $suffix;
                    },
                });

                // If user no action is on, we hide the tooltip
                // And we set the value to null
                if($sliderNoActionEl.val()=="1")
                {
                    $('#javatbd' + myfname).find('div.tooltip').hide();
                    $inputEl.attr('value', '');
                }

                // When user change the value of the slider :
                // we need to show the tooltip (if it was hidden)
                // and to update the value of the input element with correct format
                mySlider_<?php echo $myfname; ?>.on('slideStop', function(){
                    $('#javatbd' + myfname).find('div.tooltip').show(); // Show the tooltip
                    $sliderNoActionEl.val(0); // The user did an action
                    value = $inputEl.val(); // We get the current value of the bootstrapSlider
                    displayValue = value.toString().replace('.',$separator); // We format it with the right separator
                    $inputEl.val(displayValue); // We parse it to the element
                    LEMrel<?php echo $qid; ?>() // We call the EM
                });
                $("#vmsg_<?php echo $qid;?>_default").text('<?php eT('Please click and drag the slider handles to enter your answer.');?>');
            });
        -->
    </script>
<?php endif; ?>
