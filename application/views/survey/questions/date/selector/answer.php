<?php
/**
 * Date Html, selector style :
 * @var $name
 * @var $qid
 * @var $iLength
 * @var $dateoutput
 * @var $mindate
 * @var $maxdate
 * @var $dateformatdetails
 * @var $dateformatdetailsjs
 * @var $goodchars
 * @var $checkconditionFunction
 * @var $language
 * @var $hidetip
 */
?>

<!-- Date, selector layout -->

<!-- answer -->
<div class='question answer-item text-item date-item form-group'>
    <label for='answer<?php echo $name;?>' class='hide label'>
        <?php echo sprintf(gT('Date in the format: %s'), $dateformatdetails); ?>
    </label>

    <div class='col-xs-12 col-sm-4'>

        <?php /* Old input, not used since switching to Bootstrap DateTimePicker
        <input
            class='form-control'
            type="text"
            size="<?php echo $iLength;?>"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>"
            value="<?php echo $dateoutput;?>"
            maxlength="<?php echo $iLength;?>"
            onkeypress="<?php echo $goodchars;?>"
            onchange="<?php echo $checkconditionFunction;?>"
        />
        */
        ?>

        <?php $this->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                'name' => $name,
                'id' => "answer" . $name,
                'value' => $dateoutput,
                'pluginOptions' => array(
                    'format' => $dateformatdetailsjs,
                    'allowInputToggle' =>true,
                    'showClear' => true,
                            'tooltips' => array(
                                'clear'=> gT('Clear selection'),
                                'prevMonth'=> gT('Previous month'),
                                'nextMonth'=> gT('Next month'),
                                'selectYear'=> gT('Select year'),
                                'prevYear'=> gT('Previous year'),
                                'nextYear'=> gT('Next year'),
                                'selectDecade'=> gT('Select decade'),
                                'prevDecade'=> gT('Previous decade'),
                                'nextDecade'=> gT('Next decade'),
                                'prevCentury'=> gT('Previous century'),
                                'nextCentury'=> gT('Next century'),
                                'selectTime'=> gT('Select time')
                            ),
                    'locale' => convertLStoDateTimePickerLocale($language),

                    /**
                     * $maxdate and $mindate can be expressions from EM. In that case, set them to 1900.
                     * The expressions will be evaluated dynamically later (see divs at bottom of this page).
                     */
                    'maxDate' => $maxdate[0] == '{' ? '1900' : $maxdate,
                    'minDate' => $mindate[0] == '{' ? '1900' : $mindate,
                    'sideBySide' => true
                ),
                'htmlOptions' => array(
                    'onkeypress' => $goodchars,
                    'onchange' => "$checkconditionFunction"
                )
            ));
        ?>
    </div>
    <script>
        $(document).ready(function() {
            // Min and max date sets default value, so use this to override it
            $('#answer<?php echo $name; ?>').val('<?php echo $dateoutput; ?>');
        });
    </script>

    <input
        type='hidden'
        name="dateformat<?php echo $name;?>"
        id="dateformat<?php echo $name;?>"
        value="<?php echo $dateformatdetailsjs;?>"
    />

    <input
        type='hidden'
        name="datelanguage<?php echo $name;?>"
        id="datelanguage<?php echo $name;?>"
        value="<?php echo $language;?>"
    />

    <?php if($hidetip):?>
        <div class='col-xs-12'>
            <p class="tip help-block">
                <?php echo sprintf(gT('Format: %s'),$dateformatdetails); ?>
            </p>
        </div>
    <?php endif;?>

</div>

<div class='hidden nodisplay' style='display:none'>
    <!-- Obs: No spaces in the div - it will mess up Javascript string parsing -->
    <div id='datemin<?php echo $name;?>'><?php echo $mindate; ?></div>
    <div id='datemax<?php echo $name;?>'><?php echo $maxdate; ?></div>
</div>

<input type='hidden' class="namecontainer" data-name="<?php echo $qid; ?>" />

<!-- end of answer -->

<script>
$(document).ready(function() {
    $('#answer' + '<?php echo $name; ?>' + '_datetimepicker').on('dp.show', function(ev) {
        setPickerOptions('<?php echo $name; ?>');
    });
});
</script>
