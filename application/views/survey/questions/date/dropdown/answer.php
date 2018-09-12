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
<div class="question date answer-item dropdown-item date-item">

    <?php
        // rows/*.php
        echo $sRows;
    ?>

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
</div>

<input type="hidden" id="dateformat<?php echo $name; ?>" value="<?php echo $dateformatdetails; ?>"/>
<!-- end of answer -->
