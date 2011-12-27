<div class='header ui-widget-header'><?php echo $clang->gT("Add token entry"); ?></div>
<div class='messagebox ui-corner-all'>
    <?php if ($success)
    { ?>
        <div class='successheader'><?php echo $clang->gT("Success"); ?></div>
        <br /><?php echo $clang->gT("New token was added."); ?><br /><br />
        <input type='button' value='<?php echo $clang->gT("Display tokens"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid"); ?>', '_top')" /><br />
        <input type='button' value='<?php echo $clang->gT("Add another token entry"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/addnew/surveyid/$surveyid"); ?>', '_top')" /><br />
<?php }
else
{ ?>
        <div class='warningheader'><?php echo $clang->gT("Failed"); ?></div>
        <br /><?php echo $clang->gT("There is already an entry with that exact token in the table. The same token cannot be used in multiple entries."); ?><br /><br />
        <input type='button' value='<?php echo $clang->gT("Display tokens"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid"); ?>', '_top')" /><br />
        <input type='button' value='<?php echo $clang->gT("Add new token entry"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/addnew/surveyid/$surveyid"); ?>', '_top')" /><br />
<?php } ?>
</div>
