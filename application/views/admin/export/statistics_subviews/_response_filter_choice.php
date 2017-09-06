<div class='row'>
    <div class="col-sm-12" id="filterchoices" <?php if ($filterchoice_state!='' || !empty($summary)) { echo " style='display:none' "; } ?>>

        <?php foreach ($aGroups as $groupKey => $aGroup):?>

            <!-- Group Title -->
            <div class="row group-container">
                <div class="col-sm-12 box" >
                    <div class='header '>
                        <input type="checkbox" id='btn_<?php echo $aGroup['gid']; ?>' onclick="selectCheckboxes('grp_<?php echo $aGroup['gid']; ?>', 'summary[]', 'btn_<?php echo $aGroup['gid']; ?>');" />

                        <span class="groupTitle">

                                <?php echo flattenText($aGroup['name']); ?>

                            (<?php echo gT("Question group").$aGroup['gid']; ?>)
                        </span>
                        <span class="glyphicon glyphicon-chevron-up group-question-chevron" data-grouptohide="grp_question_container_<?php echo $aGroup['gid']; ?>"></span>
                    </div>
                </div>


                <!-- Questions container -->
                <div class="col-sm-12 questionContainer" id="grp_question_container_<?php echo $aGroup['gid']; ?>">
                    <div id='grp_<?php echo $aGroup['gid']; ?>' class="row filtertable ">
                        <div class="col-sm-12">
                            <?php $count=0;?>
                    <?php foreach($aGroup['questions'] as  $key1 => $flt ): ?>
                        <?php
                            $count = $count+1;
                            if ( $count ==1 )
                            {
                                echo '<div class="row">';
                                $rowIsOpen = 1;
                            }
                        ?>
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

                        <?php
                            if($count==3)
                            {
                                echo '</div>';
                                $count = 0;
                                $rowIsOpen = 0;
                            }
                        ?>
                    <?php endforeach; ?>
                            <?php if($rowIsOpen):?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach;?>
</div></div>
