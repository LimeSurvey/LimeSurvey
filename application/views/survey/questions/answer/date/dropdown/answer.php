<?php
/**
 * Date Html, dropdown style
 *
 * @var $sRows      : the rows, generated with the view rows/*.php
 *
 * @var $name
 * @var $dateoutput
 * @var $checkconditionFunction
 * @var $dateformatdetails
 * @var $dateformat
 */
?>

<!-- Date, dropdown layout -->

<!-- answer -->
<div class="<?php echo $coreClass;?> form-group form-inline" role="group" aria-labelledby="ls-question-text-<?php echo $name; ?>">
    <?php
        // rows/*.php
        echo $sRows;
    ?>
</div>

<!-- For Expression Manager ? -->
<input
        class="text"
        type="text"
        size="10"
        name="<?php echo $name; ?>"
        style="display: none"
        id="answer<?php echo $name; ?>"
        value="<?php echo $dateoutput;?>"
        maxlength="10"
        onchange="<?php echo $checkconditionFunction; ?>"
        title="<?php echo sprintf(gT('Date in the format : %s'),$dateformat);?>"
/>
<input type="hidden" id="dateformat<?php echo $name; ?>" value="<?php echo $dateformatdetails; ?>"/>
<!-- end of answer -->
