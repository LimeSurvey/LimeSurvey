<div class='header ui-widget-header'><?php $clang->eT("Response summary"); ?></div>
<p><table class='statisticssummary'>
    <tfoot><tr><th><?php $clang->eT("Total responses:"); ?></th><td><?php echo $num_total_answers; ?></td></tr></tfoot>
    <tbody>
        <tr><th><?php $clang->eT("Full responses:"); ?></th><td><?php echo $num_completed_answers; ?></td></tr>
        <tr><th><?php $clang->eT("Incomplete responses:"); ?></th><td><?php echo ($num_total_answers - $num_completed_answers); ?></td></tr></tbody>
</table>
