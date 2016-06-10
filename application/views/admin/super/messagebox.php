<?php
/**
 * Display error messages
 * @var $title string
 * @var $message html
 */
?>
<div class="row">
    <div class="col-sm-11 col-sm-offset-1 content-right">
        <!-- Message box from super admin -->
        <div class="jumbotron message-box <?php echo isset($class) ? $class : ""; ?>">
            <h2 ><?php echo $title;?></h2>
            <?php echo $message;?>
        </div>
    </div>
</div>
