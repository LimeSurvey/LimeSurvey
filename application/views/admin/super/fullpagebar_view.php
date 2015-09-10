<div class='menubar' id="fullpagebar">
    <div class='row container-fluid'>
    	<div class="col-md-8">
    	</div>
    	
    	<div class="col-md-4 text-right">
    		<?php if(isset($fullpagebar['savebutton']['form'])):?>
            	<a class="btn btn-success" href="#" role="button" id="save-form-button" aria-data-form-id="<?php echo $fullpagebar['savebutton']['form']; ?>">
            		<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
            		<?php eT("Save");?>
            	</a>
            <?php endif;?>
            
            <?php if(isset($fullpagebar['closebutton']['url'])):?>
            	<a class="btn btn-danger" href="<?php echo $this->createUrl($fullpagebar['closebutton']['url']); ?>" role="button">
            		<span class="glyphicon glyphicon-close" aria-hidden="true"></span>
            		<?php eT("Close");?>
            	</a>
            <?php endif;?>
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