<?php
/**
* This view render the textaera to display the list of files modified or deleted by the updater but already modified in the local installation
* If no modified file, display a success message
*
* @var array $readonlyfiles array continaing the readonly files.
* @var obj clang
*/
?>
<?php
if (count($modifiedfiles)>0): ?>
    <p>
        <br/><br/>
        <?php eT('The following files will be modified or deleted but were already modified by someone else.');?>
        <br/>
        <?php eT('These files will be backed up and then replaced by the update procedure.');?>
    </p>
    <textarea readonly="readonly" style="background-color: #FFF; width: 800px; height: 150px; font-family: Monospace; font-size: 11px;"><?php
        sort($modifiedfiles);
        foreach ($modifiedfiles as $modifiedfile)
        {
            echo htmlspecialchars($modifiedfile['file'])."\n";
    }?></textarea>
    <?php endif;?>

