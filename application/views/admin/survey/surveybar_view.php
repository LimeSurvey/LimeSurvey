<?php
/**
 * Display the survey bar.
 * Used for all survey editing action, and group / questions lists.
 * @var AdminController $this
 * @var Survey $oSurvey
 */
?>
<?php
/*
<topbar></topbar>
*/ 
?>
<div class='menubar surveybar' id="surveybarid">
    <div class='row container-fluid row-button-margin-bottom'>

        <?php // If there are no save or close buttons, take up some more space (useful for 1366x768 screens) ?>
        <?php if (!isset($surveybar['savebutton']['form']) && (!isset($surveybar['saveandclosebutton'])) && (!isset($surveybar['closebutton']))): ?>
            <div class="col-md-12 col-xs-6">
        <?php else : ?>
            <div class="col-md-8 col-xs-6">
        <?php endif; ?>

        <?php App()->getController()->renderPartial(
            '/admin/survey/surveybar_addgroupquestion',
            [
                'surveybar'      => $surveybar,
                'oSurvey'        => $oSurvey,
                'surveyHasGroup' => isset($surveyHasGroup) ? $surveyHasGroup : false
            ]
        ); ?>

        <!-- Left buttons for survey summary -->
        <?php if (isset($surveybar['buttons']['view'])):?>

            <?php App()->getController()->renderPartial(
                '/admin/survey/surveybar_activation',
                [
                    'oSurvey'       => $oSurvey,
                    'canactivate'   => $canactivate,
                    'surveycontent' => $surveycontent,
                    'icontext'      => $icontext,
                    'expired'       => $expired,
                    'notstarted'    => $notstarted
                ]
            ); ?>

            <!-- Tools  -->
            <?php if ($showToolsMenu): ?>
                <?php 
                    App()->getController()->renderPartial(
                        '/admin/survey/surveybar_tools',
                        [
                            'surveydelete'           => $surveydelete,
                            'oSurvey'                => $oSurvey,
                            'surveytranslate'        => $surveytranslate,
                            'hasadditionallanguages' => $hasadditionallanguages,
                            'conditionscount'        => $conditionscount,
                            'surveycontentread'      => $surveycontentread,
                            'onelanguage'            => $onelanguage,
                            'extraToolsMenuItems'    => $extraToolsMenuItems
                        ]
                    );
                ?>
            <?php endif; ?>

            <!-- Display/export -->
            <?php if ($surveyexport): ?>
                <?php App()->getController()->renderPartial(
                    '/admin/survey/surveybar_displayexport',
                    [
                        'respstatsread' => $respstatsread,
                        'surveyexport'  => $surveyexport,
                        'oSurvey'       => $oSurvey,
                        'onelanguage'   => $onelanguage
                    ]
                ); ?>
            <?php endif; ?>

            <!-- Token -->
            <?php if($tokenmanagement):?>
                <a class="btn btn-default pjax btntooltip hidden-xs" href="<?php echo $this->createUrl("admin/tokens/sa/index/surveyid/$surveyid"); ?>" role="button">
                    <span class="fa fa-user"></span>
                    <?php eT("Survey participants"); ?>
                </a>
            <?php endif; ?>

            <!-- Statistics -->
            <?php if ($respstatsread || $responsescreate || $responsesread):?>
                <?php App()->getController()->renderPartial(
                    '/admin/survey/surveybar_statistics',
                    [
                        'oSurvey'         => $oSurvey,
                        'respstatsread'   => $respstatsread,
                        'responsescreate' => $responsescreate,
                        'responsesread'   => $responsesread
                    ]
                ); ?>
            <?php endif; ?>

        <?php endif; ?>

        <!-- Extra menus from plugins -->
        <?php App()->getController()->renderPartial(
            '/admin/survey/surveybar_plugins',
            [
                'beforeSurveyBarRender' => $beforeSurveyBarRender
            ]
        ); ?>

        <?php if ($permission):?>
            <!-- List Groups -->
                <!-- admin/survey/sa/view/surveyid/838454 listquestiongroups($iSurveyID)-->
                <a class="btn btn-default hidden-sm  hidden-md hidden-lg" href="<?php echo $this->createUrl("admin/survey/sa/listquestiongroups/surveyid/$surveyid"); ?>">
                    <span class="fa fa-list"></span>
                    <?php eT("List question groups"); ?>
                </a>

            <!-- List Questions -->
                <a class="btn btn-default hidden-sm  hidden-md hidden-lg" href="<?php echo $this->createUrl("admin/survey/sa/listquestions/surveyid/$surveyid"); ?>">
                    <span class="fa fa-list"></span>
                    <?php eT("List questions"); ?>
                </a>
        <?php endif; ?>

        <?php if (isset($surveybar['importquestion'])):?>
            <a class="btn btn-default" href="<?php echo Yii::App()->createUrl('admin/questions/sa/importview/groupid/'.$groupid.'/surveyid/'.$surveyid); ?>" role="button">
                <span class="icon-import"></span>
                <?php eT('Import a question'); ?>
            </a>
        <?php endif; ?>

        <?php if (isset($surveybar['importquestiongroup'])):?>
            <a class="btn btn-default" href="<?php echo Yii::App()->createUrl('admin/questiongroups/sa/importview/surveyid/'.$surveyid); ?>" role="button">
                <span class="icon-import"></span>
                <?php eT('Import a group'); ?>
            </a>
        <?php endif; ?>
    </div>

    <!-- right action buttons -->
    <?php App()->getController()->renderPartial(
        '/admin/survey/surveybar_rightactionbuttons',
        [
            'surveybar'      => $surveybar,
            'surveyid'       => $oSurvey->sid
        ]
    ); ?>
    </div>
</div>
