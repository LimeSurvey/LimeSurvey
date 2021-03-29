<?php
    //$aTutorials = Tutorial::model()->getActiveTutorials();
    // Hide this until we have fixed the tutorial
    // @TODO FIX TUTORIAL
    $aTutorials = [];
?>
<?php
if (!empty($aTutorials) && Permission::model()->hasGlobalPermission('surveys', 'create')) { ?>
<li class="dropdown dropdown-submenu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <span class="fa fa-rocket" ></span>
        <?php eT('Tutorials');?>
        <i class="fa fa-chevron-right pull-right"></i>
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
            <li class="divider"></li>
            <li>
                <a href="<?=App()->createUrl('admin/tutorials/sa/view')?>">
                    <span class="fa fa-rocket" ></span>
                    <?php eT('See all Tutorial');?>
                    <i class="fa fa-search pull-right"></i>
                </a>
            </li>
            <li>
                <a href="<?=App()->createUrl('admin/tutorials/sa/create')?>">
                    <span class="fa fa-rocket" ></span>
                    <?php eT('Create Tutorial');?>
                    <i class="fa fa-plus pull-right"></i>
                </a>
            </li>
        <?php } ?>
    </ul>
</li>
<?php } ?>

