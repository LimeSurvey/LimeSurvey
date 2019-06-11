<?php
/**
 * This view render gross total row
 *
 * @var $label
 * @var $grawdata
 * @var $gdata
 * @var $i
 * @var $extraline
 * @var $sColumnNameForView
 * potentialy not set when controller will try to load them in $aData (so will need defaut values)
 * @var $aggregated
 * @var $aggregatedPercentage
 * @var $sumitems
 * @var $sumpercentage
 * @var $TotalCompleted
 * @var $casepercentage
 */
?>

<!-- _statisticsoutput_gross_total -->
<tr>

    <tr>
        <td align='right'>
            <strong>
                <?php eT("Total");?>(<?php eT("gross"); ?>)
            </strong>
        </td>
        <td align='center'>
            <strong>
                <?php echo $sumallitems; ?>
            </strong>
        </td>
        <td align='right' colspan="2">
            <strong>
            <?php if ($sumallitems > 0){ ?>
                100.00%
            <?php } else { ?>
                0.00%
                <?php } ?>
            </strong>
        </td>
    </tr>



<!-- end of _statisticsoutput_gross_total -->
