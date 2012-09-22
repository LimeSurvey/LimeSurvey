    </table>
    <input id='deleteanswer' name='deleteanswer' value='' type='hidden' />
</form>
<br />
<?php if ($statistics['count']) { ?>
<div class="header ui-widget-header"><?php $clang->eT('Interview time'); ?></div>
    <table class="statisticssummary">
            <tr><th><?php $clang->eT('Average interview time:'); ?></th><td title=""><?php printf($clang->gT("%s min. %s sec."),$statistics['avgmin'],$statistics['avgsec']) ?></td></tr>
            <tr><th><?php $clang->eT('Median:'); ?></th><td><?php printf($clang->gT("%s min. %s sec."),$statistics['allmin'],$statistics['allsec']) ?> </td></tr>
    </table>
<?php } ?>

