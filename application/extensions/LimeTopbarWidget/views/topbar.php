<?php
/** @var string $leftSide this could be a simple text or a breadcrumb */
/** @var ButtonWidget[] $middle */
/** @var ButtonWidget[] $rightSide */

?>

<div class="topbar sticky-top" id="pjax-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Title or breadcrumb -->
            <div class="ls-breadcrumb col-12">
                <h1 class="align-items-center d-flex">
                    <?php
                        if ($titleBackLink !== null) {
                            echo '<a class="h1" href="'.$titleBackLink.'">
                            <i class="ri-arrow-left-s-line"></i></a>' ;
                        }
                    ?>
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
