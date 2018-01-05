<?php 
    $extraClass = isset($extraClass) ? $extraClass : '';
    $title = isset($title) ? $title : '';
?>
<div class='row container-fluid  ls-space padding left-0'>
    <div class="col-sm-12 ls-space padding left-0">
        <div id="breadcrumb-container" class="ls-ba">
            <div class="">
                <ol class="breadcrumb ls-flex-row align-items-center align-content-flex-start <?=$extraClass?>">
                    <li>
                        <a id="breadcrumb__surveylist--link" class="pjax animate home-icon" href="<?php echo App()->createUrl('admin/survey/sa/listsurveys'); ?>">
                            <img src="<?=LOGO_ICON_URL ?>" height="26" style="display:block;" alt="Survey list" title="<?php et('Survey list')?>" />
                        </a>
                    </li>
                    <?php //First create the basis with a surveylink if set?>
                    <?php if (isset($oSurvey)): ?>
                        <?php if (!isset($active) || isset($oQuestionGroup)): ?>
                            <li>
                                <div>
                                    <a id="breadcrumb__survey--overview" class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/', ['surveyid' => $oSurvey->sid]); ?>">
                                        <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title); ?>
                                        (<?php echo flattenText($oSurvey->sid); ?>)
                                    </a>
                                </div>
                            </li>
                        <?php else: ?>
                            <li>
                                <div>
                                    <a id="breadcrumb__survey--overview" class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/surveyid/'.$oSurvey->sid); ?>">
                                        <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title); ?>
                                    </a>
                                </div>
                            </li>
                            <li class="marks_as_active">
                                <?php echo $active;?>
                            </li>
                        <?php endif; ?>

                        <?php if(isset($sSubaction)): ?>
                            <?php if(isset($sSimpleSubaction)): ?>
                                <li class="marks_as_active">
                                    <?php echo $sSimpleSubaction;?>
                                </li>
                            <?php else: ?>
                                <li>
                                    <div>
                                        <a id="breadcrumb__survey--subaction-<?=strtolower(preg_replace('/\s/','',$sSubaction)); ?>" class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/', ['surveyid' => $oSurvey->sid, 'subaction' => $sSubaction]); ?>">
                                            <?php echo $sSubaction; ?>
                                        </a>
                                    </div>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>

                    <?php endif; ?>

                    <?php //If we are in a questiongroup view render the breadcrumb with question group ?>
                    <?php if (isset($oQuestionGroup)): ?>
                        <?php //If the questiongroup view is active right now, don't link it?>
                        <?php if(!isset($active) && !isset($oQuestion)): ?>
                            <li class="marks_as_active">
                                <?php echo flattenText($oQuestionGroup->questionGroupL10ns[$oSurvey->language]->group_name);?>
                            </li>                                                                     
                        <?php else: ?>
                            <li>
                                <div>
                                    <a id="breadcrumb__group--detail" class="pjax animate" href="<?php echo App()->createUrl('admin/questiongroups/sa/view/', ['surveyid' => $oQuestionGroup->sid, 'gid'=>$oQuestionGroup->gid]); ?>">
                                        <?php echo flattenText($oQuestionGroup->questionGroupL10ns[$oSurvey->language]->group_name); ?>
                                    </a>
                                </div>
                            </li>
                            <?php if(!isset($oQuestion)): ?>
                                <li class="marks_as_active">
                                    <?php echo $active;?>
                                </li>
                            <?php endif; ?>

                    
                            <?php if(!isset($oQuestion) && isset($sSubaction)): ?>
                                <li class="marks_as_active">
                                    <?php echo $sSubaction;?>
                                </li>
                            <?php endif; ?>

                        <?php endif; ?>
                    <?php endif; ?>

                    <?php //If we are in a question view render the breadcrumb with the question ?>
                    <?php if (isset($oQuestion)): ?>
                        <?php //If the question view is active right now, don't link it?>    
                        <?php if(!isset($active)): ?>
                            <li class="marks_as_active">
                                <?php echo flattenText($oQuestion->title);?>
                            </li>
                        <?php else: ?>
                            <li>
                                <div>
                                    <a id="breadcrumb__question--detail" class="pjax animate" href="<?php echo App()->createUrl('/admin/questions/sa/view/', ['surveyid' => $oQuestion->sid, 'gid' => $oQuestion->gid, 'qid' => $oQuestion->qid]); ?>">
                                        <?php echo flattenText($oQuestion->title); ?>
                                    </a>
                                </div>
                            </li>
                            <li class="marks_as_active">
                                <?php echo $active;?>
                            </li>
                        <?php endif; ?>


                        <?php //If a subaction is defined, display it ?>
                        <?php if(isset($sSubaction)): ?>
                            <li class="marks_as_active">
                                <?php echo $sSubaction;?>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>


                        
                    <?php if (isset($token)): ?>
                        <li>
                            <a id="breadcrumb__survey--participants" class="pjax animate" href="<?php echo App()->createUrl('admin/tokens/sa/index/', ['surveyid' => $oSurvey->sid]); ?>">
                                <?php eT('Survey participants'); ?>
                            </a>
                        </li>
                        <li class="marks_as_active">
                            <?php echo $active;?>
                        </li>
                    <?php endif; ?>

                    </ol>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="gettheuserid" value="<?php echo Yii::app()->user->id; ?>" />
