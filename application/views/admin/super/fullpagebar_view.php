<?php
/**
 * Menu Bar show for full pages (without sidebar, inside configuration menus)
 */
?>

<!-- Full page menu bar -->
<div class='menubar' id="fullpagebar">
    <div class='row container-fluid'>
        
        <!-- Right Actions -->
        <div class="col-md-8">
            
            <!-- Create a new survey  -->
            <?php if (isset($fullpagebar['button']['newsurvey'])):?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/survey/sa/newsurvey"); ?>" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/add.png" />
                    <?php eT("Create a new survey");?>
                </a>
            <?php endif;?>
        </div>
        
        <!-- Left actions -->
        <div class="col-md-4 text-right">
            
            <!-- Save -->
            <?php if(isset($fullpagebar['savebutton']['form'])):?>
                <a class="btn btn-success" href="#" role="button" id="save-form-button" aria-data-form-id="<?php echo $fullpagebar['savebutton']['form']; ?>">
                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    <?php eT("Save");?>
                </a>
            <?php endif;?>
            
            <!-- Close -->
            <?php if(isset($fullpagebar['closebutton']['url'])):?>
                <a class="btn btn-danger" href="<?php echo $this->createUrl($fullpagebar['closebutton']['url']); ?>" role="button">
                    <span class="glyphicon glyphicon-close" aria-hidden="true"></span>
                    <?php eT("Close");?>
                </a>
            <?php endif;?>
            
            <!-- Return -->
            <?php if(isset($fullpagebar['returnbutton']['url'])):?>
                <a class="btn btn-default" href="<?php echo $this->createUrl($fullpagebar['returnbutton']['url']); ?>" role="button">
                    <span class="glyphicon glyphicon-backward" aria-hidden="true"></span>
                    &nbsp;&nbsp;
                    <?php echo $fullpagebar['returnbutton']['text']; ?>
                </a>
            <?php endif;?>            
        </div>
    </div>
</div>