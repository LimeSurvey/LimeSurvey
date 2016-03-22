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
                    data-slider-startvalue='<?php echo $slider_startvalue; ?>'
                    data-slider-displaycallout='<?php echo $slider_displaycallout; ?>'
                />
            <?php endif;?>
            <?php echo $sliderright;?>
        </div>  <!-- xs-12 -->
        <div class='col-xs-12 col-sm-8'>
            <?php if ($slider_reset): ?>
                <span id="answer<?php echo $myfname; ?>_resetslider" class='btn btn-default fa fa-times slider-reset'>&nbsp;<?php eT("Reset"); ?></span>
            <?php endif; ?>
        </div>
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
            // TODO: This code should be moved to e.g. numerical-slider.js
            $(document).ready(function(){
                var myfname = '<?php echo $myfname; ?>';
                var dispVal = parseFloat('<?php echo $dispVal; ?>');
                var id = '#answer' + myfname;
                var resetSliderId = id + '_resetslider';
                var slider_prefix = $(id).attr('data-slider-prefix')
                var slider_suffix = $(id).attr('data-slider-suffix')

                var mySlider_<?php echo $myfname; ?> = $(id).bootstrapSlider({
                    formatter: function (value) {
                        var displayValue = '' + value;
                        var displayValue = displayValue.replace(/\./,LSvar.sLEMradix);
                        return slider_prefix + displayValue + slider_suffix;
                    },
                    value: parseFloat('<?php echo $dispVal; ?>')
                });

                // Hide tooltip
                $('#javatbd' + myfname).find('.tooltip').hide();

                // Set value at init
                var slider_startvalue = $(id).attr('data-slider-startvalue');
                if (slider_startvalue === 'NULL' && !isNaN(dispVal)) {
                    $(id).attr('stringvalue', dispVal);  // stringvalue is the value handed to EM, since Bootstrap slider can't handle ","
                    LEMrel<?php echo $qid; ?>();
                    $(id).triggerHandler("keyup");

                    // Show tooltip
                    $('#javatbd' + myfname).find('.tooltip').show();
                }
                else if (slider_startvalue !== 'NULL') {
                    var displayValue = '' + slider_startvalue;
                    var displayValue = displayValue.replace(/\./,LSvar.sLEMradix);
                    $(id).attr('stringvalue', displayValue);  // stringvalue is the value handed to EM, since Bootstrap slider can't handle ","
                    LEMrel<?php echo $qid; ?>();
                    $(id).triggerHandler("keyup");

                    // Show tooltip
                    $('#javatbd' + myfname).find('.tooltip').show();
                }

                // Reset on click on .slider-reset
                $(resetSliderId).on("click", function() {
                    var slider_startvalue = $(id).attr('data-slider-startvalue');

                    // Callout is the thing above the slider that displays the 
                    // current number. Boolean variable.
                    var slider_displaycallout = $(id).attr('data-slider-displaycallout');

                    if(slider_startvalue == "NULL") {
                        $(id).attr('value', '');  // Never use bootstrapSlider('setValue', ...), because it won't accept empty string (will convert to 0)
                        $(id).attr('stringvalue', '');

                        // Hide tooltip
                        $('#javatbd' + myfname).find('.tooltip').hide();
                    }
                    else {
                        $(id).bootstrapSlider('setValue', parseFloat(slider_startvalue));
                        $(id).attr('stringvalue', slider_startvalue);

                        // Show tooltip
                        $('#javatbd' + myfname).find('.tooltip').show();
                    }

                    if(slider_displaycallout && slider_startvalue != "NULL") {
                        $(id).attr('stringvalue', slider_prefix + slider_startvalue.replace(/\./,LSvar.sLEMradix) + slider_suffix);
                        $(id).bootstrapSlider('setValue', parseFloat(slider_startvalue));

                        // Show tooltip
                        $('#javatbd' + myfname).find('.tooltip').show();
                    }
                    else {
                        $(id).attr('value', '');
                        $(id).attr('stringvalue', '');

                        // Hide tooltip
                        $('#javatbd' + myfname).find('.tooltip').hide();
                    }

                    LEMrel<?php echo $qid; ?>();
                    $(id).triggerHandler("keyup"); // Needed for EM

                });

                mySlider_<?php echo $myfname; ?>.on('slideStop', function(event) {
                    var displayValue = '' + event.value;  // Type-cast to string
                    var displayValue = displayValue.replace(/\./,LSvar.sLEMradix);

                    // fixnum_checkconditions can't handle dot if it expects comma, and
                    // Bootstrap won't save comma in value. So we need another attribute.
                    $(id).attr('stringvalue', displayValue);

                    LEMrel<?php echo $qid; ?>();

                    // EM needs this
                    $(id).triggerHandler("keyup");

                });
                mySlider_<?php echo $myfname; ?>.on('slideStart', function(event) {
                    // Show tooltip
                    $('#javatbd' + myfname).find('.tooltip').show();
                });

                $("#vmsg_<?php echo $qid;?>_default").text('<?php eT('Please click and drag the slider handles to enter your answer.');?>');
            });
        -->
    </script>
<?php endif; ?>
