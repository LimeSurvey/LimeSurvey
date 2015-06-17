<div class='header ui-widget-header'><?php eT("Import label set resources") ?></div>
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
        $ImportListHeader .= "<br /><strong><u>" . gT("Imported files list") . ":</u></strong><br />\n";
        $ErrorListHeader = '';
    }
    elseif (count($aErrorFilesInfo) &&count($aImportedFilesInfo))
    {
        $status = gT("Partial");
        $statusClass = 'partialheader';
        $okfiles = count($aImportedFilesInfo);
        $errfiles = count($aErrorFilesInfo);
        $ErrorListHeader = "<br /><strong><u>" . gT("Error files list") . ":</u></strong><br />\n";
        $ImportListHeader .= "<br /><strong><u>" . gT("Imported files list") . ":</u></strong><br />\n";
    }
    else
    {
        $okfiles = 0;
        $status = gT("Error");
        $statusClass = 'warningheader';
        $errfiles = count($aErrorFilesInfo);
        $ImportListHeader = '';
        $ErrorListHeader = "<br /><strong><u>" . gT("Error files list") . ":</u></strong><br />\n";
    }
?>

    <strong><?php eT("Imported resources for") ?> LID:</strong><?php echo $lid ?><br /><br />
    <div class="<?php echo $statusClass ?>"><?php echo $status ?></div><br />
    <strong><u><?php eT("Resources import summary") ?></u></strong><br />
    <?php echo gT("Total imported files") . ": $okfiles" ?><br />
    <?php echo gT("Total errors") . ": $errfiles" ?><br />
    <?php echo $ImportListHeader; ?>

 <?php
    foreach ($aImportedFilesInfo as $entry)
    {
 ?>
        <li><?php echo gT("File") . ": " . $entry["filename"] ?></li>
<?php
    }
    if (!is_null($aImportedFilesInfo))
    {
?>
        </ul><br />
<?php
    }
    echo $ErrorListHeader;
    foreach ($aErrorFilesInfo as $entry)
    {
?>
        <li><?php echo gT("File") . ": " . $entry['filename'] . " (" . $entry['status'] . ")" ?></li>
<?php
    }
    if (!is_null($aErrorFilesInfo))
    {
?>
        </ul><br />
<?php
    }
?>
    <input type='submit' value='<?php eT("Back") ?>' onclick="window.open('<?php echo $this->createUrl('admin/labels/sa/view/lid/' . $lid) ?>', '_top')" />
</div>