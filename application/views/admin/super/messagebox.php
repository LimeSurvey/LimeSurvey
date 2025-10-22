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
            <div class="jumbotron message-box <?php echo $class ?? ""; ?>">
                <div class="h2" role="heading" aria-level="2"><?php echo $title;?></div>
                <?php echo $message;?>
            </div>
        </div>
    </div>
</div>
