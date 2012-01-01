<div class='header ui-widget-header'><?php echo $clang->gT("Import survey resources"); ?></div>
<div class='messagebox ui-corner-all'>
    <strong><?php echo $clang->gT("Imported Resources for"); ?>" SID:</strong> <?php echo $surveyid; ?><br /><br />
    <div class="<?php echo $statusClass; ?>">
        <?php echo $status; ?>
    </div>
    <br />
    <strong>
        <u><?php echo $clang->gT("Resources Import Summary"); ?></u>
    </strong>
    <br />
    <?php echo $clang->gT("Total Imported files"); ?>: <?php echo count($aImportedFilesInfo); ?><br />
    <?php echo $clang->gT("Total Errors"); ?>: <?php echo $count($aErrorFilesInfo); ?><br />
    <div class="successheader"><?php echo !empty($aImportedFilesInfo) && empty($aErrorFilesInfo)? $clang->gT("Success") : $clang->gT("Partial"); ?></div><br /><br />
    <?php echo $clang->gT("File upload succeeded.") ?><br /><br />
    <?php echo $clang->gT("Reading file...") ?><br /><br />
<?php
if (!empty($aImportedFilesInfo))
{
?>
    <strong><?php echo $clang->gT("Imported Files List") ?>:</strong><br />
    <ul>
<?php
    foreach ($aImportedFilesInfo as $entry)
    {
        echo CHtml::tag('li', array(), $clang->gT("File") . ': ' . $entry["filename"]);
    }
?>
    </ul>
<?php
}
?>
<?php
if (!empty($aErrorFilesInfo))
{
?>
    <strong><?php echo $clang->gT("Error Files List") ?>:</strong><br />
    <ul>
<?php
    foreach ($aErrorFilesInfo as $entry)
    {
        echo CHtml::tag('li', array(), $clang->gT("File") . ': ' . $entry["filename"]);
    }
?>
    </ul>
<?php
}
?>
    <input type='submit' value='<?php echo $clang->gT("Back"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/survey/sa/editsurveysettings/surveyid/' . $surveyid); ?>', '_top')" />\n";
</div>
