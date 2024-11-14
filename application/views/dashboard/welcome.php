<?php

/**
 * The welcome page is the home page
 * TODO : make a recursive function, taking any number of box in the database, calculating how much rows are needed.
 */

/**
 * @var $belowLogoHtml String
 * @var $this AdminController
 * @var $oldDashboard bool
 **/

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('index');
?>

<?php
// Boxes are defined by user. We still want the default boxes to be translated.
gT('Create survey');
gT('Create a new survey');
gT('List surveys');
gT('List available surveys');
gT('Global settings');
gT('Edit global settings');
gT('ComfortUpdate');
gT('Stay safe and up to date');
gT('Label sets');
gT('Edit label sets');
gT('Themes');
?>

<!-- Welcome view -->
<div class="welcome">

    <!-- Logo & Presentation -->
    <?php if ($bShowLogo && $oldDashboard) : ?>
        <div class="jumbotron" id="welcome-jumbotron">
            <img alt="logo" src="<?php echo LOGO_URL; ?>" id="lime-logo" class="profile-img-card img-fluid" />
            <p class="d-xs-none"><?php echo PRESENTATION; // Defined in AdminController
            ?></p>
        </div>
    <?php endif; ?>

    <!-- Extra banner after logo-->
    <?= $belowLogoHtml ?>

    <!-- Message when first start -->
    <?php if ($countSurveyList == 0  && Permission::model()->hasGlobalPermission('surveys', 'create')) : ?>
        <script type="text/javascript">
            window.onload = function() {
                var welcomeModal = new bootstrap.Modal(document.getElementById('welcomeModal'));
                welcomeModal.show()
            };
        </script>

        <div class="modal fade" id="welcomeModal" aria-labelledby="welcome-modal-title">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5
                            class="modal-title"
                            id="welcome-modal-title"
                        ><?php echo sprintf(gT("Welcome to %s!"), 'LimeSurvey'); ?></h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                            aria-hidden="true"></button>
                    </div>
                    <div class="modal-body">
                        <div id="selector__welcome-modal--simplesteps">
                            <p><?php eT("Some piece-of-cake steps to create your very own first survey:"); ?></p>
                            <div>
                                <ol>
                                    <li><?php echo sprintf(
                                        gT('Create a new survey by clicking on the %s icon.'),
                                        "<i class='ri-add-circle-fill text-success'></i>"
                                        ); ?></li>
                                    <li><?php eT('Create a new question group inside your survey.'); ?></li>
                                    <li><?php eT('Create one or more questions inside the new question group.'); ?></li>
                                    <li><?php
                                        echo sprintf(
                                            gT('Done. Test your survey using the %s icon.'),
                                            "<i class='ri-settings-5-fill text-success'></i>"
                                        );
                                        ?></li>
                                </ol>
                            </div>
                            <div>
                                <hr />
                            </div>

                            <?php
                            // Hide this until we have fixed the tutorial
                            // @TODO FIX TUTORIAL
                            if (Permission::model()->hasGlobalPermission('surveys', 'create') && 1 == 2) { ?>
                                <div class="row" id="selector__welcome-modal--tutorial">
                                    <p><?php eT('Or, try out our interactive tutorial tour'); ?> </p>
                                    <p class="text-center">
                                        <button
                                            type="button"
                                            class="btn btn-primary btn-lg"
                                            id="selector__welcome-modal--starttour">
                                            <?php eT("Start the tour"); ?>
                                        </button>
                                    </p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal"><?php eT('Close'); ?></button>
                        <a
                            href="<?php echo $this->createUrl("surveyAdministration/newSurvey") ?>"
                            class="btn btn-primary">
                            <?php eT('Create a new survey'); ?>
                        </a>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    <?php endif; ?>

    <?php //Check for IE and show a warning box ?>
    <?php if (
    preg_match('~MSIE|Internet Explorer~i', (string)$_SERVER['HTTP_USER_AGENT'])
        || (strpos((string)$_SERVER['HTTP_USER_AGENT'], 'Trident/7.0') !== false
            && strpos((string)$_SERVER['HTTP_USER_AGENT'], 'rv:11.0') !== false
        )
) : ?>
        <div class="container">
            <?php
            $htmlContent = "
                <div class='row'>
                    <h4 class='col-12'><span class='ri-error-warning-fill'></span>" . gT('Warning!') . "</h4>
                </div>
                <div class='row'>
                    <div class='col-12'>" .
                gT('You are using Microsoft Internet Explorer.') . "<br/><br/>" .
                gT('LimeSurvey 3.x or newer does not support Internet Explorer for the LimeSurvey administration, anymore. However most of the functionality should still work.') . "<br/>" .
                gT('If you have any issues, please try using a modern browser first, before reporting it.') .
                "</div>
                </div>";
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => $htmlContent,
                'type' => 'danger',
                'showIcon' => false,
                'showCloseButton' => false,
                'htmlOptions' => ['id' => 'warningIE11']
            ]);
            ?>
        </div>
    <?php endif;?>
    <?php
    App()->getClientScript()->registerScript(
        'WelcomeCheckIESafety',
        "
    if(!/(MSIE|Trident\/)/i.test(navigator.userAgent)) {
        $('#warningIE11').remove();
    }
    ",
        LSYii_ClientScript::POS_POSTSCRIPT
    );
    ?>
    <!-- Last visited survey/question -->
    <?php
    // bShowLastSurveyAndQuestion is the homepage setting,
    // - showLastSurvey & showLastQuestion are about if infos are available
    if ($bShowLastSurveyAndQuestion && ($showLastSurvey || $showLastQuestion)) : ?>
        <div class="container-fluid text-end recent-activity p-2">
        <?php if ($showLastSurvey) : ?>
                <div id="last_survey" class=""> <!-- to enable rotation again set class back to "rotateShown" -->
                    <?php eT("Last visited survey:"); ?>
                    <a href="<?php echo $surveyUrl; ?>">
                        <?= viewHelper::flatEllipsizeText($surveyTitle, true, 60) ?>
                    </a>
                </div>
        <?php endif; ?>

        <?php if ($showLastQuestion) : ?>
                <div id="last_question" class=""> <!-- to enable rotation again set class back to "rotateHidden" -->
                    <?php eT("Last visited question:"); ?>
                    <a href="<?php echo $last_question_link; ?>">
                        <?= viewHelper::flatEllipsizeText($last_question_name, true, 60) ?>
                    </a>
                </div>
        <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Rendering all boxes in database -->
    <?php if ($oldDashboard) : ?>
        <?php $this->widget('ext.PanelBoxWidget.PanelBoxWidget', [
            'display'          => 'allboxesinrows',
            'boxesbyrow'       => $iBoxesByRow,
            'offset'           => $sBoxesOffSet,
            'boxesincontainer' => $bBoxesInContainer
        ]);
        ?>
    <?php endif; ?>

    <div class="survey-dashboard">
        <?php if (empty(App()->request->getQuery('viewtype')) && empty(SettingsUser::getUserSettingValue('welcome_page_widget'))) : ?>
                <div class="col-12">
                    <?php $this->widget('ext.admin.survey.ListSurveysWidget.ListSurveysWidget', [
                        'model' => $oSurveySearch,
                        'bRenderSearchBox' => $bShowSurveyListSearch,
                        'switch' => true
                    ]);
                    ?>
            </div>
        <?php elseif (
            (!empty(App()->request->getQuery('viewtype'))
                && App()->request->getQuery('viewtype') === 'list-widget'
            )
                    || (empty(App()->request->getQuery('viewtype'))
                    && (SettingsUser::getUserSettingValue('welcome_page_widget') === 'list-widget')
            )
        ) : ?>
                <div class="col-12">
                    <?php $this->widget('ext.admin.survey.ListSurveysWidget.ListSurveysWidget', [
                        'model' => $oSurveySearch,
                        'bRenderSearchBox' => $bShowSurveyListSearch,
                        'switch' => true
                    ]);
                    ?>
            </div>
        <?php else : ?>
                <div class="col-12">
                <?php $this->widget('ext.admin.BoxesWidget.BoxesWidget', [
                    'switch' => true,
                    'items'  => [
                        [
                            'type'  => 0,
                            'model' => Survey::model(),
                            'limit' => 20, // choose value according to pageSizeOptions
                        ],
                    ]
                ]);
                ?>
            </div>
        <?php endif; ?>
    </div>
    <!-- Notification setting -->
    <input type="hidden" id="absolute_notification" />
</div>
<style>
    /* This is only for the dashboard and should be removed once the background color is changed to $g-200 */
    body {
        background-color: #EEEFF7;
    }
</style>

