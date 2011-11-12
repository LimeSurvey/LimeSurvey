<div class='header ui-widget-header'><?php echo $clang->gT("Response summary");?></div>
<p><table class='statisticssummary'>
<tfoot><tr><th><?php echo $clang->gT("Total responses:");?></th><td><?php echo $num_total_answers;?></td></tr></tfoot>
<tbody>
<tr><th><?php echo $clang->gT("Full responses:");?></th><td><?php echo $num_completed_answers;?></td></tr>
<tr><th><?php echo $clang->gT("Incomplete responses:");?></th><td><?php echo ($num_total_answers-$num_completed_answers);?></td></tr></tbody>
</table>