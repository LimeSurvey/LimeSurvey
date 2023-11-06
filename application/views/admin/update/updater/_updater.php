<?php
/**
 * This view build the updater layout. Content is injected inside #updaterContainer by the ajax process
 */
?>
<div id="updaterLayout" style="display: none;" class="row">
    <div class="col-sm-3 hidden-xs">
        <?php
            // The left bar, with progress (steps such as welcome, or pre-installation check, etc.)
            $this->renderPartial("./update/updater/_progress" );
        ?>
    </div>
    <div class="col-sm-9">
        <?php
            // The right part of the updater, containing the steps.
             $this->renderPartial("./update/updater/_right_container" );
        ?>
    </div>
</div>
