<?php
/**
 * Display error messages
 * @var $title string
 * @var $message html
 */
?>
<div class='side-body  <?php echo getSideBodyClass(false); ?>'>   
    <div class="row">
        <div class="col-sm-12 content-center">
            <!-- Message box from super admin -->
            <div class="jumbotron message-box <?php echo isset($class) ? $class : ""; ?>">
                <div class="h2"><?php echo $title;?></div>
                <?php echo $message;?>
            </div>
        </div>
    </div>
</div>
