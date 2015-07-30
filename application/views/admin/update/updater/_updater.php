<?php
/**
 * This view build the updater layout. Content is injected inside #updaterContainer by the ajax process
 */
?>  
<div id="updaterLayout" style="display: none;  background-color: #fff">
    <?php
        // The left bar, with progress (steps such as welcome, or pre-installation check, etc.) 
        $this->renderPartial("./update/updater/_progress" );
        
        // The right part of the updater, containing the steps.
         $this->renderPartial("./update/updater/_right_container" );
    ?> 
</div>