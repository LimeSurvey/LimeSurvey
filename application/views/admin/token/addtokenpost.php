<div class='header ui-widget-header'><?php $clang->eT("Add token entry"); ?></div>
<div class='messagebox ui-corner-all'>
    <?php if ($success)
    { ?>
        <div class='successheader'><?php $clang->eT("Success"); ?></div>
        <br /><?php $clang->eT("New token was added."); ?><br /><br />
        <input type='button' value='<?php $clang->eT("Display tokens"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid"); ?>', '_top')" /><br />
        <input type='button' value='<?php $clang->eT("Add another token entry"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/addnew/surveyid/$surveyid"); ?>', '_top')" /><br />
<?php }
else
{ ?>
        <div class='warningheader'><?php $clang->eT("Failed"); ?></div>
        <br /><?php $clang->eT("There is already an entry with that exact token in the table. The same token cannot be used in multiple entries."); ?><br /><br />
        <input type='button' value='<?php $clang->eT("Display tokens"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid"); ?>', '_top')" /><br />
        <input type='button' value='<?php $clang->eT("Add new token entry"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/addnew/surveyid/$surveyid"); ?>', '_top')" /><br />
<?php } ?>
</div>
