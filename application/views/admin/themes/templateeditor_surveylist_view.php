<?php
/**
 * @var Survey[] $publicSurveys
 */
    $outputSurveys = 0;
    $list = "<div class='surveys-list-container'>";
    $list .= "<ul class='list-unstyled surveys-list'>";

    foreach($publicSurveys as $survey)
    {
        $outputSurveys++;
        /* get final lang of survey */
        if(!in_array(App()->language,$survey->getAllLanguages())){
            $surveylang=$survey->language;
        }else{
            $surveylang=App()->language;
        }
        /* get the col class for with (src : http://encosia.com/using-btn-block-bootstrap-3-dropdown-button-groups) */
        if ($survey->isPublicStatistics){
            $colClass="col-xs-10 col-md-11";
        }else{
            $colClass="col-xs-12";
        }
        $surveyLine = CHtml::link(
            $survey->currentLanguageSettings->surveyls_title,
            array(
                'survey/index',
                'sid' => $survey->sid,
                'lang' => $surveylang,
            ),
            array(
                'class' => "surveytitle btn btn-primary {$colClass}",
                'title'=>gT('Start survey'),
                'lang'=>$surveylang // Must add dir ?
            )
        );
        if ($survey->isPublicStatistics){
            $surveyLine .= CHtml::link('<span class="fa fa-bar-chart" aria-hidden="true"></span><span class="sr-only">'. gT('View statistics') .'</span>',
                array('statistics_user/action', 'surveyid' => $survey->sid,'language' => $surveylang),
                array(
                    'class'=>'view-stats btn btn-success col-xs-2 col-md-1',
                    'title'=>gT('View statistics'),
                )
            );
        }
        $list .= CHtml::tag("li",
            array("class"=>"btn-group btn-block"),
            $surveyLine
        );
    }

    $list .= "</ul>";
    $list .= "</div>";

    if (!empty($futureSurveys)) /* Dis someone use it ? */
    {
        $list .= "<div class=\"survey-list-heading\">".  gT("Following survey(s) are not yet active but you can register for them.")."</div>";
        $list .= "<div class='surveys-list-container futuresurveys-list-container'>";
        $list .= "<ul class='list-unstyled surveys-list'>";
        foreach($futureSurveys as $survey)
        {
            $outputSurveys++;
            if(!in_array(App()->language,$survey->getAllLanguages())){
                $surveylang=$survey->language;
            }else{
                $surveylang=App()->language;
            }
            $surveyLine = CHtml::link(
                $survey->currentLanguageSettings->surveyls_title,
                array(
                    'survey/index',
                    'sid' => $survey->sid,
                    'lang' => $surveylang,
                ),
                array(
                    'class' => "surveytitle btn btn-primary col-xs-12",
                    'title'=>gT('Start survey'),
                    // broken : jquery-ui tooltip replace bs tooltip 'data-toggle'=>'tooltip',
                    'lang'=>$surveylang // Must add dir ?
                )
            );
            $surveyLine .=  CHtml::tag('div', array(
                'data-regformsurvey' => $survey->sid,
                'class' => 'col-xs-12'
            ));
        }
        $list .= "</ul>";
        $list .= "</div>";
    }

    $listheading="<div class='h3 '>
                    ".gT("The following surveys are available:")."
                    </div>";
    if( $outputSurveys==0)
    {
        $list=CHtml::openTag('div',array('class'=>'col-xs-12')).gT("No available surveys").CHtml::closeTag('div');
    }
    echo $list;
