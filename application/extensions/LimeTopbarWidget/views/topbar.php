<?php
/** @var string $leftSide this could be a simple text or a breadcrumb */
/** @var ButtonWidget[] $middle */
/** @var ButtonWidget[] $rightSide */

?>

<div class="topbar sticky-top" id="pjax-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Title or breadcrumb -->
            <div class="ls-breadcrumb col-xl-4 col-xxl-3">
                <h1 class="align-items-center d-flex"><?= $leftSide ?></h1>
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
