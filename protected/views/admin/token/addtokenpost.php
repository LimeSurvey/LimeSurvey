<div class='header ui-widget-header'><?php eT("Add token entry"); ?></div>
<div class='messagebox ui-corner-all'>
    <?php if ($success)
    { ?>
        <div class='successheader'><?php eT("Success"); ?></div>
        <br /><?php eT("New token was added."); ?><br /><br />
        <input type='button' value='<?php eT("Display tokens"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid"); ?>', '_top')" /><br />
        <input type='button' value='<?php eT("Add another token entry"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/addnew/surveyid/$surveyid"); ?>', '_top')" /><br />
<?php }
else
{ ?>
        <div class='warningheader'><?php eT("Failed"); ?></div>
        <br /><?php eT("There is already an entry with that exact token in the table. The same token cannot be used in multiple entries."); ?><br /><br />
        <input type='button' value='<?php eT("Display tokens"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid"); ?>', '_top')" /><br />
        <input type='button' value='<?php eT("Add new token entry"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/addnew/surveyid/$surveyid"); ?>', '_top')" /><br />
<?php } ?>
</div>
