<?php 
    $extraClass = isset($extraClass) ? $extraClass : '';
    $title  = isset($title) ? $title : '';
?>
<div class='row container-fluid'>
    <div class="col-xs-12 col-md-6">
        <div id="breadcrumb-container" class="ls-ba">
            <div class="">
                <ol class="breadcrumb <?=$extraClass?>">
                    <li>
                        <a class="pjax animate home-icon" href="<?php echo App()->createUrl('admin/survey/sa/listsurveys');?>">
                            <i class="fa fa-list-alt bigIcon" title="<?php et('Survey List')?>" >&nbsp;</i>
                        </a>
                    </li>
                    <?php if(isset($oQuestion)): ?>
                        <li>
                            <a class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/',['surveyid' => $oQuestion->sid] );?>">
                                <?php echo flattenText($oQuestion->survey->defaultlanguage->surveyls_title);?>
                            </a>
                        </li>

                        <li>
                            <a class="pjax animate" href="<?php echo App()->createUrl('admin/questiongroups/sa/view/',['surveyid' => $oQuestion->sid,'gid'=>$oQuestion->gid] );?>">
                                <?php echo flattenText($oQuestion->groups->group_name);?>
                            </a>
                        </li>
                        <?php if(!isset($active)): ?>
                            <li class="active">
                                <?php echo flattenText($oQuestion->title);?>
                            </li>
                        <?php else: ?>
                            <li>
                                <a class="pjax animate" href="<?php echo App()->createUrl('/admin/questions/sa/view/',['surveyid' => $oQuestion->sid,'gid' => $oQuestion->gid,'qid' => $oQuestion->qid] );?>">
                                    <?php echo flattenText($oQuestion->title);?>
                                </a>
                            </li>
                            <li class="active">
                                <?php echo $active;?>
                            </li>
                        <?php endif; ?>
                    <?php elseif(isset($oQuestionGroup)): ?>
                        <li>
                            <a class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/',['surveyid' => $oQuestionGroup->sid] );?>">
                                <?php echo flattenText($oQuestionGroup->survey->defaultlanguage->surveyls_title);?>
                            </a>
                        </li>

                        <?php if(!isset($active)): ?>
                        <li class="active">
                                <?php echo flattenText($oQuestionGroup->group_name);?>
                        </li>
                        <?php else: ?>
                            <li>
                                <a class="pjax animate" href="<?php echo App()->createUrl('admin/questiongroups/sa/view/',['surveyid' => $oQuestionGroup->sid,'gid'=>$oQuestionGroup->gid]  );?>">
                                    <?php echo flattenText($oQuestionGroup->group_name);?>
                                </a>
                            </li>
                            <li class="active">
                                <?php echo $active;?>
                            </li>
                        <?php endif; ?>
                    <?php elseif(isset($token)): ?>
                        <li>
                            <a class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/',['surveyid' => $oSurvey->sid] );?>">
                                <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title);?>
                            </a>
                        </li>
                        <li>
                            <a class="pjax animate" href="<?php echo App()->createUrl('admin/tokens/sa/index/',['surveyid' => $oSurvey->sid] );?>">
                                <?php eT('Survey participants');?>
                            </a>
                        </li>
                        <li class="active">
                            <?php echo $active;?>
                        </li>
                    <?php elseif(isset($oSurvey) && isset($sSubaction)): ?>
                            <li>
                                <a class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/', ['surveyid' => $oSurvey->sid] );?>">
                                    <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title);?>
                                </a>
                            </li>
                            <li>
                                <a class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/', ['surveyid' => $oSurvey->sid , 'subaction' => $sSubaction] );?>">
                                    <?php echo $sSubaction;?>
                                </a>
                            </li>
                    <?php elseif(isset($oSurvey)): ?>
                        <?php if(!isset($active)): ?>
                            <li>
                                <a class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/',['surveyid' => $oSurvey->sid] );?>">
                                    <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title);?>
                                </a>
                            </li>
                        <?php else: ?>
                        <li>
                            <a class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/surveyid/'. $oSurvey->sid );?>">
                                <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title);?>
                            </a>
                        </li>
                            <li class="active">
                                <?php echo $active;?>
                            </li>
                        <?php endif; ?>
                    <?php endif;?>
                    </ol>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-6 text-right">
            <p class="breadcrumb-title"><?= $title ?></p>
        </div>
    </div>
</div>
<input type="hidden" id="gettheuserid" value="<?php echo Yii::app()->user->id;?>" />