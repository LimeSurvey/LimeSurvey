<?php
/**
 * Display error messages
 * @var $title string
 * @var $message html
 * @deprecated please use application/views/layouts/messagebox.php instead or the Yii::app()->user->setFlash()
 */
?>
<div class='side-body'>
    <div class="row">
        <div class="col-12 content-center">
            <!-- Message box from super admin -->
            <div id="admin-status-message" class="jumbotron message-box <?php echo $class ?? ""; ?>" role="status" aria-live="polite" aria-atomic="true" tabindex="-1">
                <h1 class="h2" ><?php echo $title;?></h1>
                <?php echo $message;?>
            </div>
        </div>
    </div>
</div>