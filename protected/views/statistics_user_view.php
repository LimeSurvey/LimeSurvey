<script type='text/javascript'>
    var graphUrl="<?php use ls\models\Template;echo Yii::app()->getController()->createUrl("admin/statistics/sa/graph"); ?>";
</script>
        <div id='statsContainer'>
            <div id='statsHeader'>
                <div class='statsSurveyTitle'><?php echo $thisSurveyTitle; ?></div>
                <div class='statsNumRecords'><?php echo gT("Total records in survey")." : $totalrecords"; ?></div>
            </div>
            <?php if (isset($statisticsoutput) && $statisticsoutput) { echo $statisticsoutput; } ?><br />
        </div>
<?php 
    echo \ls\helpers\Replacements::templatereplace(file_get_contents(Template::getTemplatePath($sTemplatePath) . "/endpage.pstpl"), array(),
        $redata);
?>

