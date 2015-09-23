<div class='header ui-widget-header'><?php eT("Import survey resources"); ?></div>
<div class='messagebox ui-corner-all'>
    <div class="successheader"><?php eT("Success") ?></div><br />
    <?php eT("File upload succeeded.") ?><br /><br />
    <?php eT("Reading file..") ?><br /><br />
<?php
    $ImportListHeader = '';
    if (!count($aErrorFilesInfo) &&count($aImportedFilesInfo))
    {
        $status = gT("Success");
        $statusClass = 'successheader';
        $okfiles = count($aImportedFilesInfo);
        $errfiles=0;
        $ImportListHeader .= "<br /><strong><u>" . gT("Imported Files List") . ":</u></strong><br />\n";
    }
    elseif (count($aErrorFilesInfo) &&count($aImportedFilesInfo))
    {
        $status = gT("Partial");
        $statusClass = 'partialheader';
        $okfiles = count($aImportedFilesInfo);
        $errfiles = count($aErrorFilesInfo);
        $ErrorListHeader = "<br /><strong><u>" . gT("Error Files List") . ":</u></strong><br />\n";
        $ImportListHeader .= "<br /><strong><u>" . gT("Imported Files List") . ":</u></strong><br />\n";
    }
    else
    {
        $okfiles = 0;
        $status = gT("Error");
        $statusClass = 'warningheader';
        $errfiles = count($aErrorFilesInfo);
        $ErrorListHeader = "<br /><strong><u>" . gT("Error Files List") . ":</u></strong><br />\n";
    }
?>
    <strong><?php eT("Imported Resources for"); ?>" SID:</strong> <?php echo $surveyid; ?><br /><br />
    <div class="<?php echo $statusClass; ?>">
        <?php echo $status; ?>
    </div>
    <br />
    <strong>
        <u><?php eT("Resources Import Summary"); ?></u>
    </strong>
    <br />
    <?php eT("Total Imported files"); ?>: <?php echo count($okfiles); ?><br />
    <?php eT("Total Errors"); ?>: <?php echo count($errfiles); ?><br />
    <?php
if (!empty($aImportedFilesInfo))
{
?>
    <strong><?php eT("Imported Files List") ?>:</strong><br />
    <ul>
<?php
    foreach ($aImportedFilesInfo as $entry)
    {
        echo CHtml::tag('li', array(), gT("File") . ': ' . $entry["filename"]);
    }
?>
    </ul>
<?php
}
?>
<?php
if (!empty($aErrorFilesInfo))
{
    echo $ErrorListHeader;
    foreach ($aErrorFilesInfo as $entry)
    {
        echo CHtml::tag('li', array(), gT("File") . ': ' . $entry['filename'] . " (" . $entry['status'] . ")");
    }
?>
    </ul>
<?php
}
?>
    <input type='submit' value='<?php eT("Back"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/survey/sa/editsurveysettings/surveyid/' . $surveyid); ?>', '_top')" />
</div>
