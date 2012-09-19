    </table>
    <input id='deleteanswer' name='deleteanswer' value='' type='hidden' />
</form>
<br />

<div class="header ui-widget-header"><?php $clang->eT('Interview time'); ?></div>
<table class="statisticssummary">
    <?php if ($result) { ?>
        <tr><th><?php $clang->eT('Average interview time:'); ?></th><td><?php echo $avgmin; ?> min. <?php echo $avgsec; ?> sec.</td></tr>
    <?php } ?>
    <?php if ($count) { ?>
        <tr><th><?php $clang->eT('Median:'); ?></th><td><?php echo $allmin; ?> min. <?php echo $allsec; ?> sec.</td></tr>
    <?php } ?>
</table>

