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
<div class='<?php echo $coreClass;?> form-group form-inline'>
    <label for='answer<?php echo $name;?>' class='sr-only control-label'>
        <?php echo sprintf(gT('Date in the format: %s'), $dateformatdetails); ?>
    </label>
    <div id="answer<?php echo $name; ?>_datetimepicker" class="input-group answer-item date-timepicker-group" data-basename="<?php echo $name;?>"><!-- data-basename used in js function -->
        <?php echo CHtml::textField($name,$dateoutput,array(
            'id' => "answer" . $name,
            'class'=>"form-control date-control date",
            'aria-describedby' => "ls-question-text-{$name}",
        )); ?>
        <div class="input-group-addon btn btn-primary">
            <i class="fa fa-calendar" aria-hidden="true"></i><span class="sr-only"><?php echo gT("Open the date time chooser"); ?></span>
        </div>
    </div>
    <?php if($hidetip):?>
    <p class="tip help-block">
        <?php echo sprintf(gT('Format: %s'),$dateformatdetails); ?>
    </p>
    <?php endif;?>

</div>

<div class='hidden' style='display:none'>
    <!-- Obs: No spaces in the div - it will mess up Javascript string parsing -->
    <div id='datemin<?php echo $name;?>'><?php echo $mindate; ?></div>
    <div id='datemax<?php echo $name;?>'><?php echo $maxdate; ?></div>
</div>
<?php
    /* Set option for launch, can not set to default : maybe more than one datetimepicker in page */
    $aJsonOption=array(
        'format' => $dateformatdetailsjs,
        /* get the same default value than qanda_helper */
        'minDate' => $mindate[0] == '{' ? '1900-01-01' : $mindate,
        'maxDate' => $maxdate[0] == '{' ? '2037-12-31' : $maxdate,
    );
    $jsonOptions=json_encode($aJsonOption);
    App()->getClientScript()->registerScript("doDatetimepicker_{$name}","jQuery('#answer{$name}_datetimepicker').datetimepicker({$jsonOptions});",LSYii_ClientScript::POS_POSTSCRIPT);
     // Min and max date sets default value, so use this to override it
    App()->getClientScript()->registerScript("resetDate{$name}","$('#answer{$name}').val('{$dateoutput}');;",LSYii_ClientScript::POS_POSTSCRIPT);
    ?>
<!-- end of answer -->
