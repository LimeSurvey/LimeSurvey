<?php
/**
 * This view render the textaera to display the list of existing files in the local installation but that shoulb created by the updater
 * If no existing file, display a success message
 *
 * @var array $existingfiles array containing the readonly files.
 * @var obj clang
 */
?>

<?php if (count($existingfiles)>0): ?>
    <br/><br/>
    <?php eT('The following files would be added by the update but already exist. This is unusual and may result from an earlier update attempt.'); ?>
    <br />
    <?php eT('These files will be backed up and then replaced by the update procedure.');?>
    <br />
<textarea readonly="readonly" style="background-color: #FFF; width: 800px; height: 150px; font-family: Monospace; font-size: 11px;">
<?php
sort($existingfiles);
foreach ($existingfiles as $existingfile)
{
echo htmlspecialchars((string) $existingfile['file'])."\n";
}
?>
</textarea>
<?php endif;?>
