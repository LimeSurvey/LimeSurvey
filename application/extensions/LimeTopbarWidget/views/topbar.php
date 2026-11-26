<?php
/** @var string $leftSide this could be a simple text or a breadcrumb */
/** @var ButtonWidget[] $middle */
/** @var ButtonWidget[] $rightSide */
/** @var bool $isBreadCrumb */
/** @var string $titleBackLink */
/** @var array $titleParents parent pages of the title: [['text' => ..., 'href' => ...], ...] */
/** @var bool $editorEnabled */
?>

<div class="topbar sticky-top <?php if ($editorEnabled) {
    echo 'editor';
                              } ?>" id="pjax-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Title or breadcrumb -->
            <div class="ls-breadcrumb col-12">
                <div class="align-items-center d-flex">
                    <?php
                    if ($titleBackLink !== null) {
                        // Keep the back link outside <h1> so it is announced only as a link, not with heading level.
                        echo '<a class="h1 me-1 ls-link" href="' . CHtml::encode($titleBackLink) . '" aria-label="' . gT('Back') . '">'
                            . '<i class="ri-arrow-left-s-line" aria-hidden="true"></i></a>';
                    }
                    ?>
                    <?php if (!$isBreadCrumb && !empty($titleParents)) : ?>
                        <?php // Breadcrumb navigation: the parent pages are links, the page heading is the current page ?>
                        <nav aria-label="<?= gT('Breadcrumb') ?>">
                            <ol class="list-unstyled d-flex flex-wrap align-items-center mb-0 h1">
                                <?php foreach ($titleParents as $titleParent) : ?>
                                    <li>
                                        <a class="ls-link" href="<?= CHtml::encode($titleParent['href']) ?>"><?= CHtml::encode($titleParent['text']) ?></a>
                                        <span class="text-muted mx-1" aria-hidden="true">/</span>
                                    </li>
                                <?php endforeach; ?>
                                <li aria-current="page"><h1 class="h1 mb-0"><?= $leftSide ?></h1></li>
                            </ol>
                        </nav>
                    <?php elseif (!$isBreadCrumb) : ?>
                        <h1 class="h1 mb-0"><?= $leftSide ?></h1>
                    <?php else : ?>
                        <?= $leftSide ?>
                    <?php endif; ?>
                </div>
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
