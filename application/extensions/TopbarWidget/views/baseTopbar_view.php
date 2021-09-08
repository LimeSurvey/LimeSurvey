<?php
/**
 * Base toolbar layout
 * This is the base view for toolbars that have one part aligned to the left and one part aligned to the right
 *
 * @var string $topbarId defaults to 'surveybarid'
 * @var string $leftSideContent the left side content
 * @var string $rightSideContent the right side content
 *
 */

?>

<div class='menubar surveybar' id="<?= !(empty($topbarId)) ? $topbarId : 'surveybarid' ?>">
    <div class='row'>
        <?php if (!empty($leftSideContent)) : ?>
            <!-- Left Side -->
            <div class="<?= !empty($rightSideContent) ? 'col-md-6' : 'col-md-12' ?>">
                <?= $leftSideContent ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($rightSideContent)) : ?>
            <!-- Right Side -->
            <div class="<?= !empty($leftSideContent) ? 'col-md-6' : 'col-md-12' ?> pull-right text-right">
                <?= $rightSideContent ?>
            </div>
        <?php endif; ?>
    </div>
</div>
