<h3 class="pagetitle"><?php eT("Check data integrity");?></h3>
        
<div class="row" style="margin-bottom: 100px">
    <div class="col-lg-12">
        <div class="jumbotron message-box">
                <h2><?php eT("Data consistency check"); ?></h2>
                <p class="lead"><?php eT("If errors are showing up you might have to execute this script repeatedly."); ?></p>
                <p>
                    <ul>
                    <?php foreach ($messages as $sMessage) {?>
                     <li><?php echo $sMessage;?></li>
                    <?php } ?>
                    </ul>           
                </p>
                <p>
                    <a class="btn btn-lg btn-success" href='<?php echo $this->createUrl('admin/checkintegrity');?>'><?php eT("Check again"); ?></a>
                </p>
        </div>        
        
    </div>
</div>  
  
