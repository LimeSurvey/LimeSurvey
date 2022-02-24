<?php if ($exist): ?>
    <?php if ($hasResponsesUpdatePermission && isset($rlanguage)): ?>
    <a class="btn btn-default" href='<?php echo Yii::App()->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/{$surveyid}/id/{$id}/lang/$rlanguage"); ?>' role="button">

        <span class="fa fa-pencil text-success"></span>
        <?php eT("Edit this entry"); ?>
    </a>
    <?php endif;?>
    <?php if ($hasResponsesDeletePermission && isset($rlanguage)): ?>
    <a class="btn btn-default" href='#' role="button" onclick='if (confirm("<?php eT("Are you sure you want to delete this entry?", "js"); ?>")) { <?php echo convertGETtoPOST(Yii::App()->createUrl("admin/dataentry/sa/delete/", ['id' => $id, 'sid' => $surveyid])); ?>}'>
        <span class="fa fa-trash text-danger"></span>
        <?php eT("Delete this entry"); ?>
    </a>
    <?php endif;?>

    <?php if ($bHasFile): ?>
    <a class="btn btn-default" href='<?php echo Yii::app()->createUrl("responses/downloadfiles", ["surveyId" =>$surveyid, "responseIds" =>$id]); ?>' role="button" >
        <span class="fa  fa-download-alt text-success"></span>
        <?php eT("Download files"); ?>
    </a>
    <?php endif;?>

    <a class="btn btn-default" href='<?php echo Yii::App()->createUrl("admin/export/sa/exportresults/surveyid/$surveyid/id/$id"); ?>' role="button" >
        <span class="icon-export text-success downloadfile"></span>
        <?php eT("Export this response"); ?>
    </a>
<?php endif;?>

<a href='<?php echo Yii::App()->createUrl("responses/view/", ['surveyId' => $surveyid, 'id' => $previous]); ?>' title='<?php eT("Show previous..."); ?>'
    class="btn btn-default <?php if (!$previous) {echo 'disabled';}?>">
    <span class="icon-databack text-success" title='<?php eT("Show previous..."); ?>'></span> <?php eT("Show previous..."); ?>
</a>
<a href='<?php echo Yii::App()->createUrl("responses/view/", ['surveyId' => $surveyid, 'id' => $next]); ?>' title='<?php eT("Show next..."); ?>'
    class="btn btn-default <?php if (!$next) {echo 'disabled';}?>">
    <span class="icon-dataforward text-success" title='<?php eT("Show next..."); ?>'></span> <?php eT("Show next..."); ?>
</a>

<a class="btn btn-danger" href="<?php echo $closeUrl; ?>" role="button">
    <span class="fa fa-close"></span>
    <?php eT("Close");?>
</a>
