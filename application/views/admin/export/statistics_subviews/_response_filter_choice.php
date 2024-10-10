<div id="filterchoices">
        <?php foreach ($aGroups as $groupKey => $aGroup) : ?>
            <div class="accordion mb-3" id="accordion_<?php echo $aGroup['gid']; ?>">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="grp_<?php echo $aGroup['gid']; ?>">
                        <button
                            class="accordion-button"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#grp_body_<?php echo $aGroup['gid']; ?>"
                            aria-expanded="true"
                            aria-controls="grp_<?php echo $aGroup['gid']; ?>">
                            <?php echo flattenText($aGroup['name']); ?>
                            (<?php echo gT("Question group") . $aGroup['gid']; ?>)
                        </button>
                    </h2>
                    <div
                        id="grp_body_<?php echo $aGroup['gid']; ?>"
                        class="accordion-collapse collapse show"
                        aria-labelledby="grp_<?php echo $aGroup['gid']; ?>"
                        data-bs-parent="#accordion_<?php echo $aGroup['gid']; ?>"
                    >
                        <div class="accordion-body">
                            <!-- Questions container -->
                            <div class="mb-3">
                                <input
                                    type="checkbox"
                                    id='btn_<?php echo $aGroup['gid']; ?>'
                                    onclick="selectCheckboxes('grp_body_<?php echo $aGroup['gid']; ?>', 'summary[]', 'btn_<?php echo $aGroup['gid']; ?>');"
                                /> All
                            </div>
                            <div id='grp_<?php echo $aGroup['gid']; ?>' class="ls-flex-row wrap filtertable">
                                <?php foreach ($aGroup['questions'] as  $key1 => $flt) : ?>
                                    <!-- Questions -->
                                    <?php $this->renderPartial('/admin/export/statistics_subviews/_question', array(
                                        'key1' => $key1,
                                        'flt' => $flt,
                                        'dateformatdetails' => $dateformatdetails,
                                        'filterchoice_state' => $filterchoice_state,
                                        'filters' => $filters,
                                        'aGroups' => $aGroups,
                                        'surveyid' => $surveyid,
                                        'result' => $result,
                                        'fresults' => $fresults,
                                        'summary' => $summary,
                                        'oStatisticsHelper' => $oStatisticsHelper,
                                        'language' => $language,
                                        'dshresults' => $dshresults,
                                        'dshresults2' => $dshresults2,
                                    )); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <?php endforeach; ?>
</div>
