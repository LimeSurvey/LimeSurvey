<?php
$extraClass = isset($extraClass) ? $extraClass : '';
// TODO: never used $aData['title_bar']['title'] is checked for rendering of breadcrumb but $aData['subaction'] is what defines the actual title @link LayoutHelper::rendertitlebar
$title = isset($title) ? $title : '';
?>
<div class='row container-fluid  ls-space padding left-0'>
    <div class="col-12 ls-space padding left-0">
        <div id="breadcrumb-container" class="ls-ba ps-2">
            <div class="">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb ls-flex-row align-items-center align-content-flex-start <?php echo $extraClass?>">
                        <li class="breadcrumb-item" aria-current="page">
                        <a id="breadcrumb__surveylist--link" class="pjax animate home-icon" href="<?php echo App()->createUrl('surveyAdministration/listsurveys'); ?>">
                                <?php et('Surveys') ?>
                            </a>
                        </li>
                        <?php //First create the basis with a surveylink if set?>
                        <?php if (isset($oSurvey)): ?>
                            <?php if (!isset($oQuestionGroup)): ?>
                                <li class="breadcrumb-item" aria-current="page">
                                    <div>
                                    <a id="breadcrumb__survey--overview" class="pjax animate"
                                       href="<?php echo App()->createUrl('/surveyAdministration/view/', ['iSurveyID' => $oSurvey->sid]); ?>">
                                        <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title,1); ?>
                                            (<?php echo $oSurvey->sid; ?>)
                                        </a>
                                    </div>
                                </li>
                            <?php else: ?>
                                <li class="breadcrumb-item" aria-current="page">
                                    <div>
                                    <a id="breadcrumb__survey--overview" class="pjax animate"
                                       href="<?php echo App()->createUrl('/surveyAdministration/view/', ['iSurveyID' => $oSurvey->sid]); ?>">
                                        <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title,1); ?>
                                        </a>
                                    </div>
                                </li>
                            <?php endif; ?>
                        <?php echo $sSimpleSubaction; ?>

                        <?php if (isset($sSubaction) && !isset($oQuestionGroup) && !isset($oQuestion)): ?>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <div>
                                        <?php echo $sSubaction; ?>
                                    </div>
                            </li>
                        <?php /* else: ?>
                                <li class="breadcrumb-item">
                                    <div>
                                        <a id="breadcrumb__survey--subaction-<?php echo strtolower(preg_replace('/\s/','',$sSubaction)); ?>" class="pjax animate" href="<?php echo App()->createUrl('/surveyAdministration/view/', ['surveyid' => $oSurvey->sid, 'subaction' => $sSubaction]); ?>">
                                            <?php echo gT($sSubaction);?>
                                        </a>
                                    </div>
                                </li>
                            <?php */
                        endif; ?>

                    <?php endif; ?>

                    <?php //If we are in a questiongroup view render the breadcrumb with question group ?>
                    <?php if (isset($oQuestionGroup)): ?>
                        <?php //If the questiongroup view is active right now, don't link it?>
                        <?php if (!$sSubaction && !isset($oQuestion)): ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?= (
                                $oQuestionGroup->isNewRecord
                                    ? gT('New question group')
                                    : viewHelper::flatEllipsizeText($oQuestionGroup->questiongroupl10ns[$oSurvey->language]->group_name, 1)
                                ); ?>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item" aria-current="page">
                                <div>
                                    <a id="breadcrumb__group--detail" class="pjax animate"
                                       href="<?php echo App()->createUrl('questionGroupsAdministration/view/',
                                           ['surveyid' => $oQuestionGroup->sid, 'gid' => $oQuestionGroup->gid]); ?>">
                                        <?php echo viewHelper::flatEllipsizeText($oQuestionGroup->questiongroupl10ns[$oSurvey->language]->group_name,
                                            1,
                                            60,
                                            '...'); ?>
                                    </a>
                                </div>
                            </li>
                            <?php if (isset($sSubaction) && !isset($oQuestion)): ?>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <?php echo gT($sSubaction) ?>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php //If we are in a question view render the breadcrumb with the question ?>
                    <?php if (isset($oQuestion)): ?>
                        <?php //If the question view is active right now, don't link it?>
                        <?php if (!isset($sSubaction)): ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?php echo $oQuestion->title; ?>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item">
                                <div>
                                    <a id="breadcrumb__question--detail" class="pjax animate"
                                       href="<?php echo App()->createUrl('questionAdministration/view/',
                                           ['surveyid' => $oQuestion->sid, 'gid' => $oQuestion->gid, 'qid' => $oQuestion->qid]); ?>">
                                        <?php echo $oQuestion->title; ?>
                                    </a>
                                </div>
                            </li>

                            <li class="breadcrumb-item active" aria-current="page">
                                <?php echo gT($sSubaction) ?>
                            </li>

                        <?php endif; ?>

                    <?php endif; ?>

                    <?php if (isset($token)): ?>
                        <li class="breadcrumb-item" aria-current="page">
                            <a id="breadcrumb__survey--participants" class="pjax animate"
                               href="<?php echo App()->createUrl('admin/tokens/sa/index/', ['surveyid' => $oSurvey->sid]); ?>">
                                <?php eT('Survey participants'); ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?php echo gT($active) ?>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($module_subaction)): ?>
                        <li class="breadcrumb-item" aria-current="page">
                            <a id="breadcrumb__module--subaction" class="pjax animate" href="<?php echo $module_subaction_url ?>">
                                <?php echo $module_subaction; ?>
                            </a>
                        </li>
                        <?php if (isset($module_current_action)): ?>
                        <li class="breadcrumb-item active" aria-current="page">
                        <li class="marks_as_active">
                                <?php echo $module_current_action; ?>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="gettheuserid" value="<?php echo Yii::app()->user->id; ?>" />
