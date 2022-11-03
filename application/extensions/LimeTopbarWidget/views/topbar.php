<?php
 /** @var string  $leftSide this could be a simple text or a breadcrumb */
 /** @var ButtonWidget[] $middle */
 /** @var ButtonWidget[] $rightSide */
?>

<div class="menubar topbar" id="fullpagebar">
    <div class="container-fluid">
        <div class="row">
            <!-- Title or breadcrumb -->
            <div class="col-md-3 text-start h1">
                <h1><?= $leftSide ?></h1>

            </div>

            <!-- middle part with buttons -->
            <div class="col">
            <?php
            foreach ($middle as $buttonWidget) {
                echo $buttonWidget;
            }
            ?>
            </div>

            <!-- left part with buttons -->
            <div class="col-md-auto text-end">
                <?php
                if ($rightSide !== null) {
                    foreach ($rightSide as $buttonWidget) {
                        echo $buttonWidget;
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>
