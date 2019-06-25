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
<?php $translate = array(
    'table' => array(
        'idColumn' => gT('ID'),
        'actionColumn' => gT('Action'),
        'parameterColumn' => gT('Parameter'),
        'questionColumn' => gT('Target question'),
        'sidColumn' => gT('Survey id'),
        'qidColumn' => gT('Question id'),
        'sqidColumn' => gT('Subquestion ID'),
        'addParameter' => gT('Add URL parameter'),
    ),
    'popup' => array(
        'editParam' => gT('Edit URL parameter'),
        'newParam' => gT('Add URL parameter'),
        'paramName' => gT('Parameter'),
        'targetQuestion' => gT('Target question'),
        'noTargetQuestion' => gT('(No target question)'),
        'sureToDelete' => gT('Are you sure you want to delete this URL parameter?'),
        'deleteCancel' => gT('No, cancel'),
        'deleteConfirm' => gT('Yes, delete'),
        'save' => gT('Save'),
        'cancel' => gT('Cancel'),
    ),
);
?>
  <!-- Datatable translation-data -->
  <!-- Container -->
  <lspanelparametertable :sid="<?php  echo $surveyid; ?>" json-url="<?php echo App()->createUrl('admin/survey/sa/getUrlParamsJson', array('surveyid' => $surveyid))?>" :translate="<?php echo CHTML::encode(json_encode($translate)); ?>"></lspanelparametertable>

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
