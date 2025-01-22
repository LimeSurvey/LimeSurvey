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

$surveyCounts = $this->getSurveyCounts();
$activeSurveys = $this->getFirstFiveActiveSurveysWithResponses();
$surveyList = $this->getSurveyList();
$recentActivities = $this->getRecentActivitySummary();
?>

<style>
    .survey-icon {
        border-radius: 50%;
        background-color: rgba(221, 225, 230, 1);
        width: fit-content;
        padding: 10px 12px
    }

    .card {
        border-radius: 12px;
    }

    .quick-actions .btn {
        width: 100%;
        margin-bottom: 10px;
    }

    .active-surveys,
    .recent-activity {
        max-height: 400px;
        overflow-y: auto;
    }

    .chart-container {
        height: 250px;
    }

    .nav-tabs {
        border-bottom: 1px solid #ddd;
    }

    .nav-tabs .nav-link {
        color: #555;

        font-weight: bold;
    }

    .nav-tabs .nav-link.active {
        border-bottom: 3px solid #122867;

        border-top: none;
        background: none;

    }
</style>



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
                            id="welcome-modal-title"><?php echo sprintf(gT("Welcome to %s!"), 'GititSurvey'); ?></h5>
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
                                            "<i class='ri-add-circle-fill text-primary'></i>"
                                        ); ?></li>
                                    <li><?php eT('Create a new question group inside your survey.'); ?></li>
                                    <li><?php eT('Create one or more questions inside the new question group.'); ?></li>
                                    <li><?php
                                        echo sprintf(
                                            gT('Done. Test your survey using the %s icon.'),
                                            "<i class='ri-settings-5-fill text-primary'></i>"
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

    <?php //Check for IE and show a warning box 
    ?>
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
                gT('GititSurvey 3.x or newer does not support Internet Explorer for the GititSurvey administration, anymore. However most of the functionality should still work.') . "<br/>" .
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
    <?php endif; ?>
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
        <?php elseif (
            (!empty(App()->request->getQuery('viewtype'))
                && App()->request->getQuery('viewtype') === 'list-widget'
            )
            || (empty(App()->request->getQuery('viewtype'))
                && (SettingsUser::getUserSettingValue('welcome_page_widget') === 'list-widget')
            )
        ) : ?>
            <div class="col-12">
                <div class="d-flex gap-3 mt-2 align-items-center align-center">

                    <h3 class="mt-3 fw-bolder fs-3"> Dashboard</h3>



                    <?php if (Permission::model()->hasGlobalPermission('surveys', 'create')) : ?>
                        <a href="<?= Yii::app()->createUrl('surveyAdministration/newSurvey') ?>" id="createSurvey" class="btn btn-outline-info survey-actionbar-button bg-transparent">
                            <i class="ri-add-line"></i>
                            <?= gT('Create survey') ?>
                        </a>
                    <?php endif; ?>
                </div>


                <div class="row mt-4">

                    <!-- Active survey -->
                    <div class="col-lg-3 col-sm-12 mb-4 mb-lg-0">
                        <div class="card">
                            <div class="card-body">

                                <div class="d-flex gap-3">

                                    <div class="survey-icon">
                                        <div>

                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M4 10V20H16V10H4ZM6 8V4C6 3.46957 6.21071 2.96086 6.58579 2.58579C6.96086 2.21071 7.46957 2 8 2H20C20.5304 2 21.0391 2.21071 21.4142 2.58579C21.7893 2.96086 22 3.46957 22 4V14C22 14.5304 21.7893 15.0391 21.4142 15.4142C21.0391 15.7893 20.5304 16 20 16H18V20C18 20.5304 17.7893 21.0391 17.4142 21.4142C17.0391 21.7893 16.5304 22 16 22H4C3.46957 22 2.96086 21.7893 2.58579 21.4142C2.21071 21.0391 2 20.5304 2 20V10C2 9.46957 2.21071 8.96086 2.58579 8.58579C2.96086 8.21071 3.46957 8 4 8H6ZM8 8H16C16.5304 8 17.0391 8.21071 17.4142 8.58579C17.7893 8.96086 18 9.46957 18 10V14H20V4H8V8ZM8 17C7.20435 17 6.44129 16.6839 5.87868 16.1213C5.31607 15.5587 5 14.7956 5 14C5 13.2044 5.31607 12.4413 5.87868 11.8787C6.44129 11.3161 7.20435 11 8 11C8.79565 11 9.55871 11.3161 10.1213 11.8787C10.6839 12.4413 11 13.2044 11 14C11 14.7956 10.6839 15.5587 10.1213 16.1213C9.55871 16.6839 8.79565 17 8 17ZM8 15C8.26522 15 8.51957 14.8946 8.70711 14.7071C8.89464 14.5196 9 14.2652 9 14C9 13.7348 8.89464 13.4804 8.70711 13.2929C8.51957 13.1054 8.26522 13 8 13C7.73478 13 7.48043 13.1054 7.29289 13.2929C7.10536 13.4804 7 13.7348 7 14C7 14.2652 7.10536 14.5196 7.29289 14.7071C7.48043 14.8946 7.73478 15 8 15ZM9 8C9 7.20435 9.31607 6.44129 9.87868 5.87868C10.4413 5.31607 11.2044 5 12 5C12.7956 5 13.5587 5.31607 14.1213 5.87868C14.6839 6.44129 15 7.20435 15 8H13C13 7.73478 12.8946 7.48043 12.7071 7.29289C12.5196 7.10536 12.2652 7 12 7C11.7348 7 11.4804 7.10536 11.2929 7.29289C11.1054 7.48043 11 7.73478 11 8H9ZM10.864 21.518L9.136 20.511L11.861 15.838C12.0975 15.4322 12.4261 15.0876 12.8201 14.832C13.2142 14.5763 13.6627 14.4168 14.1297 14.3662C14.5967 14.3156 15.069 14.3754 15.5087 14.5407C15.9483 14.706 16.343 14.9723 16.661 15.318L17.749 16.502L16.276 17.856L15.189 16.672C15.083 16.5568 14.9514 16.4681 14.8048 16.413C14.6582 16.358 14.5007 16.3381 14.3451 16.355C14.1894 16.372 14.0399 16.4252 13.9086 16.5105C13.7773 16.5958 13.6678 16.7107 13.589 16.846L10.864 21.518ZM17.376 8.548C17.9375 8.33129 18.5513 8.28884 19.1373 8.4262C19.7233 8.56355 20.2543 8.87434 20.661 9.318L21.749 10.502L20.276 11.856L19.189 10.672C19.0414 10.5114 18.8453 10.4034 18.6306 10.3646C18.416 10.3258 18.1945 10.3582 18 10.457V10C18 9.429 17.76 8.913 17.376 8.549V8.548Z" fill="#697077" />
                                            </svg>
                                        </div>


                                    </div>
                                    <div>
                                        <span style="color:#14AE5C"> Active Survey</span>
                                        <p class="m-0 fw-bolder">
                                            <?= $surveyCounts['active'] ?>
                                        </p>
                                    </div>
                                </div>




                            </div>

                        </div>

                    </div>

                    <!-- draft -->
                    <div class="col-lg-3 col-sm-12 mb-4 mb-lg-0">
                        <div class="card">
                            <div class="card-body">

                                <div class="d-flex gap-3">

                                    <div class="survey-icon">
                                        <div>

                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M4 10V20H16V10H4ZM6 8V4C6 3.46957 6.21071 2.96086 6.58579 2.58579C6.96086 2.21071 7.46957 2 8 2H20C20.5304 2 21.0391 2.21071 21.4142 2.58579C21.7893 2.96086 22 3.46957 22 4V14C22 14.5304 21.7893 15.0391 21.4142 15.4142C21.0391 15.7893 20.5304 16 20 16H18V20C18 20.5304 17.7893 21.0391 17.4142 21.4142C17.0391 21.7893 16.5304 22 16 22H4C3.46957 22 2.96086 21.7893 2.58579 21.4142C2.21071 21.0391 2 20.5304 2 20V10C2 9.46957 2.21071 8.96086 2.58579 8.58579C2.96086 8.21071 3.46957 8 4 8H6ZM8 8H16C16.5304 8 17.0391 8.21071 17.4142 8.58579C17.7893 8.96086 18 9.46957 18 10V14H20V4H8V8ZM8 17C7.20435 17 6.44129 16.6839 5.87868 16.1213C5.31607 15.5587 5 14.7956 5 14C5 13.2044 5.31607 12.4413 5.87868 11.8787C6.44129 11.3161 7.20435 11 8 11C8.79565 11 9.55871 11.3161 10.1213 11.8787C10.6839 12.4413 11 13.2044 11 14C11 14.7956 10.6839 15.5587 10.1213 16.1213C9.55871 16.6839 8.79565 17 8 17ZM8 15C8.26522 15 8.51957 14.8946 8.70711 14.7071C8.89464 14.5196 9 14.2652 9 14C9 13.7348 8.89464 13.4804 8.70711 13.2929C8.51957 13.1054 8.26522 13 8 13C7.73478 13 7.48043 13.1054 7.29289 13.2929C7.10536 13.4804 7 13.7348 7 14C7 14.2652 7.10536 14.5196 7.29289 14.7071C7.48043 14.8946 7.73478 15 8 15ZM9 8C9 7.20435 9.31607 6.44129 9.87868 5.87868C10.4413 5.31607 11.2044 5 12 5C12.7956 5 13.5587 5.31607 14.1213 5.87868C14.6839 6.44129 15 7.20435 15 8H13C13 7.73478 12.8946 7.48043 12.7071 7.29289C12.5196 7.10536 12.2652 7 12 7C11.7348 7 11.4804 7.10536 11.2929 7.29289C11.1054 7.48043 11 7.73478 11 8H9ZM10.864 21.518L9.136 20.511L11.861 15.838C12.0975 15.4322 12.4261 15.0876 12.8201 14.832C13.2142 14.5763 13.6627 14.4168 14.1297 14.3662C14.5967 14.3156 15.069 14.3754 15.5087 14.5407C15.9483 14.706 16.343 14.9723 16.661 15.318L17.749 16.502L16.276 17.856L15.189 16.672C15.083 16.5568 14.9514 16.4681 14.8048 16.413C14.6582 16.358 14.5007 16.3381 14.3451 16.355C14.1894 16.372 14.0399 16.4252 13.9086 16.5105C13.7773 16.5958 13.6678 16.7107 13.589 16.846L10.864 21.518ZM17.376 8.548C17.9375 8.33129 18.5513 8.28884 19.1373 8.4262C19.7233 8.56355 20.2543 8.87434 20.661 9.318L21.749 10.502L20.276 11.856L19.189 10.672C19.0414 10.5114 18.8453 10.4034 18.6306 10.3646C18.416 10.3258 18.1945 10.3582 18 10.457V10C18 9.429 17.76 8.913 17.376 8.549V8.548Z" fill="#697077" />
                                            </svg>
                                        </div>


                                    </div>
                                    <div>
                                        <span style="color:#6EDEEF"> Draft</span>
                                        <p class="m-0 fw-bolder">
                                            <?= $surveyCounts['draft'] ?>
                                        </p>
                                    </div>
                                </div>


                            </div>

                        </div>

                    </div>

                    <!-- closed surveys -->
                    <div class="col-lg-4 col-sm-12 mb-4 mb-lg-0">
                        <div class="card">

                            <div class="card-body">

                                <div class="d-flex gap-3">

                                    <div class="survey-icon">
                                        <div>

                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M4 10V20H16V10H4ZM6 8V4C6 3.46957 6.21071 2.96086 6.58579 2.58579C6.96086 2.21071 7.46957 2 8 2H20C20.5304 2 21.0391 2.21071 21.4142 2.58579C21.7893 2.96086 22 3.46957 22 4V14C22 14.5304 21.7893 15.0391 21.4142 15.4142C21.0391 15.7893 20.5304 16 20 16H18V20C18 20.5304 17.7893 21.0391 17.4142 21.4142C17.0391 21.7893 16.5304 22 16 22H4C3.46957 22 2.96086 21.7893 2.58579 21.4142C2.21071 21.0391 2 20.5304 2 20V10C2 9.46957 2.21071 8.96086 2.58579 8.58579C2.96086 8.21071 3.46957 8 4 8H6ZM8 8H16C16.5304 8 17.0391 8.21071 17.4142 8.58579C17.7893 8.96086 18 9.46957 18 10V14H20V4H8V8ZM8 17C7.20435 17 6.44129 16.6839 5.87868 16.1213C5.31607 15.5587 5 14.7956 5 14C5 13.2044 5.31607 12.4413 5.87868 11.8787C6.44129 11.3161 7.20435 11 8 11C8.79565 11 9.55871 11.3161 10.1213 11.8787C10.6839 12.4413 11 13.2044 11 14C11 14.7956 10.6839 15.5587 10.1213 16.1213C9.55871 16.6839 8.79565 17 8 17ZM8 15C8.26522 15 8.51957 14.8946 8.70711 14.7071C8.89464 14.5196 9 14.2652 9 14C9 13.7348 8.89464 13.4804 8.70711 13.2929C8.51957 13.1054 8.26522 13 8 13C7.73478 13 7.48043 13.1054 7.29289 13.2929C7.10536 13.4804 7 13.7348 7 14C7 14.2652 7.10536 14.5196 7.29289 14.7071C7.48043 14.8946 7.73478 15 8 15ZM9 8C9 7.20435 9.31607 6.44129 9.87868 5.87868C10.4413 5.31607 11.2044 5 12 5C12.7956 5 13.5587 5.31607 14.1213 5.87868C14.6839 6.44129 15 7.20435 15 8H13C13 7.73478 12.8946 7.48043 12.7071 7.29289C12.5196 7.10536 12.2652 7 12 7C11.7348 7 11.4804 7.10536 11.2929 7.29289C11.1054 7.48043 11 7.73478 11 8H9ZM10.864 21.518L9.136 20.511L11.861 15.838C12.0975 15.4322 12.4261 15.0876 12.8201 14.832C13.2142 14.5763 13.6627 14.4168 14.1297 14.3662C14.5967 14.3156 15.069 14.3754 15.5087 14.5407C15.9483 14.706 16.343 14.9723 16.661 15.318L17.749 16.502L16.276 17.856L15.189 16.672C15.083 16.5568 14.9514 16.4681 14.8048 16.413C14.6582 16.358 14.5007 16.3381 14.3451 16.355C14.1894 16.372 14.0399 16.4252 13.9086 16.5105C13.7773 16.5958 13.6678 16.7107 13.589 16.846L10.864 21.518ZM17.376 8.548C17.9375 8.33129 18.5513 8.28884 19.1373 8.4262C19.7233 8.56355 20.2543 8.87434 20.661 9.318L21.749 10.502L20.276 11.856L19.189 10.672C19.0414 10.5114 18.8453 10.4034 18.6306 10.3646C18.416 10.3258 18.1945 10.3582 18 10.457V10C18 9.429 17.76 8.913 17.376 8.549V8.548Z" fill="#697077" />
                                            </svg>
                                        </div>


                                    </div>
                                    <div>
                                        <span style="color:#697077"> Closed Surveys</span>
                                        <p class="m-0 fw-bolder">
                                            <?= $surveyCounts['closed'] ?>
                                        </p>
                                    </div>
                                </div>


                            </div>



                        </div>

                    </div>
                </div>



                <div class="container mt-5">

                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs" id="customTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">Overview</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="surveys-tab" data-bs-toggle="tab" data-bs-target="#surveys" type="button" role="tab" aria-controls="surveys" aria-selected="false">Surveys(<?= $surveyCounts['all'] ?>)</button>
                        </li>
                        <!-- <li class="nav-item" role="presentation">
            <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button" role="tab" aria-controls="reports" aria-selected="false">Reports & Analytics</button>
        </li> -->
                    </ul>
                    <!-- Tab Content -->
                    <div class="tab-content bg-transparent p-0" id="customTabsContent">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">



                            <!-- overView Tab -->

                            <div class="container-fluid">
                                <div class="row my-2">
                                    <div class="col-md-12 col-lg-5">
                                        <div class="card card-body">
                                            <h4 class="mb-4 fs-6">Quick Actions</h4>

                                            <div class="row">
                                                <div class="col-md-12 col-lg-6">
                                                    <a href="<?= Yii::app()->createUrl('surveyAdministration/newSurvey') ?>" class="card card-body">

                                                        <div class="d-flex align-center align-items-center gap-3">

                                                            <div class="survey-icon" style="padding:9px">
                                                                <div>

                                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M4 10V20H16V10H4ZM6 8V4C6 3.46957 6.21071 2.96086 6.58579 2.58579C6.96086 2.21071 7.46957 2 8 2H20C20.5304 2 21.0391 2.21071 21.4142 2.58579C21.7893 2.96086 22 3.46957 22 4V14C22 14.5304 21.7893 15.0391 21.4142 15.4142C21.0391 15.7893 20.5304 16 20 16H18V20C18 20.5304 17.7893 21.0391 17.4142 21.4142C17.0391 21.7893 16.5304 22 16 22H4C3.46957 22 2.96086 21.7893 2.58579 21.4142C2.21071 21.0391 2 20.5304 2 20V10C2 9.46957 2.21071 8.96086 2.58579 8.58579C2.96086 8.21071 3.46957 8 4 8H6ZM8 8H16C16.5304 8 17.0391 8.21071 17.4142 8.58579C17.7893 8.96086 18 9.46957 18 10V14H20V4H8V8ZM8 17C7.20435 17 6.44129 16.6839 5.87868 16.1213C5.31607 15.5587 5 14.7956 5 14C5 13.2044 5.31607 12.4413 5.87868 11.8787C6.44129 11.3161 7.20435 11 8 11C8.79565 11 9.55871 11.3161 10.1213 11.8787C10.6839 12.4413 11 13.2044 11 14C11 14.7956 10.6839 15.5587 10.1213 16.1213C9.55871 16.6839 8.79565 17 8 17ZM8 15C8.26522 15 8.51957 14.8946 8.70711 14.7071C8.89464 14.5196 9 14.2652 9 14C9 13.7348 8.89464 13.4804 8.70711 13.2929C8.51957 13.1054 8.26522 13 8 13C7.73478 13 7.48043 13.1054 7.29289 13.2929C7.10536 13.4804 7 13.7348 7 14C7 14.2652 7.10536 14.5196 7.29289 14.7071C7.48043 14.8946 7.73478 15 8 15ZM9 8C9 7.20435 9.31607 6.44129 9.87868 5.87868C10.4413 5.31607 11.2044 5 12 5C12.7956 5 13.5587 5.31607 14.1213 5.87868C14.6839 6.44129 15 7.20435 15 8H13C13 7.73478 12.8946 7.48043 12.7071 7.29289C12.5196 7.10536 12.2652 7 12 7C11.7348 7 11.4804 7.10536 11.2929 7.29289C11.1054 7.48043 11 7.73478 11 8H9ZM10.864 21.518L9.136 20.511L11.861 15.838C12.0975 15.4322 12.4261 15.0876 12.8201 14.832C13.2142 14.5763 13.6627 14.4168 14.1297 14.3662C14.5967 14.3156 15.069 14.3754 15.5087 14.5407C15.9483 14.706 16.343 14.9723 16.661 15.318L17.749 16.502L16.276 17.856L15.189 16.672C15.083 16.5568 14.9514 16.4681 14.8048 16.413C14.6582 16.358 14.5007 16.3381 14.3451 16.355C14.1894 16.372 14.0399 16.4252 13.9086 16.5105C13.7773 16.5958 13.6678 16.7107 13.589 16.846L10.864 21.518ZM17.376 8.548C17.9375 8.33129 18.5513 8.28884 19.1373 8.4262C19.7233 8.56355 20.2543 8.87434 20.661 9.318L21.749 10.502L20.276 11.856L19.189 10.672C19.0414 10.5114 18.8453 10.4034 18.6306 10.3646C18.416 10.3258 18.1945 10.3582 18 10.457V10C18 9.429 17.76 8.913 17.376 8.549V8.548Z" fill="#697077" />
                                                                    </svg>
                                                                </div>


                                                            </div>
                                                            <div>
                                                                <span style="font-size: 14px;">


                                                                    Create Survey</span>

                                                            </div>
                                                        </div>


                                                    </a>

                                                </div>
                                                <div class="col-md-12 col-lg-6 mt-3 mt-lg-0">
                                                    <div class="card card-body">

                                                        <div class="d-flex align-center align-items-center gap-3">

                                                            <div class="survey-icon" style="padding:9px">
                                                                <div>

                                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M4 10V20H16V10H4ZM6 8V4C6 3.46957 6.21071 2.96086 6.58579 2.58579C6.96086 2.21071 7.46957 2 8 2H20C20.5304 2 21.0391 2.21071 21.4142 2.58579C21.7893 2.96086 22 3.46957 22 4V14C22 14.5304 21.7893 15.0391 21.4142 15.4142C21.0391 15.7893 20.5304 16 20 16H18V20C18 20.5304 17.7893 21.0391 17.4142 21.4142C17.0391 21.7893 16.5304 22 16 22H4C3.46957 22 2.96086 21.7893 2.58579 21.4142C2.21071 21.0391 2 20.5304 2 20V10C2 9.46957 2.21071 8.96086 2.58579 8.58579C2.96086 8.21071 3.46957 8 4 8H6ZM8 8H16C16.5304 8 17.0391 8.21071 17.4142 8.58579C17.7893 8.96086 18 9.46957 18 10V14H20V4H8V8ZM8 17C7.20435 17 6.44129 16.6839 5.87868 16.1213C5.31607 15.5587 5 14.7956 5 14C5 13.2044 5.31607 12.4413 5.87868 11.8787C6.44129 11.3161 7.20435 11 8 11C8.79565 11 9.55871 11.3161 10.1213 11.8787C10.6839 12.4413 11 13.2044 11 14C11 14.7956 10.6839 15.5587 10.1213 16.1213C9.55871 16.6839 8.79565 17 8 17ZM8 15C8.26522 15 8.51957 14.8946 8.70711 14.7071C8.89464 14.5196 9 14.2652 9 14C9 13.7348 8.89464 13.4804 8.70711 13.2929C8.51957 13.1054 8.26522 13 8 13C7.73478 13 7.48043 13.1054 7.29289 13.2929C7.10536 13.4804 7 13.7348 7 14C7 14.2652 7.10536 14.5196 7.29289 14.7071C7.48043 14.8946 7.73478 15 8 15ZM9 8C9 7.20435 9.31607 6.44129 9.87868 5.87868C10.4413 5.31607 11.2044 5 12 5C12.7956 5 13.5587 5.31607 14.1213 5.87868C14.6839 6.44129 15 7.20435 15 8H13C13 7.73478 12.8946 7.48043 12.7071 7.29289C12.5196 7.10536 12.2652 7 12 7C11.7348 7 11.4804 7.10536 11.2929 7.29289C11.1054 7.48043 11 7.73478 11 8H9ZM10.864 21.518L9.136 20.511L11.861 15.838C12.0975 15.4322 12.4261 15.0876 12.8201 14.832C13.2142 14.5763 13.6627 14.4168 14.1297 14.3662C14.5967 14.3156 15.069 14.3754 15.5087 14.5407C15.9483 14.706 16.343 14.9723 16.661 15.318L17.749 16.502L16.276 17.856L15.189 16.672C15.083 16.5568 14.9514 16.4681 14.8048 16.413C14.6582 16.358 14.5007 16.3381 14.3451 16.355C14.1894 16.372 14.0399 16.4252 13.9086 16.5105C13.7773 16.5958 13.6678 16.7107 13.589 16.846L10.864 21.518ZM17.376 8.548C17.9375 8.33129 18.5513 8.28884 19.1373 8.4262C19.7233 8.56355 20.2543 8.87434 20.661 9.318L21.749 10.502L20.276 11.856L19.189 10.672C19.0414 10.5114 18.8453 10.4034 18.6306 10.3646C18.416 10.3258 18.1945 10.3582 18 10.457V10C18 9.429 17.76 8.913 17.376 8.549V8.548Z" fill="#697077" />
                                                                    </svg>
                                                                </div>


                                                            </div>
                                                            <div>
                                                                <span style="font-size: 14px;"> Analyze Responses</span>

                                                            </div>
                                                        </div>


                                                    </div>

                                                </div>
                                                <div class="col-md-12 col-lg-6 mt-3">
                                                    <div class="card card-body">

                                                        <div class="d-flex align-center align-items-center gap-3">

                                                            <div class="survey-icon" style="padding:9px">
                                                                <div>

                                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M4 10V20H16V10H4ZM6 8V4C6 3.46957 6.21071 2.96086 6.58579 2.58579C6.96086 2.21071 7.46957 2 8 2H20C20.5304 2 21.0391 2.21071 21.4142 2.58579C21.7893 2.96086 22 3.46957 22 4V14C22 14.5304 21.7893 15.0391 21.4142 15.4142C21.0391 15.7893 20.5304 16 20 16H18V20C18 20.5304 17.7893 21.0391 17.4142 21.4142C17.0391 21.7893 16.5304 22 16 22H4C3.46957 22 2.96086 21.7893 2.58579 21.4142C2.21071 21.0391 2 20.5304 2 20V10C2 9.46957 2.21071 8.96086 2.58579 8.58579C2.96086 8.21071 3.46957 8 4 8H6ZM8 8H16C16.5304 8 17.0391 8.21071 17.4142 8.58579C17.7893 8.96086 18 9.46957 18 10V14H20V4H8V8ZM8 17C7.20435 17 6.44129 16.6839 5.87868 16.1213C5.31607 15.5587 5 14.7956 5 14C5 13.2044 5.31607 12.4413 5.87868 11.8787C6.44129 11.3161 7.20435 11 8 11C8.79565 11 9.55871 11.3161 10.1213 11.8787C10.6839 12.4413 11 13.2044 11 14C11 14.7956 10.6839 15.5587 10.1213 16.1213C9.55871 16.6839 8.79565 17 8 17ZM8 15C8.26522 15 8.51957 14.8946 8.70711 14.7071C8.89464 14.5196 9 14.2652 9 14C9 13.7348 8.89464 13.4804 8.70711 13.2929C8.51957 13.1054 8.26522 13 8 13C7.73478 13 7.48043 13.1054 7.29289 13.2929C7.10536 13.4804 7 13.7348 7 14C7 14.2652 7.10536 14.5196 7.29289 14.7071C7.48043 14.8946 7.73478 15 8 15ZM9 8C9 7.20435 9.31607 6.44129 9.87868 5.87868C10.4413 5.31607 11.2044 5 12 5C12.7956 5 13.5587 5.31607 14.1213 5.87868C14.6839 6.44129 15 7.20435 15 8H13C13 7.73478 12.8946 7.48043 12.7071 7.29289C12.5196 7.10536 12.2652 7 12 7C11.7348 7 11.4804 7.10536 11.2929 7.29289C11.1054 7.48043 11 7.73478 11 8H9ZM10.864 21.518L9.136 20.511L11.861 15.838C12.0975 15.4322 12.4261 15.0876 12.8201 14.832C13.2142 14.5763 13.6627 14.4168 14.1297 14.3662C14.5967 14.3156 15.069 14.3754 15.5087 14.5407C15.9483 14.706 16.343 14.9723 16.661 15.318L17.749 16.502L16.276 17.856L15.189 16.672C15.083 16.5568 14.9514 16.4681 14.8048 16.413C14.6582 16.358 14.5007 16.3381 14.3451 16.355C14.1894 16.372 14.0399 16.4252 13.9086 16.5105C13.7773 16.5958 13.6678 16.7107 13.589 16.846L10.864 21.518ZM17.376 8.548C17.9375 8.33129 18.5513 8.28884 19.1373 8.4262C19.7233 8.56355 20.2543 8.87434 20.661 9.318L21.749 10.502L20.276 11.856L19.189 10.672C19.0414 10.5114 18.8453 10.4034 18.6306 10.3646C18.416 10.3258 18.1945 10.3582 18 10.457V10C18 9.429 17.76 8.913 17.376 8.549V8.548Z" fill="#697077" />
                                                                    </svg>
                                                                </div>


                                                            </div>
                                                            <div>
                                                                <span style="font-size: 14px;"> Review Drafts</span>

                                                            </div>
                                                        </div>


                                                    </div>

                                                </div>
                                            </div>

                                        </div>


                                        <div class="card card-body mt-3  mb-4 mb-lg-0">
                                            <div class="d-flex gap-3 align-items-center">
                                                <div class="border rounded-circle" style="width:fit-content;  border:#D4E8DD; padding:10px 8px">
                                                    <i class="ri ri-record-circle-line align-center align-middle" style="color:#6EDEEF; font-size:30px"></i>
                                                </div>

                                                <h4 class="mt-1 fs-6">Active Surveys</h4>
                                            </div>



                                            <ul class="list-group border-0">
                                                <?php if (empty($activeSurveys)): ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        No active surveys
                                                    </li>
                                                <?php else: ?>
                                                    <?php foreach ($activeSurveys as $activeSurvey): ?>
                                                        <li class="mt-4 d-flex justify-content-between align-items-center border-bottom py-2">
                                                            <div class="d-flex gap-2 ">

                                                                <div class="survey-icon" style="padding:9px">
                                                                    <div>

                                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M4 10V20H16V10H4ZM6 8V4C6 3.46957 6.21071 2.96086 6.58579 2.58579C6.96086 2.21071 7.46957 2 8 2H20C20.5304 2 21.0391 2.21071 21.4142 2.58579C21.7893 2.96086 22 3.46957 22 4V14C22 14.5304 21.7893 15.0391 21.4142 15.4142C21.0391 15.7893 20.5304 16 20 16H18V20C18 20.5304 17.7893 21.0391 17.4142 21.4142C17.0391 21.7893 16.5304 22 16 22H4C3.46957 22 2.96086 21.7893 2.58579 21.4142C2.21071 21.0391 2 20.5304 2 20V10C2 9.46957 2.21071 8.96086 2.58579 8.58579C2.96086 8.21071 3.46957 8 4 8H6ZM8 8H16C16.5304 8 17.0391 8.21071 17.4142 8.58579C17.7893 8.96086 18 9.46957 18 10V14H20V4H8V8ZM8 17C7.20435 17 6.44129 16.6839 5.87868 16.1213C5.31607 15.5587 5 14.7956 5 14C5 13.2044 5.31607 12.4413 5.87868 11.8787C6.44129 11.3161 7.20435 11 8 11C8.79565 11 9.55871 11.3161 10.1213 11.8787C10.6839 12.4413 11 13.2044 11 14C11 14.7956 10.6839 15.5587 10.1213 16.1213C9.55871 16.6839 8.79565 17 8 17ZM8 15C8.26522 15 8.51957 14.8946 8.70711 14.7071C8.89464 14.5196 9 14.2652 9 14C9 13.7348 8.89464 13.4804 8.70711 13.2929C8.51957 13.1054 8.26522 13 8 13C7.73478 13 7.48043 13.1054 7.29289 13.2929C7.10536 13.4804 7 13.7348 7 14C7 14.2652 7.10536 14.5196 7.29289 14.7071C7.48043 14.8946 7.73478 15 8 15ZM9 8C9 7.20435 9.31607 6.44129 9.87868 5.87868C10.4413 5.31607 11.2044 5 12 5C12.7956 5 13.5587 5.31607 14.1213 5.87868C14.6839 6.44129 15 7.20435 15 8H13C13 7.73478 12.8946 7.48043 12.7071 7.29289C12.5196 7.10536 12.2652 7 12 7C11.7348 7 11.4804 7.10536 11.2929 7.29289C11.1054 7.48043 11 7.73478 11 8H9ZM10.864 21.518L9.136 20.511L11.861 15.838C12.0975 15.4322 12.4261 15.0876 12.8201 14.832C13.2142 14.5763 13.6627 14.4168 14.1297 14.3662C14.5967 14.3156 15.069 14.3754 15.5087 14.5407C15.9483 14.706 16.343 14.9723 16.661 15.318L17.749 16.502L16.276 17.856L15.189 16.672C15.083 16.5568 14.9514 16.4681 14.8048 16.413C14.6582 16.358 14.5007 16.3381 14.3451 16.355C14.1894 16.372 14.0399 16.4252 13.9086 16.5105C13.7773 16.5958 13.6678 16.7107 13.589 16.846L10.864 21.518ZM17.376 8.548C17.9375 8.33129 18.5513 8.28884 19.1373 8.4262C19.7233 8.56355 20.2543 8.87434 20.661 9.318L21.749 10.502L20.276 11.856L19.189 10.672C19.0414 10.5114 18.8453 10.4034 18.6306 10.3646C18.416 10.3258 18.1945 10.3582 18 10.457V10C18 9.429 17.76 8.913 17.376 8.549V8.548Z" fill="#697077" />
                                                                        </svg>
                                                                    </div>

                                                                </div>
                                                                <div>
                                                                    <span> <?php echo CHtml::encode($activeSurvey['survey_title']); ?></span><br>
                                                                    <div style="font-size: 12px;"><?php echo CHtml::encode($activeSurvey['date_created']); ?></div>
                                                                </div>


                                                            </div>

                                                            <div>
                                                                <?php echo CHtml::encode($activeSurvey['response_count']); ?> Responses
                                                                <i class="ri-bar-chart-2-fill text-primary"></i>
                                                            </div>

                                                        </li>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </ul>

                                        </div>

                                    </div>

                                    <div class="col-lg-4 col-md-12">


                                        <div class="card mb-4">
                                            <div class="card-body">
                                                <div class="d-flex gap-4 mb-5">
                                                    <h5 style="font-size:small">Response Trends</h5>

                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <select id="surveyDropdown" class="form-select w-auto">
                                                            <option value="" disabled>Select Survey</option>
                                                            <?php

                                                            foreach ($surveyList as $survey) {
                                                                echo "<option value='{$survey['sid']}'>{$survey['surveyls_title']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>


                                                <div class="chart-container">
                                                    <canvas id="responseChart"></canvas>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 
                        <div class="card  mb-4">
                            <h5 class="card-title">Response Trends2</h5>


                            <div class="chart-container">
                                <canvas id="responseChart2"></canvas>
                            </div>
                        </div> -->


                                    </div>

                                    <div class="col-lg-3 col-md-12  mb-4 mb-lg-0">
                                        <div class="card p-3 ">
                                            <h5 class="card-title">Recent Activity</h5>
                                            <ul class="list-group list-group-flush" style="height:50vh; overflow-x:scroll">
                                                <!-- <li class="list-group-item"> -->
                                                <div>
                                                    <!-- <div class="" style="background:#8E8E8E; width:1px; height:50vh; border:'1px solid black'; position:absolute; left:18px">

                                    </div> -->
                                                    <?php


                                                    foreach ($recentActivities as $activity) {
                                                        echo "
                                            <div class='d-flex gap-3'>
                                            
                                             <div class='border rounded-circle mt-3 ' style='background:#122867; width:9px; height:9px'>
                                                
                                        </div> 
                                       <div style='width:100%;'> {$activity['message']}
</div>                                                
                                            

                                            </div>
                                            <div class='d-flex justify-content-center' >
                                             <button class='btn w-75 btn-primary btn-sm  border-0' style='background:rgba(43, 0, 255, 0.1); color:#1E1E1E'; >View </button>
           
                                           </div>
                                          
                                        ";
                                                    }
                                                    ?>
                                                </div>



                                                <!-- </li> -->
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- survey table -->


                        <div class="tab-pane fade" id="surveys" role="tabpanel" aria-labelledby="surveys-tab">
                            <div class="container-fluid p-4">
                                <div class="row">
                                    <!-- Header -->
                                    <div class="col-12  align-items-center mb-4">

                                        <?php $this->widget('ext.admin.survey.ListSurveysWidget.ListSurveysWidget', [
                                            'model' => $oSurveySearch,
                                            'switch' => true
                                        ]);
                                        ?>
                                    </div>
                                </div>

                            </div>


                            <!-- <div class="card card-body"> -->
                            <?php
                            // $this->render('application.extensions.admin.survey.ListSurveysWidget.views.listSurveys');
                            ?>
                            <!-- </div> -->


                        </div>
                        <div class="tab-pane fade" id="reports" role="tabpanel" aria-labelledby="reports-tab">


                            <div class="row">
                                <div class="col-7">Response Trends</div>
                                <div class="container-fluid">
                                    <div class="row my-2">
                                        <div class="col-md-7">
                                            <div class="card card-body">
                                                <h4 class="mb-4 fs-6">Response Trends</h4>



                                            </div>


                                            <div class="card card-body mt-3">

                                                <div>

                                                </div>
                                                <h4 class="mt-1 fs-6">Average Response Time Across Surveys</h4>
                                                <ul class="list-group">
                                                    <?php if (empty($activeSurveys)): ?>
                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                            No active surveys
                                                        </li>
                                                    <?php else: ?>
                                                        <?php foreach ($activeSurveys as $activeSurvey): ?>
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                <?php echo CHtml::encode($activeSurvey['survey_title']); ?>
                                                                <span class="badge bg-primary rounded-pill">
                                                                    <?php echo CHtml::encode($activeSurvey['response_count']); ?> Responses

                                                                </span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </ul>

                                            </div>

                                        </div>

                                        <div class="col-md-4">


                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <div class="d-flex gap-4 mb-5">
                                                        <h5 style="font-size:small">Responses Across Surveys</h5>

                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <select id="surveyDropdown" class="form-select w-auto">
                                                                <option value="" disabled>Select Survey</option>
                                                                <?php

                                                                foreach ($surveyList as $survey) {
                                                                    echo "<option value='{$survey['sid']}'>{$survey['surveyls_title']}</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>


                                                    <div class="chart-container">
                                                        <canvas id="responseChart"></canvas>
                                                    </div>
                                                </div>
                                            </div>











                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <div class="d-flex gap-4 mb-5">
                                                        <h5 style="font-size:small">Distribution Across Surveys</h5>

                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <select id="surveyDropdown" class="form-select w-auto">
                                                                <option value="" disabled>Select Survey</option>
                                                                <?php
                                                                foreach ($surveyList as $survey) {
                                                                    echo "<option value='{$survey['sid']}'>{$survey['surveyls_title']}</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>


                                                    <div class="chart-container">
                                                        <canvas id="responseChart"></canvas>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>



                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

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


<!-- todo: load chartjs locally -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.onload = function() {
        // Fetch default survey from PHP variable
        let defaultSurvey = <?= json_encode($surveyList) ?>;
        const csrfTokenName = '<?= Yii::app()->request->csrfTokenName ?>';
        const csrfToken = '<?= Yii::app()->request->csrfToken ?>';
        let selectedSurveyId = defaultSurvey.length > 0 ? defaultSurvey[0].sid : null; // Default to the first survey ID

        console.log(defaultSurvey, "default");
        const url = '<?= Yii::app()->createUrl('searchBoxWidget/getSurveyResponseTrends') ?>';

        // Populate dropdown on page load
        const surveyDropdown = document.getElementById('surveyDropdown');
        if (surveyDropdown) {

            surveyDropdown.innerHTML = ''; // Clear existing options
            defaultSurvey.forEach(survey => {
                const option = document.createElement('option');
                option.value = survey.sid;
                option.textContent = survey.surveyls_title;
                surveyDropdown.appendChild(option);
            });

            // Set the default selected survey
            surveyDropdown.value = selectedSurveyId;

            // Fetch and display data for the default survey
            fetchSurveyData(selectedSurveyId);
        }

        surveyDropdown.addEventListener('change', function() {
            selectedSurveyId = this.value;
            fetchSurveyData(selectedSurveyId);
        });

        // fetch and update the chart
        function fetchSurveyData(surveyId) {
            fetch(`${url}?surveyid=${surveyId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        [csrfTokenName]: csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    const labels = data.map(item => item.response_date);
                    const responseData = data.map(item => item.response_count);

                    responseChart.data.labels = labels;
                    responseChart.data.datasets[0].data = responseData;
                    responseChart.update();
                })
                .catch(error => {
                    console.error('Error fetching survey response trends:', error);
                });
        }

        // Chart.js setup
        const ctx = document.getElementById('responseChart').getContext('2d');
        const responseChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    label: 'Responses',
                    data: [0, 0, 0, 0, 0, 0, 0], // Initial data
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    };
</script>