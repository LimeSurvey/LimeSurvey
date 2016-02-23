<?php
/**
 * Date Html, dropdown style : Footer
 * @var $name $ia[1]
 * @var $dateoutput                     htmlspecialchars($dateoutput,ENT_QUOTES,'utf-8')
 * @var $checkconditionFunction         $checkconditionFunction.'(this.value, this.name, this.type)
 * @var $dateformatdetails              $dateformatdetails['jsdate']
 * @var $dateformat                      $dateformatdetails['dateformat']
 */
?>
<!-- Date footer-->
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
</p> <!-- Date item -->
<input type="hidden" id="dateformat<?php echo $name; ?>" value="<?php echo $dateformatdetails; ?>"/>
