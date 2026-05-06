<!-- AUTOSCROLLING DIV CONTAINING QUESTION FILTERS -->
<div id="statisticsresponsefilters" class="statisticsfilters scrollheight_400">
    <input type='hidden' id='filterchoice_state' name='filterchoice_state' value='<?php echo $filterchoice_state; ?>' />

    <?php
        $dshresults = $dshresults ?? '';
        $dshresults2 = $dshresults2 ?? '';
    ?>
    <!-- Filter choice -->
    <?php $this->renderPartial(
        '/admin/export/statistics_subviews/_response_filter_choice',
        array(
            'filterchoice_state' => $filterchoice_state,
            'filters' => $filters,
            'aGroups' => $aGroups,
            'surveyid' => $surveyid,
            'result' => $result,
            'fresults' => $fresults,
            'summary' => $summary,
            'dateformatdetails' => $dateformatdetails,
            'oStatisticsHelper' => $oStatisticsHelper,
            'language' => $language,
            'dshresults' => $dshresults,
            'dshresults2' => $dshresults2,
        )
    );
    ?>

</div>

<p id='vertical_slide2'>
    <input type='submit' class="d-none" value='<?php eT("View statistics"); ?>' />
    <input type='button' class="d-none" value='<?php eT("Clear"); ?>' onclick="window.open('<?php echo Yii::app()->getController()->createUrl("admin/statistics/sa/index/surveyid/$surveyid"); ?>', '_top')" />
    <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
    <input type='hidden' name='display' value='stats' />
</p>
