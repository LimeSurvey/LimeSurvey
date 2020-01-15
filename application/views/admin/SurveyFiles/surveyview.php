<?php
/**
 * Filemanager interface
 */
?>

<?php $this->renderPartial("/admin/SurveyFiles/_jsVariables", ['data' => $jsData]); ?>
<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <div class="container-fluid">
        <div id="limeSurveyFileManager"><filemanager <?=(isset($presetFolder) ? ' preset-folder="'.$presetFolder.'" ' : '')?> /></div>
    </div>
</div>
