<?php

/**
 * Menu Bar show for full pages (without sidemenu, inside configuration menus)
 */

?>

<!-- Full page menu bar -->
<div class='menubar surveybar' id="fullpagebar">
    <div class="container-fluid">
        <div class='row'>

            <!-- Left actions -->
            <div class="col text-start">
                <!-- Add new Menu -->
                <?php if (isset($fullpagebar['menus'])): ?>
                    <?php if (isset($fullpagebar['menus']['buttons']['addMenu']) && $fullpagebar['menus']['buttons']['addMenu']): ?>
                    <a class="btn btn-outline-secondary tab-dependent-button"
                           id="createnewmenu"
                           data-tab="#surveymenues"
                           title="<?php eT('Add new menu'); ?>"
                        >
                            <i class="ri-add-circle-fill text-success"></i>&nbsp;<?php eT('New menu') ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Add new Menu entry -->
                <?php if (isset($fullpagebar['menus'])) : ?>
                    <?php if (isset($fullpagebar['menus']['buttons']['addMenuEntry']) && $fullpagebar['menus']['buttons']['addMenuEntry']) : ?>
                    <a class="btn btn-outline-secondary tab-dependent-button"
                           id="createnewmenuentry"
                           data-tab="#surveymenuentries"
                           style="display:none;"
                           title="<?php eT('Add new menu entry'); ?>"
                        >
                            <i class="ri-add-circle-fill text-success"></i>&nbsp;<?php eT('New menu entry') ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Right actions -->
            <div class="col-md-auto text-end">

                <!-- White Close button -->
                <?php if (isset($fullpagebar['white_closebutton']['url'])) : ?>
                <a class="btn btn-outline-secondary" href="<?php echo $fullpagebar['white_closebutton']['url']; ?>" role="button">
                        <span class="ri-close-fill"></span>
                        <?php eT("Close"); ?>
                    </a>
                <?php endif; ?>

                <!-- Return -->
                <?php if (isset($fullpagebar['returnbutton']['url'])) : ?>
                <a class="btn btn-outline-secondary" href="<?php echo $this->createUrl($fullpagebar['returnbutton']['url']); ?>" role="button">
                        <span class="ri-rewind-fill"></span>
                        &nbsp;&nbsp;
                        <?php echo $fullpagebar['returnbutton']['text']; ?>
                    </a>
                <?php endif; ?>

                <!-- Reset -->
                <?php if (isset($fullpagebar['menus']['buttons']['reset']) && $fullpagebar['menus']['buttons']['reset']) : ?>
                    <a class="btn btn-warning"
                       id="restoreBtn"
                       href="#"
                    >
                        <i class="ri-refresh-line"></i>&nbsp;
                        <?php eT('Reset') ?>
                    </a>
                <?php endif; ?>

                <!-- Reorder -->
                <?php if (isset($fullpagebar['menus']['buttons']['reorder']) && $fullpagebar['menus']['buttons']['reorder']) : ?>
                    <a class="btn btn-warning"
                    type="button"
                    class="btn btn-warning"
                       id="reorderentries">
                        <i class="ri-order-play-fill"></i>&nbsp;
                    &nbsp;
                        <?php eT('Reorder') ?>
                    </a>
                <?php endif; ?>

                <!-- Save and Close -->
                <?php if (isset($fullpagebar['saveandclosebutton']['form'])) : ?>
                <a 
                    class="btn btn-outline-secondary" 
                    href="#" 
                    type="button" 
                    id="save-and-close-form-button" 
                    onclick="$(this).addClass('disabled').attr('onclick', 'return false;');" 
                    data-form-id="<?php echo $fullpagebar['saveandclosebutton']['form']; ?>">
                        <span class="ri-checkbox-circle-fill"></span>
                        <?php eT("Save and close"); ?>
                </a>
                <?php endif; ?>

                <!-- Save -->
                <?php if (isset($fullpagebar['savebutton']['form'])) : ?>
                <a 
                    class="btn btn-primary"
                    href="#"
                    type="button"
                    id="save-form-button" 
                    onclick="$(this).addClass('disabled').attr('onclick', 'return false;');" 
                    data-form-id="<?php echo $fullpagebar['savebutton']['form']; ?>">
                        <span class="ri-check-fill"></span>
                        <?php eT("Save"); ?>
                </a>
                <?php endif; ?>

                <!-- Close -->
                <?php if (isset($fullpagebar['closebutton']['url'])) : ?>
                <a 
                    class="btn btn-danger"
                    href="<?php echo $fullpagebar['closebutton']['url']; ?>"
                    type="button" 
                    style="box-shadow: 3px 3px 3px;">
                        <span class="ri-close-fill"></span>
                        <?php eT("Close"); ?>
                    </a>
                <?php endif; ?>

                <!-- Manage your Key -->
                <?php if (isset($fullpagebar['update'])) : ?>
                <a 
                    href="<?php echo $this->createUrl('admin/update/sa/managekey/');?>" 
                    class="btn btn-outline-secondary" 
                >
                        <span class="ri-key-2-fill text-success"></span>
                        <?php eT("Manage your key"); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
