<?php 
    $extraClass = isset($extraClass) ? $extraClass : '';
    $title  = isset($title) ? $title : '';
?>
<div class='row container-fluid  ls-space padding left-0'>
    <div class="col-sm-12 ls-space padding left-0">
        <div id="breadcrumb-container" class="ls-ba">
            <div class="">
                <ol class="breadcrumb ls-flex-row align-items-center align-content-flex-start <?=$extraClass?>">
                    <li>
                        <a id="breadcrumb__surveylist--link" class="pjax animate home-icon" href="<?php echo App()->createUrl('admin/survey/sa/listsurveys');?>">
                            <img src="<?=LOGO_ICON_URL ?>" height="26" style="display:block;" title="<?php et('Survey List')?>" ></img>
                        </a>
                    </li>
                    <?php //If we are in a question view render the breadcrumb with question and question group ?>
                    <?php if(isset($oQuestion)): ?>
                        <li>
                            <a id="breadcrumb__survey--overview" class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/',['surveyid' => $oQuestion->sid] );?>">
                                <?php echo flattenText($oQuestion->survey->defaultlanguage->surveyls_title);?>
                                    (<?php echo flattenText($oSurvey->sid);?>)
                            </a>
                        </li>

                        <li>
                            <a id="breadcrumb__group--detail" class="pjax animate" href="<?php echo App()->createUrl('admin/questiongroups/sa/view/',['surveyid' => $oQuestion->sid,'gid'=>$oQuestion->gid] );?>">
                                <?php echo flattenText($oQuestion->groups->group_name);?>
                            </a>
                        </li>
                        <?php //If the question view is active right now, don't link it?>    
                        <?php if(!isset($active)): ?>
                            <li class="active">
                                <?php echo flattenText($oQuestion->title);?>
                            </li>
                        <?php else: ?>
                            <li>
                                <a id="breadcrumb__question--detail" class="pjax animate" href="<?php echo App()->createUrl('/admin/questions/sa/view/',['surveyid' => $oQuestion->sid,'gid' => $oQuestion->gid,'qid' => $oQuestion->qid] );?>">
                                    <?php echo flattenText($oQuestion->title);?>
                                </a>
                            </li>
                            <li class="active">
                                <?php echo $active;?>
                            </li>
                        <?php endif; ?>
                        <?php //If a subaction is defined, display it ?>
                        <?php if(isset($sSubaction)): ?>
                            <li class="active">
                                <?php echo $sSubaction;?>
                            </li>
                        <?php endif; ?>
                    <?php //If we are in a questiongroup view render the breadcrumb with question and question group ?>
                    <?php elseif(isset($oQuestionGroup)): ?>
                        <li>
                            <a id="breadcrumb__survey--overview" class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/',['surveyid' => $oQuestionGroup->sid] );?>">
                                <?php echo flattenText($oQuestionGroup->survey->defaultlanguage->surveyls_title);?>
                            </a>
                        </li>
                        <?php //If the questiongroup view is active right now, don't link it?>
                        <?php if(!isset($active)): ?>
                            <li class="active">
                                    <?php echo flattenText($oQuestionGroup->group_name);?>
                            </li>
                        <?php else: ?>
                            <li>
                                <a id="breadcrumb__group--detail" class="pjax animate" href="<?php echo App()->createUrl('admin/questiongroups/sa/view/',['surveyid' => $oQuestionGroup->sid,'gid'=>$oQuestionGroup->gid]  );?>">
                                    <?php echo flattenText($oQuestionGroup->group_name);?>
                                </a>
                            </li>
                            <li class="active">
                                <?php echo $active;?>
                            </li>
                            <?php if(isset($sSubaction)): ?>
                                <li class="active">
                                    <?php echo $sSubaction;?>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php elseif(isset($token)): ?>
                        <li>
                            <a id="breadcrumb__survey--overview" class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/',['surveyid' => $oSurvey->sid] );?>">
                                <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title);?>
                            </a>
                        </li>
                        <li>
                            <a id="breadcrumb__survey--participants" class="pjax animate" href="<?php echo App()->createUrl('admin/tokens/sa/index/',['surveyid' => $oSurvey->sid] );?>">
                                <?php eT('Survey participants');?>
                            </a>
                        </li>
                        <li class="active">
                            <?php echo $active;?>
                        </li>
                    <?php elseif(isset($oSurvey) && isset($sSubaction)): ?>
                            <li>
                                <a id="breadcrumb__survey--overview" class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/', ['surveyid' => $oSurvey->sid] );?>">
                                    <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title);?>
                                    (<?php echo flattenText($oSurvey->sid);?>)
                                </a>
                            </li>
                            <?php if(isset($sSimpleSubaction)): ?>
                                <li class="active">
                                    <?php echo $sSimpleSubaction;?>
                                </li>
                            <?php else: ?>
                                <li>
                                    <a id="breadcrumb__survey--subaction-<?php echo $sSubaction;?>" class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/', ['surveyid' => $oSurvey->sid , 'subaction' => $sSubaction] );?>">
                                        <?php echo $sSubaction;?>
                                    </a>
                                </li>
                            <?php endif; ?>

                    <?php elseif(isset($oSurvey)): ?>
                        <?php if(!isset($active)): ?>
                            <li>
                                <a id="breadcrumb__survey--overview" class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/',['surveyid' => $oSurvey->sid] );?>">
                                    <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title);?>
                                    (<?php echo flattenText($oSurvey->sid);?>)
                                </a>
                            </li>
                        <?php else: ?>
                        <li>
                            <a id="breadcrumb__survey--overview" class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/surveyid/'. $oSurvey->sid );?>">
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
    </div>
</div>
<input type="hidden" id="gettheuserid" value="<?php echo Yii::app()->user->id;?>" />
