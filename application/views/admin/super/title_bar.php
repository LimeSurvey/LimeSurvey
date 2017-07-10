<?php
/**
 * Needs an array now
 * $breadCrumbArray = array('oSurvey'=>$oSurvey, 'oQuestionGroup' => $oQuestionGroup, 'oQuestion' => $oQuestion, 'sSubaction' =>$sSubaction,  'active'=>$active))
 */


$oSurvey = Survey::model()->findByPk((int) $surveyid);
$oQuestion = isset($qid) ? @Question::model()->find('qid=:qid',['qid'=> $qid]) : null;
$oQuestionGroup = isset($gid) ? @QuestionGroup::model()->find('gid=:gid',['gid'=> $gid]) : null;

$subaction = isset($subaction) ? $subaction : null;

$breadCrumbArray = array(
    'oSurvey' => $oSurvey,
    'oQuestion' => $oQuestion,
    'oQuestionGroup' => $oQuestionGroup,
    'sSubaction' => $subaction
    //'active' => ($oQuestion != null ? $oQuestion->title : ( $oQuestionGroup != null ? $oQuestionGroup->group_name : $oSurvey->defaultlanguage->surveyls_title ) )
);

$breadCrumbArray['extraClass'] = "title-bar-breadcrumb";
?>
<div class='menubar surveymanagerbar'>
    <div class='row container-fluid'>
        <div class="col-xs-12 col-md-6">
            <?php  $this->renderPartial('/admin/survey/breadcrumb', $breadCrumbArray); ?>    
        </div>
        <div class="col-xs-12 col-md-6 text-right">
            <?= $title_bar['title'] ?>
        </div>
    </div>
</div>
<!-- 
    <div id="survey_title" class="h3"><a href="<?php echo Yii::app()->createUrl('admin/survey/sa/view/surveyid/'.$surveyid);?>"><?php echo $title_bar['title']; ?></a></div>
    <div class='menubar surveymanagerbar'  id="vue-top-menu-app">
        <topbar main-href="<?php echo Yii::app()->createUrl('admin/survey/sa/view/surveyid/'.$surveyid);?>" main-title="<?php echo $title_bar['title']; ?>"></topbar>
    </div> 
-->
<script>
    console.log(<?=json_encode($debug)?>);
</script>