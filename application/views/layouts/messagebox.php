<?php
/**
 * Display error messages
 * @var $title string
 * @var $message html
 */
?>
<div class='side-body'>
    <div class="row">
        <div class="col-12 content-center">
            <!-- Message box from super admin -->
            <div id="admin-status-message" class="jumbotron message-box <?php echo $class ?? ""; ?>" role="status" aria-live="polite" aria-atomic="true" tabindex="-1">
                <div class="h2"><?php echo $title;?></div>
                <?php echo $message;?>
            </div>
        </div>
    </div>
</div>
<?php
App()->getClientScript()->registerScript(
    'adminStatusMessageFocus',
    "jQuery(function(){ var el = document.getElementById('admin-status-message'); if (el) { el.focus(); } });",
    CClientScript::POS_READY
);
?>
