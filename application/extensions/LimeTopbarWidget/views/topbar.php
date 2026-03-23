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
                            echo '<a class="topbar-back-link" aria-label="' . CHtml::encode(gT('Back')) . '" href="' . CHtml::encode($titleBackLink) . '">
                            <i class="ri-arrow-left-s-line" aria-hidden="true"></i></a>';
                        }
                    ?>
                    <h1 class="mb-0"><?= $leftSide ?></h1>
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
