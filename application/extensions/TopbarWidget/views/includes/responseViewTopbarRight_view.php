<?php if ($exist): ?>
    <?php if ($hasResponsesUpdatePermission && isset($rlanguage)): ?>
    <button 
        class="btn btn-outline-secondary" 
        href='<?php echo Yii::App()->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/{$surveyid}/id/{$id}/lang/$rlanguage"); ?>' 
        type="button">
        <span class="fa fa-pencil text-success"></span>
        <?php eT("Edit this entry"); ?>
    </button>
    <?php endif;?>
    <?php if ($hasResponsesDeletePermission && isset($rlanguage)): ?>
    <button 
        class="btn btn-outline-secondary"
        href='#'
        type="button" 
        onclick='if (confirm("<?php eT("Are you sure you want to delete this entry?", "js"); ?>")) { <?php echo convertGETtoPOST(Yii::App()->createUrl("admin/dataentry/sa/delete/", ['id' => $id, 'sid' => $surveyid])); ?>}'>
        <span class="fa fa-trash text-danger"></span>
        <?php eT("Delete this entry"); ?>
    </button>
    <?php endif;?>

    <?php if ($bHasFile): ?>
    <button
        class="btn btn-outline-secondary"
        href='<?php echo Yii::app()->createUrl("responses/downloadfiles", ["surveyId" =>$surveyid, "responseIds" =>$id]); ?>'
        type="button" >
        <span class="fa  fa-download-alt text-success"></span>
        <?php eT("Download files"); ?>
    </button>
    <?php endif;?>

    <button 
        class="btn btn-outline-secondary" 
        href='<?php echo Yii::App()->createUrl("admin/export/sa/exportresults/surveyid/$surveyid/id/$id"); ?>' 
        type="button">
        <span class="icon-export text-success downloadfile"></span>
        <?php eT("Export this response"); ?>
    </button>
<?php endif;?>

<button 
    href='<?php echo Yii::App()->createUrl("responses/view/", ['surveyId' => $surveyid, 'id' => $previous]); ?>'
    title='<?php eT("Show previous..."); ?>'
    class="btn btn-outline-secondary <?php if (!$previous) {echo 'disabled';}?>">
    <span class="icon-databack text-success" title='<?php eT("Show previous..."); ?>'></span>
    <?php eT("Show previous..."); ?>
</button>
<button 
    href='<?php echo Yii::App()->createUrl("responses/view/", ['surveyId' => $surveyid, 'id' => $next]); ?>'
    title='<?php eT("Show next..."); ?>'
    class="btn btn-outline-secondary <?php if (!$next) {echo 'disabled';}?>">
    <span class="icon-dataforward text-success" title='<?php eT("Show next..."); ?>'></span> 
    <?php eT("Show next..."); ?>
</button>

<button class="btn btn-danger" href="<?php echo $closeUrl; ?>" type="button">
    <span class="fa fa-close"></span>
    <?php eT("Close");?>
</button>
