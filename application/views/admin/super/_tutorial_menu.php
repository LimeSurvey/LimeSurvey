<?php
    $aTutorials = Tutorial::model()->getActiveTutorials();
?>
<?php if(!empty($aTutorials) && Permission::model()->hasGlobalPermission('surveys', 'create')) { ?>
<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <span class="fa fa-rocket" ></span>
        <?php eT('Tutorials');?>
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu " id="tutorials-dropdown">
        <?php foreach($aTutorials as $oTutorial) { ?>
            <li>
                <a href="#" onclick="window.tourLibrary.triggerTourStart('<?=$oTutorial->name?>')">
                    <i class="fa <?=$oTutorial->icon?>"></i>&nbsp;<?=$oTutorial->title?>
                </a>
            </li>
        <?php } ?>
    </ul>
</li>
<?php } ?>
