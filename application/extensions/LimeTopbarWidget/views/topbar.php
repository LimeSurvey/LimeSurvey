<?php
/** @var string $leftSide this could be a simple text or a breadcrumb */
/** @var ButtonWidget[] $middle */
/** @var ButtonWidget[] $rightSide */

?>

<div class="topbar sticky-top <?php if ($editorEnabled) echo 'editor'; ?>" id="pjax-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Title or breadcrumb -->
            <div class="ls-breadcrumb col-12">
                <div class="align-items-center d-flex">
                    <?php
                    if ($titleBackLink !== null) {
                        // Keep the back link outside <h1> so it is announced only as a link, not with heading level.
                        echo '<a class="h1 me-1 text-decoration-none" href="' . CHtml::encode($titleBackLink) . '" aria-label="' . CHtml::encode(gT('Back')) . '">'
                            . '<i class="ri-arrow-left-s-line" aria-hidden="true"></i></a>';
                    }
                    ?>
                    <h1 class="h1 mb-0">
                        <?= $leftSide ?>
                    </h1>
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
