<?php
/**
 * This view render the textaera to display the list of existing files in the local installation but that shoulb created by the updater
 * If no existing file, display a success message
 * 
 * @var array $existingfiles array continaing the readonly files. 
 * @var obj clang 
 */
?>

<?php if (count($existingfiles)>0): ?>
    <?php eT('The following files would be added by the update but already exist. This is very unusual and may be co-incidental.'); ?>
    <br />
    <?php  eT('We recommend that these files should be replaced by the update procedure.');?>
    <br />
<textarea readonly="readonly" style="background-color: #FFF; width: 800px; height: 150px; font-family: Monospace; font-size: 11px;">            
<?php
sort($existingfiles);
foreach ($existingfiles as $existingfile)
{
echo htmlspecialchars($existingfile['file'])."\n";
}
?>
</textarea>
<?php else:?>
    <p class="success" style="text-align: left;">
        <?php  eT('No file added by the ComfortUpdate already exists.'); ?> 
    </p>                
<?php endif;?>