        </table>
        <input id='deleteanswer' name='deleteanswer' value='' type='hidden' />
    </form>
    <?php if ($statistics['count']) { ?>
    <div class="header ui-widget-header"><?php eT('Interview time'); ?></div>
        <table class="statisticssummary">
                <tr><th><?php eT('Average interview time:'); ?></th><td title=""><?php printf(gT("%s min. %s sec."),$statistics['avgmin'],$statistics['avgsec']) ?></td></tr>
                <tr><th><?php eT('Median:'); ?></th><td><?php printf(gT("%s min. %s sec."),$statistics['allmin'],$statistics['allsec']) ?> </td></tr>
        </table>
    <?php } ?>

</div>
