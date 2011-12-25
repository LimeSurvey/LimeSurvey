    </table>
    <input id='deleteanswer' name='deleteanswer' value='' type='hidden' />
</form>
<br />

<div class="header ui-widget-header"><?php $clang->eT('Interview time'); ?></div>
<table class="statisticssummary">
    <?php if ($result) { ?>
        <tr><th><?php $clang->eT('Average interview time:'); ?></th><td><?php echo $aData['avgmin']; ?> min. <?php echo $aData['avgsec']; ?> sec.</td></tr>
    <?php } ?>
    <?php if ($count) { ?>
        <tr><th><?php $clang->eT('Median:'); ?></th><td><?php echo $aData['allmin']; ?> min. <?php echo $aData['allsec']; ?> sec.</td></tr>
    <?php } ?>
</table>

<div class='header ui-widget-header'><?php $clang->eT('Timings'); ?></div>
<?php $clang->eT("Timing saving is disabled or the timing table does not exist. Try to reactivate survey."); ?>
