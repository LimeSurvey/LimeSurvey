<?php

/**
 * Menu Bar show for full pages (without sidemenu, inside configuration menus)
 */

?>

<!-- Full page menu bar -->
<div class='menubar' id="fullpagebar" style="box-shadow: 3px 3px 3px #35363f; margin-bottom: 10px;">
    <div class='row container-fluid'>
        <div class="col-md-6 text-left">

            <!-- List Surveys - Create a new Survey -->
            <?php if(isset($fullpagebar['listSurveys']['buttons']['createSurvey']) &&
                Permission::model()->hasGlobalPermission('surveys','create')):?>
                <a class="btn btn-default"
                   href="<?php echo $fullpagebar['listSurveys']['buttons']['createSurvey']['url']; ?>"
                   role="button"
                   style="margin-top: 10px; margin-bottom: 10px;">
                    <span class="icon-add text-success"></span>
                    <?php eT("Create a new survey");?>
                </a>
            <?php endif;?>
        </div>


        <!-- Right actions -->
        <div class="col-md-6 text-right" style="margin-bottom: 10px;">

            <!-- Close -->
            <?php if (isset($fullpagebar['closebutton']['url'])) :?>
                <a class="btn btn-default" href="<?php echo $fullpagebar['closebutton']['url']; ?>" role="button" style="margin-top: 10px;">
                    <span class="fa fa-close"></span>
                    <?php eT("Close");?>
                </a>
            <?php endif;?>

            <!-- Save and Close -->
            <?php if (isset($fullpagebar['saveandclosebutton']['form'])) :?>
                <a class="btn btn-default" href="#" role="button" id="save-and-close-form-button" onclick="$(this).addClass('disabled').attr('onclick', 'return false;');" data-form-id="<?php echo $fullpagebar['saveandclosebutton']['form']; ?>" style="margin-top: 10px;">
                    <span class="fa fa-saved"></span>
                    <?php eT("Save and close");?>
                </a>
            <?php endif; ?>

            <!-- Save -->
            <?php if (isset($fullpagebar['savebutton']['form'])) :?>
                <a class="btn btn-success" href="#" role="button" id="save-form-button" onclick="$(this).addClass('disabled').attr('onclick', 'return false;');" data-form-id="<?php echo $fullpagebar['savebutton']['form']; ?>" style="margin-top: 10px;">
                    <span class="fa fa-floppy-o"></span>
                    <?php eT("Save");?>
                </a>
            <?php endif;?>

            <!-- Box Buttons -->
            <?php if (isset($fullpagebar['boxbuttons'])) :?>
                <a href="<?php echo $this->createUrl('homepageSettings/createBox/');?>" class="btn btn-default" style="margin-top: 10px;">
                    <span class="icon-add  text-success"></span>
                    <?php eT("Create a new box");?>
                </a>
                <a href="<?php echo $this->createUrl('admin/homepagesettings/sa/resetall/');?>" class="btn btn-danger" data-confirm="<?php eT('This will delete all current boxes to restore the default ones. Are you sure you want to continue?'); ?>" style="margin-top: 10px;">
                    <span class="fa fa-refresh"></span>
                    <?php eT("Reset to default boxes");?>
                </a>
            <?php endif;?>

            <!-- Manage your Key -->
            <?php if (isset($fullpagebar['update'])) :?>
                <a href="<?php echo $this->createUrl('admin/update/sa/managekey/');?>" class="btn btn-default" style="margin-top:10px;">
                    <span class="fa fa-key text-success"></span>
                    <?php eT("Manage your key");?>
                </a>
            <?php endif;?>

            <!-- Return -->
            <?php if (isset($fullpagebar['returnbutton']['url'])) :?>
                <a class="btn btn-default" href="<?php echo $this->createUrl($fullpagebar['returnbutton']['url']); ?>" role="button" style="margin-top:10px;" >
                    <span class="fa fa-backward"></span>
                    &nbsp;&nbsp;
                    <?php echo $fullpagebar['returnbutton']['text']; ?>
                </a>
            <?php endif;?>
        </div>
    </div>
</div>
