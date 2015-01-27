<?php
/**
 * Web Installer Sidebar (Progressbar and Step-Listing) Viewscript
 */
?>
<div class="col-md-3">
<h2 class="maintitle"><?php eT("Progress"); ?></h2>
<p><?php printf(gT("%s%% completed"),$progressValue); ?></p>
<?php
    Yii::app()->bootstrap->init();
    echo TbHtml::animatedProgressBar($progressValue, ['color' => TbHtml::PROGRESS_COLOR_SUCCESS]);
    ?>
<ol>
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
        <?php eT("Optional settings"); ?>
    </li>
</ol>
</div>