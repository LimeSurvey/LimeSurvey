<?php
    $extraClass = isset($extraClass) ? $extraClass : '';
    $title = isset($title) ? $title : '';
?>
<div class='row container-fluid  ls-space padding left-0'>
    <div class="col-sm-12 ls-space padding left-0">
        <div id="breadcrumb-container" class="ls-ba">
            <div class="">
                <ol class="breadcrumb ls-flex-row align-items-center align-content-flex-start <?php echo $extraClass?>">
                    <li>
                        <a id="breadcrumb__surveylist--link" class="pjax animate home-icon" href="<?php echo App()->createUrl('admin/survey/sa/listsurveys'); ?>">
                            <img src="<?php echo LOGO_ICON_URL ?>" height="26" style="display:block;" alt="Survey list" title="<?php et('Survey list')?>" />
                        </a>
                    </li>
                    <?php //First create the basis with a surveylink if set?>
                    <?php if (isset($oSurvey)): ?>
                        <?php if (!isset($oQuestionGroup)): ?>
                            <li>
                                <div>
                                    <a id="breadcrumb__survey--overview" class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/', ['surveyid' => $oSurvey->sid]); ?>">
                                    <i class="fa fa-home"></i> <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title,1); ?>
                                        (<?php echo $oSurvey->sid; ?>)
                                    </a>
                                </div>
                            </li>
                        <?php else: ?>
                            <li>
                                <div>
                                    <a id="breadcrumb__survey--overview" class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/surveyid/'.$oSurvey->sid); ?>">
                                    <i class="fa fa-home"></i> <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title,1); ?>
                                    </a>
                                </div>
                            </li>
                        <?php endif; ?>
<?php echo $sSimpleSubaction;?>

                            <?php if(isset($sSubaction) && !isset($oQuestionGroup) && !isset($oQuestion)):  ?>
                                <li class="marks_as_active">
                                    <?php echo gT($sSubaction)?>
                                </li>
                            <?php /* else: ?>
                                <li>
                                    <div>
                                        <a id="breadcrumb__survey--subaction-<?php echo strtolower(preg_replace('/\s/','',$sSubaction)); ?>" class="pjax animate" href="<?php echo App()->createUrl('/admin/survey/sa/view/', ['surveyid' => $oSurvey->sid, 'subaction' => $sSubaction]); ?>">
                                            <?php echo gT($sSubaction);?>
                                        </a>
                                    </div>
                                </li>
                            <?php */ endif; ?>

                    <?php endif; ?>

                    <?php //If we are in a questiongroup view render the breadcrumb with question group ?>
                    <?php if (isset($oQuestionGroup) ): ?>
                        <?php //If the questiongroup view is active right now, don't link it?>
                        <?php if(!$sSubaction && !isset($oQuestion)): ?>
                            <li class="marks_as_active">
                                <?= (
                                    $oQuestionGroup->isNewRecord
                                    ? gT('New question group')
                                    : viewHelper::flatEllipsizeText($oQuestionGroup->questionGroupL10ns[$oSurvey->language]->group_name, 1)
                                ); ?>
                            </li>
                        <?php else: ?>
                            <li>
                                <div>
                                    <a id="breadcrumb__group--detail" class="pjax animate" href="<?php echo App()->createUrl('admin/questiongroups/sa/view/', ['surveyid' => $oQuestionGroup->sid, 'gid'=>$oQuestionGroup->gid]); ?>">
                                        <?php echo viewHelper::flatEllipsizeText($oQuestionGroup->questionGroupL10ns[$oSurvey->language]->group_name, 1, 60, '...');?>
                                    </a>
                                </div>
                            </li>
                            <?php if(isset($sSubaction) && !isset($oQuestion)): ?>
                                <li class="marks_as_active">
                                    <?php echo gT($sSubaction)?>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php //If we are in a question view render the breadcrumb with the question ?>
                    <?php if (isset($oQuestion)): ?>
                        <?php //If the question view is active right now, don't link it?>
                        <?php if(!isset($sSubaction)): ?>
                            <li class="marks_as_active">
                                <?php echo $oQuestion->title;?>
                            </li>
                        <?php else: ?>
                            <li>
                                <div>
                                    <a id="breadcrumb__question--detail" class="pjax animate" href="<?php echo App()->createUrl('/admin/questions/sa/view/', ['surveyid' => $oQuestion->sid, 'gid' => $oQuestion->gid, 'qid' => $oQuestion->qid]); ?>">
                                        <?php echo $oQuestion->title; ?>
                                    </a>
                                </div>
                            </li>

                            <li class="marks_as_active">
                                <?php echo gT($sSubaction)?>
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
                            <?php echo gT($active)?>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($module_subaction)): ?>
                        <li>
                            <a id="breadcrumb__module--subaction" class="pjax animate" href="<?php echo $module_subaction_url?>">
                                <?php echo $module_subaction; ?>
                            </a>
                        </li>
                        <?php if (isset($module_current_action)): ?>
                        <li class="marks_as_active">
                            <?php echo $module_current_action; ?>
                        </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    </ol>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="gettheuserid" value="<?php echo Yii::app()->user->id; ?>" />
