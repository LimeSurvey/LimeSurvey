<?php
/**
 * @var $this AdminController
 *
* Right accordion, integration panel
* Use datatables, needs surveysettings.js
*/
$yii = Yii::app();
$controller = $yii->getController();
// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyPanelIntegration');

?>
  <!-- Datatable translation-data -->
  <!-- Container -->
  <div class="simpleContainer" id="vue-parameter-table-container">
    <lspanelparametertable :sid="<?php  echo $surveyid; ?>" json-url="<?php echo App()->createUrl('admin/survey/sa/getUrlParamsJson', array('surveyid' => $surveyid))?>" :translate="{
        table: {
            idColumn : '<?php eT('ID');?>',
            actionColumn : '<?php eT('Action');?>',
            parameterColumn : '<?php eT('Parameter');?>',
            questionColumn : '<?php eT('Target question');?>',
            sidColumn : '<?php eT('Survey id');?>',
            qidColumn : '<?php eT('Question id');?>',
            sqidColumn : '<?php eT('Subquestion ID');?>',
            addParameter : '<?php eT('Add URL parameter');?>'
        },
        popup: {
            editParam : '<?php eT('Edit URL parameter');?>',
            newParam : '<?php eT('Add URL parameter');?>',
            paramName : '<?php eT('Parameter');?>',
            targetQuestion : '<?php eT('Target question');?>',
            noTargetQuestion : '<?php eT('(No target question)');?>',
            sureToDelete : '<?php eT('Are you sure you want to delete this URL parameter?'); ?>',
            deleteCancel : '<?php eT('No, cancel'); ?>',
            deleteConfirm : '<?php eT('Yes, delete'); ?>',
            save : '<?php eT('Save');?>',
            cancel : '<?php eT('Cancel');?>'
        }
    }"></lspanelparametertable>
</div>
<?php  
    App()->getClientScript()->registerScript('IntegrationPanel-variables', " 
    var jsonUrl = '".App()->createUrl('admin/survey/sa/getUrlParamsJson', array('surveyid' => $surveyid))."';  
    var imageUrl = '".$yii->getConfig("adminimageurl ")."';  
    var sProgress = '".gT('Showing _START_ to _END_ of _TOTAL_ entries','js')."';  
    var sAction = '".gT('Action','js')."';  
    var sParameter = '".gT('Parameter','js')."';  
    var sTargetQuestion = '".gT('Target question','js')."';  
    var sURLParameters = '".gT('URL parameters','js')."';  
    var sNoParametersDefined = '".gT('No parameters defined','js')."';  
    var sSearchPrompt = '".gT('Search:','js')."';  
    var sSureDelete = '".gT('Are you sure you want to delete this URL parameter?','js')."';  
    var sEnterValidParam = '".gT('You have to enter a valid parameter name.','js')."';  
    var sAddParam = '".gT('Add URL parameter','js')."';  
    var sEditParam = '".gT('Edit URL parameter','js')."';  
    var iSurveyId = '".$surveyid."';  
    var questionArray = ".json_encode($questions).";  
    ", LSYii_ClientScript::POS_BEGIN ); 
?> 
