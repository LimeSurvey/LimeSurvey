<div class='header ui-widget-header'><?php echo $clang->gT("Import template") ?></div>
<div class='messagebox ui-corner-all'>
    <div class='successheader'><?php echo $clang->gT("Success") ?></div><br />
    <?php echo $clang->gT("File upload succeeded.") ?><br /><br />
    <?php echo $clang->gT("Reading file..") ?><br /><br />
    <strong><?php echo $clang->gT("Imported template files for") ?></strong> <?php echo $lid ?><br /><br />
<?php
$okfiles = 0;
$errfiles = 0;
if (count($aErrorFilesInfo) == 0 && count($aImportedFilesInfo) > 0)
{
    $status = $clang->gT("Success");
    $statusClass = 'successheader';
    $okfiles = count($aImportedFilesInfo);
}
elseif (count($aErrorFilesInfo) > 0 && count($aImportedFilesInfo) > 0)
{
    $status = $clang->gT("Partial");
    $statusClass = 'partialheader';
    $okfiles = count($aImportedFilesInfo);
    $errfiles = count($aErrorFilesInfo);
}
else
{
    $status = $clang->gT("Error");
    $statusClass = 'warningheader';
    $errfiles = count($aErrorFilesInfo);
}
?>
    <div class="<?php echo $statusClass ?>"><?php echo $status ?></div><br />
    <strong><u><?php echo $clang->gT("Resources Import Summary") ?></u></strong><br />
    <?php echo $clang->gT("Total Imported files") . ": $okfiles" ?><br />
    <?php echo $clang->gT("Total Errors") . ": $errfiles" ?><br />
<?php
if (count($aImportedFilesInfo) > 0)
{
?>
    <br /><strong><u><?php echo $clang->gT("Imported Files List") ?>:</u></strong><br />
    <ul>
<?php
    foreach ($aImportedFilesInfo as $entry)
    {
?>
        <li><?php echo $clang->gT("File") . ": " . $entry["filename"] ?></li>
<?php
    }
}
if (count($aErrorFilesInfo) > 0)
{
?>
    </ul>
    <br /><strong><u><?php echo $clang->gT("Error Files List") ?>:</u></strong><br />
    <ul>
<?php
    foreach ($aErrorFilesInfo as $entry)
    {
?>
        <li><?php echo $clang->gT("File") . ": " . $entry["filename"] ?></li>
<?php
    }
}
?>
    </ul>
    <input type='submit' value='<?php echo $clang->gT("Open imported template") ?>' onclick="window.open('<?php echo $this->createUrl('admin/templates/sa/view/editfile/startpage.pstpl/screenname/welcome/templatename/' . $newdir) ?>', '_top')" />
</div>
