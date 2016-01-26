<?php
/**
 * Date Html, dropdown style : Footer
 * @var $name $ia[1]
 * @var $dateoutput                     htmlspecialchars($dateoutput,ENT_QUOTES,'utf-8')
 * @var $checkconditionFunction         $checkconditionFunction.'(this.value, this.name, this.type)
 * @var $dateformatdetails              $dateformatdetails['jsdate']
 */
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
        alt="<?php eT('Answer'); ?>"
        onchange="<?php echo $checkconditionFunction; ?>"
        title="<?php sprintf(gT('Date in the format : %s'),$dateformatdetails['dateformat']);?>"
/>
</p> <!-- Date item -->
<input type="hidden" id="dateformat<?php echo $name; ?>" value="<?php echo $dateformatdetails; ?>"/>
