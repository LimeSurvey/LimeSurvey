<?php
/**
 * @var Survey[] $publicSurveys
 */

    $outputSurveys = 0;
    $list = "<div class='container'>";
    $list .= "<div class='row'>";
    $divideToggle = true;
    /** @var Survey[] $publicSurveys */
    foreach ($publicSurveys as $survey) {
        $outputSurveys++;
        $divider = ($divideToggle ? " vertical-divider right " : "");
        if ($survey->isPublicStatistics) {
            $statistics = "<div class='col-md-1 col-sm-2 col-xs-2 no-divide ls-custom-padding five ".$divider."'>";
            $statistics .= CHtml::link('<span class="fa fa-bar-chart" aria-hidden="true"></span><span class="sr-only">'.gT('View statistics').'</span>',
                        array('statistics_user/action', 'surveyid' => $survey->sid, 'language' => App()->language),
                        array(
                            'class'=>'view-stats btn btn-success btn-block',
                            'title'=>gT('View statistics'),
                            'data-toggle'=>'tooltip',
                        )
                    );
            $statistics .= "</div>";
            $list .= "<div class='col-md-5 col-sm-10 col-xs-10 ls-custom-padding five'>";
        } else {
            $statistics = "";
            $list .= "<div class='col-md-6 col-xs-12 ls-custom-padding five ".$divider."'>";
        }

        $tooltips = "";
        if ($survey->isAllowRegister) {
            $tooltips .= "<i class=\"fa fa-sign-in\" aria-hidden=\"true\">&nbsp;</i>";
        } else {
            if ($survey->hasTokensTable) {
                $tooltips .= "<i class=\"fa fa-key\" aria-hidden=\"true\">&nbsp;</i>";
            }
        }
        if ($survey->isAnonymized) {
            $tooltips .= "<i class=\"fa fa-shield\" aria-hidden=\"true\">&nbsp;</i>";
        }
        if ($survey->isAllowSave) {
            $tooltips .= "<i class=\"fa fa-save\" aria-hidden=\"true\">&nbsp;</i>";
        }
        if ($survey->isAllowPrev) {
            $tooltips .= "<i class=\"fa fa-undo\" aria-hidden=\"true\">&nbsp;</i>";
        }
        $tooltips .= "<i  class=\"fa fa-clock-o\" aria-hidden=\"true\">&nbsp;</i>&nbsp;".sprintf(gt("%s minutes"), $survey->calculateEstimatedTime());

        $content = $survey->currentLanguageSettings->surveyls_title;
        $content .= "<span class='pull-right clearfix'>"
                        ."&nbsp;<span href='#' class='fa fa-question-circle' onclick='return false;' data-html='true' data-toggle=\"popover\" title=\"".gT("Survey information")."\" data-content='".$tooltips."'></span>"
                        ."</span>";

        $list .= CHtml::link(
            $content,
            array(
                'survey/index',
                'sid' => $survey->sid,
                'lang' => App()->language,
                'encode' => false
                ),
                array(
                    'class' => 'surveytitle btn btn-primary btn-block',
                    'data-html' => true,
                    'data-toggle' => 'tooltip',
                    'title' => $tooltips
                )
                );
        $list .= "</div>";
        $list .= $statistics;
        $divideToggle = !$divideToggle;
    }

    $list .= "</div>";
    $list .= "</div>";

    if (!empty($futureSurveys)) {
        $list .= "<div class=\"survey-list-heading\">".gT("Following survey(s) are not yet active but you can register for them.")."</div>";
        $list .= "<div class='container'>";
        $list .= "<div class='row'>";
        foreach ($futureSurveys as $survey) {
            $outputSurveys++;
            $list .= CHtml::openTag('div', array('class'=>'col-xs-12'));
            $list .= CHtml::link($survey->currentLanguageSettings->surveyls_title, array('survey/index', 'sid' => $survey->sid, 'lang' => App()->language), array('class' => 'surveytitle'));
            $list .= CHtml::closeTag('div');
            $list .= CHtml::tag('div', array(
                'data-regformsurvey' => $survey->sid,
                'class' => 'col-xs-12'
            ));
        }
    }


    $legendForSurvey_button = "<button class='btn btn-info' data-toggle='modal' href='#legendForSurveys'>".gt("Toggle legend")."</button>";
    $legendForSurvey = ""
    . "<div class='modal fade' id='legendForSurveys'>"
        . "<div class='modal-dialog' role='document'>"
        . "<div class='modal-content'>"
        . "<div class='modal-body'>"
            . "<div class='row'>"
                . "<div class='col-xs-3 text-right'><i class='fa fa-sign-in'>&nbsp;</i></div>"
                . "<div class='col-xs-9'>".gT('You have to register to take this survey.')."</div>"
            . "</div>"
            . "<div class='row'>"
                . "<div class='col-xs-3 text-right'><i class='fa fa-lock'>&nbsp;</i></div>"
                . "<div class='col-xs-9'>".gT('You need a valid token to take this survey.')."</div>"
            . "</div>"
            . "<div class='row'>"
                . "<div class='col-xs-3 text-right'><i class='fa fa-shield'>&nbsp;</i></div>"
                . "<div class='col-xs-9'>".gT('This survey is anonymized.')."</div>"
            . "</div>"
            . "<div class='row'>"
                . "<div class='col-xs-3 text-right'><i class='fa fa-undo'>&nbsp;</i></div>"
                . "<div class='col-xs-9'>".gT('You may change your answers in this survey.')."</div>"
            . "</div>"
            . "<div class='row'>"
                . "<div class='col-xs-3 text-right'><i class='fa fa-clock-o'>&nbsp;</i></div>"
                . "<div class='col-xs-9'>".gT('Estimated time for this survey')."</div>"
            . "</div>"
        . "</div>"
        . "</div>"
        . "</div>"
    . "</div>";

    //@TODO: Create Global configuration for showSurveyInfo
    $showSurveyInformation = false;


    $listheading = "<div class='container'>
                    <div class='h3'>
                    ".gT("The following surveys are available:")."
                    <span class='pull-right'>".$legendForSurvey_button."</span>
                    </div>
                    ".$legendForSurvey."
                    </div>";
    if ($outputSurveys == 0) {
        $list = CHtml::openTag('div', array('class'=>'col-xs-12')).gT("No available surveys").CHtml::closeTag('div');
    }
    $aReplacementData = array(
        "NOSID"=> "",
        "SURVEYLISTHEADING"=> $listheading,
        "SURVEYLIST"=> $list,
    );
    // FIXME makeLanguageChanger() is not accessible from here??
    $data['languagechanger'] = makeLanguageChanger(App()->language);
    /* must register script if template don't do it */
    App()->clientScript->registerScript("ExtendedStartpageToolTip", "$('.surveytitle,.view-stats').tooltip()", CClientScript::POS_READY);
    $oTemplate = Template::model()->getInstance("default");
    echo templatereplace(file_get_contents($oTemplate->pstplPath."/surveylist.pstpl"), $aReplacementData, $data, 'survey['.__LINE__.']');
