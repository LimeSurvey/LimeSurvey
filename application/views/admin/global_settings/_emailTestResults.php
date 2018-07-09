<div class="pagetitle h3"><?php eT("Test Email Settings");?></div>
        
<div class="row" style="margin-bottom: 100px">
    <div class="col-lg-12">
        <div class="jumbotron message-box">
            <h2><?php eT("Test Email Results"); ?></h2>
            <p class="lead"><?php eT("If errors are showing up you might have to change configuration settings and retest."); ?></p>
            <p>
                <?php echo $message;?>         
            </p>
            <p>
                <a class="btn btn-lg btn-success" href='<?php echo $this->createUrl('admin/globalsettings');?>'><?php eT("Modify settings"); ?></a>
            </p>
        </div>        
        
    </div>
</div>  
  
