<div class='row'>
    <div class="col-sm-12" id="filterchoices">

        <?php foreach ($aGroups as $groupKey => $aGroup):?>

            <!-- Group Title -->
            <div class="row">
                <div class="col-sm-12">
                    <div class='header'>
                        <input type="checkbox" id='btn_<?php echo $aGroup['gid']; ?>' onclick="selectCheckboxes('grp_<?php echo $aGroup['gid']; ?>', 'summary[]', 'btn_<?php echo $aGroup['gid']; ?>');" />
                        <span class='smalltext'>
                            <strong>
                                <?php echo $aGroup['name']; ?>
                            </strong>
                            (<?php echo gT("Question group").$aGroup['gid']; ?>)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Questions container -->
            <div class="row" id="questionContainer">
                <div class="col-sm-12">
                    <div id='grp_<?php echo $aGroup['gid']; ?>' class="row filtertable ">

                    <?php foreach($aGroup['questions'] as  $key1 => $flt ): ?>

                        <!-- Questions -->
                        <?php $this->renderPartial('/admin/export/statistics_subviews/_question', array(
                                                                                                            'key1'=>$key1,
                                                                                                            'flt'=>$flt,
                                                                                                            'filterchoice_state'=>$filterchoice_state,
                                                                                                            'filters'=>$filters,
                                                                                                            'aGroups'=>$aGroups,
                                                                                                            'surveyid'=>$surveyid,
                                                                                                            'result'=>$result,
                                                                                                            'fresults'=>$fresults,
                                                                                                            'summary'=>$summary,
                                                                                                            'oStatisticsHelper'=>$oStatisticsHelper

                                                                                                         )) ; ?>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach;?>
</div></div>
