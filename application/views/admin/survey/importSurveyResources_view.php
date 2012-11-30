<div class='header ui-widget-header'><?php $clang->eT("Import survey resources"); ?></div>
<div class='messagebox ui-corner-all'>
    <div class="successheader"><?php $clang->eT("Success") ?></div><br />
    <?php $clang->eT("File upload succeeded.") ?><br /><br />
    <?php $clang->eT("Reading file..") ?><br /><br />
<?php
    $ImportListHeader = '';
    if (!count($aErrorFilesInfo) &&count($aImportedFilesInfo))
    {
        $status = $clang->gT("Success");
        $statusClass = 'successheader';
        $okfiles = count($aImportedFilesInfo);
        $errfiles=0;
        $ImportListHeader .= "<br /><strong><u>" . $clang->gT("Imported Files List") . ":</u></strong><br />\n";
    }
    elseif (count($aErrorFilesInfo) &&count($aImportedFilesInfo))
    {
        $status = $clang->gT("Partial");
        $statusClass = 'partialheader';
        $okfiles = count($aImportedFilesInfo);
        $errfiles = count($aErrorFilesInfo);
        $ErrorListHeader = "<br /><strong><u>" . $clang->gT("Error Files List") . ":</u></strong><br />\n";
        $ImportListHeader .= "<br /><strong><u>" . $clang->gT("Imported Files List") . ":</u></strong><br />\n";
    }
    else
    {
        $okfiles = 0;
        $status = $clang->gT("Error");
        $statusClass = 'warningheader';
        $errfiles = count($aErrorFilesInfo);
        $ErrorListHeader = "<br /><strong><u>" . $clang->gT("Error Files List") . ":</u></strong><br />\n";
    }
?>
    <strong><?php $clang->eT("Imported Resources for"); ?>" SID:</strong> <?php echo $surveyid; ?><br /><br />
    <div class="<?php echo $statusClass; ?>">
        <?php echo $status; ?>
    </div>
    <br />
    <strong>
        <u><?php $clang->eT("Resources Import Summary"); ?></u>
    </strong>
    <br />
    <?php $clang->eT("Total Imported files"); ?>: <?php echo count($okfiles); ?><br />
    <?php $clang->eT("Total Errors"); ?>: <?php echo count($errfiles); ?><br />
    <?php
if (!empty($aImportedFilesInfo))
{
?>
    <strong><?php $clang->eT("Imported Files List") ?>:</strong><br />
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
    echo $ErrorListHeader;
    foreach ($aErrorFilesInfo as $entry)
    {
        echo CHtml::tag('li', array(), $clang->gT("File") . ': ' . $entry['filename'] . " (" . $entry['status'] . ")");
    }
?>
    </ul>
<?php
}
?>
    <input type='submit' value='<?php $clang->eT("Back"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/survey/sa/editsurveysettings/surveyid/' . $surveyid); ?>', '_top')" />
</div>
