<div class='header ui-widget-header'><?php $clang->eT("Import label set resources") ?></div>
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
        $ImportListHeader .= "<br /><strong><u>" . $clang->gT("Imported files list") . ":</u></strong><br />\n";
        $ErrorListHeader = '';
    }
    elseif (count($aErrorFilesInfo) &&count($aImportedFilesInfo))
    {
        $status = $clang->gT("Partial");
        $statusClass = 'partialheader';
        $okfiles = count($aImportedFilesInfo);
        $errfiles = count($aErrorFilesInfo);
        $ErrorListHeader = "<br /><strong><u>" . $clang->gT("Error files list") . ":</u></strong><br />\n";
        $ImportListHeader .= "<br /><strong><u>" . $clang->gT("Imported files list") . ":</u></strong><br />\n";
    }
    else
    {
        $okfiles = 0;
        $status = $clang->gT("Error");
        $statusClass = 'warningheader';
        $errfiles = count($aErrorFilesInfo);
        $ImportListHeader = '';
        $ErrorListHeader = "<br /><strong><u>" . $clang->gT("Error files list") . ":</u></strong><br />\n";
    }
?>

    <strong><?php $clang->eT("Imported resources for") ?> LID:</strong><?php echo $lid ?><br /><br />
    <div class="<?php echo $statusClass ?>"><?php echo $status ?></div><br />
    <strong><u><?php $clang->eT("Resources import summary") ?></u></strong><br />
    <?php echo $clang->gT("Total imported files") . ": $okfiles" ?><br />
    <?php echo $clang->gT("Total errors") . ": $errfiles" ?><br />
    <?php echo $ImportListHeader; ?>

 <?php
    foreach ($aImportedFilesInfo as $entry)
    {
 ?>
        <li><?php echo $clang->gT("File") . ": " . $entry["filename"] ?></li>
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
        <li><?php echo $clang->gT("File") . ": " . $entry['filename'] . " (" . $entry['status'] . ")" ?></li>
<?php
    }
    if (!is_null($aErrorFilesInfo))
    {
?>
        </ul><br />
<?php
    }
?>
    <input type='submit' value='<?php $clang->eT("Back") ?>' onclick="window.open('<?php echo $this->createUrl('admin/labels/sa/view/lid/' . $lid) ?>', '_top')" />
</div>