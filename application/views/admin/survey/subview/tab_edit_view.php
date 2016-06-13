<?php
/**
 * @var $aTabTitles
 * @var $aTabContents
 * @var $has_permissions
 * @var $surveyid
 * @var $surveyls_language
 */
 $count=0;
 ?>
<ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">
    <?php foreach ($aTabTitles as $i=>$eachtitle):?>
        <li role="presentation" class="<?php if($count==0) {echo "active"; }?>">
            <a data-toggle="tab" href="#edittxtele-<?php echo $count; $count++; ?>">
                <?php echo $eachtitle;?>
            </a>
        </li>
    <?php endforeach;?>
</ul>

<br/>

<div class="tab-content">
<?php foreach ($aTabContents as $i=>$sTabContent):?>
    <?php
        echo $sTabContent;
    ?>
<?php endforeach; ?>
</div>
