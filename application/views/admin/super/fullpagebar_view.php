<?php
/**
 * Menu Bar show for full pages (without sidemenu, inside configuration menus)
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
                    <span class="icon-add text-success"></span>
                    <?php eT("Create a new survey");?>
                </a>
            <?php endif;?>
        </div>

        <!-- Left actions -->
        <div class="col-md-4 text-right">

            <!-- Save -->
            <?php if(isset($fullpagebar['savebutton']['form'])):?>
                <a class="btn btn-success" href="#" role="button" id="save-form-button" data-form-id="<?php echo $fullpagebar['savebutton']['form']; ?>">
                    <span class="glyphicon glyphicon-ok"></span>
                    <?php eT("Save");?>
                </a>
            <?php endif;?>

            <!-- Close -->
            <?php if(isset($fullpagebar['closebutton']['url'])):?>
                <a class="btn btn-danger" href="<?php echo $fullpagebar['closebutton']['url']; ?>" role="button">
                    <span class="glyphicon glyphicon-close"></span>
                    <?php eT("Close");?>
                </a>
            <?php endif;?>

            <!-- Return -->
            <?php if(isset($fullpagebar['returnbutton']['url'])):?>
                <a class="btn btn-default" href="<?php echo $this->createUrl($fullpagebar['returnbutton']['url']); ?>" role="button">
                    <span class="glyphicon glyphicon-backward"></span>
                    &nbsp;&nbsp;
                    <?php echo $fullpagebar['returnbutton']['text']; ?>
                </a>
            <?php endif;?>
        </div>
    </div>
</div>
