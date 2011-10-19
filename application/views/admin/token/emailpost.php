<div class='messagebox ui-corner-all'>
<div class='header ui-widget-header'>
<?php echo $clang->gT("Sending invitations..."); ?>
</div>
<?php
if ($tokenid) {echo " (".$clang->gT("Sending to Token ID").":&nbsp;{$tokenid})";}
if ($tokenids) {echo " (".$clang->gT("Sending to Token IDs").":&nbsp;".implode(", ", $tokenids).")";}
?>
<br />
<ul>
<?php echo $tokenoutput;?>
</div>