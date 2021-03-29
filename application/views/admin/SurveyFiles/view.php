<?php
    /**
     * Global file management page
     */
?>
<?php $this->renderPartial("/admin/SurveyFiles/_jsVariables", ['data' => $jsData]); ?>
<div class="pagetitle h3"><?php eT("File management");?></div>
<div class="row" style="margin-bottom: 100px">
    <div class="container-fluid">
        <div id="limeSurveyFileManager"><filemanager /></div>
    </div>
</div>