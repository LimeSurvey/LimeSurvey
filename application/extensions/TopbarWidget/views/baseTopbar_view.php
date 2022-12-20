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
    <div class="container-fluid">
        <div class='row'>
            <!-- Left Side -->
            <div class="col">
                <?php if (!empty($leftSideContent)) : ?>
                    <?= $leftSideContent ?>
                <?php endif; ?>
            </div>

            <!-- Right Side -->
            <div class="col-md-auto float-end text-end">
                <?php if (!empty($rightSideContent)) : ?>
                    <?= $rightSideContent ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
