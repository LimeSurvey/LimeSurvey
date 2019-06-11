<?php
/**
 * This view render the arithmetic mean
 *
 * @var $am
 * @var $stddev
 */
?>
<tr><td colspan="4"><?php eT("Descriptive statistics"); ?></td></tr>
<tr>
    <td align='center'>
        <?php eT("Arithmetic mean"); ?>
    </td>
    <td>
        &nbsp;
    </td>
    <td align='center' colspan="2">
        <?php echo $am; ?>
    </td>
</tr>
<tr>
    <td align='center'>
        <?php eT("Standard deviation"); ?>
    </td>
    <td>
        &nbsp;
    </td>
    <td align='center' colspan="2">
        <?php echo $stddev; ?>
    </td>
</tr>
