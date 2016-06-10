<?php
/**
 * View for the form 'editconditions' header.
 *
 * @var $subaction
 * @var $iSurveyID
 * @var $gid
 * @var $qid
 * @var $mytitle
 * @var $showScenario                   ( $subaction != "editthiscondition" && isset($scenariocount) && ($scenariocount == 1 || $scenariocount==0)) ||( $subaction == "editthiscondition" && isset($scenario) && $scenario == 1)
 * @var $qcountI                        $qcount+1
 */
?>

<?php
    //TODO: move to script
    $scenarioaddbtnOnClickAction = "$('#scenarioaddbtn').hide(); $('#defaultscenariotxt').hide('slow'); $('#scenario').show('slow');";
?>

<div class="row">
    <div class="col-lg-12">

        <!-- Form  editconditions -->
        <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/{$subaction}/surveyid/{$iSurveyID}/gid/{$gid}/qid/{$qid}/"),'post',array('id'=>"editconditions",'name'=>"editconditions"));?>
            <h4><?php echo $mytitle;?></h4>

            <!-- Condition -->
            <div class='condition-tbl-row'>
                <div class='condition-tbl-left'>
                    <?php if($showScenario):?>
                        <a id='scenarioaddbtn' href='#' onclick="<?php echo $scenarioaddbtnOnClickAction; ?>" >
                            <span class='icon-add'></span>
                        </a>
                    <?php endif;?>
                    <?php eT("Scenario"); ?>
                </div>
                <div class='condition-tbl-right'>
                    <input type='text' name='scenario' id='scenario' value='1' size='2' <?php if($showScenario):?> style = 'display: none;' <?php endif;?>/>
                    <?php if($showScenario):?>
                        <span id='defaultscenariotxt'>
                            <?php eT("Default scenario"); ?>
                        </span>
                    <?php endif;?>
                </div>
            </div>


            <div class='condition-tbl-row'>
                <div class='condition-tbl-left'>
                    <?php eT("Question"); ?>
                </div>

                <div class='condition-tbl-right'>
                    <div id="conditionsource">
                        <ul class='nav nav-tabs'>
                            <li  role='presentation' class='active'>
                                <a data-toggle='tab' href="#SRCPREVQUEST">
                                    <span>
                                        <?php eT("Previous questions"); ?>
                                    </span>
                                </a>
                            </li>

                            <li role='presentation'>
                                <a data-toggle='tab'href="#SRCTOKENATTRS">
                                    <span>
                                        <?php eT("Survey participant attributes"); ?>
                                    </span>
                                </a>
                            </li>
                        </ul>


                        <div class="tab-content">
                            <div id='SRCPREVQUEST' class='tab-pane fade in active'>
                                <select class='form-control' name='cquestions' id='cquestions' size='<?php echo $qcountI;?>'>
