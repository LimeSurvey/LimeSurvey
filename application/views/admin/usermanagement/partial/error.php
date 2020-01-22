<?php
/**
 * Subview: Error messsage in the usermanagement panel
 * 
 * @package UserManagement
 * @author Markus Flür <markus.fluer@limesurvey.org>
 * @license GPL3.0
 */
?>
<div class="modal-header">
    <?=gT('Error')?>
</div>
<div class="modal-body">
    <div class="container-center">
        <div class="row selector--animated_row">
            <div class="col-xs-12 text-center">
                <div class="cross_mark">
                    <div class="sa-icon sa-error animate">
                        <span class="sa-line sa-tip animateerrorTip"></span>
                        <span class="sa-line sa-long animateerrorLong"></span>
                        <div class="sa-placeholder"></div>
                        <div class="sa-fix"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row ls-space margin top-15 bottom-15">
            <?php foreach($errors as $error) {
                echo "<pre>".print_r($error,true)."</pre>";
            }
            ?>
        </div>
        <?php if(!isset($noButton)): ?>
        <div class="row ls-space margin top-35">
            <button id="exitForm" class="btn btn-default">
                <?=gT('Close')?></button>
        </div>
        <?php endif; ?>
    </div>
</div>
