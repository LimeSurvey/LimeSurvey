<div class="jumbotron message-box">
<h2 class="text-success"><?php eT("Import label set resources") ?></h2>
<p class="lead text-success"><?php eT("Success") ?></p>

<?php
    $ImportListHeader = '';
if (!count($aErrorFilesInfo) && count($aImportedFilesInfo)) {
    $status = gT("Success");
    $statusClass = 'successheader';
    $okfiles = count($aImportedFilesInfo);
    $errfiles = 0;
    $ImportListHeader .= "<br /><strong><u>" . gT("Imported files list") . ":</u></strong><br />\n";
    $ErrorListHeader = '';
} elseif (count($aErrorFilesInfo) && count($aImportedFilesInfo)) {
    $status = gT("Partial");
    $statusClass = 'partialheader';
    $okfiles = count($aImportedFilesInfo);
    $errfiles = count($aErrorFilesInfo);
    $ErrorListHeader = "<br /><strong><u>" . gT("Error files list") . ":</u></strong><br />\n";
    $ImportListHeader .= "<br /><strong><u>" . gT("Imported files list") . ":</u></strong><br />\n";
} else {
    $okfiles = 0;
    $status = gT("Error");
    $statusClass = 'warningheader';
    $errfiles = count($aErrorFilesInfo);
    $ImportListHeader = '';
    $ErrorListHeader = "<br /><strong><u>" . gT("Error files list") . ":</u></strong><br />\n";
}
?>

    <p><strong><?php printf(gT("Imported resources for LID %s"), $lid); ?></strong><br /><br /><p>
    <p class="<?php echo $statusClass ?>"><?php echo $status ?></p><br />
    <p><strong><u><?php eT("Resources import summary") ?></u></strong><br /></p>
    <p><?php echo gT("Total imported files") . ": $okfiles" ?><br /></p>
    <p><?php echo gT("Total errors") . ": $errfiles" ?><br /></p>
    <p><?php echo $ImportListHeader; ?></p>
<p><ul class="list-unstyled">
 <?php
    foreach ($aImportedFilesInfo as $entry) {
        ?>
        <li><?php echo gT("File") . ": " . $entry["filename"] ?></li>
        <?php
    }
    if (!is_null($aImportedFilesInfo)) {
        ?>
        </ul><br /></p>
        <p><ul class="list-unstyled">
        <?php
    }
    echo $ErrorListHeader;
    foreach ($aErrorFilesInfo as $entry) {
        ?>
        <li><?php echo gT("File") . ": " . $entry['filename'] . " (" . $entry['status'] . ")" ?></li>
        <?php
    }
    if (!is_null($aErrorFilesInfo)) {
        ?>
        </ul></p><br />
        <?php
    }
    ?>
<p>
    <input class="btn btn-outline-secondary btn-lg" type='submit' value='<?php eT("Back") ?>' onclick="window.open('<?php echo $this->createUrl('admin/labels/sa/view/lid/' . $lid) ?>', '_top')" />
</p>
</div>
