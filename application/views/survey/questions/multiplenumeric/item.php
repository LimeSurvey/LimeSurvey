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
                <?php echo "IKI" . $dispVal;?>
                <input
                    class="text form-control <?php echo $kpclass;?>"
                    type="text"
                    size="<?php echo $tiwidth;?>"
                    name="<?php echo $myfname;?>"
                    id="answer<?php echo $myfname; ?>"
                    value="<?php echo $dispVal;?>"
                    onkeyup="<?php echo $checkconditionFunction; ?>"
                    <?php echo $maxlength; ?>
                    data-slider-value="<?php // echo $dispVal;?>5.2"
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
            $(document).ready(function(){
                var myfname = '<?php echo $myfname; ?>';
                var $inputEl = $('#answer' + myfname);
                console.log( $inputEl.attr('id'));
                var $prefix = $inputEl.data('slider-prefix');
                var $suffix = $inputEl.data('slider-suffix');
                var $separator = $inputEl.data('separator');

                var mySlider_<?php echo $myfname; ?> = $inputEl.bootstrapSlider({
                    formatter: function (value) {
                        displayValue = value.toString().replace('.',$separator);
                        return $prefix + displayValue + $suffix;
                    },
                });

                mySlider_<?php echo $myfname; ?>.on('slideStop', function(){
                    value = $inputEl.val();
                    displayValue = value.toString().replace('.',$separator);
                    $inputEl.val(displayValue);
                    //console.log('LA '+$value);
                    LEMrel<?php echo $qid; ?>()
                });
                $("#vmsg_<?php echo $qid;?>_default").text('<?php eT('Please click and drag the slider handles to enter your answer.');?>');

                // Hide tooltip
//                $('#javatbd' + myfname).find('.tooltip').hide();

                $("#vmsg_<?php echo $qid;?>_default").text('<?php eT('Please click and drag the slider handles to enter your answer.');?>');
            });
        -->
    </script>
<?php endif; ?>
