<div class="pagetitle h3"><?php eT("Test Email Settings");?></div>
        
<div class="row" style="margin-bottom: 100px">
    <div class="col-lg-12">
        <div class="jumbotron message-box">
            <h2><?php eT("Test Email Results"); ?></h2>
            <p class="lead"><?php eT("If errors are showing up you might have to change configuration settings and retest."); ?></p>
            <?php if($success) { ?>
                <p class="h4 text-success"><?php echo $message;?></p>
            <?php } else { ?>
                <p class="h4text-danger"><?php echo $message;?></p>
                <div> <?php echo $maildebug ?></div>
            <?php } ?>       
            <p>
                <a class="btn btn-lg btn-success" href='<?php echo $this->createUrl('admin/globalsettings');?>'><?php eT("Modify settings"); ?></a>
            </p>
        </div>
    </div>
</div>