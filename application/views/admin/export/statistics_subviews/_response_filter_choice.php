<div class='ls-flex-column col-12 ls-space padding top-5 left-5 bottom-5 right-5'>
    <div class="ls-flex-row wrap" id="filterchoices" <?php if ($filterchoice_state!='' || !empty($summary)) { echo " style='display:none' "; } ?>>
        
        <?php foreach ($aGroups as $groupKey => $aGroup):?>
            <!-- Group Title -->
            <div class="ls-flex-row wrap group-container">
                <div class="col-12 box" >
                    <div class='header '>
                        <input type="checkbox" id='btn_<?php echo $aGroup['gid']; ?>' onclick="selectCheckboxes('grp_<?php echo $aGroup['gid']; ?>', 'summary[]', 'btn_<?php echo $aGroup['gid']; ?>');" />

                        <span class="groupTitle">

                                <?php echo flattenText($aGroup['name']); ?>

                            (<?php echo gT("Question group").$aGroup['gid']; ?>)
                        </span>
                        <span class="fa fa-chevron-up group-question-chevron" data-grouptohide="grp_question_container_<?php echo $aGroup['gid']; ?>"></span>
                    </div>
                </div>


                <!-- Questions container -->
                <div class="ls-flex-row questionContainer" id="grp_question_container_<?php echo $aGroup['gid']; ?>">
                    <div id='grp_<?php echo $aGroup['gid']; ?>' class="ls-flex-row wrap filtertable ">
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
                                    'oStatisticsHelper'=>$oStatisticsHelper,
                                    'language'=>$language,
                                    'dshresults'=>$dshresults,
                                    'dshresults2'=>$dshresults2,
                                )) ; ?>

                        
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach;?>
</div></div>
