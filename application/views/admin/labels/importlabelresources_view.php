<div class='header ui-widget-header'><?php echo $clang->gT("Import Label Set") ?></div>
<div class='messagebox ui-corner-all'>
    <div class="successheader"><?php echo $clang->gT("Success") ?></div><br />
    <?php echo $clang->gT("File upload succeeded.") ?><br /><br />

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

    <strong><?php echo $clang->gT("Imported Resources for") ?> LID:</strong><?php echo $lid ?><br /><br />
    <div class="<?php echo $statusClass ?>"><?php echo $status ?></div><br />
    <strong><u><?php echo $clang->gT("Resources Import Summary") ?></u></strong><br />
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
    <input type='submit' value='<?php echo $clang->gT("Back") ?>' onclick="window.open('<?php echo $this->createUrl('admin/labels/sa/view/lid/' . $lid) ?>', '_top')" />
</div>