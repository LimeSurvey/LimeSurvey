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
    <td align='center'>
        <?php echo $am; ?>
    </td>
    <td>
        &nbsp;
    </td>
</tr>
<tr>
    <td align='center'>
        <?php eT("Standard deviation"); ?>
    </td>
    <td>
        &nbsp;
    </td>
    <td align='center'>
        <?php echo $stddev; ?>
    </td>
    <td>
        &nbsp;
    </td>
</tr>
