<?php
/** @var string $leftSide this could be a simple text or a breadcrumb */
/** @var ButtonWidget[] $middle */
/** @var ButtonWidget[] $rightSide */
/** @var bool $isBreadCrumb */
/** @var string $titleBackLink */
/** @var bool $editorEnabled */
?>

<div class="topbar sticky-top <?php if ($editorEnabled) {
    echo 'editor';
} ?>" id="pjax-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Title or breadcrumb -->
            <div class="ls-breadcrumb col-12">
                <h1 class="align-items-center d-flex">
                    <?php if ($titleBackLink !== null): ?>
                        <a class="h1 topbar-back-link ls-link"
                           href="<?= CHtml::encode($titleBackLink); ?>"
                           aria-label="<?= gT('Back'); ?>">
                            <i class="ri-arrow-left-s-line" aria-hidden="true"></i>
                        </a>
                    <?php endif; ?>
                    <?= $leftSide ?>
                </h1>
            </div>

            <!-- middle part with buttons -->
            <div class="ls-topbar-buttons pjax col">
                <?php
                if ($middle !== null) {
                    echo $middle;
                }
                ?>
            </div>

            <!-- left part with buttons -->
            <div class="ls-topbar-buttons pjax col-md-auto text-end">
                <?php
                if ($rightSide !== null) {
                    echo $rightSide;
                }
                ?>
            </div>

        </div>
    </div>
</div>
