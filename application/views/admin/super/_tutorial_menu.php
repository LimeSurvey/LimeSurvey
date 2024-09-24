<?php
    //$aTutorials = Tutorial::model()->getActiveTutorials();
    // Hide this until we have fixed the tutorial
    // @TODO FIX TUTORIAL
    $aTutorials = [];
?>
<?php
if (!empty($aTutorials) && Permission::model()->hasGlobalPermission('surveys', 'create')) { ?>
<li class="dropdown dropdown-submenu">
    <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
        <span class="ri-rocket-fill" ></span>
        <?php eT('Tutorials');?>
        <i class="ri-arrow-up-s-line float-end"></i>
    </a>
    <ul class="dropdown-menu larger-dropdown" id="tutorials-dropdown">
        <?php foreach ($aTutorials as $oTutorial) { ?>
            <li>
                <a href="#" onclick="window.tourLibrary.triggerTourStart('<?=$oTutorial->name?>')">
                    <i class="fa <?=$oTutorial->icon?>"></i>&nbsp;<?=$oTutorial->title?>
                </a>
            </li>
        <?php } ?>
        <?php if (!empty($aTutorials) && Permission::model()->hasGlobalPermission('superadmin', 'read')) { ?>
            <li class="dropdown-divider"></li>
            <li>
                <a href="<?=App()->createUrl('admin/tutorials/sa/view')?>">
                    <span class="ri-rocket-fill" ></span>
                    <?php eT('View all tutorials');?>
                    <i class="ri-search-line float-end"></i>
                </a>
            </li>
            <li>
                <a href="<?=App()->createUrl('admin/tutorials/sa/create')?>">
                    <span class="ri-rocket-fill" ></span>
                    <?php eT('Create tutorial');?>
                    <i class="ri-add-line float-end"></i>
                </a>
            </li>
        <?php } ?>
    </ul>
</li>
<?php } ?>

