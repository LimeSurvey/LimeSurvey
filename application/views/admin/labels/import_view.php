<div class='header ui-widget-header'><?php echo $clang->gT("Import Label Set") ?></div>
<div class='messagebox ui-corner-all'>
    <div class='successheader'><?php echo $clang->gT("Success") ?></div><br />
    <?php echo $clang->gT("File upload succeeded.") ?><br /><br />
    <?php echo $clang->gT("Reading file..") ?><br /><br />
<?php
    if (count($aImportResults['warnings']) > 0)
    {
?>
        <br />
        <div class='warningheader'><?php echo $clang->gT("Warnings") ?></div>
        <ul>
<?php
        foreach ($aImportResults['warnings'] as $warning)
        {
?>
            <li><?php echo $warning ?></li>
<?php
        }
?>
        </ul>
<?php
    }
?>
    <br />
    <div class='successheader'><?php echo $clang->gT("Success") ?></div><br />
    <strong><u><?php echo $clang->gT("Label set import summary") ?></u></strong><br />
    <ul style="text-align:left;">
        <li><?php echo $clang->gT("Label sets") . ": {$aImportResults['labelsets']}" ?></li>
        <li><?php echo $clang->gT("Labels") . ": {$aImportResults['labels']}" ?></li>
    </ul>
    <strong><?php echo $clang->gT("Import of label set(s) is completed.") ?></strong><br /><br />
    <input type='submit' value='<?php $clang->gT("Return to label set administration") ?>' onclick="window.open('<?php echo $this->createUrl('admin/labels/sa/view') ?>', '_top')" />
</div><br />