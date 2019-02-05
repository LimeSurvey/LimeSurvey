<?php
/**
 * The welcome page is the home page
 * TODO : make a recursive function, taking any number of box in the database, calculating how much rows are needed.
 */

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
<div class="container-fluid welcome full-page-wrapper">

    <!-- Logo & Presentation -->
    <?php if($bShowLogo):?>
        <div class="row">
            <div class="jumbotron" id="welcome-jumbotron">
                <img alt="logo" src="<?php echo LOGO_URL;?>" id="lime-logo"  class="profile-img-card img-responsive center-block" />
                <p class="hidden-xs custom custom-margin top-25" ><?php echo PRESENTATION; // Defined in AdminController?></p>
            </div>
        </div>
    <?php endif;?>

    <!-- Message when first start -->
    <?php if($countSurveyList==0  && Permission::model()->hasGlobalPermission('surveys','create') ):?>
        <script type="text/javascript">
            $(window).load(function(){
                $('#welcomeModal').modal('show');
            });
        </script>

        <div class="modal fade" id="welcomeModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                      <h4 class="modal-title"><?php echo sprintf(gT("Welcome to %s!"), 'LimeSurvey'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row" id="selector__welcome-modal--simplesteps">
                                <p><?php eT("Some piece-of-cake steps to create your very own first survey:"); ?></p>
                                <ol>
                                    <li><?php echo sprintf(gT('Create a new survey clicking on the %s icon.'),
                                                "<i class='icon-add text-success'></i>"); ?></li>
                                    <li><?php eT('Create a new question group inside your survey.'); ?></li>
                                    <li><?php eT('Create one or more questions inside the new question group.'); ?></li>
                                    <li><?php echo sprintf(gT('Done. Test your survey using the %s icon.'), "<i class='icon-do text-success'></i>"); ?></li>
                                </ol>
                            </div>
                            <div class="row"><hr/></div>
                            <?php if(Permission::model()->hasGlobalPermission('surveys','create')) { ?>
                                <div class="row" id="selector__welcome-modal--tutorial">
                                    <p><?php eT('Or, try out our interactive tutorial tour'); ?> </p>
                                    <p class="text-center"><button class="btn btn-primary btn-lg" id="selector__welcome-modal--starttour"><?php eT("Start the tour"); ?></button></p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close');?></button>
                      <a href="<?php echo $this->createUrl("admin/survey/sa/newsurvey") ?>" class="btn btn-primary"><?php eT('Create a new survey');?></a>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    <?php endif;?>

    <?php 
        //Check for IE and show a warning box
        if (preg_match('~MSIE|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT']) || (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'rv:11.0') !== false)) {
    ?>
    <div class="container">
        <div class="alert alert-danger" role="alert" id="warningIE11">
            <div class="container-fluid">
                <div class="row">
                    <h4 class="col-xs-12"><?=gT("Warning!")?></h4>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <?php eT("You are using Microsoft Internet Explorer."); ?><br/><br/>
                        <?php eT("LimeSurvey 3.x or newer does not support Internet Explorer for the LimeSurvey administration, anymore. However most of the functionality should still work."); ?><br/>
                        <?php eT("If you have any issues, please try using a modern browser first, before reporting it.");?>
                    </div>
                </div>
            </div>
        </div>
    </div>
        
    <?php 
    }
    App()->getClientScript()->registerScript('WelcomeCheckIESafety', "
    if(!/(MSIE|Trident\/)/i.test(navigator.userAgent)) {
        $('#warningIE11').remove();
    }
    ", LSYii_ClientScript::POS_POSTSCRIPT); 
    ?>
    <!-- Last visited survey/question -->
    <?php if( $bShowLastSurveyAndQuestion && ($showLastSurvey || $showLastQuestion)): // bShowLastSurveyAndQuestion is the homepage setting, showLastSurvey & showLastQuestion are about if infos are available ?>
        <div class="row text-right">
            <div class="col-lg-9 col-sm-9  ">
                <div class='pull-right'>
                <?php if($showLastSurvey):?>
                    <span id="last_survey" class="rotateShown">
                    <?php eT("Last visited survey:");?>
                    <a href="<?php echo $surveyUrl;?>" class=""><?php echo viewHelper::flatEllipsizeText($surveyTitle, true, 60);?></a>
                    </span>
                <?php endif; ?>

                <?php if($showLastQuestion):?>
                    <span id="last_question" class="rotateHidden">
                    <?php eT("Last visited question:");?>
                    <a href="<?php echo $last_question_link;?>" class=""><?php echo viewHelper::flatEllipsizeText($last_question_name, true, 60); ?></a>
                    </span>
                <?php endif; ?>
                </div>
                <br/><br/>
            </div>
        </div>
    <?php endif;?>

    <!-- Rendering all boxes in database -->
    <?php $this->widget('ext.PanelBoxWidget.PanelBoxWidget', array(
            'display'=>'allboxesinrows',
            'boxesbyrow'=>$iBoxesByRow,
            'offset'=>$sBoxesOffSet,
            'boxesincontainer' => $bBoxesInContainer
        ));
    ?>

    <?php if( $bShowSurveyList ): ?>
        <div class="col-sm-12 list-surveys">
            <h2><?php eT('Survey list'); ?></h2>
            <?php
                $this->widget('ext.admin.survey.ListSurveysWidget.ListSurveysWidget', array(
                            'model'            => $oSurveySearch,
                            'bRenderSearchBox' => $bShowSurveyListSearch,
                        ));
            ?>
        </div>
    <?php endif; ?>


    <!-- Boxes for smartphones -->
    <div class="row  hidden-sm  hidden-md hidden-lg ">
        <div class="panel panel-primary panel-clickable" id="panel-7" data-url="/limesurvey/LimeSurveyNext/index.php/admin/survey/sa/listsurveys" style="opacity: 1; top: 0px;">
            <div class="panel-heading">
                <div class="panel-title"><?php eT('List surveys');?></div>
            </div>
            <div class="panel-body">
                <a href='<?php echo $this->createUrl("admin/survey/sa/listsurveys") ?>'>
                    <span class="icon-list" style="font-size: 4em"></span>
            <span class="sr-only"><?php eT('List surveys');?></span>
                </a><br><br>
                <a href='<?php echo $this->createUrl("admin/survey/sa/listsurveys") ?>'><?php eT('List surveys');?></a>
            </div>
        </div>

        <div class="panel panel-primary panel-clickable" id="panel-8" data-url="/limesurvey/LimeSurveyNext/index.php/admin/globalsettings" style="opacity: 1; top: 0px;">
            <div class="panel-heading">
                <div class="panel-title"><?php eT('Edit global settings');?></div>
            </div>
            <div class="panel-body">
                <a href='<?php echo $this->createUrl("admin/globalsettings") ?>'>
                    <span class="icon-settings" style="font-size: 4em"></span>
                    <span class="sr-only"><?php eT('Edit global settings');?></span>
                </a><br><br>
                <a href='<?php echo $this->createUrl("admin/globalsettings") ?>'><?php eT('Edit global settings');?></a>
            </div>
        </div>

    </>
</div>

<!-- Notification setting -->
<input type="hidden" id="absolute_notification" />
