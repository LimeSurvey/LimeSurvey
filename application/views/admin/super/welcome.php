<?php
/**
 * The welcome page is the home page
 * TODO : make a recursive function, taking any number of box in the database, calculating how much rows are needed.
 */
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
    gT('Template editor');
    gT('Edit LimeSurvey templates');
?>

<!-- Welcome view -->
<div class="container-fluid welcome full-page-wrapper">

    <!-- Logo & Presentation -->
    <?php if($bShowLogo):?>
        <div class="row">
            <div class="jumbotron" id="welcome-jumbotron">
                <img alt="logo" src="<?php echo LOGO_URL;?>" id="lime-logo"  class="profile-img-card img-responsive center-block" style="display: inline;" />
                <p class="hidden-xs" ><?php echo PRESENTATION; // Defined in AdminController?></p>
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
                        <p><?php eT("Some piece-of-cake steps to create your very own first survey:"); ?></p>
                        <ol>
                            <li><?php echo sprintf(gT('Create a new survey clicking on the %s icon.'),
                                        "<span class='icon-add text-success'></span>"); ?></li>
                            <li><?php eT('Create a new question group inside your survey.'); ?></li>
                            <li><?php eT('Create one or more questions inside the new question group.'); ?></li>
                            <li><?php echo sprintf(gT('Done. Test your survey using the %s icon.'), "<span class='icon-do text-success'></span>"); ?></li>
                        </ol>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close');?></button>
                      <a href="<?php echo $this->createUrl("admin/survey/sa/newsurvey") ?>" class="btn btn-primary"><?php eT('Create a new survey');?></a>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->




    <?php endif;?>

    <!-- Last visited survey/question -->
    <?php if( $bShowLastSurveyAndQuestion && ($showLastSurvey || $showLastQuestion)): // bShowLastSurveyAndQuestion is the homepage setting, showLastSurvey & showLastQuestion are about if infos are available ?>
        <div class="row text-right">
            <div class="col-lg-9 col-sm-9  ">
                <div class='pull-right'>
                <?php if($showLastSurvey):?>
                    <span id="last_survey" class="rotateShown">
                    <?php eT("Last visited survey:");?>
                    <a href="<?php echo $surveyUrl;?>" class=""><?php echo $surveyTitle;?></a>
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
    <?php $this->widget('ext.PannelBoxWidget.PannelBoxWidget', array(
            'display'=>'allboxesinrows',
            'boxesbyrow'=>$iBoxesByRow,
            'offset'=>$sBoxesOffSet,
        ));
    ?>

    <?php if( $bShowSurveyList ): ?>
        <div class="col-sm-12 list-surveys">
            <h3><?php eT('Survey list'); ?></h3>
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
        <div class="panel panel-primary panel-clickable" id="pannel-7" data-url="/limesurvey/LimeSurveyNext/index.php/admin/survey/sa/listsurveys" style="opacity: 1; top: 0px;">
            <div class="panel-heading">
                <h3 class="panel-title"><?php eT('List surveys');?></h3>
            </div>
            <div class="panel-body">
                <a href='<?php echo $this->createUrl("admin/survey/sa/listsurveys") ?>'>
                    <span class="icon-list" style="font-size: 4em"></span>
                </a><br><br>
                <a href='<?php echo $this->createUrl("admin/survey/sa/listsurveys") ?>'><?php eT('List surveys');?></a>
            </div>
        </div>

        <div class="panel panel-primary panel-clickable" id="pannel-8" data-url="/limesurvey/LimeSurveyNext/index.php/admin/globalsettings" style="opacity: 1; top: 0px;">
            <div class="panel-heading">
                <h3 class="panel-title"><?php eT('Edit global settings');?></h3>
            </div>
            <div class="panel-body">
                <a href='<?php echo $this->createUrl("admin/globalsettings") ?>'>
                    <span class="icon-settings" style="font-size: 4em">
                    </span>
                </a><br><br>
                <a href='<?php echo $this->createUrl("admin/globalsettings") ?>'><?php eT('Edit global settings');?></a>
            </div>
        </div>

    </div>
</div>

<!-- Notification setting -->
<input type="hidden" id="absolute_notification" />
