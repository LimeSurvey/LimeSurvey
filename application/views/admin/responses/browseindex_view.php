<table class='statisticssummary'>
  <caption class="header ui-widget-header"><?php eT("Response summary"); ?></caption>
    <tfoot><tr><th><?php eT("Total responses"); ?></th><td><?php echo $num_total_answers; ?></td></tr></tfoot>
    <tbody>
        <tr><th><?php eT("Full responses"); ?></th><td><?php echo $num_completed_answers; ?></td></tr>
        <tr><th><?php eT("Incomplete responses"); ?></th><td><?php echo ($num_total_answers - $num_completed_answers); ?></td></tr>
    </tbody>
</table>
<?php if(isset($with_token)){ ?>
<table class='statisticssummary'>
  <caption class="header ui-widget-header"><?php eT("Token summary"); ?></caption>
    <tfoot><tr><th><?php eT("Total records in this token table"); ?></th><td><?php echo $tokeninfo['count']; ?></td></tr></tfoot>
    <tbody>
        <tr><th><?php eT("Total invitations sent"); ?></th><td><?php echo $tokeninfo['sent']; ?></td></tr>
        <tr><th><?php eT("Total surveys completed"); ?></th><td><?php echo $tokeninfo['completed']; ?></td></tr>
        <tr><th><?php eT("Total with no unique Token"); ?></th><td><?php echo $tokeninfo['invalid'] ?></td></tr>
    </tbody>
</table>
<?php } ?>
