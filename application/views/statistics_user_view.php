<script type='text/javascript'>
    var graphUrl="<?php echo Yii::app()->getController()->createUrl("admin/statistics/sa/graph"); ?>";
</script>
        <div id='statsContainer'>
            <div id='statsHeader'>
                <div class='statsSurveyTitle'><?php echo $thisSurveyTitle; ?></div>
                <div class='statsNumRecords'><?php echo $clang->gT("Total records in survey")." : $totalrecords"; ?></div>
            </div>
            <?php if (isset($statisticsoutput) && $statisticsoutput) { echo $statisticsoutput; } ?><br />
        </div>
<?php 
    echo templatereplace(file_get_contents(getTemplatePath(validateTemplateDir($sTemplatePath))."/endpage.pstpl"),array(), $redata);
?>

