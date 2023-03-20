<?php
/**
 * This view render the textaera to display the list of files changed by the update but being readonly in the local installation
 * If no readonly file, display a success message
 * 
 * @var array $readonlyfiles array containing the readonly files. 
 */
?>
<?php if (count($readonlyfiles)>0):?>
    <span class="text-danger">
        <?php eT('Warning: The following files/directories need to be updated but their permissions are set to read-only.'); ?>
        <br />
        <?php eT('You must set according write permissions on these filese before you can proceed. If you are unsure what to do please contact your system administrator for advice.'); ?><br />
    </span>

<textarea readonly="readonly" style="background-color: #FFF; width: 800px; height: 150px; font-family: Monospace; font-size: 11px;">
<?php foreach ($readonlyfiles as $readonlyfile):?>
<?php echo trim(htmlspecialchars((string) $readonlyfile))."\n"; ?>
<?php endforeach;?>
</textarea>
<?php  else:?>
    <p class="success text-start">
        <?php eT("All files in local directories are writable."); ?>
    </p>
<?php  endif;?>
