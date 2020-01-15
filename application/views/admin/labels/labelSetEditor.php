<?php
    /**
     * Global file management page
     */
?>
<script>
    window.LabelSetData = <?=json_encode($jsVariables)?>;
</script>

<div class="pagetitle h3"><?php eT("Label set editor");?></div>
<div class="row" style="margin-bottom: 100px">
    <div class="container-fluid">
        <div id="labelSetEditor"><label-set-app /></div>
    </div>
</div>