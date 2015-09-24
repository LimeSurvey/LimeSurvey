<?php
/**
 * Display error messages
 * @var $title string
 * @var $message html
 */
?>
<div class="side-body">
    <div class="row">                             
        <div class="col-lg-12 content-right">
            <!-- Message box from super admin -->
            <div class="jumbotron message-box message-box-error">
                <h2 ><?php echo $title;?></h2>
                <?php echo $message;?>
            </div>
        </div>
    </div>
</div>    
        

