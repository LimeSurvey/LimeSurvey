<?php

/**
 * Menu Bar show for full pages (without sidemenu, inside configuration menus)
 */

?>

<!-- Full page menu bar -->
<div class="menubar surveybar" id="fullpagebar">
    <div class="container-fluid">
        <div class="row">
            <!-- Left actions -->
            <div class="col text-start">

                <!-- Themes -->
                <?php if (isset($fullpagebar['themes'])) : ?>
                    <!-- Upload and Install -->
                    <?php if (isset($fullpagebar['themes']['buttons']['uploadAndInstall']) && $fullpagebar['themes']['canImport']) : ?>
                    <a id="uploadandinstall"
                       class="btn btn-outline-secondary"
                       href=""
                       role="button"
                       data-bs-toggle="modal"
                       data-bs-target="#<?php echo $fullpagebar['themes']['buttons']['uploadAndInstall']['modalSurvey']; ?>"
                    >
                            <span class="icon-import text-success"></span>
                            <?php eT("Upload & install"); ?>
                    </a>
                    <?php elseif (isset($fullpagebar['themes']['buttons']['uploadAndInstall']) && !$fullpagebar['themes']['canImport'] && isset($fullpagebar['themes']['importErrorMessage'])) : ?>
                        <!-- import disabled -->
                    <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom"
                          title="<?php echo $fullpagebar['themes']['importErrorMessage']['importErrorMessage']; ?>"
                          style="display: inline-block">
                    <button type="button" role="button" class="btn btn-outline-secondary btntooltip"
                            disabled="disabled">
                        <span class="icon-import text-success"></span>
                        <?php eT("Import"); ?>
                    </button>
                </span>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- List Surveys - Create a new Survey -->
                <?php if (
                    isset($fullpagebar['listSurveys']['buttons']['createSurvey']) &&
                    Permission::model()->hasGlobalPermission('surveys', 'create')
                ) : ?>
                <a class="btn btn-outline-secondary tab-dependent-button" data-tab="#surveys"
                   href="<?php echo $fullpagebar['listSurveys']['buttons']['createSurvey']['url']; ?>">
                        <span class="icon-add text-success"></span>
                    <?php eT("Create survey"); ?>
                </a>
            <?php endif; ?>

                <!-- List Surveys - Create a new Survey group -->
                <?php if (
                    isset($fullpagebar['listSurveys']['buttons']['createSurveyGroup']) &&
                    Permission::model()->hasGlobalPermission('surveysgroups', 'create')
                ) : ?>
                    <a class="btn btn-outline-secondary tab-dependent-button d-none"
                       data-tab="#surveygroups"
                       href="<?php echo $fullpagebar['listSurveys']['buttons']['createSurveyGroup']['url']; ?>">
                        <span class="icon-add text-success"></span>
                        <?php eT("Create survey group"); ?>
                    </a>
            <?php endif; ?>

                <!-- Dashboard - Add a new Box -->
            <?php if (isset($fullpagebar['boxbuttons'])): ?>
                    <!-- Create Box Button -->
                <a href="<?php echo $this->createUrl('homepageSettings/createBox/'); ?>"
                   class="btn btn-outline-secondary">
                        <span class="icon-add text-success"></span>
                        <?php eT("Create box"); ?>
                </a>
                <?php endif; ?>
            </div>

            <!-- Right actions -->
            <div class="col-md-auto text-end">

                <!-- Close -->
            <?php if (isset($fullpagebar['closebutton']['url'])) : ?>
                <a class="btn btn-danger"
                   href="<?php echo $fullpagebar['closebutton']['url']; ?>">
                        <span class="fa fa-close"></span>
                    <?php eT("Close"); ?>
                </a>
            <?php endif; ?>

                <!-- White Close button -->
                <?php if (isset($fullpagebar['white_closebutton']['url'])) : ?>
                <a class="btn btn-outline-secondary"
                   href="<?php echo $fullpagebar['white_closebutton']['url']; ?>">
                    <span class="fa fa-close"></span>
                    <?php eT("Close"); ?>
                </a>
            <?php endif; ?>

                <!-- Return -->
            <?php if (isset($fullpagebar['returnbutton']['url'])) : ?>
                <a class="btn btn-outline-secondary"
                   href="<?php echo $this->createUrl($fullpagebar['returnbutton']['url']); ?>">
                        <span class="fa fa-backward"></span>
                        &nbsp;&nbsp;
                        <?php echo $fullpagebar['returnbutton']['text']; ?>
                </a>
            <?php endif; ?>

                <!-- Save and Close -->
            <?php if (isset($fullpagebar['saveandclosebutton']['form'])) : ?>
                <a class="btn btn-outline-secondary"
                   href="#"
                   id="save-and-close-form-button"
                   onclick="$(this).addClass('disabled').attr('onclick', 'return false;');"
                   data-form-id="<?php echo $fullpagebar['saveandclosebutton']['form']; ?>">
                        <span class="fa fa-saved"></span>
                    <?php eT("Save and close"); ?>
                </a>
                <?php endif; ?>

                <!-- Save -->
            <?php if (isset($fullpagebar['savebutton']['form'])) : ?>
                <a class="btn btn-success"
                   href="#"
                   role="button"
                   id="save-form-button"
                   onclick="$(this).addClass('disabled').attr('onclick', 'return false;');"
                   data-form-id="<?php echo $fullpagebar['savebutton']['form']; ?>">
                        <span class="fa fa-check"></span>
                    <?php eT("Save"); ?>
                </a>
            <?php endif; ?>

                <!-- Box Buttons -->
            <?php if (isset($fullpagebar['boxbuttons'])) : ?>
                    <!-- Reset Boxes Button -->
                <a href="<?php echo $this->createUrl('homepageSettings/resetAllBoxes/'); ?>"
                   class="btn btn-warning"
                   data-confirm="<?php eT('This will delete all current boxes to restore the default ones. Are you sure you want to continue?'); ?>">
                    <span class="fa fa-refresh"></span>
                    <?php eT("Reset"); ?>
                </a>
                    <!-- Save Box Settings Button -->
                <a data-url="<?php echo $this->createUrl('homepageSettings/updateBoxesSettings'); ?>"
                   class="btn btn-success"
                   id="save_boxes_setting">
                        <?php eT('Save'); ?>
                </a>
            <?php endif; ?>

                <!-- Manage your Key -->
            <?php if (isset($fullpagebar['update'])) : ?>
                <a href="<?php echo $this->createUrl('admin/update/sa/managekey/'); ?>"
                   class="btn btn-outline-secondary">
                        <span class="fa fa-key text-success"></span>
                    <?php eT("Manage your key"); ?>
                </a>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>
