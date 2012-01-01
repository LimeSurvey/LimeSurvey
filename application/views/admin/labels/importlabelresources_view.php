<div class='header ui-widget-header'><?php $clang->eT("Import Label Set") ?></div>
<div class='messagebox ui-corner-all'>
    <div class="successheader"><?php $clang->eT("Success") ?></div><br />
    <?php $clang->eT("File upload succeeded.") ?><br /><br />

<?php
    $ImportListHeader = '';
    if (is_null($aErrorFilesInfo) &&!is_null($aImportedFilesInfo))
    {
        $status = $clang->gT("Success");
        $statusClass = 'successheader';
        $okfiles = count($aImportedFilesInfo);
        $ImportListHeader .= "<br /><strong><u>" . $clang->gT("Imported Files List") . ":</u></strong><br />\n";
    }
    elseif (!is_null($aErrorFilesInfo) &&!is_null($aImportedFilesInfo))
    {
        $status = $clang->gT("Partial");
        $statusClass = 'partialheader';
        $okfiles = count($aImportedFilesInfo);
        $errfiles = count($aErrorFilesInfo);
        $ErrorListHeader .= "<br /><strong><u>" . $clang->gT("Error Files List") . ":</u></strong><br />\n";
        $ImportListHeader .= "<br /><strong><u>" . $clang->gT("Imported Files List") . ":</u></strong><br />\n";
    }
    else
    {
        $status = $clang->gT("Error");
        $statusClass = 'warningheader';
        $errfiles = count($aErrorFilesInfo);
        $ErrorListHeader .= "<br /><strong><u>" . $clang->gT("Error Files List") . ":</u></strong><br />\n";
    }
?>

    <strong><?php $clang->eT("Imported Resources for") ?> LID:</strong><?php echo $lid ?><br /><br />
    <div class="<?php echo $statusClass ?>"><?php echo $status ?></div><br />
    <strong><u><?php $clang->eT("Resources Import Summary") ?></u></strong><br />
    <?php echo $clang->gT("Total Imported files") . ": $okfiles" ?><br />
    <?php echo $clang->gT("Total Errors") . ": $errfiles" ?><br />
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