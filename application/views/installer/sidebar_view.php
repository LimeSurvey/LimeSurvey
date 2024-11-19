<?php
/**
 * Web Installer Sidebar (Progressbar and Step-Listing) Viewscript
 */
?>
<h2 class="maintitle"><?php eT("Progress"); ?></h2>
<p><?php printf(gT("%s%% completed"),$progressValue); ?></p>
<?php
    echo TbHtml::animatedProgressBar($progressValue);
?>
<ol class="mt-3">
    <li class="<?php echo $classesForStep[0]; ?>">
        <?php eT("Welcome"); ?>
    </li>
    <li class="<?php echo $classesForStep[1]; ?>">
        <?php eT("License"); ?>
    </li>
    <li class="<?php echo $classesForStep[2]; ?>">
        <?php eT("Pre-installation check"); ?>
    </li>
    <li class="<?php echo $classesForStep[3]; ?>">
        <?php eT("Configuration"); ?>
    </li>
    <li class="<?php echo $classesForStep[4]; ?>">
        <?php eT("Database settings"); ?>
    </li>
    <li class="<?php echo $classesForStep[5]; ?>">
        <?php eT("Administrator settings"); ?>
    </li>
</ol>
