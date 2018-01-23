<?php
    $aTutorials = Tutorial::model()->getActiveTutorials();
    
?>

<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <span class="fa fa-rocket" ></span>
        <?php eT('Tutorial');?>
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

<script>
    console.log(<?=json_encode( array_map(function($tut){return $tut->attributes;}, $aTutorials) )?>);
</script>
