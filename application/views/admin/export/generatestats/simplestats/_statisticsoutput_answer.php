<?php
/**
 * This view render the list of answers in top of the graph
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
<?php /*
<tr>
    <td align='center' ><?php echo $label[$i]; ?></td>
    <td align='center' ><?php echo $grawdata[$i]; ?></td>

    <?php if ($gdata[$i] === "N/A"): ?>
        <td  align='center' >
            <?php echo sprintf("%01.2f", $gdata[$i]); ?>%
        </td>
    <?php else: ?>
        <?php if($aggregated):?>
            <td  align='center' >
                <?php echo sprintf("%01.2f", $gdata[$i]); ?>%
            </td>
            <td  align='center' >
                <?php echo sprintf("%01.2f", $aggregatedPercentage); ?>%
            </td>
            <td align='center'>
                <strong>
                    <?php eT("Sum");?>&nbsp;&nbsp;<?php eT("Answers"); ?>
                </strong>
            </td>
            <td align='center'>
                <strong>
                    <?php echo $sumitems; ?>
                </strong>
            </td>
            <td align='center'>
                <strong>
                    <?php echo $sumpercentage; ?>%
                </strong>
            </td>
            <td align='center'>
                <strong>
                    <?php echo $sumpercentage; ?>%
                </strong>
            </td>
        <?php else: ?>
            <td align='center' >
                <?php echo sprintf("%01.2f", $gdata[$i]);?>%
            </td>
        <?php endif;?>
    <?php endif;?>
</tr>

<?php if ($gdata[$i] != "N/A"): ?>
    <?php if($aggregated): ?>
    <tr>
        <td align='center'>
            <strong>
                <?php eT("Number of cases");?>
            </strong>
        </td>
        <td align='center'>
            <strong>
                <?php echo $TotalCompleted; ?>
            </strong>
        </td>
        <td align='center'>
            <strong>
                <?php echo $casepercentage; ?>%
            </strong>
        </td>
        <td align='center'>
            &nbsp;
        </td>
    </tr>
    <?php endif; ?>
<?php endif; ?>


<?php if($extraline): ?>
    <tr>
        <td class='statisticsbrowsecolumn' colspan='3' style='display: none'>
            <div class='statisticsbrowsecolumn' id='columnlist_<?php echo $sColumnNameForView; ?>'>
        </td>
    </tr>
<?php endif; ?>
*/ ?>
