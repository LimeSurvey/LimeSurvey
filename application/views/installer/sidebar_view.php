<?php
/**
 * Web Installer Sidebar (Progressbar and Step-Listing) Viewscript
 */
?>
<h2 class="maintitle"><?php $clang->eT("Progress"); ?></h2>
<p><?php printf($clang->gT("%s%% completed"),$progressValue); ?></p>
<?php
    Yii::app()->bootstrap->init();
    $this->widget('ext.bootstrap.widgets.TbProgress', array(
            'type' => 'success',
            'striped' => true,
            'animated' => true,
            'percent' => $progressValue,
        ));
    ?>
<ol>
    <li class="<?php echo $classesForStep[0]; ?>">
        <?php $clang->eT("Welcome"); ?>
    </li>
    <li class="<?php echo $classesForStep[1]; ?>">
        <?php $clang->eT("License"); ?>
    </li>
    <li class="<?php echo $classesForStep[2]; ?>">
        <?php $clang->eT("Pre-installation check"); ?>
    </li>
    <li class="<?php echo $classesForStep[3]; ?>">
        <?php $clang->eT("Configuration"); ?>
    </li>
    <li class="<?php echo $classesForStep[4]; ?>">
        <?php $clang->eT("Database settings"); ?>
    </li>
    <li class="<?php echo $classesForStep[5]; ?>">
        <?php $clang->eT("Optional settings"); ?>
    </li>
</ol>
